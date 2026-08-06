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
            'phone' => ['required', 'string', 'min:9'],
            'password' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Telefon raqami kiritilishi shart.',
            'phone.min' => "Telefon raqami to'g'ri formatda kiriting.",
            'password.required' => 'Parol kiritilishi shart.',
        ];
    }
}
