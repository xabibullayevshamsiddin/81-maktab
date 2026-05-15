<?php

namespace Tests\Unit;

use Tests\TestCase;

class LocalizationTest extends TestCase
{
    public function test_russian_locale_is_supported(): void
    {
        $this->assertArrayHasKey('ru', supported_locales());
        $this->assertSame('RU', supported_locales()['ru']);
    }

    public function test_public_translation_keys_are_complete_for_supported_locales(): void
    {
        $baseKeys = $this->flattenTranslationKeys(require lang_path('uz/public.php'));

        foreach (['en', 'ru'] as $locale) {
            $localeKeys = $this->flattenTranslationKeys(require lang_path("{$locale}/public.php"));

            $this->assertSame([], array_values(array_diff($baseKeys, $localeKeys)), "{$locale} public translations are missing keys.");
            $this->assertSame([], array_values(array_diff($localeKeys, $baseKeys)), "{$locale} public translations have extra keys.");
        }
    }

    public function test_secondary_translation_files_exist_for_english_and_russian(): void
    {
        foreach (['auth_pages', 'profile', 'pagination'] as $file) {
            $baseKeys = $this->flattenTranslationKeys(require lang_path("uz/{$file}.php"));

            foreach (['en', 'ru'] as $locale) {
                $localeKeys = $this->flattenTranslationKeys(require lang_path("{$locale}/{$file}.php"));

                $this->assertSame([], array_values(array_diff($baseKeys, $localeKeys)), "{$locale}/{$file}.php is missing keys.");
                $this->assertSame([], array_values(array_diff($localeKeys, $baseKeys)), "{$locale}/{$file}.php has extra keys.");
            }
        }
    }

    public function test_russian_static_public_text_is_resolved(): void
    {
        app()->setLocale('ru');

        $this->assertSame('Главная', __('public.layout.nav.home'));
        $this->assertSame('81-IDUM | О школе', __('public.about.page_title'));
        $this->assertSame('Уведомления', __('public.layout.notifications'));
    }

    /**
     * @return list<string>
     */
    private function flattenTranslationKeys(array $translations, string $prefix = ''): array
    {
        $keys = [];

        foreach ($translations as $key => $value) {
            $path = $prefix === '' ? (string) $key : "{$prefix}.{$key}";

            if (is_array($value) && ! array_is_list($value)) {
                array_push($keys, ...$this->flattenTranslationKeys($value, $path));

                continue;
            }

            $keys[] = $path;
        }

        sort($keys);

        return $keys;
    }
}
