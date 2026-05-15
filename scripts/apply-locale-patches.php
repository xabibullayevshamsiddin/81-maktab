<?php

/**
 * Merge exam.session + new sections into en/public.php and ru/public.php
 */

$patchEn = require __DIR__.'/locale-patch-en.php';
$patchRu = require __DIR__.'/locale-patch-ru.php';

function mergeRecursive(array $base, array $patch): array
{
    foreach ($patch as $key => $value) {
        if (is_array($value) && isset($base[$key]) && is_array($base[$key]) && ! array_is_list($value)) {
            $base[$key] = mergeRecursive($base[$key], $value);
        } else {
            $base[$key] = $value;
        }
    }

    return $base;
}

function exportPhp(array $data, string $path): void
{
    $export = var_export($data, true);
    $export = preg_replace('/^/m', '    ', $export);
    $content = "<?php\n\nreturn ".$export.";\n";
    file_put_contents($path, $content);
}

$enPath = __DIR__.'/../resources/lang/en/public.php';
$ruPath = __DIR__.'/../resources/lang/ru/public.php';

$en = mergeRecursive(require $enPath, $patchEn);
exportPhp($en, $enPath);

$ruFull = mergeRecursive(require __DIR__.'/../resources/lang/en/public.php', $patchRu);
// ru file is override-only — rebuild as merge en base + ru overrides from existing ru file
$ruOverridesOnly = require $ruPath;
$ruMerged = mergeRecursive(require $enPath, $ruOverridesOnly);
$ruMerged = mergeRecursive($ruMerged, $patchRu);

// Write ru as override file (only keys that differ from en or are ru-specific)
$ruExport = "<?php\n\n\$fallback = require __DIR__.'/../en/public.php';\n\nreturn array_replace_recursive(\$fallback, [\n";
// Too complex — write full merged ru public as standalone
exportPhp($ruMerged, $ruPath);

echo "Patched en and ru public.php\n";
