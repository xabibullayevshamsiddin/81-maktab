<?php

define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Ai\AiService;
use App\Models\AiKnowledge;

$aiService = app(AiService::class);

$testQuestions = [
    'Maktab qachon ochilgan?', // Should match seeded #1
    'maktab qacon ocilgan',    // Typo test
    'Manzilini aytvor',        // Synonym test
    'Nimalar o\'qitiladi?',    // Should match seeded #3
    'Salom, ishlar qalay?',    // Persona test
    'kusrlar haqida malumot ber', // Fuzzy category test
    'Direktor kim?',           // Should NOT match, hit fallback
    'Mening natijalarim?',     // Personalized test (need user)
];

echo "AI MATCHING DEBUG REPORT\n";
echo "=========================\n\n";

foreach ($testQuestions as $q) {
    echo "Q: $q\n";
    $result = $aiService->generateResponse($q, null); // Test as guest
    echo "Source: " . ($result['source'] ?? 'N/A') . "\n";
    echo "Result: " . Str::limit($result['text'], 100) . "\n";
    echo "-------------------------\n";
}
