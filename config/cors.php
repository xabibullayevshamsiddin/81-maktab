<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    "paths" => ["api/*", "sanctum/csrf-cookie"],

    "allowed_methods" => ["*"],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | Productionda aniq domenlar qo'yilishi kerak.
    | Local uchun APP_URL + localhost avtomatik qo'shiladi.
    |
    */

    "allowed_origins" => array_values(array_filter(array_unique([
        trim((string) env("APP_URL", "")),
        env("APP_ENV") === "local" ? "http://localhost:8000" : null,
        env("APP_ENV") === "local" ? "http://127.0.0.1:8000" : null,
        env("CORS_ALLOWED_ORIGINS", null),
    ]))),

    "allowed_origins_patterns" => [],

    "allowed_headers" => ["*"],

    "exposed_headers" => [],

    "max_age" => 0,

    /*
    |--------------------------------------------------------------------------
    | Supports Credentials
    |--------------------------------------------------------------------------
    |
    | Agar Sanctum SPA auth ishlatilsa true qilish kerak.
    | True bo'lganda allowed_origins ga aniq domen yozilishi shart (* ishlamaydi).
    |
    */

    "supports_credentials" => env("CORS_SUPPORTS_CREDENTIALS", false),

];