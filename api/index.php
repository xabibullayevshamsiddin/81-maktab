<?php

// Vercel serverless muhitida faqat /tmp papkasiga yozish mumkin.
// Laravel ishga tushishidan oldin kerakli /tmp papkalarni yaratib olamiz.
foreach ([
    '/tmp/storage/app/public',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/logs',
] as $dir) {
    if (! is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Barcha so'rovlarni Laravelning asosiy kirish fayliga yo'naltiramiz
require __DIR__ . '/../public/index.php';
