<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\Ai\AiService;

class SiteAiController extends Controller
{
    private const DAILY_QUESTION_LIMIT_PER_USER = 5;

    protected $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function generate(Request $request)
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => "AI yordamchidan foydalanish uchun avval ro'yxatdan o'ting va tizimga kiring.",
            ], 401);
        }

        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        if (! $user->isAdmin() && ! $this->consumeDailyQuestionQuota((int) $user->id)) {
            return response()->json([
                'success' => true,
                'text' => "📌 Sizning kunlik limitingiz tugadi. Kuniga faqat " . self::DAILY_QUESTION_LIMIT_PER_USER . " ta savol yubora olasiz. Ertaga yana yozib ko'ring. 😊",
                'source' => 'daily_limit',
            ]);
        }

        $userMessage = (string) $request->input('message');
        $messageCacheKey = 'ai:answer:' . sha1(mb_strtolower(trim($userMessage)));

        if ($cached = Cache::get($messageCacheKey)) {
            return response()->json([
                'success' => true,
                'text' => $cached,
                'source' => 'cache',
            ]);
        }

        $result = $this->aiService->generateResponse($userMessage, $user);

        if ($result['success']) {
            $aiText = $this->decorateAiText($result['text'], $userMessage);
            
            // Cache the result for 5 minutes during testing
            Cache::put($messageCacheKey, $aiText, now()->addMinutes(5));

            return response()->json([
                'success' => true,
                'text' => $aiText,
                'source' => $result['source'] ?? 'api',
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'] ?? "Kechirasiz, hozir javob bera olmadim.",
        ], 400);
    }

    private function decorateAiText(string $text, string $userMessage): string
    {
        $clean = trim((string) preg_replace('/\s+/u', ' ', $text));
        if ($clean === '') {
            return "✨ Kechirasiz, hozir javob bo'sh chiqdi. Iltimos, savolni qayta yuboring.";
        }

        $clean = preg_replace('/[*_#`>\-]+/u', '', $clean) ?? $clean;

        $q = mb_strtolower($userMessage);
        $isImportantQuery = Str::contains($q, [
            'muhim', 'qanday', 'nima', 'qayer', 'kim', 'narx', 'vaqt', 'muddat', 'aloqa',
        ]);

        if ($isImportantQuery && ! Str::contains(mb_strtolower($clean), 'muhim:')) {
            $parts = preg_split('/(?<=[.!?])\s+/u', $clean, 2);
            $first = trim((string) ($parts[0] ?? $clean));
            $rest = trim((string) ($parts[1] ?? ''));

            if ($rest !== '') {
                return "📌 MUHIM: {$first}\n✨ {$rest}";
            }
        }

        if (! preg_match('/[\x{1F300}-\x{1FAFF}✅✨📌🚀]/u', $clean)) {
            return "✨ {$clean}";
        }

        return $clean;
    }

    private function consumeDailyQuestionQuota(int $userId): bool
    {
        $todayKey = now()->format('Ymd');
        $counterKey = "ai:user:{$userId}:daily:{$todayKey}";
        $count = Cache::increment($counterKey);

        if ($count === 1) {
            $expiresInSeconds = max(60, now()->diffInSeconds(now()->copy()->endOfDay()));
            Cache::put($counterKey, 1, now()->addSeconds($expiresInSeconds));
            $count = 1;
        }

        return $count <= self::DAILY_QUESTION_LIMIT_PER_USER;
    }
}
