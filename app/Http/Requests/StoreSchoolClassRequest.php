<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canManageSystem();
    }

    public function rules(): array
    {
        return [
            'grade_number' => ['required', 'integer', 'between:1,11'],
            'section' => ['required', 'string', 'max:10', 'regex:/^[A-Za-z0-9]+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'grade_number.between' => 'Sinf raqami 1 dan 11 gacha bo\'lishi kerak.',
            'section.regex' => 'Sinf bo\'limida faqat lotin harflari yoki raqam ishlating.',
        ];
    }
}
