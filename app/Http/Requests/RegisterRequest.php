<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => uz_phone_rules(),
            'grade' => ['required', 'string', Rule::in(school_grade_options())],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'grade' => normalize_school_grade($this->input('grade')),
        ]);
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Ism kiritilishi shart.',
            'name.max' => 'Ism 255 belgidan oshmasligi kerak.',
            'email.required' => 'Email kiritilishi shart.',
            'email.email' => 'To\'g\'ri email manzil kiriting.',
            'email.unique' => 'Bu email allaqachon ro\'yxatdan o\'tgan.',
            'phone.required' => 'Telefon raqam kiritilishi shart.',
            'phone.regex' => uz_phone_validation_message(),
            'grade.required' => 'Sinfni tanlash shart.',
            'grade.in' => school_grade_validation_message(),
            'password.required' => 'Parol kiritilishi shart.',
            'password.min' => 'Parol kamida 6 belgidan iborat bo\'lishi kerak.',
            'password.confirmed' => 'Parol tasdiqlanmadi.',
        ];
    }
}
