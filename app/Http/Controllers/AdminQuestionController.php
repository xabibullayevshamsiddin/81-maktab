<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminQuestionController extends Controller
{
    public function __construct(private ImageService $imageService) {}

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

        $questions = $questionsQuery->paginate(10)->withQueryString();
        $totalQuestionCount = (int) $exam->questions()->count();

        $pointsSum = (int) $exam->questions()->sum('points');

        return view('admin.exams.questions.index', compact('exam', 'questions', 'pointsSum', 'totalQuestionCount'));
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

        $oldImagePath = $question->image_path;
        $nextImagePath = $oldImagePath;
        $uploadedImagePath = null;

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

            $isTextType = $validated['question_type'] === Question::TYPE_TEXT;

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

        return redirect()->route('admin.exams.questions.index', $exam)
            ->with('success', 'Savol yangilandi.');
    }

    public function destroy(Exam $exam, Question $question)
    {
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
                'points' => "Savollar ballari yig‘indisi imtihon umumiy balidan ({$total}) {$over} ball oshib ketdi. Shu savol bilan yig‘indi: ".($sum + $questionPoints).".",
            ]);
        }
    }
}
