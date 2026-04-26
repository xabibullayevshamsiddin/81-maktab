<?php

namespace App\Http\Controllers;

use App\Actions\Exams\GradeTextAnswerAction;
use App\Actions\Exams\StoreExamAction;
use App\Actions\Exams\UpdateExamAction;
use App\DataTransferObjects\Exams\ExamData;
use App\Exceptions\ExamResourceMismatchException;
use App\Http\Requests\Exams\GradeTextAnswerRequest;
use App\Http\Requests\Exams\SaveExamRequest;
use App\Models\Answer;
use App\Models\Exam;
use App\Models\Result;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminExamController extends Controller
{
    public function __construct(
        private readonly ImageService $imageService,
        private readonly StoreExamAction $storeExamAction,
        private readonly UpdateExamAction $updateExamAction,
        private readonly GradeTextAnswerAction $gradeTextAnswerAction,
    ) {}

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $query = Exam::query()->withCount('questions')->latest();

        if ($q !== '') {
            $query->where('title', 'like', '%'.$q.'%');
        }

        $exams = $query->paginate(10)->withQueryString();

        return view('admin.exams.index', compact('exams'));
    }

    public function create()
    {
        return view('admin.exams.create');
    }

    public function store(SaveExamRequest $request): RedirectResponse
    {
        $exam = $this->storeExamAction->handle(ExamData::fromRequest($request));

        return redirect()
            ->route('admin.exams.questions.index', $exam)
            ->with('success', "1-bosqich saqlandi. Endi {$exam->required_questions} ta savol qo'shing - barchasi to'lgach imtihon avtomatik faol bo'ladi.");
    }

    public function edit(Exam $exam)
    {
        $exam->loadCount('questions');

        return view('admin.exams.edit', compact('exam'));
    }

    public function update(SaveExamRequest $request, Exam $exam): RedirectResponse
    {
        $request->ensureQuestionPointsBudget($exam);

        $this->updateExamAction->handle($exam, ExamData::fromRequest($request));

        return redirect()->route('admin.exams.index')->with('success', 'Imtihon yangilandi.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
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

    public function results(Request $request)
    {
        $exams = Exam::query()->withTrashed()->orderBy('title')->get(['id', 'title']);

        $examId = $request->query('exam_id');
        $selectedExamId = $examId !== null && $examId !== '' ? (int) $examId : null;
        if ($selectedExamId && ! $exams->contains('id', $selectedExamId)) {
            $selectedExamId = null;
        }

        $query = Result::query()->with([
            'exam:id,title,created_by,deleted_at',
            'user:id,name,first_name,last_name,phone,email,grade',
        ]);

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

        return view('admin.exams.results', compact('results', 'exams', 'selectedExamId'));
    }

    public function exportResults(Request $request)
    {
        $examId = $request->query('exam_id');
        $selectedExamId = $examId !== null && $examId !== '' ? (int) $examId : null;

        $query = Result::query()
            ->with(['exam:id,title', 'user:id,name,first_name,last_name,phone,email,grade'])
            ->whereIn('status', ['submitted', 'expired']);

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

        $examTitle = $selectedExamId
            ? Exam::query()->withTrashed()->whereKey($selectedExamId)->value('title') ?? 'imtihon'
            : 'barcha_imtihonlar';

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

    public function showResult(Result $result)
    {
        $result->load([
            'exam' => fn ($builder) => $builder->withTrashed(),
            'user',
            'answers.question.options',
            'answers.option',
        ]);

        $exam = $result->exam;

        return view('admin.exams.results_show', compact('exam', 'result'));
    }

    public function gradeTextAnswer(GradeTextAnswerRequest $request, Result $result, Answer $answer): RedirectResponse
    {
        if ((int) $answer->result_id !== (int) $result->id) {
            throw new ExamResourceMismatchException('Javob ushbu natijaga tegishli emas.');
        }

        $this->gradeTextAnswerAction->handle(
            $result,
            $answer,
            $request->boolean('is_correct')
        );

        return back()->with('success', 'Matnli javob baholandi va natija yangilandi.');
    }

    public function destroyResult(Result $result): RedirectResponse
    {
        $result->delete();

        return back()->with('success', "Natija o'chirildi.");
    }
}
