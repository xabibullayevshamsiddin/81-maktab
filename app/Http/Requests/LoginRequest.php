<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email kiritilishi shart.',
            'email.email' => 'To\'g\'ri email manzil kiriting.',
            'password.required' => 'Parol kiritilishi shart.',
        ];
    }
}
