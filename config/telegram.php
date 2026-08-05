<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Configuration
    |--------------------------------------------------------------------------
    |
    | TELEGRAM_BOT_TOKEN — BotFather dan olingan token
    | TELEGRAM_BOT_USERNAME — Bot username (@ belgisisiz)
    | TELEGRAM_WEBHOOK_SECRET — Webhookni himoya qilish uchun tasodifiy satr
    |
    */

    'bot_token' => env('TELEGRAM_BOT_TOKEN', ''),
    'bot_username' => env('TELEGRAM_BOT_USERNAME', ''),
    'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | API base URL
    |--------------------------------------------------------------------------
    */
    'api_base' => 'https://api.telegram.org',

    /*
    |--------------------------------------------------------------------------
    | Verification settings
    |--------------------------------------------------------------------------
    |
    | token_length — Tasodifiy token uzunligi
    | expires_minutes — Verifikatsiya muddati (daqiqa)
    | cleanup_interval — Eskirgan yozuvlarni tozalash intervali
    |
    */

    'token_length' => 40,
    'expires_minutes' => 10,
    'cleanup_interval' => 60, // daqiqa

];
