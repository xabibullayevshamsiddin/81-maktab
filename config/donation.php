<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tolov tizimlari sozlamalari
    |--------------------------------------------------------------------------
    */

    "click_enabled" => (bool) env("CLICK_ENABLED", false),
    "click_merchant_id" => env("CLICK_MERCHANT_ID", ""),
    "click_secret_key" => env("CLICK_SECRET_KEY", ""),
    "click_service_id" => env("CLICK_SERVICE_ID", ""),
    "click_merchant_user_id" => env("CLICK_MERCHANT_USER_ID", ""),

    "payme_enabled" => (bool) env("PAYME_ENABLED", false),
    "payme_merchant_id" => env("PAYME_MERCHANT_ID", ""),
    "payme_secret_key" => env("PAYME_SECRET_KEY", ""),

    "stripe_enabled" => (bool) env("STRIPE_ENABLED", false),
    "stripe_key" => env("STRIPE_KEY", ""),
    "stripe_secret" => env("STRIPE_SECRET", ""),

    /*
    |--------------------------------------------------------------------------
    | Rank narxlari
    |--------------------------------------------------------------------------
    */

    "prices" => [
        "supporter" => (int) env("DONATION_SUPPORTER_PRICE", 15000),
        "premium" => (int) env("DONATION_PREMIUM_PRICE", 35000),
        "vip" => (int) env("DONATION_VIP_PRICE", 75000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Avatar hajmi limitlari
    |--------------------------------------------------------------------------
    */

    "default_max_avatar_kb" => 4096,
];