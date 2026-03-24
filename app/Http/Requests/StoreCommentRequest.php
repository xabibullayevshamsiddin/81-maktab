<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:500'],
            'author_name' => ['nullable', 'string', 'max:80'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'Izoh matni kiritilishi shart.',
            'body.max' => 'Izoh 500 belgidan oshmasligi kerak.',
            'author_name.max' => 'Ism 80 belgidan oshmasligi kerak.',
        ];
    }
}
