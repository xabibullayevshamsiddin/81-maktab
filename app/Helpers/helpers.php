<?php

if (! function_exists('gmail_compose_url')) {
    function gmail_compose_url(string $email, ?string $subject = null, ?string $body = null): string
    {
        $query = array_filter([
            'view' => 'cm',
            'fs' => '1',
            'to' => trim($email),
            'su' => $subject,
            'body' => $body,
        ], static fn ($value) => $value !== null && $value !== '');

        return 'https://mail.google.com/mail/?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }
}

if (! function_exists('app_public_asset')) {
    function app_public_asset(string $path): string
    {
        $path = '/'.ltrim($path, '/');

        if (app()->runningInConsole()) {
            return $path;
        }

        $baseUrl = request()->getBaseUrl();

        return ($baseUrl !== '' ? rtrim($baseUrl, '/') : '').$path;
    }
}

if (! function_exists('app_storage_asset')) {
    /**
     * Public diskdagi fayl uchun to‘liq URL (admin va sayt bir xil ishlashi uchun).
     * Eski yozuvlarda "storage/..." takrori yoki to‘liq http URL bo‘lishi mumkin — normalize qilinadi.
     */
    function app_storage_asset(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        while (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        if ($path === '') {
            return null;
        }

        // APP_URL pastki papkani hisobga olmasa, Storage::url() noto‘g‘ri URL beradi.
        // app_public_asset bilan bir xil: joriy so‘rovning base URL + /storage/...
        if (! app()->runningInConsole()) {
            $baseUrl = request()->getBaseUrl();
            if ($baseUrl !== '') {
                return rtrim($baseUrl, '/').'/storage/'.$path;
            }
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
    }
}

if (! function_exists('cache_key_home_posts')) {
    function cache_key_home_posts(): string
    {
        return 'public.home.posts.v1';
    }
}

if (! function_exists('cache_key_home_featured_teacher')) {
    function cache_key_home_featured_teacher(): string
    {
        return 'public.home.featured_teacher.v1';
    }
}

if (! function_exists('cache_key_public_post_categories')) {
    function cache_key_public_post_categories(): string
    {
        return 'public.posts.categories.v1';
    }
}

if (! function_exists('cache_namespace_version_key')) {
    function cache_namespace_version_key(string $namespace): string
    {
        return 'cache.namespace_version.'.$namespace;
    }
}

if (! function_exists('cache_namespace_version')) {
    function cache_namespace_version(string $namespace): int
    {
        $key = cache_namespace_version_key($namespace);

        if (! \Illuminate\Support\Facades\Cache::has($key)) {
            \Illuminate\Support\Facades\Cache::forever($key, 1);
        }

        return (int) \Illuminate\Support\Facades\Cache::get($key, 1);
    }
}

if (! function_exists('bump_cache_namespace_version')) {
    function bump_cache_namespace_version(string $namespace): int
    {
        $nextVersion = cache_namespace_version($namespace) + 1;
        \Illuminate\Support\Facades\Cache::forever(cache_namespace_version_key($namespace), $nextVersion);

        return $nextVersion;
    }
}

if (! function_exists('cache_key_public_courses_page')) {
    function cache_key_public_courses_page(int $page = 1): string
    {
        return 'public.courses.page.'.$page.'.v'.cache_namespace_version('public_courses');
    }
}

if (! function_exists('cache_key_public_course_show')) {
    function cache_key_public_course_show(int $courseId): string
    {
        return 'public.courses.show.'.$courseId.'.v'.cache_namespace_version('public_courses');
    }
}

if (! function_exists('cache_key_public_exams_page')) {
    function cache_key_public_exams_page(int $page = 1): string
    {
        return 'public.exams.page.'.$page.'.v'.cache_namespace_version('public_exams');
    }
}

if (! function_exists('cache_key_public_calendar_page')) {
    function cache_key_public_calendar_page(int $year, int $page = 1): string
    {
        return 'public.calendar.year.'.$year.'.page.'.$page.'.v'.cache_namespace_version('public_calendar');
    }
}

if (! function_exists('cache_key_public_calendar_counts')) {
    function cache_key_public_calendar_counts(int $year): string
    {
        return 'public.calendar.year.'.$year.'.counts.v'.cache_namespace_version('public_calendar');
    }
}

if (! function_exists('forget_public_content_caches')) {
    function forget_public_content_caches(): void
    {
        \Illuminate\Support\Facades\Cache::forget(cache_key_home_posts());
        \Illuminate\Support\Facades\Cache::forget(cache_key_public_post_categories());
    }
}

if (! function_exists('forget_public_teacher_caches')) {
    function forget_public_teacher_caches(): void
    {
        \Illuminate\Support\Facades\Cache::forget(cache_key_home_featured_teacher());
        bump_cache_namespace_version('public_teachers');
        bump_cache_namespace_version('public_courses');
    }
}

if (! function_exists('forget_public_course_caches')) {
    function forget_public_course_caches(): void
    {
        bump_cache_namespace_version('public_courses');
    }
}

if (! function_exists('forget_public_exam_caches')) {
    function forget_public_exam_caches(): void
    {
        bump_cache_namespace_version('public_exams');
    }
}

if (! function_exists('forget_public_calendar_caches')) {
    function forget_public_calendar_caches(): void
    {
        bump_cache_namespace_version('public_calendar');
    }
}

if (! function_exists('supported_locales')) {
    function supported_locales(): array
    {
        return [
            'uz' => 'UZ',
            'en' => 'EN',
        ];
    }
}

if (! function_exists('current_locale')) {
    function current_locale(): string
    {
        $locale = (string) app()->getLocale();

        return array_key_exists($locale, supported_locales())
            ? $locale
            : (string) config('app.locale', 'uz');
    }
}

if (! function_exists('localized_model_value')) {
    function localized_model_value($model, string $field, ?string $locale = null, bool $fallback = true): string
    {
        if (! $model) {
            return '';
        }

        $locale = $locale ?: current_locale();
        $baseValue = trim((string) data_get($model, $field));

        if ($locale === 'uz') {
            return $baseValue;
        }

        $localizedValue = trim((string) data_get($model, $field.'_'.$locale));
        if ($localizedValue !== '') {
            return $localizedValue;
        }

        return $fallback ? $baseValue : '';
    }
}

if (! function_exists('localized_post_kind_label')) {
    function localized_post_kind_label(?string $key, ?string $locale = null): string
    {
        $key = $key ?: 'general';
        $meta = config('post_kinds.'.$key, []);
        $locale = $locale ?: current_locale();

        $localizedLabel = data_get($meta, 'label.'.$locale);
        if (filled($localizedLabel)) {
            return (string) $localizedLabel;
        }

        $fallbackLabel = data_get($meta, 'label.uz', data_get($meta, 'label'));

        return filled($fallbackLabel) ? (string) $fallbackLabel : $key;
    }
}

if (! function_exists('sanitize_exam_rich_text')) {
    function sanitize_exam_rich_text(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $allowedTags = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 'mark', 'small', 'sub', 'sup', 'ul', 'ol', 'li', 'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td'];
        $allowedAttributes = [
            'table' => ['border', 'cellpadding', 'cellspacing'],
            'th' => ['colspan', 'rowspan', 'scope'],
            'td' => ['colspan', 'rowspan'],
        ];

        $hasSupportedHtml = preg_match('/<(\/?(p|br|strong|b|em|i|u|mark|small|sub|sup|ul|ol|li|table|thead|tbody|tfoot|tr|th|td))\b/i', $value) === 1;
        if (! $hasSupportedHtml) {
            return $value;
        }

        if (! class_exists(\DOMDocument::class)) {
            return trim(strip_tags($value, '<'.implode('><', $allowedTags).'>'));
        }

        $previousLibxml = libxml_use_internal_errors(true);
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->preserveWhiteSpace = false;

        $loaded = @$document->loadHTML(
            '<?xml encoding="utf-8" ?><div>'.$value.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        if (! $loaded) {
            libxml_clear_errors();
            libxml_use_internal_errors($previousLibxml);

            return trim(strip_tags($value, '<'.implode('><', $allowedTags).'>'));
        }

        $root = $document->getElementsByTagName('div')->item(0);
        if ($root instanceof \DOMNode) {
            sanitize_exam_rich_text_node($root, $allowedTags, $allowedAttributes);
        }

        $sanitized = '';
        if ($root instanceof \DOMNode) {
            foreach (iterator_to_array($root->childNodes) as $childNode) {
                $sanitized .= $document->saveHTML($childNode);
            }
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previousLibxml);

        return trim($sanitized);
    }
}

if (! function_exists('sanitize_exam_rich_text_node')) {
    function sanitize_exam_rich_text_node(\DOMNode $parent, array $allowedTags, array $allowedAttributes): void
    {
        foreach (iterator_to_array($parent->childNodes) as $childNode) {
            if ($childNode->nodeType === XML_COMMENT_NODE) {
                $parent->removeChild($childNode);

                continue;
            }

            if ($childNode->nodeType === XML_TEXT_NODE) {
                continue;
            }

            if ($childNode->nodeType !== XML_ELEMENT_NODE) {
                $parent->removeChild($childNode);

                continue;
            }

            $tagName = strtolower($childNode->nodeName);

            if (! in_array($tagName, $allowedTags, true)) {
                if (in_array($tagName, ['script', 'style', 'iframe', 'object', 'embed', 'link', 'meta'], true)) {
                    $parent->removeChild($childNode);

                    continue;
                }

                while ($childNode->firstChild) {
                    $parent->insertBefore($childNode->firstChild, $childNode);
                }

                $parent->removeChild($childNode);

                continue;
            }

            if ($childNode->hasAttributes()) {
                foreach (iterator_to_array($childNode->attributes) as $attribute) {
                    $attributeName = strtolower($attribute->nodeName);
                    $attributeValue = trim((string) $attribute->nodeValue);
                    $tagAllowedAttributes = $allowedAttributes[$tagName] ?? [];

                    if (str_starts_with($attributeName, 'on') || ! in_array($attributeName, $tagAllowedAttributes, true)) {
                        $childNode->removeAttribute($attribute->nodeName);

                        continue;
                    }

                    if (in_array($attributeName, ['border', 'cellpadding', 'cellspacing', 'colspan', 'rowspan'], true)
                        && preg_match('/^\d{1,2}$/', $attributeValue) !== 1) {
                        $childNode->removeAttribute($attribute->nodeName);

                        continue;
                    }

                    if ($attributeName === 'scope'
                        && ! in_array(strtolower($attributeValue), ['col', 'row', 'colgroup', 'rowgroup'], true)) {
                        $childNode->removeAttribute($attribute->nodeName);
                    }
                }
            }

            sanitize_exam_rich_text_node($childNode, $allowedTags, $allowedAttributes);
        }
    }
}

if (! function_exists('render_exam_rich_text')) {
    function render_exam_rich_text(?string $value): \Illuminate\Support\HtmlString
    {
        $value = trim((string) $value);
        if ($value === '') {
            return new \Illuminate\Support\HtmlString('');
        }

        $containsHtml = preg_match('/<[^>]+>/', $value) === 1;
        $html = $containsHtml ? $value : nl2br(e($value));

        return new \Illuminate\Support\HtmlString($html);
    }
}

if (! function_exists('uz_phone_input_pattern')) {
    function uz_phone_input_pattern(): string
    {
        return '\+998(?:[\s-]?\d{2})(?:[\s-]?\d{3})(?:[\s-]?\d{2})(?:[\s-]?\d{2})';
    }
}

if (! function_exists('uz_phone_validation_message')) {
    function uz_phone_validation_message(): string
    {
        return "Telefon raqam +998 90 123 45 67 ko'rinishida bo'lishi kerak.";
    }
}

if (! function_exists('uz_phone_input_title')) {
    function uz_phone_input_title(): string
    {
        return uz_phone_validation_message();
    }
}

if (! function_exists('uz_phone_rules')) {
    function uz_phone_rules(bool $required = true): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'max:20',
            'regex:/^'.uz_phone_input_pattern().'$/',
        ];
    }
}

if (! function_exists('uz_phone_normalize')) {
    function uz_phone_normalize(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $phone = trim($phone);
        if ($phone === '') {
            return null;
        }

        return preg_replace('/[^\d+]+/', '', $phone);
    }
}

if (! function_exists('uz_phone_format')) {
    function uz_phone_format(?string $phone): ?string
    {
        $normalized = uz_phone_normalize($phone);
        if ($normalized === null) {
            return null;
        }

        return $normalized;
    }
}

if (! function_exists('school_grade_sections')) {
    function school_grade_sections(): array
    {
        return ['A', 'B', 'C', 'D', 'E', 'F'];
    }
}

if (! function_exists('school_grade_grouped_options')) {
    function school_grade_grouped_options(): array
    {
        $groups = [];

        foreach (range(1, 11) as $gradeNumber) {
            $groupLabel = $gradeNumber.'-sinf';
            $groups[$groupLabel] = [];

            foreach (school_grade_sections() as $section) {
                $value = $gradeNumber.'-'.$section;
                $groups[$groupLabel][$value] = $value;
            }
        }

        return $groups;
    }
}

if (! function_exists('school_grade_options')) {
    function school_grade_options(): array
    {
        return collect(school_grade_grouped_options())
            ->flatMap(static fn ($options) => array_keys($options))
            ->values()
            ->all();
    }
}

if (! function_exists('school_grade_validation_message')) {
    function school_grade_validation_message(): string
    {
        return "Sinf ro'yxatdan tanlanishi kerak.";
    }
}

if (! function_exists('normalize_school_grade')) {
    function normalize_school_grade(?string $grade): ?string
    {
        if ($grade === null) {
            return null;
        }

        $grade = strtoupper(trim((string) $grade));
        if ($grade === '') {
            return null;
        }

        $grade = preg_replace('/\s+/', '', $grade);
        $grade = str_replace(['_', '/', '\\'], '-', $grade);

        if (preg_match('/^(\d{1,2})([A-Z])$/', $grade, $matches) === 1) {
            return $matches[1].'-'.$matches[2];
        }

        return $grade;
    }
}

if (! function_exists('normalize_school_grade_list')) {
    function normalize_school_grade_list($grades): array
    {
        if ($grades === null) {
            return [];
        }

        if (! is_array($grades) && ! $grades instanceof \Traversable) {
            $grades = [$grades];
        }

        $gradeOrder = array_flip(school_grade_options());

        return collect($grades)
            ->map(static fn ($grade) => is_scalar($grade) ? normalize_school_grade((string) $grade) : null)
            ->filter(static fn ($grade) => $grade !== null && isset($gradeOrder[$grade]))
            ->unique()
            ->sortBy(static fn ($grade) => $gradeOrder[$grade])
            ->values()
            ->all();
    }
}
