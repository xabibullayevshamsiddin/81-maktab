<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PromoteAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canManageSystem();
    }

    public function rules(): array
    {
        return [
            'from_year' => ['required', 'integer', 'between:2020,2100'],
            'to_year' => ['required', 'integer', 'between:2021,2101'],
            'dry_run' => ['nullable', 'boolean'],
            'force' => ['nullable', 'boolean'],
        ];
    }
}
