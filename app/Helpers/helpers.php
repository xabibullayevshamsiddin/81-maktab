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
