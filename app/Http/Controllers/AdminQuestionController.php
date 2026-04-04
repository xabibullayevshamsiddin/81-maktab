<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminQuestionController extends Controller
{
    public function index(Request $request, Exam $exam)
    {
        $q = trim((string) $request->query('q', ''));

        $questionsQuery = $exam->questions()
            ->with('options')
            ->orderBy('sort_order')
            ->orderBy('id');

        if ($q !== '') {
            $questionsQuery->where('body', 'like', '%'.$q.'%');
        }

        $questions = $questionsQuery->get();

        $pointsSum = (int) $exam->questions()->sum('points');

        return view('admin.exams.questions.index', compact('exam', 'questions', 'pointsSum'));
    }

    public function create(Exam $exam)
    {
        if ($exam->isQuestionQuotaFilled()) {
            return redirect()
                ->route('admin.exams.questions.index', $exam)
                ->with('error', "Reja bo'yicha savollar soni to'ldi. Yangi savol qo'shish uchun imtihonni tahrirlab «Savollar soni»ni oshiring.");
        }

        return view('admin.exams.questions.create', compact('exam'));
    }

    public function store(Request $request, Exam $exam)
    {
        if ($exam->isQuestionQuotaFilled()) {
            return redirect()
                ->route('admin.exams.questions.index', $exam)
                ->with('error', "Reja bo'yicha savollar soni allaqachon to'ldi.");
        }

        $validated = $this->validateQuestion($request);
        $this->assertQuestionPointsWithinBudget($exam, (int) $validated['points'], null);

        DB::transaction(function () use ($exam, $validated): void {
            $question = Question::query()->create([
                'exam_id' => $exam->id,
                'body' => $validated['body'],
                'sort_order' => (int) ($validated['sort_order'] ?? 0),
                'points' => (int) $validated['points'],
            ]);

            foreach (['A', 'B', 'C', 'D'] as $label) {
                Option::query()->create([
                    'question_id' => $question->id,
                    'label' => $label,
                    'body' => $validated['options'][$label],
                    'is_correct' => $validated['correct_label'] === $label,
                ]);
            }
        });

        $exam->syncActiveFromQuestions();

        return redirect()->route('admin.exams.questions.index', $exam)
            ->with('success', "Savol qo'shildi.");
    }

    public function edit(Exam $exam, Question $question)
    {
        abort_unless((int) $question->exam_id === (int) $exam->id, 404);
        $question->load('options');

        return view('admin.exams.questions.edit', compact('exam', 'question'));
    }

    public function update(Request $request, Exam $exam, Question $question)
    {
        abort_unless((int) $question->exam_id === (int) $exam->id, 404);
        $validated = $this->validateQuestion($request);
        $this->assertQuestionPointsWithinBudget($exam, (int) $validated['points'], $question->id);

        DB::transaction(function () use ($question, $validated): void {
            $question->update([
                'body' => $validated['body'],
                'sort_order' => (int) ($validated['sort_order'] ?? 0),
                'points' => (int) $validated['points'],
            ]);

            foreach (['A', 'B', 'C', 'D'] as $label) {
                $question->options()->updateOrCreate(
                    ['label' => $label],
                    [
                        'body' => $validated['options'][$label],
                        'is_correct' => $validated['correct_label'] === $label,
                    ]
                );
            }
        });

        $exam->syncActiveFromQuestions();

        return redirect()->route('admin.exams.questions.index', $exam)
            ->with('success', 'Savol yangilandi.');
    }

    public function destroy(Exam $exam, Question $question)
    {
        abort_unless((int) $question->exam_id === (int) $exam->id, 404);
        $question->delete();
        $exam->syncActiveFromQuestions();

        return back()->with('success', "Savol o'chirildi.");
    }

    private function validateQuestion(Request $request): array
    {
        return $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'points' => ['required', 'integer', 'min:1', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'correct_label' => ['required', 'in:A,B,C,D'],
            'options.A' => ['required', 'string', 'max:1000'],
            'options.B' => ['required', 'string', 'max:1000'],
            'options.C' => ['required', 'string', 'max:1000'],
            'options.D' => ['required', 'string', 'max:1000'],
        ]);
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

