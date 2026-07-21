<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== COURSES ===\n";
$courses = DB::table('courses')->whereNotNull('image')->where('image','!=','')->pluck('image')->take(5);
foreach ($courses as $img) {
    $url = app_storage_asset($img);
    $file = storage_path('app/public/'.ltrim(str_replace('storage/', '', $img), '/'));
    echo "DB: $img\n";
    echo "URL: $url\n";
    echo "File exists: ".(file_exists($file) ? 'YES' : 'NO')."\n\n";
}

echo "=== TEACHERS ===\n";
$teachers = DB::table('teachers')->whereNotNull('image')->where('image','!=','')->pluck('image')->take(3);
foreach ($teachers as $img) {
    $url = app_storage_asset($img);
    echo "DB: $img\n";
    echo "URL: $url\n\n";
}

echo "=== POSTS ===\n";
$posts = DB::table('posts')->whereNotNull('image')->where('image','!=','')->pluck('image')->take(3);
foreach ($posts as $img) {
    $url = app_storage_asset($img);
    echo "DB: $img\n";
    echo "URL: $url\n\n";
}
