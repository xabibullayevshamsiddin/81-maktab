<?php

/**
 * One-off: copy missing top-level keys from uz/public.php structure into en and ru.
 * Run: php scripts/sync-locale-keys.php
 */

$uz = require __DIR__.'/../resources/lang/uz/public.php';
$enPath = __DIR__.'/../resources/lang/en/public.php';
$ruPath = __DIR__.'/../resources/lang/ru/public.php';

$en = require $enPath;
$ruBase = require __DIR__.'/../resources/lang/en/public.php';
$ruOverrides = require $ruPath;

function deepMergeMissing(array $base, array $source): array
{
    foreach ($source as $key => $value) {
        if (! array_key_exists($key, $base)) {
            $base[$key] = $value;
            continue;
        }
        if (is_array($value) && is_array($base[$key]) && ! array_is_list($value)) {
            $base[$key] = deepMergeMissing($base[$key], $value);
        }
    }

    return $base;
}

// For EN: only add keys that exist in uz but not en (values stay uz - will be replaced by separate pass)
$enMerged = deepMergeMissing($en, $uz);

// Export en - use var_export is ugly; we'll use json_encode for nested parts only for missing

// Instead: read en file and patch exam.session + new sections from dedicated en translations file
echo "UZ keys: ".count(flatten($uz))."\n";
echo "EN keys before: ".count(flatten($en))."\n";
echo "Missing in EN: ".count(array_diff(flatten($uz), flatten($enMerged)))."\n";

function flatten(array $a, string $p = ''): array
{
    $k = [];
    foreach ($a as $key => $value) {
        $path = $p === '' ? (string) $key : "{$p}.{$key}";
        if (is_array($value) && ! array_is_list($value)) {
            $k = array_merge($k, flatten($value, $path));
        } else {
            $k[] = $path;
        }
    }

    return $k;
}
