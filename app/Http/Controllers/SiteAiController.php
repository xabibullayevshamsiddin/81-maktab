<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ai\GenerateAiMessageRequest;
use App\Http\Requests\Ai\StoreAiFeedbackRequest;
use App\Http\Resources\Ai\AiFeedbackResource;
use App\Http\Resources\Ai\AiResponseResource;
use App\Models\AiInteraction;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use App\Services\Ai\AiService;
use App\Services\Ai\ConversationHistoryStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SiteAiController extends Controller
{
    private const SUPPORT_WIZARD_SESSION_KEY = 'ai_support_wizard';
    private const SUPPORT_WIZARD_TIMEOUT_MINUTES = 10;
    private static ?bool $aiInteractionsTableExists = null;

    public function __construct(
        private AiService $aiService,
        private ConversationHistoryStore $conversationHistoryStore,
    ) {}

    public function generate(GenerateAiMessageRequest $request): JsonResponse
    {
        $user = $request->user();

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

        $userMessage = $request->message();
        $mockDelimiter = (string) config('ai.mock_delimiter', '<<<MOCK>>>');
        $history = $this->getConversationHistory((int) $user->id);

        if ($wizardResponse = $this->handleSupportWizardFlow($request, $userMessage, $user)) {
            return $wizardResponse;
        }

        $conversationContext = $this->aiService->prepareConversationContext(
            $userMessage,
            $history
        );

        $mockAllowed = config('ai.local_mock')
            && (
                app()->environment('local')
                || (config('ai.local_mock_on_host') && $user->isAdmin())
            );

        if ($mockAllowed && str_contains($userMessage, $mockDelimiter)) {
            $parts = explode($mockDelimiter, $userMessage, 2);
            $questionPart = trim((string) ($parts[0] ?? ''));
            $mock = trim((string) ($parts[1] ?? ''));
            Log::debug('AI local mock response', ['user_id' => $user->id, 'env' => app()->environment()]);

            $payload = [
                'success' => true,
                'text' => $mock !== ''
                    ? $this->decorateAiText($mock, $questionPart !== '' ? $questionPart : $userMessage)
                    : "Mock javob bo'sh. Ajratgichdan keyin javob matnini yozing.",
                'source' => 'local_mock',
                'actions' => $this->aiService->suggestActions(
                    $questionPart !== '' ? $questionPart : $userMessage,
                    $user,
                    'local_mock',
                    $conversationContext
                ),
                'feedback_enabled' => true,
                'clarification_requested' => false,
                'support_converted' => false,
            ];

            return $this->respondWithInteraction($user, $userMessage, $payload);
        }

        $normalizedMessage = $this->aiService->normalizeQuestionForAnalytics($userMessage);
        $historySignature = (string) ($conversationContext['history_signature'] ?? 'no-context');
        $messageCacheKey = sprintf(
            'ai:answer:user:%d:message:%s:context:%s',
            (int) $user->id,
            sha1($normalizedMessage),
            sha1($historySignature)
        );

        Log::info('ai.generate.requested', [
            'user_id' => (int) $user->id,
            'message_hash' => sha1($normalizedMessage),
            'history_signature' => $historySignature,
            'history_items' => count($history),
        ]);

        if ($cached = Cache::get($messageCacheKey)) {
            $payload = is_array($cached)
                ? $cached
                : [
                    'success' => true,
                    'text' => (string) $cached,
                    'source' => 'cache',
                ];

            $payload['actions'] = $payload['actions'] ?? $this->aiService->suggestActions(
                $userMessage,
                $user,
                $payload['source'] ?? 'cache',
                $conversationContext
            );
            $payload['feedback_enabled'] = (bool) ($payload['feedback_enabled'] ?? (($payload['source'] ?? '') !== 'clarification'));
            $payload['clarification_requested'] = (bool) ($payload['clarification_requested'] ?? (($payload['source'] ?? '') === 'clarification'));
            $payload['support_converted'] = (bool) ($payload['support_converted'] ?? false);

            return $this->respondWithInteraction($user, $userMessage, $payload);
        }

        $userLimit = $this->getUserDailyLimit($user);
        if ($userLimit !== -1 && ! $this->consumeDailyQuestionQuota((int) $user->id, $userLimit)) {
            Log::warning('ai.generate.daily_limit_reached', [
                'user_id' => (int) $user->id,
                'limit' => $userLimit,
            ]);

            $payload = [
                'success' => true,
                'text' => "Sizning kunlik limitingiz tugadi. Kuniga faqat {$userLimit} ta savol yubora olasiz. Ertaga yana yozib ko'ring.",
                'source' => 'daily_limit',
                'actions' => [
                    [
                        'type' => 'link',
                        'label' => 'Profil',
                        'url' => route('profile.show'),
                        'route' => 'profile.show',
                    ],
                ],
                'feedback_enabled' => false,
                'clarification_requested' => false,
                'support_converted' => false,
            ];

            return $this->respondWithInteraction($user, $userMessage, $payload);
        }

        $result = $this->aiService->generateResponse($userMessage, $user, $conversationContext);

        if (! ($result['success'] ?? false)) {
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? "Kechirasiz, hozir javob bera olmadim.",
            ], 400);
        }

        $payload = [
            'success' => true,
            'text' => $this->decorateAiText((string) $result['text'], $userMessage),
            'source' => $result['source'] ?? 'api',
            'actions' => $result['actions'] ?? $this->aiService->suggestActions(
                $userMessage,
                $user,
                $result['source'] ?? 'api',
                $conversationContext
            ),
            'feedback_enabled' => ($result['source'] ?? '') !== 'clarification',
            'clarification_requested' => ($result['source'] ?? '') === 'clarification',
            'support_converted' => false,
            'context_applied' => (bool) ($conversationContext['context_applied'] ?? false),
        ];

        Cache::put($messageCacheKey, $payload, now()->addMinutes(max(1, (int) config('ai.response_cache_ttl_minutes', 5))));

        return $this->respondWithInteraction($user, $userMessage, $payload);
    }

    public function feedback(StoreAiFeedbackRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $interaction = AiInteraction::query()
            ->whereKey($validated['interaction_id'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $helpful = (bool) $validated['helpful'];
        $reason = Str::limit(
            sanitize_plain_text(trim((string) ($validated['reason'] ?? ''))),
            500,
            ''
        );
        $meta = is_array($interaction->meta) ? $interaction->meta : [];
        $meta['feedback_type'] = $helpful ? 'helpful' : 'unhelpful';
        $meta['feedback_at'] = now()->toIso8601String();

        if ($helpful) {
            unset($meta['feedback_reason']);
        } elseif ($reason !== '') {
            $meta['feedback_reason'] = $reason;
        }

        $interaction->update([
            'is_helpful' => $helpful,
            'is_unanswered' => $helpful ? (bool) $interaction->clarification_requested : true,
            'meta' => $meta,
        ]);

        return (new AiFeedbackResource([
            'success' => true,
            'message' => $helpful
                ? 'Fikringiz saqlandi. Rahmat!'
                : ($reason !== ''
                    ? 'Qabul qilindi. Sabab ham analyticsga saqlandi.'
                    : 'Qabul qilindi. Bu savolni yaxshilash uchun analyticsga qo\'shdim.'),
        ]))->response();
    }

    private function handleSupportWizardFlow(GenerateAiMessageRequest $request, string $message, $user): ?JsonResponse
    {
        $wizard = $request->session()->get(self::SUPPORT_WIZARD_SESSION_KEY);

        if (is_array($wizard)) {
            $startedAt = (int) ($wizard['started_at'] ?? 0);
            if ($startedAt > 0 && \Illuminate\Support\Carbon::createFromTimestamp($startedAt)->addMinutes(self::SUPPORT_WIZARD_TIMEOUT_MINUTES)->isPast()) {
                $request->session()->forget(self::SUPPORT_WIZARD_SESSION_KEY);
                $wizard = null;
            }
        }

        if (is_array($wizard)) {
            return $this->continueSupportWizard($request, $message, $user, $wizard);
        }

        if (! $this->aiService->shouldStartSupportWizard($message)) {
            return null;
        }

        $request->session()->put(self::SUPPORT_WIZARD_SESSION_KEY, [
            'step' => 'issue_type',
            'started_at' => now()->timestamp,
            'trigger' => $message,
            'draft' => [],
        ]);

        $payload = [
            'success' => true,
            'text' => "Muammo borligini tushundim. Rasmiy murojaatni tartibli yuborish uchun 3 ta qisqa savol beraman.\n\n1. Muammo turi qaysi?",
            'source' => 'support_wizard',
            'response_type' => 'wizard_issue_type',
            'actions' => $this->supportWizardReplyActions('issue_type'),
            'feedback_enabled' => false,
            'clarification_requested' => false,
            'support_converted' => false,
            'wizard_active' => true,
        ];

        return $this->respondWithInteraction($user, $message, $payload);
    }

    private function continueSupportWizard(GenerateAiMessageRequest $request, string $message, $user, array $wizard): ?JsonResponse
    {
        $normalized = mb_strtolower(trim($message));

        if (in_array($normalized, ['bekor', 'cancel', 'stop', 'to\'xta', 'toxta'], true)) {
            $request->session()->forget(self::SUPPORT_WIZARD_SESSION_KEY);

            $payload = [
                'success' => true,
                'text' => "Rasmiy murojaat wizard'i bekor qilindi. Xohlasangiz qayta boshlash uchun yana **Muammo bor** deb yozing.",
                'source' => 'support_wizard_cancelled',
                'response_type' => 'wizard_cancelled',
                'actions' => [
                    [
                        'type' => 'reply',
                        'label' => 'Muammo bor',
                        'message' => 'Muammo bor',
                    ],
                    [
                        'type' => 'link',
                        'label' => 'Aloqa',
                        'url' => route('contact'),
                        'route' => 'contact',
                    ],
                ],
                'feedback_enabled' => false,
                'clarification_requested' => false,
                'support_converted' => false,
            ];

            return $this->respondWithInteraction($user, $message, $payload);
        }

        if ($this->shouldInterruptSupportWizard($message, $wizard)) {
            $request->session()->forget(self::SUPPORT_WIZARD_SESSION_KEY);

            return null;
        }

        $draft = is_array($wizard['draft'] ?? null) ? $wizard['draft'] : [];

        if (($wizard['step'] ?? '') === 'issue_type') {
            $draft['issue_type'] = $this->normalizeSupportWizardValue($message);
            $wizard['step'] = 'page';
            $wizard['draft'] = $draft;
            $request->session()->put(self::SUPPORT_WIZARD_SESSION_KEY, $wizard);

            $payload = [
                'success' => true,
                'text' => "2. Muammo qaysi sahifa yoki bo'limda chiqdi?",
                'source' => 'support_wizard',
                'response_type' => 'wizard_page',
                'actions' => $this->supportWizardReplyActions('page'),
                'feedback_enabled' => false,
                'clarification_requested' => false,
                'support_converted' => false,
                'wizard_active' => true,
            ];

            return $this->respondWithInteraction($user, $message, $payload);
        }

        if (($wizard['step'] ?? '') === 'page') {
            $draft['page'] = $this->normalizeSupportWizardValue($message);
            $wizard['step'] = 'details';
            $wizard['draft'] = $draft;
            $request->session()->put(self::SUPPORT_WIZARD_SESSION_KEY, $wizard);

            $payload = [
                'success' => true,
                'text' => "3. Endi qisqacha yozing: nima xato chiqdi yoki nima ishlamayapti?",
                'source' => 'support_wizard',
                'response_type' => 'wizard_details',
                'actions' => [],
                'feedback_enabled' => false,
                'clarification_requested' => false,
                'support_converted' => false,
                'wizard_active' => true,
            ];

            return $this->respondWithInteraction($user, $message, $payload);
        }

        $draft['details'] = trim($message);
        $request->session()->forget(self::SUPPORT_WIZARD_SESSION_KEY);

        $contactMessage = DB::transaction(function () use ($draft, $user) {
            return ContactMessage::query()->create([
                'name' => sanitize_plain_text(trim((string) ($user->name ?: $user->buildNameFromParts()))),
                'email' => (string) $user->email,
                'phone' => uz_phone_format((string) $user->phone),
                'note' => sanitize_plain_text('AI wizard: '.($draft['issue_type'] ?? 'Muammo')),
                'message' => sanitize_plain_text($this->buildStructuredSupportMessage($draft, $user)),
            ]);
        });

        $payload = [
            'success' => true,
            'text' => "Rasmiy murojaat tayyorlandi va yuborildi.\n\n"
                . "- Muammo turi: **".($draft['issue_type'] ?? '-')."**\n"
                . "- Sahifa: **".($draft['page'] ?? '-')."**\n"
                . "- Murojaat ID: **#{$contactMessage->id}**\n\n"
                . "Admin ichki tartibda ko'rib chiqadi. Zarur bo'lsa siz bilan bog'laniladi.",
            'source' => 'support_wizard_completed',
            'response_type' => 'wizard_completed',
            'actions' => [
                [
                    'type' => 'link',
                    'label' => 'Aloqa',
                    'url' => route('contact'),
                    'route' => 'contact',
                ],
                [
                    'type' => 'link',
                    'label' => 'Profil',
                    'url' => route('profile.show'),
                    'route' => 'profile.show',
                ],
            ],
            'feedback_enabled' => false,
            'clarification_requested' => false,
            'support_converted' => true,
        ];

        return $this->respondWithInteraction($user, $message, $payload, $contactMessage, [
            'wizard_trigger' => (string) ($wizard['trigger'] ?? ''),
            'wizard_issue_type' => (string) ($draft['issue_type'] ?? ''),
            'wizard_page' => (string) ($draft['page'] ?? ''),
        ]);
    }

    private function shouldInterruptSupportWizard(string $message, array $wizard): bool
    {
        $normalized = Str::lower(Str::squish($message));
        if ($normalized === '') {
            return false;
        }

        $step = (string) ($wizard['step'] ?? '');
        $questionWords = ['bugun', 'ertaga', 'qanday', 'qaysi', 'qachon', 'nima', 'nega', 'kim', 'qancha'];
        $supportWords = ['muammo', 'xato', 'ishlamayap', 'ochilmayap', 'bug', 'nosoz', 'kirolmay', 'parol', 'akkaunt'];

        $hasQuestionTone = Str::contains($normalized, '?') || Str::contains($normalized, $questionWords);
        $hasSupportTone = Str::contains($normalized, $supportWords);
        $wordCount = count(array_filter(explode(' ', $normalized)));

        if ($step === 'issue_type') {
            $allowedAnswers = ['texnik xato', 'kurs', 'imtihon', 'akkaunt', 'boshqa'];

            return ! in_array($normalized, $allowedAnswers, true)
                && ($hasQuestionTone || $wordCount >= 3);
        }

        if ($step === 'page') {
            $allowedAnswers = ['kurslar', 'imtihonlar', 'profil', 'aloqa', 'boshqa sahifa'];

            return ! in_array($normalized, $allowedAnswers, true)
                && ($hasQuestionTone || $wordCount >= 4);
        }

        if ($step === 'details') {
            return $hasQuestionTone && ! $hasSupportTone;
        }

        return false;
    }

    private function supportWizardReplyActions(string $step): array
    {
        return match ($step) {
            'issue_type' => [
                ['type' => 'reply', 'label' => 'Texnik xato', 'message' => 'Texnik xato'],
                ['type' => 'reply', 'label' => 'Kurs', 'message' => 'Kurs'],
                ['type' => 'reply', 'label' => 'Imtihon', 'message' => 'Imtihon'],
                ['type' => 'reply', 'label' => 'Akkaunt', 'message' => 'Akkaunt'],
                ['type' => 'reply', 'label' => 'Boshqa', 'message' => 'Boshqa'],
            ],
            'page' => [
                ['type' => 'reply', 'label' => 'Kurslar', 'message' => 'Kurslar'],
                ['type' => 'reply', 'label' => 'Imtihonlar', 'message' => 'Imtihonlar'],
                ['type' => 'reply', 'label' => 'Profil', 'message' => 'Profil'],
                ['type' => 'reply', 'label' => 'Aloqa', 'message' => 'Aloqa'],
                ['type' => 'reply', 'label' => 'Boshqa sahifa', 'message' => 'Boshqa sahifa'],
            ],
            default => [],
        };
    }

    private function normalizeSupportWizardValue(string $value): string
    {
        return Str::limit(sanitize_plain_text($value), 120, '');
    }

    private function buildStructuredSupportMessage(array $draft, $user): string
    {
        $lines = [
            'AI wizard orqali yig\'ilgan rasmiy murojaat.',
            'Muammo turi: '.($draft['issue_type'] ?? '-'),
            'Sahifa yoki bo\'lim: '.($draft['page'] ?? '-'),
            'Xato tavsifi: '.($draft['details'] ?? '-'),
            'Foydalanuvchi roli: '.($user->role_label ?? $user->role ?? 'Foydalanuvchi'),
            'Yuborilgan vaqt: '.now()->format('d.m.Y H:i'),
        ];

        return implode("\n", $lines);
    }

    private function respondWithInteraction($user, string $question, array $payload, ?ContactMessage $contactMessage = null, array $extraMeta = []): JsonResponse
    {
        $interaction = $this->storeInteraction($user, $question, $payload, $contactMessage, $extraMeta);
        $this->rememberConversationTurn((int) $user->id, $question, $payload);

        $payload['interaction_id'] = $interaction?->id;
        $payload['actions'] = array_values($payload['actions'] ?? []);

        return (new AiResponseResource($payload))->response();
    }

    private function getConversationHistory(int $userId): array
    {
        return $this->conversationHistoryStore->historyForUser($userId);
    }

    private function rememberConversationTurn(int $userId, string $question, array $payload): void
    {
        $this->conversationHistoryStore->rememberTurn($userId, $question, $payload);
    }

    private function storeInteraction($user, string $question, array $payload, ?ContactMessage $contactMessage = null, array $extraMeta = []): ?AiInteraction
    {
        if (! $this->hasAiInteractionsTable()) {
            return null;
        }

        $firstLinkAction = collect($payload['actions'] ?? [])
            ->first(fn (array $action) => ($action['type'] ?? null) === 'link');

        return AiInteraction::query()->create([
            'user_id' => $user?->id,
            'contact_message_id' => $contactMessage?->id,
            'question' => sanitize_plain_text($question),
            'normalized_question' => $this->aiService->normalizeQuestionForAnalytics($question),
            'response_text' => isset($payload['text']) ? sanitize_plain_text((string) $payload['text']) : null,
            'response_source' => (string) ($payload['source'] ?? ''),
            'response_type' => (string) ($payload['response_type'] ?? (($payload['clarification_requested'] ?? false) ? 'clarification' : 'answer')),
            'user_role' => (string) ($user?->role ?? ''),
            'suggested_route' => $firstLinkAction['route'] ?? null,
            'suggested_url' => $firstLinkAction['url'] ?? null,
            'is_unanswered' => (bool) ($payload['clarification_requested'] ?? false),
            'clarification_requested' => (bool) ($payload['clarification_requested'] ?? false),
            'support_converted' => (bool) ($payload['support_converted'] ?? false),
            'meta' => array_merge($extraMeta, [
                'actions' => $payload['actions'] ?? [],
                'feedback_enabled' => (bool) ($payload['feedback_enabled'] ?? false),
            ]),
        ]);
    }

    private function hasAiInteractionsTable(): bool
    {
        if (self::$aiInteractionsTableExists === null) {
            self::$aiInteractionsTableExists = Schema::hasTable('ai_interactions');
        }

        return self::$aiInteractionsTableExists;
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
