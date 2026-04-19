<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\SiteSetting;
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

        if (SiteSetting::get('ai_chat_enabled', '1') !== '1') {
            return response()->json([
                'success' => false,
                'error' => SiteSetting::get(
                    'ai_chat_disabled_message',
                    'AI yordamchi vaqtincha o‘chirilgan. Keyinroq urinib ko‘ring.'
                ),
                'disabled' => true,
            ], 403);
        }

        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $rawMessage = (string) $request->input('message');
        $mockDelimiter = (string) config('ai.mock_delimiter', '<<<MOCK>>>');

        /*
         * Mock: bitta maydonda `savol + ajratgich + javob` (Gemini chaqirilmaydi).
         * Local: har kim; host: faqat admin + AI_LOCAL_MOCK_ON_HOST.
         */
        $mockAllowed = config('ai.local_mock')
            && (
                app()->environment('local')
                || (config('ai.local_mock_on_host') && $user->isAdmin())
            );

        if ($mockAllowed && str_contains($rawMessage, $mockDelimiter)) {
            $parts = explode($mockDelimiter, $rawMessage, 2);
            $questionPart = trim((string) ($parts[0] ?? ''));
            $mock = trim((string) ($parts[1] ?? ''));
            Log::debug('AI local mock response', ['user_id' => $user->id, 'env' => app()->environment()]);

            return response()->json([
                'success' => true,
                'text' => $mock !== ''
                    ? $this->decorateAiText($mock, $questionPart !== '' ? $questionPart : $rawMessage)
                    : "✨ Mock javob bo'sh. Ajratgichdan keyin javob matnini yozing.",
                'source' => 'local_mock',
            ]);
        }

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

        // Markdown belgilarini olib tashlaymiz
        $clean = preg_replace('/[*_#`>\-]+/u', '', $clean) ?? $clean;

        $q = mb_strtolower($userMessage);

        // Muhim savollarga MUHIM: prefiksi
        $importantKeywords = ['muhim', 'qanday', 'nima', 'qayer', 'kim', 'narx', 'vaqt', 'muddat', 'aloqa'];
        if (Str::contains($q, $importantKeywords) && ! Str::contains(mb_strtolower($clean), 'muhim:')) {
            if (preg_match('/(?<=[.!?])\s+/u', $clean)) {
                $parts = preg_split('/(?<=[.!?])\s+/u', $clean, 2);
                $first = trim((string) ($parts[0] ?? $clean));
                $rest  = trim((string) ($parts[1] ?? ''));
                if ($rest !== '') {
                    return "📌 MUHIM: {$first}\n✨ {$rest}";
                }
            }
        }

        // Emoji yo'q bo'lsa — ✨ qo'shamiz
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
