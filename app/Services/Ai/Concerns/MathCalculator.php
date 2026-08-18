<?php

namespace App\Services\Ai\Concerns;

use Illuminate\Support\Str;

/**
 * Oddiy hisob-kitoblar uchun match metodlari.
 * AiService dan ajratilgan trait.
 */
trait MathCalculator
{
    /**
     * Oddiy matematik ifodani hisoblash: 2+2, 100-50, 10*5, 20/4
     */
    private function matchSimpleCalculation(string $message): ?string
    {
        $expression = trim(str_replace(',', '.', $message));
        $expression = preg_replace('/[=?]+$/', '', $expression) ?? $expression;
        $expression = preg_replace('/\s+/u', '', $expression) ?? $expression;

        if ($expression === '' || mb_strlen($expression) > 80) {
            return null;
        }

        if (! preg_match('/\d/', $expression) || ! preg_match('/[+\-*\/()%]/', $expression)) {
            return null;
        }

        if (! preg_match('/^[0-9+\-*\/().%]+$/', $expression)) {
            return null;
        }

        $result = $this->evaluateMathExpression($expression);

        if ($result === null) {
            return null;
        }

        return 'Javob: **'.$this->formatMathResult($result).'**.';
    }

    /**
     * Foiz hisoblash: "100 dan 25 necha foiz"
     */
    private function matchPercentCalculation(string $message): ?string
    {
        $normalized = $this->normalizeSearchText($message);

        if (preg_match('/\b(\d+(?:[.,]\d+)?)\s+dan\s+(\d+(?:[.,]\d+)?)\s+necha\s+foiz\b/u', $normalized, $matches) !== 1) {
            return null;
        }

        $base = (float) str_replace(',', '.', (string) $matches[1]);
        $value = (float) str_replace(',', '.', (string) $matches[2]);

        if ($base <= 0) {
            return "Foizni hisoblash uchun asosiy son 0 dan katta bo'lishi kerak.";
        }

        $percent = round(($value / $base) * 100, 1);

        return "**{$value}**, **{$base}** dan taxminan **{$percent}%** bo'ladi.";
    }

    /**
     * Matematik ifodani shunting-yard algoritmi bilan hisoblash
     */
    private function evaluateMathExpression(string $expression): ?float
    {
        preg_match_all('/\d+(?:\.\d+)?|[()+\-*\/%]/', $expression, $matches);
        $tokens = $matches[0] ?? [];

        if ($tokens === [] || implode('', $tokens) !== $expression) {
            return null;
        }

        $precedence = [
            'u-' => 3,
            '*' => 2,
            '/' => 2,
            '%' => 2,
            '+' => 1,
            '-' => 1,
        ];

        $output = [];
        $operators = [];
        $prevType = 'start';

        foreach ($tokens as $token) {
            if (is_numeric($token)) {
                $output[] = $token;
                $prevType = 'number';

                continue;
            }

            if ($token === '(') {
                $operators[] = $token;
                $prevType = 'left_paren';

                continue;
            }

            if ($token === ')') {
                while ($operators !== [] && end($operators) !== '(') {
                    $output[] = array_pop($operators);
                }

                if ($operators === [] || end($operators) !== '(') {
                    return null;
                }

                array_pop($operators);
                $prevType = 'right_paren';

                continue;
            }

            $operator = $token;
            if (($token === '-' || $token === '+') && in_array($prevType, ['start', 'operator', 'left_paren'], true)) {
                if ($token === '+') {
                    continue;
                }

                $operator = 'u-';
            }

            while ($operators !== [] && end($operators) !== '(') {
                $top = end($operators);
                $topPrecedence = $precedence[$top] ?? 0;
                $operatorPrecedence = $precedence[$operator] ?? 0;
                $rightAssociative = $operator === 'u-';

                if ($topPrecedence > $operatorPrecedence || ($topPrecedence === $operatorPrecedence && ! $rightAssociative)) {
                    $output[] = array_pop($operators);

                    continue;
                }

                break;
            }

            $operators[] = $operator;
            $prevType = 'operator';
        }

        if (in_array($prevType, ['start', 'operator', 'left_paren'], true)) {
            return null;
        }

        while ($operators !== []) {
            $operator = array_pop($operators);

            if ($operator === '(') {
                return null;
            }

            $output[] = $operator;
        }

        $stack = [];

        foreach ($output as $token) {
            if (is_numeric($token)) {
                $stack[] = (float) $token;

                continue;
            }

            if ($token === 'u-') {
                if ($stack === []) {
                    return null;
                }

                $stack[] = -array_pop($stack);

                continue;
            }

            if (count($stack) < 2) {
                return null;
            }

            $right = array_pop($stack);
            $left = array_pop($stack);

            $value = match ($token) {
                '+' => $left + $right,
                '-' => $left - $right,
                '*' => $left * $right,
                '/' => abs($right) < 1.0E-12 ? null : $left / $right,
                '%' => abs($right) < 1.0E-12 ? null : fmod($left, $right),
                default => null,
            };

            if ($value === null || is_nan($value) || is_infinite($value)) {
                return null;
            }

            $stack[] = $value;
        }

        if (count($stack) !== 1) {
            return null;
        }

        return $stack[0];
    }

    private function formatMathResult(float $value): string
    {
        if (abs($value - round($value)) < 1.0E-10) {
            return (string) (int) round($value);
        }

        return rtrim(rtrim(number_format($value, 10, '.', ''), '0'), '.');
    }

    /**
     * Sana farqini hisoblash: "imtihongacha necha kun qoldi"
     */
    private function matchDateDifferenceCalculation(string $message): ?string
    {
        $q = $this->normalizeSearchText($message);

        if (! Str::contains($q, ['kun', 'qold', 'qolgan', 'necha kun', 'necha soat', 'imtihon'])) {
            return null;
        }

        if (Str::contains($q, ['imtihon'])) {
            $nearestExam = \App\Models\Exam::query()
                ->where('is_active', true)
                ->whereNotNull('available_from')
                ->where('available_from', '>=', now())
                ->orderBy('available_from')
                ->first();

            if (! $nearestExam || ! $nearestExam->available_from) {
                return null;
            }

            $diffInHours = now()->diffInHours($nearestExam->available_from, false);
            if ($diffInHours < 0) {
                return null;
            }

            $diffInDays = now()->diffInDays($nearestExam->available_from);
            $startLabel = $nearestExam->availableFromLabel() ?? $nearestExam->available_from->format('d.m.Y H:i');

            if ($diffInDays >= 1) {
                return "Eng yaqin faol imtihon **{$nearestExam->title}**. Boshlanishigacha taxminan **{$diffInDays} kun** qoldi ({$startLabel}).";
            }

            return "Eng yaqin faol imtihon **{$nearestExam->title}**. Boshlanishigacha taxminan **{$diffInHours} soat** qoldi ({$startLabel}).";
        }

        if (preg_match('/\b(\d{4})[.\-\/](\d{2})[.\-\/](\d{2})\b/', $message, $matches) === 1) {
            $target = \Illuminate\Support\Carbon::createFromDate((int) $matches[1], (int) $matches[2], (int) $matches[3])->startOfDay();
            $now = now()->startOfDay();
            $diff = $now->diffInDays($target, false);

            if ($diff === 0) {
                return 'Bu sana **bugun**.';
            }

            if ($diff > 0) {
                return "Bu sanagacha taxminan **{$diff} kun** qoldi.";
            }

            return 'Bu sana **'.abs($diff)." kun oldin** o'tgan.";
        }

        return null;
    }
}
