<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use App\Models\Result;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TeacherExamController extends Controller
{
    public function __construct(private readonly ImageService $imageService) {}

    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user->canManageExams(), 403);

        $q = trim((string) $request->query('q', ''));

        $query = Exam::query()->withCount('questions')->latest();
        
        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            $query->where('created_by', $user->id);
        }

        if ($q !== '') {
            $query->where('title', 'like', '%'.$q.'%');
        }

        $exams = $query->paginate(10)->withQueryString();

        return view('profile.exams.index', compact('exams'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        abort_unless($user->canManageExams(), 403);

        return view('profile.exams.create');
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_unless($user->canManageExams(), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'required_questions' => ['required', 'integer', 'min:1', 'max:500'],
            'total_points' => ['required', 'integer', 'min:1', 'max:10000'],
            'passing_points' => ['required', 'integer', 'min:1', 'max:10000'],
            'allowed_grades' => ['nullable', 'array'],
            'allowed_grades.*' => ['string', Rule::in(school_grade_options())],
        ]);

        $validated['allowed_grades'] = $this->normalizeAllowedGrades($request->input('allowed_grades', []));

        if ($validated['passing_points'] > $validated['total_points']) {
            throw ValidationException::withMessages([
                'passing_points' => 'O‘tish uchun minimal ball umumiy baldan katta bo‘lmasligi kerak.',
            ]);
        }

        $exam = Exam::query()->create([
            'title' => $validated['title'],
            'duration_minutes' => $validated['duration_minutes'],
            'required_questions' => $validated['required_questions'],
            'total_points' => $validated['total_points'],
            'passing_points' => $validated['passing_points'],
            'allowed_grades' => $validated['allowed_grades'],
            'is_active' => false,
            'created_by' => $user->id,
        ]);
        forget_public_exam_caches();

        return redirect()
            ->route('profile.exams.questions.index', $exam)
            ->with('success', "1-bosqich saqlandi. Endi {$exam->required_questions} ta savol qo'shing — barchasi to'lgach imtihon avtomatik faol bo'ladi.");
    }

    public function edit(Request $request, Exam $exam)
    {
        $user = $request->user();
        abort_unless($user->canManageExams() && $exam->ownsExam($user), 403);

        $exam->loadCount('questions');

        return view('profile.exams.edit', compact('exam'));
    }

    public function update(Request $request, Exam $exam)
    {
        $user = $request->user();
        abort_unless($user->canManageExams() && $exam->ownsExam($user), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'required_questions' => ['required', 'integer', 'min:1', 'max:500'],
            'total_points' => ['required', 'integer', 'min:1', 'max:10000'],
            'passing_points' => ['required', 'integer', 'min:1', 'max:10000'],
            'allowed_grades' => ['nullable', 'array'],
            'allowed_grades.*' => ['string', Rule::in(school_grade_options())],
        ]);

        $validated['allowed_grades'] = $this->normalizeAllowedGrades($request->input('allowed_grades', []));

        if ($validated['passing_points'] > $validated['total_points']) {
            throw ValidationException::withMessages([
                'passing_points' => 'O‘tish uchun minimal ball umumiy baldan katta bo‘lmasligi kerak.',
            ]);
        }

        $sumPts = $exam->sumQuestionPoints();
        if ($validated['total_points'] < $sumPts) {
            throw ValidationException::withMessages([
                'total_points' => "Umumiy bal kamida savollar ballari yig‘indisi ({$sumPts}) bo‘lishi kerak. Avval savollarni tahrirlang.",
            ]);
        }

        $exam->update([
            'title' => $validated['title'],
            'duration_minutes' => $validated['duration_minutes'],
            'required_questions' => $validated['required_questions'],
            'total_points' => $validated['total_points'],
            'passing_points' => $validated['passing_points'],
            'allowed_grades' => $validated['allowed_grades'],
        ]);

        $exam->syncActiveFromQuestions();
        forget_public_exam_caches();

        return redirect()->route('profile.exams.index')->with('success', 'Imtihon yangilandi.');
    }

    public function destroy(Request $request, Exam $exam)
    {
        $user = $request->user();
        abort_unless($user->canManageExams() && $exam->ownsExam($user), 403);

        $imagePaths = $exam->questions()
            ->whereNotNull('image_path')
            ->pluck('image_path')
            ->filter()
            ->all();

        $exam->delete();
        forget_public_exam_caches();

        foreach ($imagePaths as $imagePath) {
            $this->imageService->deleteImage($imagePath);
        }

        return back()->with('success', "Imtihon o'chirildi.");
    }

    // --- Questions ---

    public function questionsIndex(Request $request, Exam $exam)
    {
        $user = $request->user();
        abort_unless($user->canManageExams() && $exam->ownsExam($user), 403);

        $q = trim((string) $request->query('q', ''));

        $questionsQuery = $exam->questions()
            ->with('options')
            ->orderBy('sort_order')
            ->orderBy('id');

        if ($q !== '') {
            $questionsQuery->where('body', 'like', '%'.$q.'%');
        }

        $questions = $questionsQuery->paginate(10)->withQueryString();
        $totalQuestionCount = (int) $exam->questions()->count();
        $pointsSum = (int) $exam->questions()->sum('points');

        return view('profile.exams.questions.index', compact('exam', 'questions', 'pointsSum', 'totalQuestionCount'));
    }

    public function questionCreate(Request $request, Exam $exam)
    {
        $user = $request->user();
        abort_unless($user->canManageExams() && $exam->ownsExam($user), 403);

        if ($exam->isQuestionQuotaFilled()) {
            return redirect()
                ->route('profile.exams.questions.index', $exam)
                ->with('error', "Reja bo'yicha savollar soni to'ldi.");
        }

        return view('profile.exams.questions.create', compact('exam'));
    }

    public function questionStore(Request $request, Exam $exam)
    {
        $user = $request->user();
        abort_unless($user->canManageExams() && $exam->ownsExam($user), 403);

        if ($exam->isQuestionQuotaFilled()) {
            return redirect()
                ->route('profile.exams.questions.index', $exam)
                ->with('error', "Reja bo'yicha savollar soni allaqachon to'ldi.");
        }

        $validated = $this->validateQuestion($request);
        $this->assertQuestionPointsWithinBudget($exam, (int) $validated['points'], null);

        $uploadedImagePath = null;
        $isTextType = $validated['question_type'] === Question::TYPE_TEXT;

        try {
            if ($request->hasFile('question_image')) {
                $uploadedImagePath = $this->imageService->uploadAndOptimize(
                    $request->file('question_image'),
                    'exam-questions',
                    1600,
                    1200
                );
            }

            DB::transaction(function () use ($exam, $validated, $uploadedImagePath, $isTextType): void {
                $question = Question::query()->create([
                    'exam_id' => $exam->id,
                    'body' => sanitize_exam_rich_text($validated['body']),
                    'image_path' => $uploadedImagePath,
                    'sort_order' => $this->nextSortOrder($exam),
                    'points' => (int) $validated['points'],
                    'question_type' => $validated['question_type'],
                    'model_answer' => $isTextType ? $validated['model_answer'] : null,
                ]);

                if (!$isTextType) {
                    foreach (['A', 'B', 'C', 'D'] as $label) {
                        Option::query()->create([
                            'question_id' => $question->id,
                            'label' => $label,
                            'body' => sanitize_exam_rich_text($validated['options'][$label]),
                            'is_correct' => $validated['correct_label'] === $label,
                        ]);
                    }
                }
            });
        } catch (\Throwable $exception) {
            if ($uploadedImagePath) {
                $this->imageService->deleteImage($uploadedImagePath);
            }
            throw $exception;
        }

        $exam->syncActiveFromQuestions();
        forget_public_exam_caches();

        return redirect()->route('profile.exams.questions.index', $exam)
            ->with('success', "Savol qo'shildi.");
    }

    public function questionEdit(Request $request, Exam $exam, Question $question)
    {
        $user = $request->user();
        abort_unless($user->canManageExams() && $exam->ownsExam($user), 403);
        abort_unless((int) $question->exam_id === (int) $exam->id, 404);

        $question->load('options');

        return view('profile.exams.questions.edit', compact('exam', 'question'));
    }

    public function questionUpdate(Request $request, Exam $exam, Question $question)
    {
        $user = $request->user();
        abort_unless($user->canManageExams() && $exam->ownsExam($user), 403);
        abort_unless((int) $question->exam_id === (int) $exam->id, 404);

        $validated = $this->validateQuestion($request);
        $this->assertQuestionPointsWithinBudget($exam, (int) $validated['points'], $question->id);

        $oldImagePath = $question->image_path;
        $nextImagePath = $oldImagePath;
        $uploadedImagePath = null;
        $isTextType = $validated['question_type'] === Question::TYPE_TEXT;

        try {
            if ($request->hasFile('question_image')) {
                $uploadedImagePath = $this->imageService->uploadAndOptimize(
                    $request->file('question_image'),
                    'exam-questions',
                    1600,
                    1200
                );
                $nextImagePath = $uploadedImagePath;
            } elseif ($request->boolean('remove_question_image')) {
                $nextImagePath = null;
            }

            DB::transaction(function () use ($question, $validated, $nextImagePath, $isTextType): void {
                $question->update([
                    'body' => sanitize_exam_rich_text($validated['body']),
                    'image_path' => $nextImagePath,
                    'points' => (int) $validated['points'],
                    'question_type' => $validated['question_type'],
                    'model_answer' => $isTextType ? $validated['model_answer'] : null,
                ]);

                if ($isTextType) {
                    $question->options()->delete();
                } else {
                    foreach (['A', 'B', 'C', 'D'] as $label) {
                        $question->options()->updateOrCreate(
                            ['label' => $label],
                            [
                                'body' => sanitize_exam_rich_text($validated['options'][$label]),
                                'is_correct' => $validated['correct_label'] === $label,
                            ]
                        );
                    }
                }
            });
        } catch (\Throwable $exception) {
            if ($uploadedImagePath) {
                $this->imageService->deleteImage($uploadedImagePath);
            }
            throw $exception;
        }

        if ($oldImagePath && $oldImagePath !== $nextImagePath) {
            $this->imageService->deleteImage($oldImagePath);
        }

        $exam->syncActiveFromQuestions();
        forget_public_exam_caches();

        return redirect()->route('profile.exams.questions.index', $exam)
            ->with('success', 'Savol yangilandi.');
    }

    public function questionDestroy(Request $request, Exam $exam, Question $question)
    {
        $user = $request->user();
        abort_unless($user->canManageExams() && $exam->ownsExam($user), 403);
        abort_unless((int) $question->exam_id === (int) $exam->id, 404);

        $imagePath = $question->image_path;
        $question->delete();

        if ($imagePath) {
            $this->imageService->deleteImage($imagePath);
        }

        $exam->syncActiveFromQuestions();
        forget_public_exam_caches();

        return back()->with('success', "Savol o'chirildi.");
    }

    // --- Results ---
    public function results(Request $request)
    {
        $user = $request->user();
        abort_unless($user->canManageExams(), 403);

        $exams = Exam::query();
        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            $exams->where('created_by', $user->id);
        }
        $exams = $exams->orderBy('title')->get(['id', 'title']);

        $examId = $request->query('exam_id');
        $selectedExamId = $examId !== null && $examId !== '' ? (int) $examId : null;

        if ($selectedExamId && ! Exam::query()->whereKey($selectedExamId)->exists()) {
            $selectedExamId = null;
        }

        $query = Result::query()->with(['exam', 'user']);

        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            $query->whereHas('exam', function($q) use ($user) {
                $q->where('created_by', $user->id);
            });
        }

        if ($selectedExamId) {
            $query->where('exam_id', $selectedExamId);
        }

        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $query->where(function ($w) use ($q): void {
                $w->whereHas('user', function ($u) use ($q): void {
                    $u->where('name', 'like', '%'.$q.'%')
                        ->orWhere('email', 'like', '%'.$q.'%')
                        ->orWhere('phone', 'like', '%'.$q.'%');
                });
            });
        }

        $status = $request->query('status');
        if ($status && in_array($status, ['started', 'submitted', 'expired'])) {
            $query->where('status', $status);
        }

        $results = $query
            ->latest('id')
            ->paginate(40)
            ->withQueryString();

        return view('profile.exams.results.index', compact('results', 'exams', 'selectedExamId'));
    }

    public function showResult(Request $request, Result $result)
    {
        $user = $request->user();
        $exam = $result->exam;
        abort_unless($user->canManageExams() && $exam->ownsExam($user), 403);

        $result->load(['user', 'answers.question.options', 'answers.option']);
        return view('profile.exams.results_show', compact('exam', 'result'));
    }

    public function gradeTextAnswer(Request $request, Result $result, Answer $answer)
    {
        $user = $request->user();
        $exam = $result->exam;
        abort_unless($user->canManageExams() && $exam->ownsExam($user), 403);
        abort_unless((int) $answer->result_id === (int) $result->id, 404);

        $validated = $request->validate([
            'is_correct' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($result, $answer, $validated) {
            $answer->update([
                'is_correct_override' => $validated['is_correct'],
            ]);

            // Recalculate result score manually
            $answers = Answer::query()
                ->where('result_id', $result->id)
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
                }

                if ($a->isCorrectAnswer()) {
                    $correctCount++;
                    $pointsEarned += (int) ($a->question?->points ?? 0);
                }
            }

            $examModel = Exam::query()->find($result->exam_id);
            $passing = (int) ($examModel?->passing_points ?? 0);
            $passed = $hasPendingManualReview
                ? null
                : ($passing > 0 ? $pointsEarned >= $passing : true);

            $result->update([
                'score' => $correctCount,
                'points_earned' => $pointsEarned,
                'passed' => $passed,
            ]);
        });

        return back()->with('success', 'Baholandi va natija qayta hisoblandi.');
    }

    private function validateQuestion(Request $request): array
    {
        $isTextType = $request->input('question_type') === Question::TYPE_TEXT;

        $rules = [
            'question_type' => ['required', Rule::in([Question::TYPE_MCQ, Question::TYPE_TEXT])],
            'body' => ['required', 'string', 'max:12000'],
            'question_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_question_image' => ['nullable', 'boolean'],
            'points' => ['required', 'integer', 'min:1', 'max:1000'],
        ];

        if ($isTextType) {
            $rules['model_answer'] = ['nullable', 'string', 'max:12000'];
        } else {
            $rules['correct_label'] = ['required', 'in:A,B,C,D'];
            $rules['options.A'] = ['required', 'string', 'max:4000'];
            $rules['options.B'] = ['required', 'string', 'max:4000'];
            $rules['options.C'] = ['required', 'string', 'max:4000'];
            $rules['options.D'] = ['required', 'string', 'max:4000'];
        }

        return $request->validate($rules);
    }

    private function normalizeAllowedGrades(array $grades): array
    {
        $normalized = normalize_school_grade_list($grades);

        return count($normalized) === count(school_grade_options())
            ? []
            : $normalized;
    }

    private function nextSortOrder(Exam $exam): int
    {
        return (int) $exam->questions()->max('sort_order') + 1;
    }

    private function assertQuestionPointsWithinBudget(Exam $exam, int $questionPoints, ?int $exceptQuestionId): void
    {
        $query = $exam->questions();
        if ($exceptQuestionId !== null) {
            $query->where('id', '!=', $exceptQuestionId);
        }
        $sum = (int) $query->sum('points');
        $total = (int) $exam->total_points;

        if ($sum + $questionPoints > $total) {
            $over = $sum + $questionPoints - $total;
            throw ValidationException::withMessages([
                'points' => "Savollar ballari yig‘indisi imtihon umumiy balidan ({$total}) {$over} ball oshib ketdi. Shu savol bilan yig‘indi: ".($sum + $questionPoints).".",
            ]);
        }
    }
}
