<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo 'locale=' . app()->getLocale() . PHP_EOL;
echo 'trans=' . trans('public.layout.group_chat') . PHP_EOL;
echo 'raw=' . __('public.layout.group_chat') . PHP_EOL;
echo 'lang_path=' . lang_path() . PHP_EOL;
echo 'resource_lang=' . base_path('resources/lang') . PHP_EOL;
