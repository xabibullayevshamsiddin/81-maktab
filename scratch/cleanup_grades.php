<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Starting grade cleanup...\n";

DB::transaction(function() {
    $users = User::whereNotNull('grade')->where('grade', '!=', '')->get();
    
    foreach ($users as $user) {
        $oldGrade = $user->grade;
        $newGrade = strtoupper(trim((string)$oldGrade));
        
        // Remove YT (Yakka Tartib)
        $newGrade = str_replace('YT', '', $newGrade);
        
        // Convert Cyrillic to Latin
        $newGrade = str_replace(
            ['Б', 'Д', 'Г', 'В', 'К', 'З', 'А', 'E', 'О'], 
            ['B', 'D', 'G', 'V', 'K', 'Z', 'A', 'E', 'O'], 
            $newGrade
        );
        
        // Normalize format (remove spaces, dots, etc.)
        $newGrade = preg_replace('/[^0-9A-Z-]/', '', $newGrade);
        
        // Ensure N-X format
        if (preg_match('/^(\d+)([A-Z])$/', $newGrade, $m)) {
            $newGrade = $m[1] . '-' . $m[2];
        }
        
        if ($oldGrade !== $newGrade) {
            $user->grade = $newGrade;
            $user->save();
            echo "Updated User #{$user->id}: '{$oldGrade}' -> '{$newGrade}'\n";
        }
    }
});

echo "Cleanup finished.\n";
