<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

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
            'is_parent' => ['nullable', 'in:1'],
            'grade' => ['required_unless:is_parent,1', 'nullable', 'string', Rule::in(school_grade_options())],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(12)
                    ->mixedCase()      // Requires uppercase & lowercase
                    ->numbers()        // Requires numbers
                    ->symbols()        // Requires special characters
                    ->uncompromised(), // Check against leaked passwords database
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'grade' => $this->input('is_parent') ? null : normalize_school_grade($this->input('grade')),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            if ($v->errors()->isNotEmpty()) {
                return;
            }
            $first = (string) $this->input('first_name', '');
            $last = (string) $this->input('last_name', '');
            if (User::isFullNameTaken($first, $last)) {
                $v->errors()->add(
                    'last_name',
                    'Bu ism va familiya bilan foydalanuvchi allaqachon ro‘yxatdan o‘tgan. Boshqa ism yoki familiya kiriting.'
                );
            }
        });
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
            'password.min' => 'Parol kamida 12 belgidan iborat bo\'lishi kerak.',
            'password.confirmed' => 'Parol tasdiqlanmadi.',
            'password.mixed_case' => 'Parol katta va kichik harflarni o\'z ichiga olishi kerak.',
            'password.numbers' => 'Parol kamida bitta raqam o\'z ichiga olishi kerak.',
            'password.symbols' => 'Parol kamida bitta maxsus belgi (!@#$%) o\'z ichiga olishi kerak.',
            'password.uncompromised' => 'Bu parol xavfsiz emas. Boshqa parol tanlang.',
        ];
    }
}
