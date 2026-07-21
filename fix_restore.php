<?php
$f = __DIR__ . '/app/Services/Ai/AiService.php';
$lines = file($f);
$total = count($lines);

// 4509 va 4589 (0-indexed: 4508 va 4588) — ikkinchi nusxalarni o'chirish
// matchFuzzySuggestion ikkinchi nusxasi: 4509-qatordan boshlanadi
// fuzzyMatchScore ikkinchi nusxasi: 4589-qatordan boshlanadi
// Ularni topib, keyingi metod boshlanguncha o'chiramiz

$result = [];
$skip = false;
$skipCount = 0;

for ($i = 0; $i < $total; $i++) {
    $t = trim($lines[$i]);

    // Ikkinchi matchFuzzySuggestion (4509, 0-indexed: 4508)
    if ($i === 4508 && strpos($lines[$i], 'private function matchFuzzySuggestion') !== false) {
        $skip = true;
    }

    if ($skip) {
        // callGemini metodiga yetguncha o'chiramiz
        if (strpos($lines[$i], 'private function callGemini') !== false) {
            $skip = false;
            $result[] = $lines[$i];
        }
        continue;
    }

    $result[] = $lines[$i];
}

file_put_contents($f, implode('', $result));
echo "done - " . count($result) . " lines\n";
