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

];
