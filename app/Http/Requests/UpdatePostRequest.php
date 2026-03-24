<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'short_content' => ['required', 'string'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Sarlavha kiritilishi shart.',
            'title.max' => 'Sarlavha 255 belgidan oshmasligi kerak.',
            'category_id.required' => 'Kategoriya tanlanishi shart.',
            'category_id.exists' => 'Tanlangan kategoriya mavjud emas.',
            'short_content.required' => 'Qisqacha mazmun kiritilishi shart.',
            'content.required' => 'Mazmun kiritilishi shart.',
            'image.image' => 'Fayl rasm bo\'lishi kerak.',
            'image.mimes' => 'Rasm jpg, jpeg, png yoki webp formatda bo\'lishi kerak.',
        ];
    }
}
