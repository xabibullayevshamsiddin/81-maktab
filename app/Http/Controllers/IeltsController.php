<?php

namespace App\Http\Controllers;

use App\Models\IeltsAnswer;
use App\Models\IeltsAttempt;
use App\Models\IeltsResult;
use App\Models\IeltsTest;
use App\Services\Ai\IeltsGradingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class IeltsController extends Controller
{
    /**
     * Landing page: explains the test and shows the "Start test" button.
     * Mirrors the 5-min cache pattern used for the active exam list.
     */
    public function index()
    {
        $tests = Cache::remember('ielts_active_tests', 300, function () {
            return IeltsTest::where('is_active', true)->get();
        });

        $user = Auth::user();

        $rank = $user?->donation_rank;
        $monthlyLimit = match ($rank) {
            'vip', 'premium' => null, // cheksiz
            'supporter' => 3,
            default => 1, // donor emas
        };

        $usedThisMonth = IeltsAttempt::where('user_id', $user->id)
            ->whereMonth('created_at', now()->month)
            ->count();

        return view('ielts.index', compact('tests', 'monthlyLimit', 'usedThisMonth', 'rank'));
    }

    /**
     * Starts (or resumes) an attempt. Shuffles question order once and stores
     * the snapshot on the attempt row — same idea as ExamController's shuffle-per-result.
     */
    public function start(Request $request, IeltsTest $ieltsTest)
    {
        $user = $request->user();

        $rank = $user?->donation_rank;
        $monthlyLimit = match ($rank) {
            'vip', 'premium' => null, // cheksiz
            'supporter' => 3,
            default => 1, // donor emas
        };

        return DB::transaction(function () use ($user, $ieltsTest, $monthlyLimit) {
            $existing = IeltsAttempt::where('user_id', $user->id)
                ->where('ielts_test_id', $ieltsTest->id)
                ->where('status', 'in_progress')
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return redirect()->route('ielts.take', $existing);
            }

            if ($monthlyLimit !== null) {
                $usedThisMonth = IeltsAttempt::where('user_id', $user->id)
                    ->whereMonth('created_at', now()->month)
                    ->count();

                if ($usedThisMonth >= $monthlyLimit) {
                    return redirect()->route('ielts.index')
                        ->with('error', 'Bu oy uchun urinishlar limitiga yetdingiz.');
                }
            }

            $questionIds = $ieltsTest->sections()
                ->with('passages.questions')
                ->get()
                ->flatMap(fn ($section) => $section->passages->flatMap(fn ($p) => $p->questions->pluck('id')))
                ->shuffle()
                ->values();

            $attempt = IeltsAttempt::create([
                'user_id' => $user->id,
                'ielts_test_id' => $ieltsTest->id,
                'question_order' => $questionIds,
                'started_at' => now(),
                'status' => 'in_progress',
            ]);

            return redirect()->route('ielts.take', $attempt);
        });
    }

    public function take(IeltsAttempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);

        $attempt->load('test.sections.passages.questions');

        return view('ielts.test', compact('attempt'));
    }

    /**
     * Saves one answer at a time (autosave-friendly) and flags rule violations,
     * following the same 5-violation disqualification threshold as ExamController.
     */
    public function saveAnswer(Request $request, IeltsAttempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);

        $data = $request->validate([
            'ielts_question_id' => 'required|exists:ielts_questions,id',
            'answer_text' => 'nullable|string',
            'rule_violation' => 'boolean',
        ]);

        if (! empty($data['rule_violation'])) {
            $attempt->increment('rule_violation_count');

            if ($attempt->rule_violation_count >= 5) {
                $attempt->update(['status' => 'submitted', 'submitted_at' => now()]);

                return response()->json(['disqualified' => true]);
            }
        }

        IeltsAnswer::updateOrCreate(
            ['ielts_attempt_id' => $attempt->id, 'ielts_question_id' => $data['ielts_question_id']],
            ['answer_text' => $data['answer_text'] ?? null]
        );

        return response()->json(['saved' => true, 'violations' => $attempt->rule_violation_count]);
    }

    /**
     * Finalizes the attempt: auto-grades objective questions, sends Writing
     * answers to AI grading, computes an approximate overall band, and stores the result.
     */
    public function submit(IeltsAttempt $attempt, IeltsGradingService $grading)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);

        $user = $attempt->user;
        $detailedFeedback = $user ? $user->isDonor() : false; // har qanday donor daraja batafsil fikr-mulohaza oladi

        $attempt->load('answers.question.passage.section');
        $sectionScores = [];

        foreach (['reading', 'listening', 'writing', 'speaking'] as $skill) {
            $skillAnswers = $attempt->answers->filter(
                fn ($a) => $a->question->passage->section->skill === $skill
            );

            if ($skillAnswers->isEmpty()) {
                continue;
            }

            if (in_array($skill, ['reading', 'listening'])) {
                $sectionScores[$skill] = $this->scoreObjectiveSection($skillAnswers);
            } elseif ($skill === 'writing') {
                $sectionScores[$skill] = $this->scoreWritingSection($skillAnswers, $grading, $detailedFeedback);
            } else {
                // Speaking: recorded but not auto-graded yet (see README "Next steps").
                $sectionScores[$skill] = null;
            }
        }

        $numericBands = array_filter($sectionScores, fn ($b) => $b !== null);
        $overallBand = $numericBands ? round((array_sum($numericBands) / count($numericBands)) * 2) / 2 : null;

        $result = IeltsResult::create([
            'ielts_attempt_id' => $attempt->id,
            'section_bands' => $sectionScores,
            'overall_band' => $overallBand,
            'level_label' => $this->bandToLevelLabel($overallBand),
        ]);

        $attempt->update(['status' => 'graded', 'submitted_at' => $attempt->submitted_at ?? now()]);

        return redirect()->route('ielts.result', $result);
    }

    public function result(IeltsResult $result)
    {
        $result->load('attempt.user');
        abort_unless($result->attempt->user_id === auth()->id(), 403);

        return view('ielts.result', compact('result'));
    }

    protected function scoreObjectiveSection($answers): float
    {
        $total = $answers->count();
        $correct = $answers->filter(fn ($a) => $a->question->correct_answer !== null
            && trim(mb_strtolower($a->answer_text ?? '')) === trim(mb_strtolower($a->question->correct_answer))
        )->count();

        foreach ($answers as $answer) {
            $answer->update([
                'is_correct' => $answer->question->correct_answer !== null
                    && trim(mb_strtolower($answer->answer_text ?? '')) === trim(mb_strtolower($answer->question->correct_answer)),
            ]);
        }

        return $this->rawScoreToApproximateBand($correct, $total);
    }

    protected function scoreWritingSection($answers, IeltsGradingService $grading, bool $detailed): ?float
    {
        $bands = [];

        foreach ($answers as $answer) {
            $feedback = $grading->gradeWriting(
                $answer->question->question_text,
                $answer->answer_text ?? '',
                $detailed
            );

            $answer->update(['ai_feedback' => $feedback]);

            if (! empty($feedback['overall_band'])) {
                $bands[] = (float) $feedback['overall_band'];
            }
        }

        return $bands ? round((array_sum($bands) / count($bands)) * 2) / 2 : null;
    }

    /**
     * Approximate raw-score-to-band conversion (public-source based, NOT official IELTS data).
     * Always show the disclaimer in the UI — see README.
     */
    protected function rawScoreToApproximateBand(int $correct, int $total): float
    {
        if ($total === 0) {
            return 0;
        }

        $percentage = $correct / $total;

        return match (true) {
            $percentage >= 0.90 => 8.5,
            $percentage >= 0.80 => 7.5,
            $percentage >= 0.70 => 6.5,
            $percentage >= 0.60 => 6.0,
            $percentage >= 0.50 => 5.5,
            $percentage >= 0.40 => 5.0,
            $percentage >= 0.30 => 4.5,
            default => 4.0,
        };
    }

    protected function bandToLevelLabel(?float $band): ?string
    {
        if ($band === null) {
            return null;
        }

        return match (true) {
            $band >= 8.0 => 'Expert / Very good user (C1-C2)',
            $band >= 7.0 => 'Good user (B2-C1)',
            $band >= 6.0 => 'Competent user (B2)',
            $band >= 5.0 => 'Modest user (B1)',
            $band >= 4.0 => 'Limited user (A2-B1)',
            default => 'Extremely limited user (A1-A2)',
        };
    }
}
