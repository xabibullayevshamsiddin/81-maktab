<?php

namespace App\Http\Controllers;

use App\Actions\Exams\GradeTextAnswerAction;
use App\Actions\Exams\StoreExamAction;
use App\Actions\Exams\UpdateExamAction;
use App\DataTransferObjects\Exams\ExamData;
use App\Exceptions\ExamAccessDeniedException;
use App\Exceptions\ExamResourceMismatchException;
use App\Http\Requests\Exams\GradeTextAnswerRequest;
use App\Http\Requests\Exams\SaveExamRequest;
use App\Models\Answer;
use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use App\Models\Result;
use App\Services\ImageService;
use App\Traits\AuthorizesExamManagement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TeacherExamController extends Controller
{
    use AuthorizesExamManagement;

    public function __construct(
        private readonly ImageService $imageService,
        private readonly StoreExamAction $storeExamAction,
        private readonly UpdateExamAction $updateExamAction,
        private readonly GradeTextAnswerAction $gradeTextAnswerAction,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $this->ensureUserCanManageExams($user);

        $q = trim((string) $request->query('q', ''));

        $query = Exam::query()->withCount('questions')->latest();

        if (! $user->isAdmin() && ! $user->isSuperAdmin()) {
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
        $this->ensureUserCanManageExams($user);

        return view('profile.exams.create');
    }

    public function store(SaveExamRequest $request): RedirectResponse
    {
        $user = $request->user();
        $this->ensureUserCanManageExams($user);

        $exam = $this->storeExamAction->handle(
            ExamData::fromRequest($request),
            (int) $user->id
        );

        return redirect()
            ->route('profile.exams.questions.index', $exam)
            ->with('success', "1-bosqich saqlandi. Endi {$exam->required_questions} ta savol qo'shing - barchasi to'lgach imtihon avtomatik faol bo'ladi.");
    }

    public function edit(Request $request, Exam $exam)
    {
        $user = $request->user();
        $this->ensureUserOwnsExam($user, $exam);

        $exam->loadCount('questions');

        return view('profile.exams.edit', compact('exam'));
    }

    public function update(SaveExamRequest $request, Exam $exam): RedirectResponse
    {
        $user = $request->user();
        $this->ensureUserOwnsExam($user, $exam);
        $request->ensureQuestionPointsBudget($exam);

        $this->updateExamAction->handle($exam, ExamData::fromRequest($request));

        return redirect()->route('profile.exams.index')->with('success', 'Imtihon yangilandi.');
    }

    public function destroy(Request $request, Exam $exam): RedirectResponse
    {
        $user = $request->user();
        $this->ensureUserOwnsExam($user, $exam);

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

    public function questionsIndex(Request $request, Exam $exam)
    {
        $user = $request->user();
        $this->ensureUserOwnsExam($user, $exam);

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
        $this->ensureUserOwnsExam($user, $exam);

        if ($exam->isQuestionQuotaFilled()) {
            return redirect()
                ->route('profile.exams.questions.index', $exam)
                ->with('error', "Reja bo'yicha savollar soni to'ldi.");
        }

        return view('profile.exams.questions.create', compact('exam'));
    }

    public function questionStore(Request $request, Exam $exam): RedirectResponse
    {
        $user = $request->user();
        $this->ensureUserOwnsExam($user, $exam);

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

                if (! $isTextType) {
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
        $this->ensureUserOwnsExam($user, $exam);

        if ((int) $question->exam_id !== (int) $exam->id) {
            throw new ExamResourceMismatchException('Savol ushbu imtihonga tegishli emas.');
        }

        $question->load('options');

        return view('profile.exams.questions.edit', compact('exam', 'question'));
    }

    public function questionUpdate(Request $request, Exam $exam, Question $question): RedirectResponse
    {
        $user = $request->user();
        $this->ensureUserOwnsExam($user, $exam);

        if ((int) $question->exam_id !== (int) $exam->id) {
            throw new ExamResourceMismatchException('Savol ushbu imtihonga tegishli emas.');
        }

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

    public function questionDestroy(Request $request, Exam $exam, Question $question): RedirectResponse
    {
        $user = $request->user();
        $this->ensureUserOwnsExam($user, $exam);

        if ((int) $question->exam_id !== (int) $exam->id) {
            throw new ExamResourceMismatchException('Savol ushbu imtihonga tegishli emas.');
        }

        $imagePath = $question->image_path;
        $question->delete();

        if ($imagePath) {
            $this->imageService->deleteImage($imagePath);
        }

        $exam->syncActiveFromQuestions();
        forget_public_exam_caches();

        return back()->with('success', "Savol o'chirildi.");
    }

    public function exportResults(Request $request)
    {
        $user = $request->user();
        $this->ensureUserCanManageExams($user);

        $examId = $request->query('exam_id');
        $selectedExamId = $examId !== null && $examId !== '' ? (int) $examId : null;

        $query = Result::query()
            ->with(['exam:id,title', 'user:id,name,first_name,last_name,phone,email,grade'])
            ->whereIn('status', ['submitted', 'expired']);

        if (! $user->isAdmin() && ! $user->isSuperAdmin()) {
            $query->whereHas('exam', fn ($builder) => $builder->where('created_by', $user->id));
        }

        if ($selectedExamId) {
            $query->where('exam_id', $selectedExamId);
        }

        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        if ($dateFrom) {
            $query->whereDate('submitted_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('submitted_at', '<=', $dateTo);
        }

        $results = $query->latest('submitted_at')->get();

        $examTitle = 'barcha_imtihonlar';
        if ($selectedExamId) {
            $examTitleQuery = Exam::query()->whereKey($selectedExamId);

            if (! $user->isAdmin() && ! $user->isSuperAdmin()) {
                $examTitleQuery->where('created_by', $user->id);
            }

            $examTitle = $examTitleQuery->value('title') ?? 'imtihon';
        }

        $filename = 'natijalar_'.Str::slug($examTitle).'_'.now()->format('Y-m-d_H-i-s').'.xls';

        $html = view('exports.exam_results_excel', [
            'results' => $results,
            'selectedExamId' => $selectedExamId,
        ])->render();

        return response($html, 200)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function results(Request $request)
    {
        $user = $request->user();
        $this->ensureUserCanManageExams($user);

        $examsQuery = Exam::query();
        if (! $user->isAdmin() && ! $user->isSuperAdmin()) {
            $examsQuery->where('created_by', $user->id);
        }

        $exams = $examsQuery->orderBy('title')->get(['id', 'title']);

        $examId = $request->query('exam_id');
        $selectedExamId = $examId !== null && $examId !== '' ? (int) $examId : null;
        if ($selectedExamId && ! $exams->contains('id', $selectedExamId)) {
            $selectedExamId = null;
        }

        $query = Result::query()->with([
            'exam:id,title,created_by,deleted_at',
            'user:id,name,first_name,last_name,phone,email,grade',
        ]);

        if (! $user->isAdmin() && ! $user->isSuperAdmin()) {
            $query->whereHas('exam', function ($builder) use ($user): void {
                $builder->where('created_by', $user->id);
            });
        }

        if ($selectedExamId) {
            $query->where('exam_id', $selectedExamId);
        }

        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $query->where(function ($builder) use ($q): void {
                $builder->whereHas('user', function ($userQuery) use ($q): void {
                    $userQuery->where('name', 'like', '%'.$q.'%')
                        ->orWhere('email', 'like', '%'.$q.'%')
                        ->orWhere('phone', 'like', '%'.$q.'%');
                });
            });
        }

        $status = $request->query('status');
        if ($status && in_array($status, ['started', 'submitted', 'expired'], true)) {
            $query->where('status', $status);
        }

        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        if ($dateFrom) {
            $query->whereDate('submitted_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('submitted_at', '<=', $dateTo);
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

        $result->load([
            'exam' => fn ($builder) => $builder->withTrashed(),
            'answers.question.options',
            'answers.option',
            'user',
        ]);

        $exam = $result->exam;
        if (! $exam) {
            throw new ExamResourceMismatchException('Imtihon topilmadi.');
        }

        if (! (($user->canManageExams() && $exam->ownsExam($user)) || ((int) $result->user_id === (int) $user->id))) {
            throw new ExamAccessDeniedException();
        }

        return view('profile.exams.results_show', compact('exam', 'result'));
    }

    public function gradeTextAnswer(GradeTextAnswerRequest $request, Result $result, Answer $answer): RedirectResponse
    {
        $user = $request->user();
        $result->loadMissing('exam:id,created_by,passing_points');

        if (! $result->exam) {
            throw new ExamResourceMismatchException('Imtihon topilmadi.');
        }

        $this->ensureUserOwnsExam($user, $result->exam);

        if ((int) $answer->result_id !== (int) $result->id) {
            throw new ExamResourceMismatchException('Javob ushbu natijaga tegishli emas.');
        }

        $this->gradeTextAnswerAction->handle(
            $result,
            $answer,
            $request->boolean('is_correct')
        );

        return back()->with('success', 'Baholandi va natija qayta hisoblandi.');
    }

    /**
     * @return array<string, mixed>
     */
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
                'points' => "Savollar ballari yig'indisi imtihon umumiy balidan ({$total}) {$over} ball oshib ketdi. Shu savol bilan yig'indi: ".($sum + $questionPoints).'.',
            ]);
        }
    }
}
