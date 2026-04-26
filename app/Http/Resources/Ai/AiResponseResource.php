<?php

namespace App\Http\Resources\Ai;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiResponseResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'success' => (bool) ($this['success'] ?? false),
            'text' => (string) ($this['text'] ?? ''),
            'source' => (string) ($this['source'] ?? ''),
            'actions' => array_values($this['actions'] ?? []),
            'feedback_enabled' => (bool) ($this['feedback_enabled'] ?? false),
            'clarification_requested' => (bool) ($this['clarification_requested'] ?? false),
            'support_converted' => (bool) ($this['support_converted'] ?? false),
            'interaction_id' => $this['interaction_id'] ?? null,
            'response_type' => $this['response_type'] ?? null,
            'wizard_active' => (bool) ($this['wizard_active'] ?? false),
            'disabled' => (bool) ($this['disabled'] ?? false),
            'context_applied' => (bool) ($this['context_applied'] ?? false),
        ];
    }
}
