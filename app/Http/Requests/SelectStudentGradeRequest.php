<?php

namespace App\Http\Requests;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SelectStudentGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User
            && ! $user->isParent()
            && $user->hasRole(User::ROLE_USER);
    }

    public function prepareForValidation(): void
    {
        if ($this->exists('grade')) {
            $this->merge([
                'grade' => normalize_school_grade($this->input('grade')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'grade' => ['required', 'string', Rule::in(school_student_grade_options())],
        ];
    }

    public function messages(): array
    {
        return [
            'grade.in' => school_grade_validation_message(),
        ];
    }

    /**
     * After basic validation passes, verify the selected class is not over capacity.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->any()) {
                return;
            }

            $grade = $this->input('grade');
            if (! $grade) {
                return;
            }

            // Parse grade string like "3-A" into grade_number=3, section="A"
            if (preg_match('/^(\d{1,2})-([A-Z0-9]+)$/i', $grade, $m) !== 1) {
                return;
            }

            $schoolClass = SchoolClass::query()
                ->active()
                ->where('grade_number', (int) $m[1])
                ->where('section', strtoupper($m[2]))
                ->first();

            if (! $schoolClass) {
                return;
            }

            // Skip capacity check if the user is just keeping their current grade
            $currentUser = $this->user();
            if ($currentUser && normalize_school_grade((string) ($currentUser->grade ?? '')) === $grade) {
                return;
            }

            if ($schoolClass->isFull()) {
                $validator->errors()->add(
                    'grade',
                    "{$schoolClass->display_name} sinfi to'liq ({$schoolClass->max_students} ta limit). Boshqa sinf tanlang."
                );
            }
        });
    }
}
