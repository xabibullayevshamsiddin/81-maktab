<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Kursni email kod bilan tasdiqlash
    |--------------------------------------------------------------------------
    |
    | Kurs email kodi umumiy mail flagiga ergashadi. Agar alohida boshqarish
    | kerak bo'lsa, MAIL_CODE_DELIVERY_ENABLED orqali override qilinadi.
    | Aks holda kurs darhol saytda (published) bo'ladi.
    |
    | .env:
    | MAIL_DELIVERY_ENABLED=false
    | MAIL_CODE_DELIVERY_ENABLED=false
    |
    */

    'require_email_verification' => filter_var(env('MAIL_DELIVERY_ENABLED', false), FILTER_VALIDATE_BOOLEAN)
        && filter_var(
            env('MAIL_CODE_DELIVERY_ENABLED', env('MAIL_DELIVERY_ENABLED', false)),
            FILTER_VALIDATE_BOOLEAN
        ),

];
