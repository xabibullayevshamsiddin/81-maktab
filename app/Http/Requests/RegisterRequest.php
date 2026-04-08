<?php

namespace App\Http\Requests;

use App\Models\User;
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
            'first_name' => User::nameValidationRules(),
            'last_name' => User::nameValidationRules(),
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => uz_phone_rules(),
            'grade' => ['required', 'string', Rule::in(school_grade_options())],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
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
        $nameMsg = User::nameValidationMessage();

        return [
            'first_name.required' => 'Ism kiritilishi shart.',
            'first_name.max' => 'Ism 120 belgidan oshmasligi kerak.',
            'first_name.regex' => $nameMsg,
            'last_name.required' => 'Familiya kiritilishi shart.',
            'last_name.max' => 'Familiya 120 belgidan oshmasligi kerak.',
            'last_name.regex' => $nameMsg,
            'email.required' => 'Email kiritilishi shart.',
            'email.email' => 'To\'g\'ri email manzil kiriting.',
            'email.unique' => 'Bu email allaqachon ro\'yxatdan o\'tgan.',
            'phone.required' => 'Telefon raqam kiritilishi shart.',
            'phone.regex' => uz_phone_validation_message(),
            'grade.required' => 'Sinfni tanlash shart.',
            'grade.in' => school_grade_validation_message(),
            'password.required' => 'Parol kiritilishi shart.',
            'password.min' => 'Parol kamida 8 belgidan iborat bo\'lishi kerak.',
            'password.confirmed' => 'Parol tasdiqlanmadi.',
        ];
    }
}
