<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestCourseOpenAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isTeacher();
    }

    public function rules(): array
    {
        $user = $this->user();

        if (! $user || $user->hasReachedCourseOpenLimit() || $user->hasCourseOpenApproval() || $user->hasPendingCourseOpenRequest()) {
            return [
                'reason' => ['nullable', 'string', 'max:1000'],
            ];
        }

        return [
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => "Nima uchun kurs ochmoqchi ekaningizni yozing.",
            'reason.min' => "Sabab kamida 10 ta belgidan iborat bo'lishi kerak.",
            'reason.max' => "Sabab 1000 ta belgidan oshmasligi kerak.",
        ];
    }
}
