<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use App\Models\Result;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class ExamController extends Controller
{
    /** Shu miqdorga yetsa qoidabuzarlik: avtomatik 0 ball, yiqildi. */
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

        $isParent = $user->isParent();

        return view('exam.index', compact('exams', 'resultByExam', 'user', 'hasRestrictedExams', 'isParent'));
    }

    public function startPage(Request $request, Exam $exam)
    {
        if ($request->user()->isParent()) {
            return redirect()->route('exam.index')
                ->with('error', 'Ota-onalar imtihon topshira olmaydi.')
                ->with('toast_type', 'error');
        }

        abort_unless($exam->is_active, 404);

        $existing = Result::query()
            ->where('exam_id', $exam->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $exam->allowsUser($request->user()) && ! $existing) {
            return redirect()
                ->route('exam.index')
                ->with('error', "Bu imtihon sizning sinfingiz uchun emas.")
                ->with('toast_type', 'error');
        }

        return view('exam.start', [
            'exam' => $exam,
            'existing' => $existing,
            'canStartNow' => $exam->isOpenForStarting(),
        ]);
    }

    public function start(Request $request, Exam $exam)
    {
        if ($request->user()->isParent()) {
            return redirect()->route('exam.index')
                ->with('error', 'Ota-onalar imtihon topshira olmaydi.')
                ->with('toast_type', 'error');
        }

        $existing = Result::query()
            ->where('exam_id', $exam->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($redirect = $this->ensureExamAvailable($exam, $existing)) {
            return $redirect;
        }

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

        try {
            $result = Result::query()->create([
                'exam_id' => $exam->id,
                'user_id' => $request->user()->id,
                'user_grade' => $request->user()->grade,
                'question_order_json' => $questionIds,
                'total_questions' => count($questionIds),
                'points_max' => (int) $exam->total_points,
                'started_at' => $startedAt,
                'expires_at' => $expiresAt,
                'status' => 'started',
            ]);
        } catch (QueryException $e) {
            $concurrentResult = Result::query()
                ->where('exam_id', $exam->id)
                ->where('user_id', $request->user()->id)
                ->first();

            if ($concurrentResult) {
                if ($concurrentResult->status === 'submitted' || $concurrentResult->status === 'expired') {
                    return redirect()->route('exam.result.show', $concurrentResult);
                }

                return redirect()->route('exam.session', $concurrentResult);
            }

            throw $e;
        }

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
            && (int) $result->rule_violation_count >= self::RULE_VIOLATION_DISQUALIFY_THRESHOLD
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
            ->get(['question_id', 'option_id', 'text_answer'])
            ->keyBy('question_id');

        $result->load('exam');

        return response()
            ->view('exam.session', compact('result', 'orderedQuestions', 'answerMap'))
            ->header('Cache-Control', 'private, no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache');
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
            'option_id' => ['nullable', 'integer'],
            'text_answer' => ['nullable', 'string', 'max:12000'],
        ]);

        $questionId = (int) $validated['question_id'];

        $order = collect($result->question_order_json ?? [])->map(fn ($id) => (int) $id);
        abort_unless($order->contains($questionId), 403);

        $question = Question::query()->findOrFail($questionId);

        if ($question->isTextType()) {
            $textAnswer = trim((string) ($validated['text_answer'] ?? ''));

            if ($textAnswer === '') {
                Answer::query()
                    ->where('result_id', $result->id)
                    ->where('question_id', $questionId)
                    ->delete();

                return response()->json(['ok' => true, 'answered' => false]);
            }

            Answer::query()->updateOrCreate(
                [
                    'result_id' => $result->id,
                    'question_id' => $questionId,
                ],
                [
                    'option_id' => null,
                    'text_answer' => $textAnswer,
                    'is_correct_override' => null,
                    'answered_at' => now(),
                ]
            );

            return response()->json(['ok' => true, 'answered' => true]);
        }

        $optionId = (int) ($validated['option_id'] ?? 0);
        abort_if($optionId <= 0, 422, "Variant tanlanmadi.");

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
                'text_answer' => null,
                'is_correct_override' => null,
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

            if ($count >= self::RULE_VIOLATION_DISQUALIFY_THRESHOLD) {
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
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'ok' => true,
                    'redirect' => route('exam.result.show', $result),
                    'passed' => (isset($result->passed) ? (bool)$result->passed : null),
                    'score_raw' => $result->points_earned
                ]);
            }
            return redirect()->route('exam.result.show', $result);
        }

        $this->finalizeResult($result, $this->isExpired($result));
        $fresh = $result->fresh();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'redirect' => route('exam.result.show', $fresh),
                'passed' => (isset($fresh->passed) ? (bool)$fresh->passed : null),
                'score_raw' => $fresh->points_earned
            ]);
        }

        // 303: POST → GET; brauzer tarixida imtihon sessiyasiga "qayta yuborish" chalkashmasin
        return redirect()->route('exam.result.show', $fresh, 303)
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

            if ((int) $row->rule_violation_count >= self::RULE_VIOLATION_DISQUALIFY_THRESHOLD) {
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
                ->with(['option:id,is_correct', 'question:id,points,question_type'])
                ->get();

            $correctCount = 0;
            $pointsEarned = 0;
            $hasPendingManualReview = false;
            foreach ($answers as $a) {
                if ($a->question?->isTextType()) {
                    if ($a->is_correct_override === null && filled($a->text_answer)) {
                        $hasPendingManualReview = true;
                        continue;
                    }

                    if ($a->isCorrectAnswer()) {
                        $correctCount++;
                        $pointsEarned += (int) ($a->question?->points ?? 0);
                    }

                    continue;
                }

                if ($a->option?->is_correct) {
                    $correctCount++;
                    $pointsEarned += (int) ($a->question?->points ?? 0);
                }
            }

            $passing = (int) ($exam?->passing_points ?? 0);
            $passed = $hasPendingManualReview
                ? null
                : ($passing > 0 ? $pointsEarned >= $passing : true);

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

    /**
     * @return RedirectResponse|null
     */
    private function ensureExamAvailable(Exam $exam, ?Result $existing = null)
    {
        abort_unless($exam->is_active, 404);

        if ($existing && $existing->status === 'started') {
            return null;
        }

        if (! $exam->isOpenForStarting()) {
            $dateLabel = $exam->availableFromLabel() ?? '';

            return redirect()
                ->route('exam.index')
                ->with('error', $dateLabel !== ''
                    ? "Bu imtihonni {$dateLabel} dan boshlash mumkin (shu kundan)."
                    : 'Bu imtihon hozircha boshlash uchun ochilmagan.')
                ->with('toast_type', 'error');
        }

        return null;
    }

    private function authorizeResult(Request $request, Result $result): void
    {
        abort_unless((int) $result->user_id === (int) $request->user()->id, 403);
    }
}
