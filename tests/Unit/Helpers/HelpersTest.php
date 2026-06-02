<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;

class HelpersTest extends TestCase
{
    /**
     * LIKE Wildcard Escape Tests
     */
    public function test_escape_like_wildcards_escapes_percent(): void
    {
        $result = escape_like_wildcards('100%');

        $this->assertEquals('100\\%', $result);
    }

    public function test_escape_like_wildcards_escapes_underscore(): void
    {
        $result = escape_like_wildcards('hello_world');

        $this->assertEquals('hello\\_world', $result);
    }

    public function test_escape_like_wildcards_escapes_backslash(): void
    {
        $result = escape_like_wildcards('path\\to\\file');

        $this->assertEquals('path\\\\to\\\\file', $result);
    }

    public function test_escape_like_wildcards_returns_empty_for_null(): void
    {
        $result = escape_like_wildcards(null);

        $this->assertEquals('', $result);
    }

    public function test_escape_like_wildcards_handles_normal_text(): void
    {
        $result = escape_like_wildcards('hello world');

        $this->assertEquals('hello world', $result);
    }

    public function test_safe_like_query_wraps_with_wildcards(): void
    {
        $result = safe_like_query('test');

        $this->assertEquals('%test%', $result);
    }

    public function test_safe_like_query_escapes_dangerous_input(): void
    {
        $result = safe_like_query('%admin%');

        $this->assertEquals('%\\%admin\\%%', $result);
    }

    public function test_safe_like_query_escapes_underscore_wildcard(): void
    {
        $result = safe_like_query('a_b');

        $this->assertEquals('%a\\_b%', $result);
    }

    /**
     * Phone Validation Tests
     */
    public function test_uz_phone_rules_required(): void
    {
        $rules = uz_phone_rules(true);

        $this->assertContains('required', $rules);
        $this->assertContains('string', $rules);
    }

    public function test_uz_phone_rules_nullable(): void
    {
        $rules = uz_phone_rules(false);

        $this->assertContains('nullable', $rules);
        $this->assertNotContains('required', $rules);
    }

    public function test_uz_phone_normalize_removes_spaces(): void
    {
        $result = uz_phone_normalize('+998 90 123 45 67');

        $this->assertEquals('+998901234567', $result);
    }

    public function test_uz_phone_normalize_returns_null_for_empty(): void
    {
        $this->assertNull(uz_phone_normalize(''));
        $this->assertNull(uz_phone_normalize(null));
        $this->assertNull(uz_phone_normalize('   '));
    }

    /**
     * School Grade Tests
     */
    public function test_school_grade_options_returns_array(): void
    {
        $options = school_grade_options();

        $this->assertIsArray($options);
        $this->assertNotEmpty($options);
    }

    public function test_school_grade_options_contains_valid_grades(): void
    {
        $options = school_grade_options();

        $this->assertContains('1-A', $options);
        $this->assertContains('9-A', $options);
        $this->assertContains('11-V', $options);
    }

    public function test_normalize_school_grade_formats_correctly(): void
    {
        $this->assertEquals('9-A', normalize_school_grade('9-A'));
        $this->assertEquals('9-A', normalize_school_grade('9A'));
        $this->assertEquals('9-A', normalize_school_grade(' 9-A '));
        $this->assertEquals('9-A', normalize_school_grade('9-a'));
    }

    public function test_normalize_school_grade_returns_null_for_empty(): void
    {
        $this->assertNull(normalize_school_grade(null));
        $this->assertNull(normalize_school_grade(''));
    }

    public function test_school_grade_grouped_options_has_correct_structure(): void
    {
        $groups = school_grade_grouped_options();

        $this->assertIsArray($groups);
        $this->assertArrayHasKey('1-sinf', $groups);
        $this->assertArrayHasKey('11-sinf', $groups);
    }

    /**
     * Sanitize Plain Text Tests
     */
    public function test_sanitize_plain_text_removes_html_tags(): void
    {
        $result = sanitize_plain_text('<script>alert("xss")</script>Hello');

        $this->assertEquals('alert("xss")Hello', $result);
    }

    public function test_sanitize_plain_text_removes_control_characters(): void
    {
        $result = sanitize_plain_text("Hello\x00World\x0B");

        $this->assertEquals('HelloWorld', $result);
    }

    public function test_sanitize_plain_text_trims_whitespace(): void
    {
        $result = sanitize_plain_text('  hello  ');

        $this->assertEquals('hello', $result);
    }

    public function test_sanitize_plain_text_handles_null(): void
    {
        $result = sanitize_plain_text(null);

        $this->assertEquals('', $result);
    }

    /**
     * Locale Tests
     */
    public function test_supported_locales_returns_uz_and_en(): void
    {
        $locales = supported_locales();

        $this->assertArrayHasKey('uz', $locales);
        $this->assertArrayHasKey('en', $locales);
    }

    public function test_current_locale_returns_valid_locale(): void
    {
        $locale = current_locale();

        $this->assertContains($locale, ['uz', 'en']);
    }

    /**
     * Cache Key Tests
     */
    public function test_cache_key_home_posts_returns_string(): void
    {
        $key = cache_key_home_posts();

        $this->assertIsString($key);
        $this->assertNotEmpty($key);
    }

    public function test_cache_key_public_post_categories_returns_string(): void
    {
        $key = cache_key_public_post_categories();

        $this->assertIsString($key);
        $this->assertNotEmpty($key);
    }

    /**
     * Turnstile Tests
     */
    public function test_turnstile_enabled_returns_boolean(): void
    {
        $result = turnstile_enabled();

        $this->assertIsBool($result);
    }

    /**
     * Gmail URL Tests
     */
    public function test_gmail_compose_url_generates_valid_url(): void
    {
        $url = gmail_compose_url('test@example.com', 'Hello', 'World');

        $this->assertStringContainsString('mail.google.com', $url);
        $this->assertStringContainsString('test%40example.com', $url);
        $this->assertStringContainsString('Hello', $url);
    }

    public function test_gmail_compose_url_without_optional_params(): void
    {
        $url = gmail_compose_url('test@example.com');

        $this->assertStringContainsString('mail.google.com', $url);
        $this->assertStringContainsString('test%40example.com', $url);
    }
}
