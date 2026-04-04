<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Result;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminExamController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $query = Exam::query()->withCount('questions')->latest();

        if ($q !== '') {
            $query->where('title', 'like', '%'.$q.'%');
        }

        $exams = $query->get();

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

        return redirect()->route('admin.exams.index')->with('success', 'Imtihon yangilandi.');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();

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
