<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ];
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
            'password.required' => 'Parol kiritilishi shart.',
            'password.min' => 'Parol kamida 6 belgidan iborat bo\'lishi kerak.',
            'password.confirmed' => 'Parol tasdiqlanmadi.',
        ];
    }
}
