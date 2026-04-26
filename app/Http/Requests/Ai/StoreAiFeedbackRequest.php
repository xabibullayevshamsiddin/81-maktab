<?php

namespace App\Http\Requests\Ai;

use Illuminate\Foundation\Http\FormRequest;

class StoreAiFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'interaction_id' => ['required', 'integer', 'exists:ai_interactions,id'],
            'helpful' => ['required', 'boolean'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
