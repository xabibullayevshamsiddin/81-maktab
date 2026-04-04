<?php

if (! function_exists('truncate_words')) {
    function truncate_words(string $text, int $words = 20, string $end = '...'): string
    {
        $text = strip_tags($text);
        $wordsArray = explode(' ', $text);
        if (count($wordsArray) <= $words) {
            return $text;
        }

        return implode(' ', array_slice($wordsArray, 0, $words)).$end;
    }
}

if (! function_exists('format_date')) {
    function format_date($date, string $format = 'd.m.Y'): string
    {
        if (! $date) {
            return '';
        }

        return \Carbon\Carbon::parse($date)->format($format);
    }
}

if (! function_exists('format_datetime')) {
    function format_datetime($date, string $format = 'd.m.Y H:i'): string
    {
        if (! $date) {
            return '';
        }

        return \Carbon\Carbon::parse($date)->format($format);
    }
}

if (! function_exists('time_ago')) {
    function time_ago($date): string
    {
        if (! $date) {
            return '';
        }

        return \Carbon\Carbon::parse($date)->diffForHumans();
    }
}

if (! function_exists('active_route')) {
    function active_route(string $route, string $class = 'active'): string
    {
        return request()->routeIs($route) ? $class : '';
    }
}

if (! function_exists('is_url')) {
    function is_url(string $string): bool
    {
        return filter_var($string, FILTER_VALIDATE_URL) !== false;
    }
}

if (! function_exists('get_file_size')) {
    function get_file_size(int $bytes, int $decimals = 2): string
    {
        $size = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen((string) $bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)).@$size[$factor];
    }
}

if (! function_exists('slugify')) {
    function slugify(string $text, string $separator = '-'): string
    {
        return \Illuminate\Support\Str::slug($text, $separator);
    }
}

if (! function_exists('random_color')) {
    function random_color(): string
    {
        $colors = ['primary', 'success', 'danger', 'warning', 'info'];

        return $colors[array_rand($colors)];
    }
}

if (! function_exists('number_format_uz')) {
    function number_format_uz(int|float $number): string
    {
        return number_format($number, 0, '.', ' ');
    }
}

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
