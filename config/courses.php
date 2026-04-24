<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Kursni email kod bilan tasdiqlash
    |--------------------------------------------------------------------------
    |
    | Bu sozlama endi global mail switch bilan birga ishlaydi.
    | MAIL_DELIVERY_ENABLED=true bo'lsa kurs email kodi ishlaydi.
    | MAIL_DELIVERY_ENABLED=false bo'lsa kurs darhol saytda (published) bo'ladi.
    |
    | .env: MAIL_DELIVERY_ENABLED=false
    |
    */

    'require_email_verification' => filter_var(
        env('MAIL_DELIVERY_ENABLED', true),
        FILTER_VALIDATE_BOOLEAN
    ),

];
