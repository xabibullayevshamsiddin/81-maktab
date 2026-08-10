<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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
            'phone' => array_merge(uz_phone_rules(), [
                function ($attribute, $value, $fail) {
                    $normalized = uz_phone_format($value);
                    if ($normalized && User::query()->where('phone', $normalized)->exists()) {
                        $fail('Bu telefon raqami allaqachon ro\'yxatdan o\'tgan.');
                    }
                },
            ]),
            'is_parent' => ['nullable', 'in:1'],
            'grade' => ['required_unless:is_parent,1', 'nullable', 'string', Rule::in(school_student_grade_options())],
            'password' => ['required', 'string', 'min:8', 'max:32', 'confirmed'],
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

            $grade = $this->input('grade');
            if ($grade && ! $this->input('is_parent')) {
                if (preg_match('/^(\d{1,2})-([A-Z0-9]+)$/i', $grade, $m) === 1) {
                    $schoolClass = \App\Models\SchoolClass::query()
                        ->active()
                        ->where('grade_number', (int) $m[1])
                        ->where('section', strtoupper($m[2]))
                        ->first();

                    if ($schoolClass && $schoolClass->isFull()) {
                        $v->errors()->add(
                            'grade',
                            "{$schoolClass->display_name} sinfi to'liq ({$schoolClass->max_students} ta limit). Boshqa sinf tanlang."
                        );
                    }
                }
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
            'phone.unique' => 'Bu telefon raqami allaqachon ro\'yxatdan o\'tgan.',
            'grade.required' => 'Sinfni tanlash shart.',
            'grade.in' => school_grade_validation_message(),
            'password.required' => 'Parol kiritilishi shart.',
            'password.min' => 'Parol kamida 8 belgidan iborat bo\'lishi kerak.',
            'password.max' => 'Parol 32 belgidan oshmasligi kerak.',
            'password.confirmed' => 'Parol tasdiqlanmadi.',
        ];
    }
}
