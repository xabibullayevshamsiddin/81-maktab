<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Kursni email kod bilan tasdiqlash
    |--------------------------------------------------------------------------
    |
    | true: kurs yaratilgach Gmail/emailga kod ketadi, keyin kod kiritiladi.
    | false: kurs darhol saytda (published), email yuborilmaydi.
    |
    | .env: COURSE_REQUIRE_EMAIL_VERIFICATION=false
    |
    */

    'require_email_verification' => filter_var(
        env('COURSE_REQUIRE_EMAIL_VERIFICATION', true),
        FILTER_VALIDATE_BOOLEAN
    ),

];
