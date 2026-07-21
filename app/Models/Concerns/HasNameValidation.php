<?php

namespace App\Models\Concerns;

/**
 * Trait HasNameValidation
 * 
 * Handles name validation and formatting for User model.
 * Includes first name, last name validation rules and uniqueness checks.
 */
trait HasNameValidation
{
    /**
     * Name Validation Rules
     */
    public static function nameValidationRules(bool $required = true): array
    {
        $base = ['string', 'max:120', 'regex:/^[\p{L}\s\'\-\.]+$/u'];

        return $required ? array_merge(['required'], $base) : array_merge(['nullable'], $base);
    }

    public static function nameValidationMessage(): string
    {
        return 'Faqat harflar, probel, apostrof va defis ishlatilishi mumkin.';
    }

    /**
     * Name Building
     */
    public function buildNameFromParts(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    /**
     * Check if same first name + last name combination already exists (case-insensitive).
     * Individual first name or last name repetition is allowed.
     */
    public static function isFullNameTaken(string $firstName, string $lastName): bool
    {
        $fn = mb_strtolower(trim($firstName));
        $ln = mb_strtolower(trim($lastName));
        
        if ($fn === '' || $ln === '') {
            return false;
        }

        return static::query()
            ->whereRaw('LOWER(TRIM(first_name)) = ?', [$fn])
            ->whereRaw('LOWER(TRIM(last_name)) = ?', [$ln])
            ->exists();
    }
}
