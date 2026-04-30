<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mock javob (o‘zingiz yozgan matnni qaytarish)
    |--------------------------------------------------------------------------
    |
    | AI_LOCAL_MOCK=true bo‘lsa:
    | - APP_ENV=local: har qanday tizimga kirgan foydalanuvchi `mock_answer` ishlata oladi.
    | - Boshqa muhit (host): faqat admin — AI_LOCAL_MOCK_ON_HOST=true bo‘lsa.
    |
    */
    'local_mock' => (bool) env('AI_LOCAL_MOCK', false),

    'local_mock_on_host' => (bool) env('AI_LOCAL_MOCK_ON_HOST', false),

    /*
    | Bitta xabarda mock: savol, keyin alohida qatorda ajratgich, keyin javob matni.
    */
    'mock_delimiter' => '<<<MOCK>>>',

    /*
    | AI javobida taqvim (CalendarEvent) — bitta savolda ko‘rsatiladigan yozuvlar
    | va har bir tadbir matnining maksimal uzunligi (xavfsizlik / token).
    */
    'calendar_max_events_per_answer' => (int) env('AI_CALENDAR_MAX_EVENTS', 15),
    'calendar_max_body_chars' => (int) env('AI_CALENDAR_MAX_BODY_CHARS', 280),

    /*
    |--------------------------------------------------------------------------
    | Conversation History Store
    |--------------------------------------------------------------------------
    |
    | Session bloat'ni oldini olish uchun AI suhbat history'ni alohida cache
    | store'da saqlaymiz. Production uchun Redis tavsiya etiladi.
    |
    */
    'history_store' => env('AI_HISTORY_CACHE_STORE', env('CACHE_LIMITER_STORE', env('CACHE_DRIVER', 'file'))),
    'history_ttl_minutes' => (int) env('AI_HISTORY_TTL_MINUTES', 180),
    'history_max_items' => (int) env('AI_HISTORY_MAX_ITEMS', 10),

    /*
    |--------------------------------------------------------------------------
    | Queue & Cache knobs
    |--------------------------------------------------------------------------
    */
    'response_cache_ttl_minutes' => (int) env('AI_RESPONSE_CACHE_TTL_MINUTES', 5),

];
