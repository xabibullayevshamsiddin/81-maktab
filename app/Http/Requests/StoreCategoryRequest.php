<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
            'name_en' => ['nullable', 'string', 'max:100', 'unique:categories,name_en'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Kategoriya nomi kiritilishi shart.',
            'name.max' => 'Kategoriya nomi 100 belgidan oshmasligi kerak.',
            'name.unique' => 'Bu kategoriya allaqachon mavjud.',
            'name_en.max' => 'English kategoriya nomi 100 belgidan oshmasligi kerak.',
            'name_en.unique' => 'Bu English kategoriya allaqachon mavjud.',
        ];
    }
}
