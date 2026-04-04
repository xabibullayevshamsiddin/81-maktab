<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use App\Models\Result;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    /** Shundan ortiq qoidabuzarlik: avtomatik 0 ball, yiqildi. */
    private const RULE_VIOLATION_DISQUALIFY_THRESHOLD = 5;

    public function index(Request $request)
    {
        $user = $request->user()->loadMissing('roleRelation');

        $exams = Exam::query()
            ->where('is_active', true)
            ->latest('id')
            ->get();

        $resultByExam = Result::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('exam_id');

        $hasRestrictedExams = $exams->contains(
            fn (Exam $exam) => ! $exam->allowsUser($user) && ! $resultByExam->has($exam->id)
        );

        return view('exam.index', compact('exams', 'resultByExam', 'user', 'hasRestrictedExams'));
    }

    public function startPage(Request $request, Exam $exam)
    {
        $existing = Result::query()
            ->where('exam_id', $exam->id)
            ->where('user_id', $request->user()->id)
            ->first();

        $this->ensureExamAvailable($exam);

        if (! $exam->allowsUser($request->user()) && ! $existing) {
            return redirect()
                ->route('exam.index')
                ->with('error', "Bu imtihon sizning sinfingiz uchun emas.")
                ->with('toast_type', 'error');
        }

        return view('exam.start', compact('exam', 'existing'));
    }

    public function start(Request $request, Exam $exam)
    {
        $existing = Result::query()
            ->where('exam_id', $exam->id)
            ->where('user_id', $request->user()->id)
            ->first();

        $this->ensureExamAvailable($exam);

        if ($existing) {
            if ($existing->status === 'submitted' || $existing->status === 'expired') {
                return redirect()->route('exam.result.show', $existing);
            }

            return redirect()->route('exam.session', $existing);
        }

        if (! $exam->allowsUser($request->user())) {
            return redirect()
                ->route('exam.index')
                ->with('error', "Bu imtihon sizning sinfingiz uchun emas.")
                ->with('toast_type', 'error');
        }

        $questionIds = $exam->questions()->pluck('id')->shuffle()->values()->all();
        abort_if(empty($questionIds), 422, "Bu imtihonda savollar yo'q.");

        $startedAt = now();
        $expiresAt = (clone $startedAt)->addMinutes((int) $exam->duration_minutes);

        $result = Result::query()->create([
            'exam_id' => $exam->id,
            'user_id' => $request->user()->id,
            'question_order_json' => $questionIds,
            'total_questions' => count($questionIds),
            'points_max' => (int) $exam->total_points,
            'started_at' => $startedAt,
            'expires_at' => $expiresAt,
            'status' => 'started',
        ]);

        return redirect()->route('exam.session', $result);
    }

    public function session(Request $request, Result $result)
    {
        $this->authorizeResult($request, $result);

        if ($result->status !== 'started') {
            return redirect()->route('exam.result.show', $result);
        }

        if ($this->isExpired($result)) {
            $this->finalizeResult($result, true);

            return redirect()->route('exam.result.show', $result);
        }

        $result->refresh();
        if (
            $result->status === 'started'
            && (int) $result->rule_violation_count > self::RULE_VIOLATION_DISQUALIFY_THRESHOLD
        ) {
            $this->finalizeResult($result, false);

            return redirect()->route('exam.result.show', $result->fresh());
        }

        $questionIds = collect($result->question_order_json ?? [])->map(fn ($id) => (int) $id)->all();
        abort_if(empty($questionIds), 422, "Savollar tartibi topilmadi.");

        $questions = Question::query()
            ->whereIn('id', $questionIds)
            ->with(['options' => fn ($q) => $q->orderBy('label')])
            ->get()
            ->keyBy('id');

        $orderedQuestions = collect($questionIds)
            ->map(fn ($id) => $questions->get($id))
            ->filter()
            ->values();

        $answerMap = Answer::query()
            ->where('result_id', $result->id)
            ->pluck('option_id', 'question_id');

        $result->load('exam');

        return view('exam.session', compact('result', 'orderedQuestions', 'answerMap'));
    }

    public function answer(Request $request, Result $result)
    {
        $this->authorizeResult($request, $result);

        if ($result->status !== 'started' || $this->isExpired($result)) {
            $this->finalizeResult($result, true);

            return response()->json(['ok' => false, 'message' => "Imtihon vaqti tugagan."], 422);
        }

        $validated = $request->validate([
            'question_id' => ['required', 'integer'],
            'option_id' => ['required', 'integer'],
        ]);

        $questionId = (int) $validated['question_id'];
        $optionId = (int) $validated['option_id'];

        $order = collect($result->question_order_json ?? [])->map(fn ($id) => (int) $id);
        abort_unless($order->contains($questionId), 403);

        $option = Option::query()
            ->where('id', $optionId)
            ->where('question_id', $questionId)
            ->firstOrFail();

        Answer::query()->updateOrCreate(
            [
                'result_id' => $result->id,
                'question_id' => $questionId,
            ],
            [
                'option_id' => $option->id,
                'answered_at' => now(),
            ]
        );

        return response()->json(['ok' => true]);
    }

    public function reportViolation(Request $request, Result $result): JsonResponse
    {
        $this->authorizeResult($request, $result);

        if ($result->status !== 'started') {
            return response()->json([
                'disqualified' => false,
                'redirect' => route('exam.result.show', $result),
            ]);
        }

        if ($this->isExpired($result)) {
            $this->finalizeResult($result, true);

            return response()->json([
                'disqualified' => false,
                'redirect' => route('exam.result.show', $result->fresh()),
            ]);
        }

        return DB::transaction(function () use ($result): JsonResponse {
            $row = Result::query()->whereKey($result->id)->lockForUpdate()->firstOrFail();

            if ($row->status !== 'started') {
                return response()->json([
                    'disqualified' => false,
                    'redirect' => route('exam.result.show', $row),
                ]);
            }

            if ($this->isExpired($row)) {
                $this->finalizeResult($row, true);

                return response()->json([
                    'disqualified' => false,
                    'redirect' => route('exam.result.show', $row->fresh()),
                ]);
            }

            $row->increment('rule_violation_count');
            $row->refresh();

            $count = (int) $row->rule_violation_count;

            if ($count > self::RULE_VIOLATION_DISQUALIFY_THRESHOLD) {
                $this->finalizeResult($row, false);

                return response()->json([
                    'disqualified' => true,
                    'count' => $count,
                    'redirect' => route('exam.result.show', $row->fresh()),
                ]);
            }

            return response()->json([
                'disqualified' => false,
                'count' => $count,
            ]);
        });
    }

    public function submit(Request $request, Result $result)
    {
        $this->authorizeResult($request, $result);

        if ($result->status !== 'started') {
            return redirect()->route('exam.result.show', $result);
        }

        $this->finalizeResult($result, $this->isExpired($result));

        return redirect()->route('exam.result.show', $result)
            ->with('success', 'Imtihon yakunlandi.');
    }

    private function finalizeResult(Result $result, bool $expired): void
    {
        if ($result->status !== 'started') {
            return;
        }

        DB::transaction(function () use ($result, $expired): void {
            $row = Result::query()->whereKey($result->id)->lockForUpdate()->firstOrFail();

            if ($row->status !== 'started') {
                return;
            }

            $exam = Exam::query()->find($row->exam_id);
            $maxPoints = (int) ($exam?->total_points ?? $row->points_max ?? 0);

            if ((int) $row->rule_violation_count > self::RULE_VIOLATION_DISQUALIFY_THRESHOLD) {
                $row->update([
                    'score' => 0,
                    'points_earned' => 0,
                    'points_max' => $maxPoints > 0 ? $maxPoints : null,
                    'passed' => false,
                    'submitted_at' => now(),
                    'status' => $expired ? 'expired' : 'submitted',
                ]);

                return;
            }

            $answers = Answer::query()
                ->where('result_id', $row->id)
                ->whereNotNull('option_id')
                ->with(['option:id,is_correct', 'question:id,points'])
                ->get();

            $correctCount = 0;
            $pointsEarned = 0;
            foreach ($answers as $a) {
                if ($a->option?->is_correct) {
                    $correctCount++;
                    $pointsEarned += (int) ($a->question?->points ?? 0);
                }
            }

            $passing = (int) ($exam?->passing_points ?? 0);
            $passed = $passing > 0 ? $pointsEarned >= $passing : true;

            $row->update([
                'score' => $correctCount,
                'points_earned' => $pointsEarned,
                'points_max' => $maxPoints > 0 ? $maxPoints : null,
                'passed' => $passed,
                'submitted_at' => now(),
                'status' => $expired ? 'expired' : 'submitted',
            ]);
        });
    }

    private function isExpired(Result $result): bool
    {
        return now()->greaterThan($result->expires_at);
    }

    private function ensureExamAvailable(Exam $exam): void
    {
        abort_unless($exam->is_active, 404);
    }

    private function authorizeResult(Request $request, Result $result): void
    {
        abort_unless((int) $result->user_id === (int) $request->user()->id, 403);
    }
}
