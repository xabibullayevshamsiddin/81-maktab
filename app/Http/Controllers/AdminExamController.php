<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Exam;
use App\Models\Result;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminExamController extends Controller
{
    public function __construct(private readonly ImageService $imageService) {}

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

    public function store(Request $request)
    {
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
        ]);
        forget_public_exam_caches();

        return redirect()
            ->route('admin.exams.questions.index', $exam)
            ->with('success', "1-bosqich saqlandi. Endi {$exam->required_questions} ta savol qo'shing — barchasi to'lgach imtihon avtomatik faol bo'ladi.");
    }

    public function edit(Exam $exam)
    {
        $exam->loadCount('questions');

        return view('admin.exams.edit', compact('exam'));
    }

    public function update(Request $request, Exam $exam)
    {
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

        return redirect()->route('admin.exams.index')->with('success', 'Imtihon yangilandi.');
    }

    public function destroy(Exam $exam)
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
        $exams = Exam::query()->orderBy('title')->get(['id', 'title']);

        $examId = $request->query('exam_id');
        $selectedExamId = $examId !== null && $examId !== '' ? (int) $examId : null;

        if ($selectedExamId && ! Exam::query()->whereKey($selectedExamId)->exists()) {
            $selectedExamId = null;
        }

        $query = Result::query()->with(['exam', 'user']);

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

        $results = $query
            ->latest('id')
            ->paginate(40)
            ->withQueryString();

        return view('admin.exams.results', compact('results', 'exams', 'selectedExamId'));
    }

    public function showResult(Result $result)
    {
        $exam = $result->exam;
        $result->load(['user', 'answers.question.options', 'answers.option']);

        return view('admin.exams.results_show', compact('exam', 'result'));
    }

    public function gradeTextAnswer(Request $request, Result $result, Answer $answer)
    {
        abort_unless((int) $answer->result_id === (int) $result->id, 404);

        $validated = $request->validate([
            'is_correct' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($result, $answer, $validated): void {
            $answer->update([
                'is_correct_override' => $validated['is_correct'],
            ]);

            $answers = Answer::query()
                ->where('result_id', $result->id)
                ->with(['option:id,is_correct', 'question:id,points,question_type'])
                ->get();

            $correctCount = 0;
            $pointsEarned = 0;
            $hasPendingManualReview = false;

            foreach ($answers as $item) {
                if ($item->question?->isTextType()) {
                    if ($item->is_correct_override === null && filled($item->text_answer)) {
                        $hasPendingManualReview = true;
                        continue;
                    }
                }

                if ($item->isCorrectAnswer()) {
                    $correctCount++;
                    $pointsEarned += (int) ($item->question?->points ?? 0);
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

        return back()->with('success', 'Matnli javob baholandi va natija yangilandi.');
    }

    public function destroyResult(Result $result)
    {
        $result->delete();

        return back()->with('success', 'Natija o‘chirildi.');
    }
    private function normalizeAllowedGrades(array $grades): array
    {
        $normalized = normalize_school_grade_list($grades);

        return count($normalized) === count(school_grade_options())
            ? []
            : $normalized;
    }
}
