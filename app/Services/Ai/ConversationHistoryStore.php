<?php

namespace App\Services\Ai;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ConversationHistoryStore
{
    public function historyForUser(int $userId): array
    {
        $history = $this->cache()->get($this->key($userId), []);

        if (! is_array($history)) {
            return [];
        }

        return collect($history)
            ->map(function ($item): ?array {
                if (! is_array($item)) {
                    return null;
                }

                $text = trim((string) ($item['text'] ?? ''));
                if ($text === '') {
                    return null;
                }

                return [
                    'role' => ($item['role'] ?? 'user') === 'assistant' ? 'assistant' : 'user',
                    'text' => Str::limit($text, 700, ''),
                    'source' => trim((string) ($item['source'] ?? '')),
                ];
            })
            ->filter()
            ->take(-$this->maxItems())
            ->values()
            ->all();
    }

    public function rememberTurn(int $userId, string $question, array $payload): void
    {
        $source = (string) ($payload['source'] ?? '');

        if (Str::startsWith($source, 'support_wizard')) {
            return;
        }

        $history = $this->historyForUser($userId);
        $history[] = [
            'role' => 'user',
            'text' => Str::limit(sanitize_plain_text($question), 500, ''),
            'source' => 'user',
        ];

        $responseText = trim((string) ($payload['text'] ?? ''));
        if ($responseText !== '') {
            $history[] = [
                'role' => 'assistant',
                'text' => Str::limit(sanitize_plain_text($responseText), 700, ''),
                'source' => $source,
            ];
        }

        $this->cache()->put(
            $this->key($userId),
            array_slice($history, -$this->maxItems()),
            now()->addMinutes($this->ttlMinutes())
        );
    }

    public function clear(int $userId): void
    {
        $this->cache()->forget($this->key($userId));
    }

    private function cache(): CacheRepository
    {
        return Cache::store((string) config('ai.history_store', config('cache.default')));
    }

    private function key(int $userId): string
    {
        return 'ai:history:user:'.$userId;
    }

    private function ttlMinutes(): int
    {
        return max(10, (int) config('ai.history_ttl_minutes', 180));
    }

    private function maxItems(): int
    {
        return max(2, (int) config('ai.history_max_items', 10));
    }
}
