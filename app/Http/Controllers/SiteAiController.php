<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Services\Ai\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
                    "AI yordamchi vaqtincha o'chirilgan. Keyinroq urinib ko'ring."
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
                    : "Mock javob bo'sh. Ajratgichdan keyin javob matnini yozing.",
                'source' => 'local_mock',
            ]);
        }

        $userMessage = (string) $request->input('message');
        $normalizedMessage = mb_strtolower(trim($userMessage));
        $messageCacheKey = 'ai:answer:user:' . (int) $user->id . ':' . sha1($normalizedMessage);

        if ($cached = Cache::get($messageCacheKey)) {
            return response()->json([
                'success' => true,
                'text' => $cached,
                'source' => 'cache',
            ]);
        }

        $userLimit = $this->getUserDailyLimit($user);
        if ($userLimit !== -1 && ! $this->consumeDailyQuestionQuota((int) $user->id, $userLimit)) {
            return response()->json([
                'success' => true,
                'text' => "Sizning kunlik limitingiz tugadi. Kuniga faqat {$userLimit} ta savol yubora olasiz. Ertaga yana yozib ko'ring.",
                'source' => 'daily_limit',
            ]);
        }

        $result = $this->aiService->generateResponse($userMessage, $user);

        if ($result['success']) {
            $aiText = $this->decorateAiText($result['text'], $userMessage);

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
        $clean = trim((string) $text);
        $clean = str_replace(["\r\n", "\r"], "\n", $clean);
        $clean = preg_replace("/[ \t]+/u", ' ', $clean) ?? $clean;
        $clean = preg_replace("/\n{3,}/u", "\n\n", $clean) ?? $clean;

        if ($clean === '') {
            return "Kechirasiz, hozir javob bo'sh chiqdi. Iltimos, savolni qayta yuboring.";
        }

        $q = mb_strtolower($userMessage);

        if (Str::contains($q, ['muhim', 'shoshilinch', 'urgent'])
            && ! Str::contains(mb_strtolower($clean), 'muhim:')) {
            if (preg_match('/(?<=[.!?])\s+/u', $clean)) {
                $parts = preg_split('/(?<=[.!?])\s+/u', $clean, 2);
                $first = trim((string) ($parts[0] ?? $clean));
                $rest = trim((string) ($parts[1] ?? ''));

                if ($rest !== '') {
                    return "MUHIM: {$first}\n\n{$rest}";
                }
            }
        }

        return $clean;
    }

    private function getUserDailyLimit($user): int
    {
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return -1;
        }
        if ($user->isTeacher() || $user->isEditor() || $user->isModerator()) {
            return 10;
        }

        return 7;
    }

    private function consumeDailyQuestionQuota(int $userId, int $limit): bool
    {
        $todayKey = now()->format('Ymd');
        $counterKey = "ai:user:{$userId}:daily:{$todayKey}";
        $count = Cache::increment($counterKey);

        if ($count === 1) {
            $expiresInSeconds = max(60, now()->diffInSeconds(now()->copy()->endOfDay()));
            Cache::put($counterKey, 1, now()->addSeconds($expiresInSeconds));
            $count = 1;
        }

        return $count <= $limit;
    }
}
