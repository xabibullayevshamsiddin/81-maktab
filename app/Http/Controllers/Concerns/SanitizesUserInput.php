<?php

namespace App\Http\Controllers\Concerns;

trait SanitizesUserInput
{
    /**
     * @param  array{body: string, author_name?: string|null}  $validated
     * @return array{body: string, author_name?: string|null}
     */
    protected function sanitizeCommentFields(array $validated): array
    {
        $validated['body'] = sanitize_plain_text($validated['body']);
        if (array_key_exists('author_name', $validated)) {
            $v = $validated['author_name'];
            $validated['author_name'] = ($v !== null && $v !== '')
                ? sanitize_plain_text((string) $v)
                : null;
        }

        return $validated;
    }
}
