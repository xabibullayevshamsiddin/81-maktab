<?php

namespace App\Http\Requests\Exams;

use App\Models\Exam;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SaveExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('available_from') === '') {
            $this->merge(['available_from' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'required_questions' => ['required', 'integer', 'min:1', 'max:500'],
            'total_points' => ['required', 'integer', 'min:1', 'max:10000'],
            'passing_points' => ['required', 'integer', 'min:1', 'max:10000'],
            'allowed_grades' => ['required', 'array', 'min:1'],
            'allowed_grades.*' => ['string', Rule::in(school_grade_options())],
            'available_from' => ['nullable', 'date_format:Y-m-d H:i'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'allowed_grades.required' => 'Kamida bitta sinfni tanlashingiz shart.',
            'allowed_grades.min' => 'Kamida bitta sinfni tanlashingiz shart.',
        ];
    }

    public function ensureQuestionPointsBudget(Exam $exam): void
    {
        $sumPoints = $exam->sumQuestionPoints();
        $totalPoints = (int) $this->validated('total_points');

        if ($totalPoints < $sumPoints) {
            throw ValidationException::withMessages([
                'total_points' => "Umumiy bal kamida savollar ballari yig'indisi ({$sumPoints}) bo'lishi kerak. Avval savollarni tahrirlang.",
            ]);
        }
    }

    protected function passedValidation(): void
    {
        if ((int) $this->validated('passing_points') > (int) $this->validated('total_points')) {
            throw ValidationException::withMessages([
                'passing_points' => "O'tish uchun minimal ball umumiy baldan katta bo'lmasligi kerak.",
            ]);
        }
    }
}
