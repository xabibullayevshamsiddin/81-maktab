<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Kursni email kod bilan tasdiqlash
    |--------------------------------------------------------------------------
    |
    | Kurs email kodi faqat umumiy mail va OTP/kod yuborish flaglari ikkalasi
    | ham yoqilganda ishlaydi. Aks holda kurs darhol saytda (published) bo'ladi.
    |
    | .env:
    | MAIL_DELIVERY_ENABLED=true
    | MAIL_CODE_DELIVERY_ENABLED=false
    |
    */

    'require_email_verification' => filter_var(env('MAIL_DELIVERY_ENABLED', true), FILTER_VALIDATE_BOOLEAN)
        && filter_var(env('MAIL_CODE_DELIVERY_ENABLED', false), FILTER_VALIDATE_BOOLEAN),

];
