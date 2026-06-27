<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
}
