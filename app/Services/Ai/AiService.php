<?php

namespace App\Services\Ai;

use App\Models\AiKnowledge;
use App\Models\CalendarEvent;
use App\Models\ContactMessage;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Exam;
use App\Models\Post;
use App\Models\Result;
use App\Models\SiteSetting;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AiService
{
    private const GEMINI_CALLS_PER_MINUTE_SOFT_LIMIT = 14;

    private static ?bool $aiKnowledgeTableExists = null;

    /**
     * Main entry point for generating a response.
     */
    public function generateResponse(string $userMessage, ?object $user = null, array $conversationContext = []): array
    {
        $conversationContext = $this->finalizeConversationContext($userMessage, $conversationContext);
        $message = trim((string) ($conversationContext['resolved_message'] ?? $userMessage));

        // 0. Smart analytics (intent-based, not keyword-locked)
        if ($analytics = $this->matchAnalyticalData($message)) {
            return ['success' => true, 'text' => $analytics, 'source' => 'analytics_data'];
        }

        // 0.05 Taqvim / sanaga bog'liq tadbirlar (DB: calendar_events)
        if ($calendar = $this->matchCalendarAndEvents($message)) {
            return ['success' => true, 'text' => $calendar, 'source' => 'calendar_data'];
        }

        // 0.07 Direktor haqida maxsus savol (turli yozilishlar, imlo xatolari)
        if ($director = $this->matchDirectorQuery($message)) {
            return ['success' => true, 'text' => $director, 'source' => 'director_data'];
        }

        // 0.08 Sayt mualliflari / ishtirokchilar / texnik jamoa
        if ($siteCredits = $this->matchSiteCreditsQuery($message)) {
            return ['success' => true, 'text' => $siteCredits, 'source' => 'site_credits'];
        }

        // 0.1 School profile / internal data summary
        if ($schoolData = $this->matchSchoolProfileData($message)) {
            return ['success' => true, 'text' => $schoolData, 'source' => 'school_profile'];
        }

        // 0.2 Saytdagi bo'limlar va AI yordamchi imkoniyatlari
        if ($siteGuide = $this->matchSiteGuideQuery($message, $user)) {
            return ['success' => true, 'text' => $siteGuide, 'source' => 'site_guide'];
        }

        // 0.25 Rollar va vazifalar
        if ($roleGuide = $this->matchRoleResponsibilitiesQuery($message)) {
            return ['success' => true, 'text' => $roleGuide, 'source' => 'role_guide'];
        }

        // 0.26 Saytning maqsadi va vazifasi
        if ($sitePurpose = $this->matchSitePurposeQuery($message)) {
            return ['success' => true, 'text' => $sitePurpose, 'source' => 'site_purpose'];
        }

        // 0.3 Admin, support va aloqa bo'yicha amaliy yo'l-yo'riq
        if ($supportContact = $this->matchSupportContactQuery($message)) {
            return ['success' => true, 'text' => $supportContact, 'source' => 'support_contact'];
        }

        if ($localUtility = $this->matchLocalUtility($message)) {
            return ['success' => true, 'text' => $localUtility, 'source' => 'local_utility'];
        }

        // 1. Try Local Machine/Static Knowledge (Greetings, Persona)
        if ($static = $this->matchStaticKnowledge($message)) {
            return ['success' => true, 'text' => $static, 'source' => 'static_knowledge'];
        }

        // 1.5 Live course catalog must win over generic knowledge-base answers.
        if ($courseCatalog = $this->matchCourseCatalogQuery($message)) {
            return ['success' => true, 'text' => $courseCatalog, 'source' => 'dynamic_data'];
        }

        // 2. Admin tomonidan kiritilgan AI bilimlar bazasi
        if ($knowledge = $this->matchKnowledgeBase($message)) {
            return ['success' => true, 'text' => $knowledge, 'source' => 'knowledge_base'];
        }

        // 3. Universal Site Stats & Contact
        if ($universal = $this->matchUniversalData($message)) {
            return ['success' => true, 'text' => $universal, 'source' => 'universal_data'];
        }

        // 4. Try Dynamic Data (Personal Results, Courses, Teachers)
        if ($dynamic = $this->matchDynamicData($message, $user)) {
            return ['success' => true, 'text' => $dynamic, 'source' => 'dynamic_data'];
        }

        // 4.5 Ask for clarification before falling back to a broad answer.
        if ($clarification = $this->matchClarificationFallback($message)) {
            return ['success' => true, 'text' => $clarification, 'source' => 'clarification'];
        }

        // 5. Fallback to Gemini API
        return $this->callGemini($userMessage, $user, $conversationContext);
    }

    public function suggestActions(string $message, ?object $user = null, ?string $source = null, array $conversationContext = []): array
    {
        $conversationContext = $this->finalizeConversationContext($message, $conversationContext);
        $effectiveMessage = (string) ($conversationContext['resolved_message'] ?? $message);
        $q = $this->normalizeCourseIntentText($effectiveMessage);
        $actions = [];

        if ($source === 'clarification') {
            return $this->clarificationReplyActions();
        }

        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            $actions[] = $this->makeLinkAction('Admin panel', route('dashboard'), 'dashboard');
        }

        if ($user && method_exists($user, 'canManageExams') && $user->canManageExams()) {
            if (Str::contains($q, ['imtihon', 'savol', 'natija', 'bahola', 'ball'])) {
                $actions[] = $this->makeLinkAction('Imtihonlarim', route('profile.exams.index'), 'profile.exams.index');
                $actions[] = $this->makeLinkAction('Natijalar', route('profile.exams.results'), 'profile.exams.results');
            }
        } elseif ($user && Str::contains($q, ['natija', 'imtihon', 'ball'])) {
            $actions[] = $this->makeLinkAction('Natijalarim', route('profile.show').'#exam-results-section', 'profile.show');
        }

        if (Str::contains($q, ['kurs', 'yozil', 'ariza', 'enroll'])) {
            $actions[] = $this->makeLinkAction('Kurslar', route('courses'), 'courses');
            if ($user) {
                $actions[] = $this->makeLinkAction('Profil', route('profile.show'), 'profile.show');
            }
        }

        if (Str::contains($q, ['murojaat', 'muammo', 'shikoyat', 'aloqa', 'support', 'bug', 'xato'])) {
            $actions[] = $this->makeLinkAction('Aloqa', route('contact'), 'contact');
            $actions[] = $this->makeReplyAction('Muammo bor', 'Muammo bor');
        }

        if ($user && method_exists($user, 'isTeacher') && $user->isTeacher()) {
            if (Str::contains($q, ['imtihon yarat', 'savol qo', 'kurs och'])) {
                if (! $user->hasReachedCourseOpenLimit() && $user->hasCourseOpenApproval()) {
                    $actions[] = $this->makeLinkAction('Kurs ochish', route('teacher.courses.create'), 'teacher.courses.create');
                } else {
                    $actions[] = $this->makeLinkAction('Profil', route('profile.show').'#course-open-request', 'profile.show');
                }

                $actions[] = $this->makeLinkAction('Yangi imtihon', route('profile.exams.create'), 'profile.exams.create');
            }
        }

        if ($source === 'site_guide' && $user) {
            $actions = array_merge($actions, $this->roleEntryActions($user));
        }

        return $this->uniqueActions($actions);
    }

    public function shouldStartSupportWizard(string $message): bool
    {
        $q = $this->normalizeCourseIntentText($message);

        return Str::contains($q, [
            'muammo bor', 'xato chiq', 'ishlamayap', 'ishga tushmayap', 'bug',
            'shikoyat', 'murojaat qilmoq', 'texnik muammo', 'problem', 'nosozlik',
        ]);
    }

    public function normalizeQuestionForAnalytics(string $message): string
    {
        return Str::limit($this->normalizeCourseIntentText($message), 255, '');
    }

    public function prepareConversationContext(string $userMessage, array $history = []): array
    {
        $history = $this->sanitizeConversationHistory($history);
        $recentTopic = $this->recentConversationTopic($history);
        $resolvedMessage = trim($userMessage);
        $contextApplied = false;

        if ($recentTopic !== null && $this->shouldUseConversationContext($userMessage, $recentTopic)) {
            $resolvedMessage = $this->enrichMessageWithConversationTopic($resolvedMessage, $recentTopic);
            $contextApplied = $resolvedMessage !== trim($userMessage);
        }

        return [
            'history' => $history,
            'recent_topic' => $recentTopic,
            'resolved_message' => $resolvedMessage,
            'context_applied' => $contextApplied,
            'history_signature' => $this->historySignature($history),
            'fingerprint' => sha1(json_encode($history, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]'),
        ];
    }

    private function finalizeConversationContext(string $userMessage, array $conversationContext = []): array
    {
        if (array_key_exists('resolved_message', $conversationContext) && array_key_exists('history', $conversationContext)) {
            return $conversationContext + [
                'recent_topic' => $conversationContext['recent_topic'] ?? $this->recentConversationTopic($conversationContext['history'] ?? []),
                'context_applied' => (bool) ($conversationContext['context_applied'] ?? false),
                'history_signature' => (string) ($conversationContext['history_signature'] ?? $this->historySignature($conversationContext['history'] ?? [])),
                'fingerprint' => (string) ($conversationContext['fingerprint'] ?? sha1('[]')),
            ];
        }

        return $this->prepareConversationContext($userMessage, $conversationContext);
    }

    private function sanitizeConversationHistory(array $history): array
    {
        return collect($history)
            ->map(function ($item): ?array {
                if (! is_array($item)) {
                    return null;
                }

                $text = trim((string) ($item['text'] ?? ''));
                if ($text === '') {
                    return null;
                }

                return [
                    'role' => ($item['role'] ?? 'user') === 'assistant' ? 'assistant' : 'user',
                    'text' => Str::limit($text, 500, ''),
                    'source' => trim((string) ($item['source'] ?? '')),
                ];
            })
            ->filter()
            ->take(-6)
            ->values()
            ->all();
    }

    private function historySignature(array $history): string
    {
        $normalizedHistory = collect($history)
            ->map(function ($item): ?array {
                if (! is_array($item)) {
                    return null;
                }

                $text = trim((string) ($item['text'] ?? ''));
                if ($text === '') {
                    return null;
                }

                return [
                    'role' => ($item['role'] ?? 'user') === 'assistant' ? 'assistant' : 'user',
                    'text' => $this->normalizeSearchText($text),
                ];
            })
            ->filter()
            ->values()
            ->all();

        return sha1(json_encode($normalizedHistory, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]');
    }

    private function recentConversationTopic(array $history): ?string
    {
        foreach (array_reverse($history) as $item) {
            $topic = $this->detectConversationTopic(
                (string) ($item['text'] ?? ''),
                (string) ($item['source'] ?? '')
            );

            if ($topic !== null) {
                return $topic;
            }
        }

        return null;
    }

    private function detectConversationTopic(string $text, string $source = ''): ?string
    {
        $q = $this->normalizeCourseIntentText($text);

        if ($q === '') {
            return null;
        }

        if ($source !== '') {
            if (Str::startsWith($source, 'support_wizard') || $source === 'support_contact') {
                return 'contact';
            }

            if ($source === 'calendar_data') {
                return 'calendar';
            }
        }

        return match (true) {
            Str::contains($q, ['kurs', 'yozil', 'ariza', 'enroll']) => 'course',
            Str::contains($q, ['imtihon', 'natija', 'ball', 'baho', 'topshir']) => 'exam',
            Str::contains($q, ['ustoz', "o'qituvchi", 'teacher', 'domla', 'muallim']) => 'teacher',
            Str::contains($q, ['aloqa', 'murojaat', 'muammo', 'support', 'shikoyat']) => 'contact',
            Str::contains($q, ['taqvim', 'tadbir', 'sanada', 'hafta', 'calendar']) => 'calendar',
            Str::contains($q, ['profil', 'akkaunt']) => 'profile',
            Str::contains($q, ['admin', 'panel', 'dashboard']) => 'admin',
            default => null,
        };
    }

    private function shouldUseConversationContext(string $message, string $recentTopic): bool
    {
        if ($recentTopic === '') {
            return false;
        }

        if ($this->detectConversationTopic($message) !== null) {
            return false;
        }

        $q = $this->normalizeSearchText($message);
        if ($q === '') {
            return false;
        }

        if (
            $this->hasGreetingIntent($q)
            || $this->hasThanksIntent($q)
            || $this->hasFarewellIntent($q)
            || $this->containsNormalizedPhrase($q, ['ok', 'xo\'p', 'hop', 'bekor', 'cancel'])
        ) {
            return false;
        }

        if ($this->containsNormalizedPhrase($q, [
            'o\'sha', 'osha', 'shu', 'u qaysi', 'qaysi biri', 'o\'shani', 'oshani', 'yana',
        ])) {
            return true;
        }

        if (preg_match('/\b[\p{L}\p{N}\']+chi\b/u', $q) === 1) {
            return true;
        }

        $tokens = preg_split('/\s+/u', $q) ?: [];
        $meaningfulTokens = $this->meaningfulTokens($q);

        return count($tokens) <= 4 && count($meaningfulTokens) <= 1;
    }

    private function enrichMessageWithConversationTopic(string $message, string $recentTopic): string
    {
        $suffix = match ($recentTopic) {
            'course' => 'kurslar kurs ariza yozilish',
            'exam' => 'imtihon natija ball',
            'teacher' => "ustoz o'qituvchi",
            'contact' => 'aloqa murojaat muammo',
            'calendar' => 'taqvim tadbir',
            'profile' => 'profil akkaunt',
            'admin' => 'admin panel',
            default => '',
        };

        if ($suffix === '') {
            return trim($message);
        }

        return Str::limit(Str::squish(trim($message.' '.$suffix)), 500, '');
    }

    /**
     * Taqvimdagi tadbirlar: aniq sana (masalan 20 aprel) yoki «taqvim» so‘zi bilan yaqinlashuvchi voqealar.
     */
    private function matchCalendarAndEvents(string $message): ?string
    {
        $q = mb_strtolower(trim($message));

        $hasCalendarWords = Str::contains($q, [
            'taqvim', 'tadbir', 'kalendar', 'kalendr', 'sanada', 'voqea', 'voqe', 'jadval', 'calendar',
        ]);
        $hasWeekIntent = Str::contains($q, [
            'shu hafta', 'bu hafta', 'haftadagi', 'hafta ichida', 'week',
        ]);

        $parsedDate = $this->parseCalendarDateFromMessage($message);

        $hasDateQuestionIntent = Str::contains($q, [
            'nima', 'qanday', 'qachon', 'dars', 'kun', 'reja', 'uchrashuv', 'bo\'ladi', 'boladi', 'bo‘ladi',
            'boshlan', 'tugay', 'bo\'ladi', 'qanaqa',
        ]);

        if ($parsedDate === null && ! $hasCalendarWords && ! $hasWeekIntent) {
            return null;
        }

        // Sana topildi, lekin «taqvim» emas — faqat savol kontekstida (tug‘ilgan kun va hok. chalkashmasin)
        if ($parsedDate !== null && ! $hasCalendarWords && ! $hasDateQuestionIntent) {
            return null;
        }

        $maxEvents = max(1, (int) config('ai.calendar_max_events_per_answer', 15));
        $maxBody = max(0, (int) config('ai.calendar_max_body_chars', 280));
        $calendarUrl = route('calendar');

        if ($hasWeekIntent) {
            $rows = CalendarEvent::query()
                ->whereBetween('event_date', [Carbon::now()->startOfWeek()->startOfDay(), Carbon::now()->endOfWeek()->endOfDay()])
                ->orderBy('event_date')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit($maxEvents)
                ->get();

            if ($rows->isEmpty()) {
                return "рџ“… **Shu hafta** uchun taqvimda tadbir topilmadi.\n"
                    ."рџ“† To'liq jadval: {$calendarUrl}";
            }

            $lines = [];
            foreach ($rows as $ev) {
                $d = $ev->event_date instanceof Carbon ? $ev->event_date : Carbon::parse($ev->event_date);
                $lines[] = 'вЂў '.$d->format('d.m.Y').' вЂ” '.$this->formatCalendarEventLine($ev, $maxBody);
            }

            return "рџ“… **Shu haftadagi tadbirlar**:\n"
                .implode("\n\n", $lines)
                ."\n\nрџ“† To'liq jadval: {$calendarUrl}";
        }

        if ($parsedDate !== null) {
            $rows = CalendarEvent::query()
                ->whereDate('event_date', $parsedDate->format('Y-m-d'))
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit($maxEvents)
                ->get();

            $dateLabel = $parsedDate->format('d.m.Y');
            if ($rows->isEmpty()) {
                return "📅 **{$dateLabel}** sanasi bo‘yicha taqvimda tadbir yozuvi topilmadi.\n"
                    ."📆 To‘liq jadval: {$calendarUrl}";
            }

            $lines = [];
            foreach ($rows as $ev) {
                $lines[] = $this->formatCalendarEventLine($ev, $maxBody);
            }

            return "📅 **{$dateLabel}** kuni taqvim bo‘yicha:\n"
                .implode("\n\n", $lines)
                ."\n\n📆 Batafsil: {$calendarUrl}";
        }

        $rows = CalendarEvent::query()
            ->where('event_date', '>=', Carbon::today()->startOfDay())
            ->orderBy('event_date')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit($maxEvents)
            ->get();

        if ($rows->isEmpty()) {
            return "📆 Hozircha rejalashtirilgan yaqin tadbirlar yo‘q.\n"
                ."Taqvim: {$calendarUrl}";
        }

        $lines = [];
        foreach ($rows as $ev) {
            $d = $ev->event_date instanceof Carbon ? $ev->event_date : Carbon::parse($ev->event_date);
            $lines[] = '• '.$d->format('d.m.Y').' — '.$this->formatCalendarEventLine($ev, $maxBody);
        }

        return "📆 **Yaqinlashayotgan tadbirlar** (oxirgi {$maxEvents} ta):\n"
            .implode("\n\n", $lines)
            ."\n\n📆 To‘liq taqvim: {$calendarUrl}";
    }

    private function formatCalendarEventLine(CalendarEvent $ev, int $maxBody): string
    {
        $title = localized_model_value($ev, 'title');
        $time = localized_model_value($ev, 'time_note');
        $body = localized_model_value($ev, 'body');
        $line = $title;
        if (filled($time)) {
            $line .= "\n  ⏱ ".$time;
        }
        if ($maxBody > 0 && filled($body)) {
            $plain = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $body)) ?? '');
            $line .= "\n  ".Str::limit($plain, $maxBody);
        }

        return $line;
    }

    private function parseCalendarDateFromMessage(string $message): ?Carbon
    {
        $q = mb_strtolower($message);
        $tz = (string) config('app.timezone', 'UTC');

        if (preg_match('/\bbugun\b/u', $q)) {
            return Carbon::now($tz)->startOfDay();
        }
        if (preg_match('/\bertaga\b/u', $q)) {
            return Carbon::now($tz)->addDay()->startOfDay();
        }

        $year = (int) Carbon::now($tz)->year;
        if (preg_match('/\b(20[0-9]{2})\b/', $message, $ym)) {
            $y = (int) $ym[1];
            if ($y >= 2000 && $y <= 2100) {
                $year = $y;
            }
        }

        $monthRx = $this->calendarMonthRegexFragment();

        if (preg_match('/\b([1-9]|[12]\d|3[01])\s*[-]?\s*('.$monthRx.')\b/u', $q, $m)) {
            $day = (int) $m[1];
            $month = $this->monthNameToNumber($m[2]);
            if ($month !== null) {
                return $this->safeCalendarDate($year, $month, $day, $tz);
            }
        }

        if (preg_match('/\b('.$monthRx.')\s*[-]?\s*([1-9]|[12]\d|3[01])\b/u', $q, $m)) {
            $month = $this->monthNameToNumber($m[1]);
            $day = (int) $m[2];
            if ($month !== null) {
                return $this->safeCalendarDate($year, $month, $day, $tz);
            }
        }

        return null;
    }

    private function calendarMonthRegexFragment(): string
    {
        return 'yanvar(?:da|dan|dagi)?|fevral(?:da|dan|dagi)?|mart(?:da|dan|dagi)?|aprel(?:da|dan|dagi)?|april(?:da|dan|dagi)?'
            .'|may(?:da|dan|dagi)?|iyun(?:da|dan|dagi)?|iyul(?:da|dan|dagi)?|avgust(?:da|dan|dagi)?'
            .'|sentyabr(?:da|dan|dagi)?|oktyabr(?:da|dan|dagi)?|noyabr(?:da|dan|dagi)?|dekabr(?:da|dan|dagi)?';
    }

    private function monthNameToNumber(string $name): ?int
    {
        $base = mb_strtolower(preg_replace('/(da|dan|dagi)$/u', '', mb_strtolower(trim($name))) ?? '');
        $map = [
            'yanvar' => 1, 'fevral' => 2, 'mart' => 3, 'aprel' => 4, 'april' => 4,
            'may' => 5, 'iyun' => 6, 'iyul' => 7, 'avgust' => 8,
            'sentyabr' => 9, 'oktyabr' => 10, 'noyabr' => 11, 'dekabr' => 12,
        ];

        return $map[$base] ?? null;
    }

    private function safeCalendarDate(int $year, int $month, int $day, string $tz): ?Carbon
    {
        if ($day < 1 || $day > 31 || $month < 1 || $month > 12) {
            return null;
        }
        try {
            $d = Carbon::createFromDate($year, $month, $day, $tz)->startOfDay();
            if ((int) $d->day !== $day || (int) $d->month !== $month) {
                return null;
            }

            return $d;
        } catch (\Throwable) {
            return null;
        }
    }

    private function matchLocalUtility(string $message): ?string
    {
        $q = $this->normalizeSearchText($message);

        if ($q === '') {
            return null;
        }

        if ($this->containsNormalizedPhrase($q, [
            'men hayr demadim',
            'men xayr demadim',
            'hayr demadim',
            'xayr demadim',
        ])) {
            return "Uzr, noto'g'ri tushundim. Siz xayrlashmagansiz. Savolingizni davom ettirishingiz mumkin.";
        }

        if ($this->hasAnyExactToken($q, ['jalab', 'ahmoq', 'tentak', 'eshak', 'lanati', "la'nati"])) {
            return "Hurmat bilan yozsangiz, yaxshiroq yordam bera olaman. Men asosan maktab va sayt bo'limlari bo'yicha yordam beraman.";
        }

        if ($percent = $this->matchPercentCalculation($message)) {
            return $percent;
        }

        if ($dateDifference = $this->matchDateDifferenceCalculation($message)) {
            return $dateDifference;
        }

        if ($calculation = $this->matchSimpleCalculation($message)) {
            return $calculation;
        }

        if ($this->containsNormalizedPhrase($q, [
            'nima qila olasan',
            'nima qila olasan san',
            'nima qilolasan',
            'nima qilolasan san',
            'nima qilolisan',
            'nima qilolisan san',
            'nimalarga javob berasan',
            'qanday yordam berasan',
            'nima yordam berasan',
            'sen nima qilasan',
            'ai nima qila oladi',
            'ai nima qiladi',
            'yordam bera olasan',
            'yordam bera olasanmi',
            'qaysi savollarga javob berasan',
        ])) {
            $schoolName = SiteSetting::get('school_name', (string) __('public.layout.school_name'));

            return "Men {$schoolName} saytining ichki AI yordamchisiman.\n"
                ."- Maktab, ustozlar, kurslar, imtihonlar, natijalar, taqvim va aloqa bo'limlari bo'yicha yordam bera olaman.\n"
                ."- Saytdan foydalanish: ro'yxatdan o'tish, profil, kurs arizasi va imtihon jarayonlari haqida yo'l-yo'riq bera olaman.\n"
                ."- Oddiy hisob-kitoblarni ham chiqarib bera olaman.\n"
                ."- Maktabdan tashqari keng va global mavzular uchun mo'ljallanmaganman.";
        }

        return null;
    }

    private function matchSimpleCalculation(string $message): ?string
    {
        $expression = trim(str_replace(',', '.', $message));
        $expression = preg_replace('/[=?]+$/', '', $expression) ?? $expression;
        $expression = preg_replace('/\s+/u', '', $expression) ?? $expression;

        if ($expression === '' || mb_strlen($expression) > 80) {
            return null;
        }

        if (! preg_match('/\d/', $expression) || ! preg_match('/[+\-*\/()%]/', $expression)) {
            return null;
        }

        if (! preg_match('/^[0-9+\-*\/().%]+$/', $expression)) {
            return null;
        }

        $result = $this->evaluateMathExpression($expression);

        if ($result === null) {
            return null;
        }

        return 'Javob: **'.$this->formatMathResult($result).'**.';
    }

    private function evaluateMathExpression(string $expression): ?float
    {
        preg_match_all('/\d+(?:\.\d+)?|[()+\-*\/%]/', $expression, $matches);
        $tokens = $matches[0] ?? [];

        if ($tokens === [] || implode('', $tokens) !== $expression) {
            return null;
        }

        $precedence = [
            'u-' => 3,
            '*' => 2,
            '/' => 2,
            '%' => 2,
            '+' => 1,
            '-' => 1,
        ];

        $output = [];
        $operators = [];
        $prevType = 'start';

        foreach ($tokens as $token) {
            if (is_numeric($token)) {
                $output[] = $token;
                $prevType = 'number';

                continue;
            }

            if ($token === '(') {
                $operators[] = $token;
                $prevType = 'left_paren';

                continue;
            }

            if ($token === ')') {
                while ($operators !== [] && end($operators) !== '(') {
                    $output[] = array_pop($operators);
                }

                if ($operators === [] || end($operators) !== '(') {
                    return null;
                }

                array_pop($operators);
                $prevType = 'right_paren';

                continue;
            }

            $operator = $token;
            if (($token === '-' || $token === '+') && in_array($prevType, ['start', 'operator', 'left_paren'], true)) {
                if ($token === '+') {
                    continue;
                }

                $operator = 'u-';
            }

            while ($operators !== [] && end($operators) !== '(') {
                $top = end($operators);
                $topPrecedence = $precedence[$top] ?? 0;
                $operatorPrecedence = $precedence[$operator] ?? 0;
                $rightAssociative = $operator === 'u-';

                if ($topPrecedence > $operatorPrecedence || ($topPrecedence === $operatorPrecedence && ! $rightAssociative)) {
                    $output[] = array_pop($operators);

                    continue;
                }

                break;
            }

            $operators[] = $operator;
            $prevType = 'operator';
        }

        if (in_array($prevType, ['start', 'operator', 'left_paren'], true)) {
            return null;
        }

        while ($operators !== []) {
            $operator = array_pop($operators);

            if ($operator === '(') {
                return null;
            }

            $output[] = $operator;
        }

        $stack = [];

        foreach ($output as $token) {
            if (is_numeric($token)) {
                $stack[] = (float) $token;

                continue;
            }

            if ($token === 'u-') {
                if ($stack === []) {
                    return null;
                }

                $stack[] = -array_pop($stack);

                continue;
            }

            if (count($stack) < 2) {
                return null;
            }

            $right = array_pop($stack);
            $left = array_pop($stack);

            $value = match ($token) {
                '+' => $left + $right,
                '-' => $left - $right,
                '*' => $left * $right,
                '/' => abs($right) < 1.0E-12 ? null : $left / $right,
                '%' => abs($right) < 1.0E-12 ? null : fmod($left, $right),
                default => null,
            };

            if ($value === null || is_nan($value) || is_infinite($value)) {
                return null;
            }

            $stack[] = $value;
        }

        if (count($stack) !== 1) {
            return null;
        }

        return $stack[0];
    }

    private function formatMathResult(float $value): string
    {
        if (abs($value - round($value)) < 1.0E-10) {
            return (string) (int) round($value);
        }

        return rtrim(rtrim(number_format($value, 10, '.', ''), '0'), '.');
    }

    private function matchPercentCalculation(string $message): ?string
    {
        $normalized = $this->normalizeSearchText($message);

        if (preg_match('/\b(\d+(?:[.,]\d+)?)\s+dan\s+(\d+(?:[.,]\d+)?)\s+necha\s+foiz\b/u', $normalized, $matches) !== 1) {
            return null;
        }

        $base = (float) str_replace(',', '.', (string) $matches[1]);
        $value = (float) str_replace(',', '.', (string) $matches[2]);

        if ($base <= 0) {
            return "Foizni hisoblash uchun asosiy son 0 dan katta bo'lishi kerak.";
        }

        $percent = round(($value / $base) * 100, 1);

        return "**{$value}**, **{$base}** dan taxminan **{$percent}%** bo'ladi.";
    }

    private function matchDateDifferenceCalculation(string $message): ?string
    {
        $q = $this->normalizeSearchText($message);

        if (! Str::contains($q, ['kun', 'qold', 'qolgan', 'necha kun', 'necha soat', 'imtihon'])) {
            return null;
        }

        if (Str::contains($q, ['imtihon'])) {
            $nearestExam = Exam::query()
                ->where('is_active', true)
                ->whereNotNull('available_from')
                ->where('available_from', '>=', now())
                ->orderBy('available_from')
                ->first();

            if (! $nearestExam || ! $nearestExam->available_from) {
                return null;
            }

            $diffInHours = now()->diffInHours($nearestExam->available_from, false);
            if ($diffInHours < 0) {
                return null;
            }

            $diffInDays = now()->diffInDays($nearestExam->available_from);
            $startLabel = $nearestExam->availableFromLabel() ?? $nearestExam->available_from->format('d.m.Y H:i');

            if ($diffInDays >= 1) {
                return "Eng yaqin faol imtihon **{$nearestExam->title}**. Boshlanishigacha taxminan **{$diffInDays} kun** qoldi ({$startLabel}).";
            }

            return "Eng yaqin faol imtihon **{$nearestExam->title}**. Boshlanishigacha taxminan **{$diffInHours} soat** qoldi ({$startLabel}).";
        }

        if (preg_match('/\b(\d{4})[.\-\/](\d{2})[.\-\/](\d{2})\b/', $message, $matches) === 1) {
            $target = Carbon::createFromDate((int) $matches[1], (int) $matches[2], (int) $matches[3])->startOfDay();
            $now = now()->startOfDay();
            $diff = $now->diffInDays($target, false);

            if ($diff === 0) {
                return 'Bu sana **bugun**.';
            }

            if ($diff > 0) {
                return "Bu sanagacha taxminan **{$diff} kun** qoldi.";
            }

            return 'Bu sana **'.abs($diff)." kun oldin** o'tgan.";
        }

        return null;
    }

    /**
     * Hardcoded static knowledge and small talk.
     */
    private function matchStaticKnowledge(string $message): ?string
    {
        $q = $this->cleanMessage($message);
        $normalized = $this->normalizeSearchText($message);
        $hour = (int) Carbon::now((string) config('app.timezone', 'UTC'))->format('H');

        $hasStrictGreeting = $this->hasGreetingIntent($normalized);
        if ($hasStrictGreeting) {
            $schoolName = SiteSetting::get('school_name', (string) __('public.layout.school_name'));

            // Islomiy salomlashuv: "assalomu alaykum" → "Va alaykum assalom!"
            $isIslamicGreeting = $this->queryHasApproximateToken($normalized, ['assalom', 'assalomu', 'asalom', 'asalomu'])
                && $this->queryHasApproximateToken($normalized, ['alaykum', 'aleykum', 'alekum', 'alaykom', 'alekom']);

            if ($isIslamicGreeting) {
                return "Va alaykum assalom! 🌙 Men **{$schoolName}** saytining AI yordamchisiman.\n"
                    ."Quyidagi mavzularda yordam bera olaman:\n"
                    ."- Maktab, kurslar, ustozlar va aloqa bo'limlari\n"
                    ."- Imtihonlar, natijalar va taqvim\n"
                    ."- Saytdan foydalanish: profil, kurs arizasi, login va boshqa jarayonlar\n"
                    ."- Oddiy hisob-kitoblar\n\n"
                    .'Savolingizni yozing.';
            }

            if ($hour >= 5 && $hour < 12) {
                $greeting = 'Hayrli tong';
            } elseif ($hour >= 12 && $hour < 17) {
                $greeting = 'Hayrli kun';
            } elseif ($hour >= 17 && $hour < 22) {
                $greeting = 'Hayrli kech';
            } else {
                $greeting = 'Assalomu alaykum';
            }

            return "{$greeting}! 😊 Men **{$schoolName}** saytining AI yordamchisiman.\n"
                ."Quyidagi mavzularda yordam bera olaman:\n"
                ."- Maktab, kurslar, ustozlar va aloqa bo'limlari\n"
                ."- Imtihonlar, natijalar va taqvim\n"
                ."- Saytdan foydalanish: profil, kurs arizasi, login va boshqa jarayonlar\n"
                ."- Oddiy hisob-kitoblar\n\n"
                .'Savolingizni yozing.';
        }

        $hasStrictFarewell = $this->hasFarewellIntent($normalized);
        if ($hasStrictFarewell) {
            return "Xayr! Yana savolingiz bo'lsa, yozavering.";
        }

        $hasStrictThanks = $this->hasThanksIntent($normalized);
        if ($hasStrictThanks) {
            return "Arziydi! Boshqa savolingiz bo'lsa, yozing.";
        }

        if ($this->containsNormalizedPhrase($normalized, ['qandaysan', 'yaxshimi', 'tuzukmi', 'kimsan', 'sen kimsan', 'siz kimsiz'])) {
            return "Men 81-IDUM saytining ichki AI yordamchisiman. Asosan maktab va sayt bo'limlari bo'yicha yordam beraman, oddiy hisob-kitoblarni ham chiqarib bera olaman.";
        }

        // Xayr / Ko'rishguncha
        if (Str::contains($q, ['xayr', 'ko\'rishguncha', 'sog\'lik', 'hayr', 'bye', 'goodbye', 'chao'])) {
            return "Xayr! 👋 Sizga yordam bera olganimdan mamnunman. Yana savollaringiz bo'lsa, doim shu yerdaman! 😊✨";
        }

        // Rahmat
        if (Str::contains($q, ['rahmat', 'katta rahmat', 'minnatdor', 'bor bo\'ling', 'tashakkur', 'raxmat', 'thanks', 'thank you'])) {
            return "Arziydi! 😊 Yordam bera olganimdan xursandman. Boshqa savollaringiz bo'lsa, yozing! ✅";
        }

        // Kimsan / Qandaysan
        if (Str::contains($q, ['qandaysan', 'yaxshimi', 'tuzukmi', 'kimsan', 'nima qilasan', 'sen kimsan', 'siz kimsiz'])) {
            return 'Men 81-IDUM saytining AI yordamchisiman! ✨ Maktab haqida, darslarga oid, math va fan savollariga — hammaga javob berishga harakat qilaman. Savol bering! 🚀';
        }

        if (Str::contains($q, ['muallfi', 'mualif', 'kim ishtirok', 'ishtirok etgan', 'saytda kim'])) {
            if ($creditsAnswer = $this->matchSiteCreditsQuery('sayt '.$q)) {
                return $creditsAnswer;
            }
        }

        // Kim yaratgan
        if (Str::contains($q, ['kim yasagan', 'muallif', 'kim yaratgan', 'saytni kim', 'developer', 'dasturchi'])) {
            $siteCreditsIntro = (string) __('public.about.site_credits_intro');
            $siteCredits = trans('public.about.site_credits_members');
            $names = [];
            if (is_array($siteCredits)) {
                foreach ($siteCredits as $member) {
                    if ($name = trim((string) ($member['name'] ?? ''))) {
                        $names[] = $name;
                    }
                }
            }
            $nameStr = empty($names) ? 'Jamoa' : implode(', ', $names);

            return "{$siteCreditsIntro} ✨ Mualliflar: **{$nameStr}**. 👨‍💻";
        }

        // Admin / Boshqaruvchi
        $adminIdentityIntent = Str::contains($q, [
            'admin kim', 'sayt admini', 'kim boshqaradi', 'kim yuritadi',
            'boshqaruvchi', 'mas\'ul', 'kim mas\'ul', 'kim masul',
        ]);
        $adminContactIntent = Str::contains($q, [
            'bog\'lan', 'boglan', 'aloqa', 'murojaat', 'xabar', 'telefon',
            'email', 'support', 'kontakt', 'yoz',
        ]);
        if ($adminIdentityIntent && ! $adminContactIntent) {
            return 'Hozirgi paytda saytni **Xabibullayev Shamsiddin** boshqaradi. Qolgan hamkorlar moderator va editor sifatida yordam beradi. ✨';
        }

        // Hozir soat nechchi / bugungi sana
        if (Str::contains($q, ['soat nech', 'vaqt nech', 'bugun necha', 'bugungi sana', 'nechinchi'])) {
            $now = Carbon::now((string) config('app.timezone', 'UTC'));

            return "🕐 Hozir soat **{$now->format('H:i')}** ({$now->format('d.m.Y')}, {$now->translatedFormat('l')}).";
        }

        // --- Qoshimcha Maktab Ma'lumotlari (Static) ---

        // Maktab tarixi
        if (Str::contains($q, ['tashkil', 'qachon ochilgan', 'yilida ochilgan', 'qachon qurilgan', 'tarixi'])) {
            return "81-IDUM maktabi o'z faoliyatini 1980-yillarda boshlagan va hozirda zamonaviy ta'lim markazlaridan biri hisoblanadi. 🏫";
        }

        // Fanlar
        if (Str::contains($q, ['fanlar', 'nimalar oqtiladi', 'nimalar o\'qitiladi', 'chuqurlashtirilgan'])) {
            return "Maktabimizda barcha davlat standartidagi fanlar bilan birga IT, Matematika va Ingliz tili chuqurlashtirib o'tiladi. 📚";
        }

        // Dars vaqtlari
        if (Str::contains($q, ['dars vaqti', 'soat nechada boshlanadi', 'soat nechada tugaydi', 'dars jadvali', 'tanaffus'])) {
            return "🏫 **Dars vaqtlari:**\n"
                ."• 1-soat: 08:30 - 09:15\n"
                ."• 2-soat: 09:20 - 10:05\n"
                ."• 3-soat: 10:20 - 11:05 (Katta tanaffus)\n"
                ."• 4-soat: 11:10 - 11:55\n"
                ."• 5-soat: 12:00 - 12:45\n"
                ."✨ Eslatma: Sinf darajasiga qarab o'zgarishi mumkin.";
        }

        // To'garaklar
        if (Str::contains($q, ['togarak', 'to\'garak', 'mashgulot', 'mashg\'ulot'])) {
            return "🎨 **Bizda quyidagi to'garaklar mavjud:**\n"
                ."• Fan to'garaklari (Matematika, Ingliz tili)\n"
                ."• Sport (Futbol, Shaxmat)\n"
                ."• San'at (Raqs, Musiqa)\n"
                ."Batafsil ma'lumot uchun maktab ma'muriyatiga murojaat qiling. 🚀";
        }

        return null;
    }

    private function matchSiteCreditsQuery(string $message): ?string
    {
        $q = $this->normalizeSearchText($message);

        $hasSiteWord = Str::contains($q, [
            'sayt', 'sait', 'sayti', 'veb', 'web', 'website', 'platforma', 'portal', 'loyiha', 'dastur',
        ]);

        $hasCreatorIntent = Str::contains($q, [
            'muallif', 'muallfi', 'muallf', 'mualif', 'avtor', 'avtori', 'yarat', 'yasag', 'qilgan', 'tuzgan', 'ishlab chiq',
            'dasturchi', 'developer', 'programmist', 'ishtirok', 'qatnash', 'jamoa',
            'kimlar', 'kim qildi', 'kim qilgan', 'credits', 'credits members',
        ]);

        if (! $hasSiteWord || ! $hasCreatorIntent) {
            return null;
        }

        $credits = $this->siteCreditsPayload();
        $members = $credits['members'];
        $names = array_map(static fn ($member) => $member['name'], $members);
        $memberLines = array_map(function ($member): string {
            $date = trim((string) ($member['date'] ?? ''));

            return $date !== ''
                ? "- **{$member['name']}** ({$date})"
                : "- **{$member['name']}**";
        }, $members);

        $memberText = $memberLines !== []
            ? implode("\n", $memberLines)
            : "- **10-E sinf o'quvchilari jamoasi**";

        $shortNames = $names !== [] ? implode(', ', $names) : "10-E sinf o'quvchilari";
        $schoolName = SiteSetting::get('school_name', (string) __('public.layout.school_name'));

        return "**Sayt mualliflari**\n"
            ."- Jamoa: 10-\"E\" sinf o'quvchilari\n"
            ."- Qisqa javob: **{$schoolName} saytini {$shortNames} ishlab chiqqan.**\n\n"
            ."**Ishtirokchilar**\n"
            ."{$memberText}\n\n"
            ."**Izoh**\n"
            ."{$credits['intro']}";
    }

    private function matchSiteGuideQuery(string $message, ?object $user = null): ?string
    {
        $q = $this->normalizeSearchText($message);

        $hasGuideIntent = Str::contains($q, [
            'saytda nima bor', 'sayt nima qiladi', 'sayt imkoniyat', 'funksiya',
            'bolim', 'bo lim', 'bo\'lim', 'sahifa', 'nima qila olaman',
            'qayerdan topaman', 'qanday ishlaydi', 'ai nima qila oladi',
            'yordamchi nima qiladi', 'nimalarga javob beradi',
            'qanday savollar', 'qanaqa savollar', 'nima deb sorasam', 'nima deb so\'rasam',
            'qaysi savollar', 'hamma savollar', 'nimalarni sorash mumkin', 'nimalarni so\'rash mumkin',
        ]);

        if (! $hasGuideIntent) {
            return null;
        }

        $posts = Post::count();
        $teachers = Teacher::where('is_active', true)->count();
        $courses = Course::where('status', Course::STATUS_PUBLISHED)->count();
        $activeExams = Exam::where('is_active', true)->count();
        $upcomingEvents = CalendarEvent::where('event_date', '>=', now())->count();

        $lines = [
            "- Yangiliklar: {$posts} ta post va e'lonlar. ".route('post'),
            "- Ustozlar: {$teachers} ta faol ustoz profili. ".route('teacher'),
            "- Kurslar: {$courses} ta nashr etilgan kurs. ".route('courses'),
            "- Imtihonlar: {$activeExams} ta faol imtihon. ".route('exam.index'),
            "- Taqvim: {$upcomingEvents} ta yaqin tadbir. ".route('calendar'),
            "- Aloqa: murojaat yuborish va maktab bilan bog'lanish. ".route('contact'),
        ];

        if ($user) {
            foreach ($this->roleGuideLines($user) as $line) {
                $lines[] = $line;
            }
        }

        $questionGroups = [
            '- **Maktab**: direktor kim, manzil qayerda, telefon raqami nima, maktab qachon ochilgan',
            '- **Sayt**: sayt muallifi kim, kimlar ishtirok etgan, saytda nimalar bor',
            "- **Yangiliklar**: so'nggi yangiliklar qayerda, qaysi post yangi, tadbirlar bormi",
            "- **Ustozlar**: falon ustoz kim, qaysi fan o'qituvchisi kim, ustozlar ro'yxati",
            '- **Kurslar**: kursga qanday yozilaman, arizam holati qayerda, kursni kim tasdiqlaydi',
            "- **Imtihonlar**: imtihon qayerda boshlanadi, natijam qayerda, ballim qancha, qayta topshirsa bo'ladimi",
            "- **Profil va akkaunt**: ro'yxatdan qanday o'taman, parolni unutdim, emailni qanday o'zgartiraman",
            "- **Aloqa va support**: rasmiy murojaatni qayerga yuboraman, murojaatimni kim ko'radi, texnik muammo bo'lsa qayerga yozaman",
            "- **Chat va izohlar**: global chat nima, chat o'chsa nima qilaman, izohni tahrirlasa bo'ladimi",
            '- **Panel va rollar**: admin panelga kim kira oladi, teacher panelda nima qilish mumkin',
        ];

        return "**Sayt imkoniyatlari**\n"
            .implode("\n", $lines)
            ."\n\n**AI'ga berish mumkin bo'lgan savollar**\n"
            .implode("\n", $questionGroups)
            ."\n\nMasalan: **\"texnik muammo bo'lsa qayerga murojaat qilaman?\"**, **\"kurs arizam qayerda ko'rinadi?\"**, **\"emailimni qanday o'zgartiraman?\"**";
    }

    private function roleGuideLines(object $user): array
    {
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            $lines = [
                '- Siz **admin** sifatida boshqaruv paneliga kirib, foydalanuvchilar, kurslar, imtihonlar va murojaatlarni nazorat qilasiz: '.route('dashboard'),
            ];

            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                $lines[] = "- Siz **super admin** bo'lsangiz, AI analytics va bilim bazasini ham boshqarasiz.";
            }

            return $lines;
        }

        if (method_exists($user, 'isTeacher') && $user->isTeacher()) {
            return [
                "- Siz **o'qituvchi** sifatida o'z profilingizdan imtihon yaratish, savol qo'shish va natijalarni ko'rishingiz mumkin: ".route('profile.exams.index'),
                "- Kurs ochish ruxsati bo'lsa, kurs ochish formasiga shu yerdan o'tasiz: ".route('profile.show').'#course-open-request',
            ];
        }

        return [
            "- Siz **o'quvchi** sifatida profilingizda natijalar, kurs arizalari va email/parol sozlamalarini ko'rasiz: ".route('profile.show'),
            "- Faol imtihonlarni boshlash uchun imtihon bo'limidan, kursga yozilish uchun esa kurslar sahifasidan foydalanasiz.",
        ];
    }

    private function roleEntryActions(object $user): array
    {
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            $actions = [
                $this->makeLinkAction('Admin panel', route('dashboard'), 'dashboard'),
            ];

            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                $actions[] = $this->makeLinkAction('AI bilim bazasi', route('ai-knowledges.index'), 'ai-knowledges.index');
            }

            return $actions;
        }

        if (method_exists($user, 'isTeacher') && $user->isTeacher()) {
            $actions = [
                $this->makeLinkAction('Imtihonlarim', route('profile.exams.index'), 'profile.exams.index'),
                $this->makeLinkAction('Yangi imtihon', route('profile.exams.create'), 'profile.exams.create'),
            ];

            if (! $user->hasReachedCourseOpenLimit() && $user->hasCourseOpenApproval()) {
                $actions[] = $this->makeLinkAction('Kurs ochish', route('teacher.courses.create'), 'teacher.courses.create');
            } else {
                $actions[] = $this->makeLinkAction('Profil', route('profile.show').'#course-open-request', 'profile.show');
            }

            return $actions;
        }

        return [
            $this->makeLinkAction('Profil', route('profile.show'), 'profile.show'),
            $this->makeLinkAction('Kurslar', route('courses'), 'courses'),
            $this->makeLinkAction('Imtihonlar', route('exam.index'), 'exam.index'),
        ];
    }

    private function matchClarificationFallback(string $message): ?string
    {
        $q = $this->normalizeSearchText($message);
        $tokens = $this->meaningfulTokens($q);

        $isVeryShort = count($tokens) <= 2;
        $isGeneric = $this->containsNormalizedPhrase($q, [
            'buni', 'mana bu', 'shu narsa', 'qayerda', 'qanday qilaman',
            'bunaqa', 'shunaqa', 'yordam kerak', 'qarab ber', 'tushunmadim',
        ]);

        $hasSiteContextWord = Str::contains($q, [
            'kurs', 'imtihon', 'natija', 'ustoz', 'teacher', 'aloqa', 'murojaat',
            'profil', 'chat', 'taqvim', 'yangilik',
        ]);

        if (! $isVeryShort && ! $isGeneric && ! $hasSiteContextWord) {
            return null;
        }

        return "Savol biroz noaniq ko'rindi. Aniqlashtirib yozing: **kurs**, **imtihon**, **ustoz** yoki **aloqa** haqida so'rayapsizmi?";
    }

    private function clarificationReplyActions(): array
    {
        return [
            $this->makeReplyAction('Kurslar', 'Kurslar haqida'),
            $this->makeReplyAction('Imtihonlar', 'Imtihonlar haqida'),
            $this->makeReplyAction('Ustozlar', 'Ustozlar haqida'),
            $this->makeReplyAction('Aloqa', 'Aloqa haqida'),
        ];
    }

    private function makeLinkAction(string $label, string $url, string $route): array
    {
        return [
            'type' => 'link',
            'label' => $label,
            'url' => $url,
            'route' => $route,
        ];
    }

    private function makeReplyAction(string $label, string $message): array
    {
        return [
            'type' => 'reply',
            'label' => $label,
            'message' => $message,
        ];
    }

    private function uniqueActions(array $actions): array
    {
        $seen = [];
        $unique = [];

        foreach ($actions as $action) {
            $key = ($action['type'] ?? 'unknown').'|'.($action['label'] ?? '').'|'.($action['url'] ?? $action['message'] ?? '');
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $unique[] = $action;
        }

        return array_values($unique);
    }

    private function matchSupportContactQuery(string $message): ?string
    {
        $q = $this->normalizeSearchText($message);

        $hasSupportIntent = Str::contains($q, [
            'admin bilan', 'adminga', 'admin email', 'admin telefon',
            'support', 'texnik yordam', 'boglan', "bog'lan", 'aloqa', 'kontakt',
            'murojaat', 'xabar yubor', 'xat yubor', 'qayerga yoz', 'shikoyat',
            'taklif', 'kim koradi', "kim ko'radi", 'kim javob', 'javob qayerdan',
            'aloqa bolimi', "aloqa bo'limi",
        ]);

        if (! $hasSupportIntent) {
            return null;
        }

        $phone = SiteSetting::get('school_phone', '+998 71 123 45 67');
        $email = SiteSetting::get('school_email', 'info@school81.uz');
        $address = SiteSetting::get('school_address', (string) __('public.about.quick_facts.0.value'));
        $contactUrl = route('contact');

        return "**Rasmiy murojaat tartibi**\n"
            ."- Oddiy foydalanuvchi uchun **admin bilan to'g'ridan-to'g'ri yozish yoki alohida admin kontakt** mavjud emas.\n"
            ."- Agar muammo, shikoyat yoki texnik masala bo'lsa, **Aloqa** sahifasi orqali rasmiy murojaat qoldiriladi: {$contactUrl}\n"
            ."- Murojaat avval qabul qilinadi, keyin ichki tartibda **tegishli admin, moderator yoki mas'ul xodimga** yuboriladi.\n"
            ."- Zarur bo'lsa javob emailingiz yoki qoldirilgan aloqa ma'lumotingiz orqali beriladi.\n\n"
            ."**Rasmiy aloqa kanallari**\n"
            ."- Telefon: **{$phone}**\n"
            ."- Email: **{$email}**\n"
            ."- Manzil: **{$address}**\n"
            ."- Bu kontaktlar maktabning rasmiy aloqa yo'llari bo'lib, shaxsiy admin chat'i hisoblanmaydi.\n\n"
            ."Texnik muammo bo'lsa, qaysi sahifada xato chiqqani, nima qilishga uringaningiz va muammo qachon chiqqanini ham yozing.";
    }

    private function matchRoleResponsibilitiesQuery(string $message): ?string
    {
        $q = $this->normalizeSearchText($message);

        $hasRoleIntent = Str::contains($q, [
            'nima ish qiladi', 'nima qiladi', 'vazifasi nima', 'vazifasi',
            'nimaga javobgar', 'qaysi ishlarni qiladi', 'nimalar qila oladi',
            'roli nima', 'vakolati nima', 'ishga javobgar',
        ]);

        if (! $hasRoleIntent) {
            return null;
        }

        if ($this->queryHasApproximateToken($q, ['moderator', 'moderato', 'moderat', 'modirator'])) {
            return "**Moderator vazifalari**\n"
                ."- Aloqa bo'limiga kelgan murojaatlarni ko'radi.\n"
                ."- Post va ustozlar sahifasidagi izohlarni boshqaradi.\n"
                ."- Global chatdagi tartibni saqlashda yordam beradi.\n"
                .'- Odatda tizim sozlamalari, AI bilim bazasi yoki umumiy system boshqaruvi moderator vakolatiga kirmaydi.';
        }

        if ($this->queryHasApproximateToken($q, ['editor', 'editr', 'edtor'])) {
            return "**Editor vazifalari**\n"
                ."- Yangiliklar, postlar, kategoriyalar va taqvim materiallarini boshqaradi.\n"
                ."- Kontentni tahrirlash va nashrga tayyorlash bilan ishlaydi.\n"
                .'- Odatda aloqa inboxi, system sozlamalari yoki super-admin darajadagi boshqaruv editor vakolatiga kirmaydi.';
        }

        if ($this->queryHasApproximateToken($q, ['super', 'superadmin', 'super admin'])) {
            return "**Super Admin vazifalari**\n"
                ."- Saytning eng yuqori darajadagi boshqaruviga ega.\n"
                ."- Sozlamalar, AI bilim bazasi, foydalanuvchilar va admin bo'limlarini to'liq boshqaradi.\n"
                ."- Kerak bo'lsa akkauntlarni bloklaydi yoki qayta faollashtiradi.\n"
                ."- Qolgan rollar ko'rmaydigan ko'proq texnik va maxfiy ma'lumotlarni ham ko'radi.";
        }

        if ($this->queryHasApproximateToken($q, ['admin', 'administrator', 'admn'])) {
            return "**Admin vazifalari**\n"
                ."- Admin paneldagi asosiy boshqaruv bo'limlari bilan ishlaydi.\n"
                ."- Ustozlar, imtihonlar, kurslar, contact xabarlari va comment moderatsiyasini boshqaradi.\n"
                ."- Saytdagi tartib, ta'lim bo'limlari va foydalanuvchi jarayonlarini nazorat qiladi.";
        }

        if ($this->queryHasApproximateToken($q, ['teacher', 'ustoz', "o'qituvchi", 'oqituvchi', 'muallim'])) {
            return "**O'qituvchi vazifalari**\n"
                ."- O'z kurslarini ochishi va boshqarishi mumkin.\n"
                ."- Kursga yozilgan foydalanuvchilarni ko'radi, tasdiqlaydi yoki rad etadi.\n"
                ."- Rol va ruxsatga qarab ta'lim bo'limlaridagi ayrim amallar bilan ham ishlaydi.";
        }

        return null;
    }

    private function matchSitePurposeQuery(string $message): ?string
    {
        $q = $this->normalizeSearchText($message);

        $hasSiteWord = Str::contains($q, ['sayt', 'sait', 'platforma', 'portal']);
        $hasPurposeIntent = Str::contains($q, [
            'nima uchun kerak', 'nimaga kerak', 'nima ga kerak', 'qaysi maqsadda',
            'vazifasi nima', 'maqsadi nima', 'nega kerak', 'nima uchun ishlatiladi',
        ]);

        if (! $hasSiteWord || ! $hasPurposeIntent) {
            return null;
        }

        $schoolName = SiteSetting::get('school_name', (string) __('public.layout.school_name'));

        return "**Saytning vazifasi**\n"
            ."- **{$schoolName}** ga oid asosiy ma'lumotlarni bir joyga jamlaydi.\n"
            ."- O'quvchilar uchun: kurslar, imtihonlar, natijalar, profil va chat imkoniyatlarini beradi.\n"
            ."- Ota-ona va mehmonlar uchun: yangiliklar, ustozlar, taqvim va aloqa bo'limlarini ko'rsatadi.\n"
            ."- Admin va xodimlar uchun: kontent, murojaatlar va ta'lim jarayonlarini boshqarishga yordam beradi.\n\n"
            .'Qisqa javob: bu sayt maktabning raqamli platformasi.';
    }

    private function matchKnowledgeBase(string $message): ?string
    {
        if (! $this->hasAiKnowledgeTable()) {
            return null;
        }

        $q = $this->normalizeSearchText($message);
        if ($q === '') {
            return null;
        }

        $tokens = array_slice($this->meaningfulTokens($q), 0, 10);
        if ($tokens === []) {
            return null;
        }

        $searchColumns = AiKnowledge::availableColumns([
            'question',
            'question_en',
            'keywords',
            'synonyms',
            'category',
        ]);
        $selectColumns = AiKnowledge::availableColumns([
            'question',
            'question_en',
            'answer',
            'answer_en',
            'keywords',
            'synonyms',
            'category',
            'priority',
        ]);

        if (! in_array('question', $searchColumns, true) || ! in_array('answer', $selectColumns, true)) {
            return null;
        }

        $rows = AiKnowledge::query()
            ->active()
            ->where(function ($query) use ($tokens, $searchColumns): void {
                foreach ($tokens as $token) {
                    foreach ($searchColumns as $column) {
                        $query->orWhere($column, 'like', '%'.$token.'%');
                    }
                }
            })
            ->orderedForMatching()
            ->limit(80)
            ->get($selectColumns);

        if ($rows->isEmpty()) {
            return null;
        }

        $best = null;
        $bestScore = 0;

        foreach ($rows as $row) {
            $questionEn = in_array('question_en', $selectColumns, true) ? (string) $row->question_en : '';
            $keywords = in_array('keywords', $selectColumns, true) ? (string) $row->keywords : '';
            $synonyms = in_array('synonyms', $selectColumns, true) ? (string) $row->synonyms : '';
            $category = in_array('category', $selectColumns, true) ? (string) $row->category : '';
            $priority = in_array('priority', $selectColumns, true) ? (int) $row->priority : 0;

            $candidateText = trim((string) $row->question.' '.$questionEn.' '.$keywords.' '.$synonyms.' '.$category);
            $candidateTokens = $this->meaningfulTokens($candidateText);
            $sharedTokens = $this->sharedTokenCount($tokens, $candidateTokens);

            if (count($tokens) >= 3 && $sharedTokens < 2) {
                continue;
            }

            if (count($tokens) === 2 && $sharedTokens < 1 && ! Str::contains($this->normalizeSearchText($candidateText), $q)) {
                continue;
            }

            $questionScore = $this->textMatchScore($q, (string) $row->question.' '.$questionEn);
            $keywordScore = $this->textMatchScore($q, $keywords);
            $synonymScore = $this->textMatchScore($q, $synonyms);
            $categoryScore = $this->textMatchScore($q, $category);
            $score = max($questionScore, min(100, $keywordScore + 12), min(100, $synonymScore + 18), $categoryScore);

            if ($sharedTokens > 0) {
                $score = min(100, $score + min(12, $sharedTokens * 4));
            }

            $score = min(100, $score + max(0, $priority));

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $row;
            }
        }

        $minScore = count($tokens) >= 3 ? 58 : 50;
        if (! $best || $bestScore < $minScore) {
            return null;
        }

        $answer = trim((string) $best->answer);
        if ($answer === '') {
            return null;
        }

        $category = in_array('category', $selectColumns, true)
            ? trim((string) $best->category)
            : '';
        $prefix = $category !== '' ? "**{$category}:**\n" : '';

        return $prefix.$answer;
    }

    private function siteCreditsPayload(): array
    {
        $intro = trim((string) __('public.about.site_credits_intro'));
        $rawMembers = trans('public.about.site_credits_members');

        $members = [];
        if (is_array($rawMembers)) {
            foreach ($rawMembers as $member) {
                $name = trim((string) ($member['name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                $members[] = [
                    'name' => $name,
                    'date' => trim((string) ($member['date'] ?? '')),
                ];
            }
        }

        return [
            'intro' => $intro !== '' ? $intro : "Ushbu sayt 10-E sinf o'quvchilari tomonidan ishlab chiqilgan.",
            'members' => $members,
        ];
    }

    private function normalizeSearchText(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $text = str_replace(['`', '‘', '’', 'ʼ', 'ʻ', '´'], "'", $text);
        $text = str_replace(['o‘', 'o’', 'g‘', 'g’'], ["o'", "o'", "g'", "g'"], $text);
        $text = preg_replace('/[^\p{L}\p{N}\']+/u', ' ', $text) ?? $text;

        return Str::squish($text);
    }

    private function normalizeCourseIntentText(string $text): string
    {
        $normalized = $this->normalizeSearchText($text);
        if ($normalized === '') {
            return '';
        }

        $tokens = preg_split('/\s+/u', $normalized) ?: [];
        $tokens = array_map(fn (string $token): string => $this->normalizeCourseToken($token), $tokens);

        return Str::squish(implode(' ', $tokens));
    }

    private function normalizeCourseToken(string $token): string
    {
        $plain = str_replace("'", '', $token);
        $typos = [
            'kusr' => 'kurs',
            'kusir' => 'kurs',
            'krs' => 'kurs',
            'kusrlar' => 'kurslar',
            'kusirlar' => 'kurslar',
            'kursla' => 'kurslar',
            'kurlar' => 'kurslar',
            'kurslani' => 'kurslarni',
        ];

        if (isset($typos[$plain])) {
            return $typos[$plain];
        }

        if (str_starts_with($plain, 'kusr')) {
            return 'kurs'.substr($plain, 4);
        }

        if (strlen($plain) >= 3 && strlen($plain) <= 5 && levenshtein($plain, 'kurs') <= 1) {
            return 'kurs';
        }

        if (strlen($plain) >= 5 && strlen($plain) <= 9 && levenshtein($plain, 'kurslar') <= 2) {
            return 'kurslar';
        }

        return $token;
    }

    private function hasGreetingIntent(string $text): bool
    {
        $normalized = $this->normalizeSearchText($text);

        if ($normalized === '') {
            return false;
        }

        if ($this->queryHasApproximateToken($normalized, [
            'salom', 'assalom', 'assalomu', 'asalom', 'asalomu', 'salam', 'hello', 'qalay',
            'hayrli', 'xayrli',
        ])) {
            return true;
        }

        if ($this->containsNormalizedPhrase($normalized, [
            'xush kelibsiz',
            'qalay ishlar',
            'hayr li',
            'xayr li',
        ])) {
            return true;
        }

        if (
            $this->queryHasApproximateToken($normalized, ['assalom', 'assalomu', 'asalom', 'asalomu', 'salom', 'salam'])
            && $this->queryHasApproximateToken($normalized, ['alaykum', 'aleykum', 'aleykum', 'alekum', 'alaykom', 'alekom'])
        ) {
            return true;
        }

        return $this->matchesApproximatePhraseWindow($normalized, [
            'assalomu alaykum',
            'assalomu aleykum',
            'assalomu alekum',
            'assalom alaykum',
            'assalom aleykum',
            'assalom alekum',
            'asalomu alaykum',
            'asalomu aleykum',
            'asalomu alekum',
            'asalom alaykum',
            'asalom aleykum',
            'asalom alekum',
            'salom alaykum',
            'salom aleykum',
            'salom alekum',
            'salam alaykum',
            'salam aleykum',
            'salam alekum',
            'assalomualaykum',
            'assalomualeykum',
            'assalomualekum',
            'asalomualaykum',
            'asalomualeykum',
            'asalomualekum',
        ], 3);
    }

    private function hasFarewellIntent(string $text): bool
    {
        $normalized = $this->normalizeSearchText($text);

        if ($normalized === '') {
            return false;
        }

        return $this->queryHasApproximateToken($normalized, ['xayr', 'hayr', 'bye', 'goodbye', 'chao'])
            || $this->containsNormalizedPhrase($normalized, ["ko'rishguncha", 'korishguncha', "sog' bo'ling", 'sog boling']);
    }

    private function hasThanksIntent(string $text): bool
    {
        $normalized = $this->normalizeSearchText($text);

        if ($normalized === '') {
            return false;
        }

        return $this->queryHasApproximateToken($normalized, ['rahmat', 'raxmat', 'tashakkur', 'thanks'])
            || $this->containsNormalizedPhrase($normalized, ['katta rahmat', 'minnatdor', "bor bo'ling", 'thank you']);
    }

    private function matchesApproximatePhraseWindow(string $text, array $variants, int $maxWindowSize = 3): bool
    {
        $tokens = preg_split('/\s+/u', $this->normalizeSearchText($text)) ?: [];

        if ($tokens === []) {
            return false;
        }

        $windows = [];
        $tokenCount = count($tokens);
        $maxWindowSize = max(1, $maxWindowSize);

        for ($start = 0; $start < $tokenCount; $start++) {
            for ($length = 1; $length <= $maxWindowSize && ($start + $length) <= $tokenCount; $length++) {
                $windows[] = implode('', array_slice($tokens, $start, $length));
            }
        }

        foreach ($variants as $variant) {
            $needle = str_replace(' ', '', $this->normalizeSearchText($variant));
            if ($needle === '') {
                continue;
            }

            foreach ($windows as $window) {
                if ($window === $needle) {
                    return true;
                }

                $maxErrors = mb_strlen($needle) >= 12 ? 2 : 1;
                if (abs(mb_strlen($window) - mb_strlen($needle)) > $maxErrors) {
                    continue;
                }

                if (levenshtein($window, $needle) <= $maxErrors) {
                    return true;
                }
            }
        }

        return false;
    }

    private function containsNormalizedPhrase(string $text, array $phrases): bool
    {
        $haystack = $this->normalizeSearchText($text);

        if ($haystack === '') {
            return false;
        }

        foreach ($phrases as $phrase) {
            $needle = $this->normalizeSearchText($phrase);

            if ($needle !== '' && Str::contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function hasAnyExactToken(string $text, array $variants): bool
    {
        $tokens = preg_split('/\s+/u', $this->normalizeSearchText($text)) ?: [];

        if ($tokens === []) {
            return false;
        }

        $lookup = [];
        foreach ($variants as $variant) {
            $needle = $this->normalizeSearchText($variant);
            if ($needle !== '') {
                $lookup[$needle] = true;
            }
        }

        foreach ($tokens as $token) {
            if (isset($lookup[$token])) {
                return true;
            }
        }

        return false;
    }

    private function meaningfulTokens(string $text): array
    {
        $text = $this->normalizeSearchText($text);
        $tokens = preg_split('/\s+/u', $text) ?: [];
        $stopWords = [
            'men', 'menga', 'meni', 'sen', 'siz', 'biz', 'ular', 'shu', 'bu', 'ana',
            'kim', 'nima', 'qanday', 'qanaqa', 'qaysi', 'qayerda', 'qayer', 'qachon',
            'necha', 'qancha', 'haqida', 'kerak', 'iltimos', 'ayt', 'ayting', 'ber',
            'bering', 'bor', 'yoq', 'yo\'q', 'ham', 'va', 'yoki', 'bilan', 'uchun',
            'the', 'a', 'an', 'is', 'are', 'what', 'who', 'where', 'when', 'how',
        ];

        return array_values(array_unique(array_filter($tokens, function ($token) use ($stopWords): bool {
            return mb_strlen($token) >= 3 && ! in_array($token, $stopWords, true);
        })));
    }

    private function textMatchScore(string $query, string $candidate): int
    {
        $query = $this->normalizeSearchText($query);
        $candidate = $this->normalizeSearchText($candidate);

        if ($query === '' || $candidate === '') {
            return 0;
        }

        if ($query === $candidate) {
            return 100;
        }

        if (mb_strlen($query) >= 5 && Str::contains($candidate, $query)) {
            return 96;
        }

        if (mb_strlen($candidate) >= 5 && Str::contains($query, $candidate)) {
            return 90;
        }

        $queryTokens = $this->meaningfulTokens($query);
        $candidateTokens = $this->meaningfulTokens($candidate);

        if ($queryTokens === [] || $candidateTokens === []) {
            return 0;
        }

        $hits = 0;
        foreach ($queryTokens as $qToken) {
            foreach ($candidateTokens as $cToken) {
                if ($qToken === $cToken) {
                    $hits += 2;
                    break;
                }

                if (Str::startsWith($cToken, $qToken) || Str::startsWith($qToken, $cToken)) {
                    $hits++;
                    break;
                }

                $maxErr = mb_strlen($qToken) <= 5 ? 1 : 2;
                if (levenshtein($qToken, $cToken) <= $maxErr) {
                    $hits++;
                    break;
                }
            }
        }

        $tokenScore = (int) round(($hits / (count($queryTokens) * 2)) * 100);

        similar_text(implode(' ', $queryTokens), implode(' ', $candidateTokens), $percent);

        return max($tokenScore, (int) round($percent));
    }

    private function sharedTokenCount(array $queryTokens, array $candidateTokens): int
    {
        if ($queryTokens === [] || $candidateTokens === []) {
            return 0;
        }

        $hits = 0;
        foreach ($queryTokens as $qToken) {
            foreach ($candidateTokens as $cToken) {
                if ($qToken === $cToken) {
                    $hits++;
                    break;
                }

                if (Str::startsWith($cToken, $qToken) || Str::startsWith($qToken, $cToken)) {
                    $hits++;
                    break;
                }

                $maxErr = mb_strlen($qToken) <= 5 ? 1 : 2;
                if (levenshtein($qToken, $cToken) <= $maxErr) {
                    $hits++;
                    break;
                }
            }
        }

        return $hits;
    }

    private function queryHasApproximateToken(string $text, array $variants): bool
    {
        $tokens = preg_split('/\s+/u', $this->normalizeSearchText($text)) ?: [];

        foreach ($tokens as $token) {
            foreach ($variants as $variant) {
                $variant = $this->normalizeSearchText($variant);
                if ($variant === '') {
                    continue;
                }

                if ($token === $variant) {
                    return true;
                }

                if (mb_strlen($token) >= 4 && (Str::startsWith($token, $variant) || Str::startsWith($variant, $token))) {
                    return true;
                }

                $maxErr = mb_strlen($variant) >= 8 ? 2 : 1;
                if (levenshtein($token, $variant) <= $maxErr) {
                    return true;
                }
            }
        }

        return false;
    }

    private function knowledgeSnippetsForPrompt(): string
    {
        if (! $this->hasAiKnowledgeTable()) {
            return 'Bilim bazasi jadvali hali mavjud emas.';
        }

        $selectColumns = AiKnowledge::availableColumns([
            'question',
            'answer',
            'keywords',
            'synonyms',
            'category',
            'priority',
        ]);

        if (! in_array('question', $selectColumns, true) || ! in_array('answer', $selectColumns, true)) {
            return "Bilim bazasi eski schema bilan ishlayapti. Qo'shimcha AI maydonlari hali migratsiya qilinmagan.";
        }

        $rows = AiKnowledge::query()
            ->active()
            ->orderedForMatching()
            ->take(12)
            ->get($selectColumns);

        if ($rows->isEmpty()) {
            return "Admin tomonidan kiritilgan maxsus savol-javoblar hali yo'q.";
        }

        return $rows->map(function ($row) use ($selectColumns): string {
            $category = in_array('category', $selectColumns, true) ? trim((string) $row->category) : '';
            $keywords = in_array('keywords', $selectColumns, true) ? trim((string) $row->keywords) : '';
            $synonyms = in_array('synonyms', $selectColumns, true) ? trim((string) $row->synonyms) : '';
            $priority = in_array('priority', $selectColumns, true) ? (int) $row->priority : 0;
            $answer = Str::limit(trim(preg_replace('/\s+/u', ' ', (string) $row->answer) ?? ''), 220);

            return '- '.($category !== '' ? "[{$category}] " : '')
                ."Savol: {$row->question}"
                .($keywords !== '' ? " | Kalitlar: {$keywords}" : '')
                .($synonyms !== '' ? " | Sinonimlar: {$synonyms}" : '')
                .($priority !== 0 ? " | Priority: {$priority}" : '')
                ." | Javob: {$answer}";
        })->implode("\n");
    }

    private function hasAiKnowledgeTable(): bool
    {
        if (self::$aiKnowledgeTableExists === null) {
            self::$aiKnowledgeTableExists = Schema::hasTable('ai_knowledges');
        }

        return self::$aiKnowledgeTableExists;
    }

    /**
     * Cleans noise words while PRESERVING intent words (qachon, kim, nima).
     */
    private function cleanMessage(string $text): string
    {
        // Faqat "shovqin" so'zlar — qidiruv mantig'iga hissa qo'shmaydigan so'zlar
        $noise = [
            'savolim', 'edi', 'ayting', 'bilasiz', 'bering', 'yana',
            'iltimos', 'menga', 'biror', 'qilib',
            '?', '!', '.', ',', '...', '-', ':', ';',
        ];

        // Grammatik qo'shimchalar: -da, -dan, -ga, -ni, -ning, -lar va h.k. (18+ qo'shimcha)
        // NB: bular so'z OXIRIAN olib tashlanadi, ildizini saqlab qoladi
        $suffixes = [
            'larning', 'larga', 'lardan', 'larni', 'larda',
            'ning', 'dan', 'dagi', 'dagi', 'dagi',
            'ga', 'ni', 'da', 'lar',
        ];

        $text = mb_strtolower(trim($text));

        foreach ($noise as $n) {
            $text = str_replace($n, ' ', $text);
        }

        // So'z oxiridagi grammatik qo'shimchalarni olib tashlash
        $words = preg_split('/\s+/', $text) ?: [];
        $cleaned = [];
        foreach ($words as $word) {
            $word = trim($word);
            if ($word === '') {
                continue;
            }
            foreach ($suffixes as $suffix) {
                if (mb_strlen($word) > mb_strlen($suffix) + 3
                    && mb_substr($word, -mb_strlen($suffix)) === $suffix) {
                    $word = mb_substr($word, 0, mb_strlen($word) - mb_strlen($suffix));
                    break;
                }
            }
            $cleaned[] = $word;
        }

        return Str::squish(implode(' ', $cleaned));
    }

    /**
     * Matches general site statistics and contact info.
     */
    private function matchUniversalData(string $message): ?string
    {
        $q = $this->normalizeCourseIntentText($message);
        $qClean = $this->cleanMessage($q);

        // 1. School Statistics
        if ($this->isMatch($q, $qClean, ['qancha', 'necha kishi', 'nechta', 'soni', 'statistika'])) {
            $teachers = Teacher::count();
            $users = \App\Models\User::count();
            $results = Result::count();

            return "Bizning maktabimiz haqida qisqacha ma'lumotlar:\n"
                ."• Ustozlarimiz soni: **{$teachers} ta** 👨‍🏫\n"
                ."• Ro'yxatdan o'tgan o'quvchilar: **{$users} ta** 🎓\n"
                ."• Topshirilgan imtihonlar: **{$results} ta** ✅\n"
                ."Biz doimo o'sib bormoqdamiz! 🚀";
        }

        // 2. Contact & Location — admin paneldan olinadi (SiteSetting orqali)
        if ($this->isMatch($q, $qClean, ['telefon', 'raqam', 'nomer', 'manzil', 'lokatsiya', 'qayerda', 'aloqa'])) {
            $phone = SiteSetting::get('school_phone', '+998 71 123 45 67');
            $address = SiteSetting::get('school_address', (string) __('public.about.quick_facts.0.value'));

            return "Biz bilan bog'lanish uchun:\n"
                ."📞 Telefon: **{$phone}**\n"
                ."📍 Manzil: **{$address}**\n"
                ."Batafsil ma'lumotni 'Aloqa' sahifasidan olishingiz mumkin. ✨";
        }

        // 3. School Identity / Principal
        if ($this->isMatch($q, $qClean, ['direktor', 'maktab haqida', 'idum nima', '81'])) {
            $schoolName = SiteSetting::get('school_name', (string) __('public.layout.school_name'));

            return "**{$schoolName}** - bu zamonaviy ta'lim texnologiyalari va tajribali ustozlar jamlangan ilm maskani. ✨\n"
                ."Direktor va ma'muriyat haqida ma'lumot 'Maktab ma'muriyati' bo'limida keltirilgan. 😊";
        }

        return null;
    }

    /**
     * Direktor / rahbar haqidagi turli xil savollarni ushlab olish.
     * Qo'llab-quvvatlash: "direktor kim", "derektir kim", "maktab direktori kimlar",
     * "direktoru kimlar", "maktab rahbari" va shunga o'xshash 15+ variantlar.
     */
    private function matchDirectorQuery(string $message): ?string
    {
        $q = mb_strtolower(trim($message));

        // Direktor so'zining barcha mumkin bo'lgan yozilishlari (imlo xatolari bilan)
        $directorPatterns = [
            'direktor', 'derektir', 'direktoru', 'direktorlar', 'direktori',
            'deroktor', 'derektr', 'direkktor', 'dirktor', 'direktar',
            'rahbar', 'mudur', 'mudiru', 'boshliq', 'boshlig',
        ];

        $hasDirWord = false;
        foreach ($directorPatterns as $pat) {
            if (Str::contains($q, $pat)) {
                $hasDirWord = true;
                break;
            }
        }

        // Fuzzy: levenshtein orqali ham tekshiramiz
        if (! $hasDirWord) {
            $words = preg_split('/\s+/', $q) ?: [];
            foreach ($words as $word) {
                if (mb_strlen($word) >= 5) {
                    foreach (['direktor', 'rahbar'] as $base) {
                        if (levenshtein($word, $base) <= 2) {
                            $hasDirWord = true;
                            break 2;
                        }
                    }
                }
            }
        }

        if (! $hasDirWord) {
            return null;
        }

        // DB dan direktor lavozimli shaxslarni topamiz
        $directorKeywords = ['direktor', 'rahbar', 'boshqaruvchi', 'mudur', 'boshliq'];
        $directorsFromDb = Teacher::where('is_active', true)
            ->where(function ($query) use ($directorKeywords) {
                foreach ($directorKeywords as $kw) {
                    $query->orWhere('lavozim', 'like', "%{$kw}%");
                }
            })
            ->select(['full_name', 'lavozim', 'subject', 'experience_years'])
            ->get();

        // Locale dan direktori nomini olishga urinamiz
        $localeDirector = $this->extractDirectorNameFromLocale();

        $schoolName = SiteSetting::get('school_name', (string) __('public.layout.school_name'));

        // Agar DB da direktor lavozimdagi ustoz topilsa
        if ($directorsFromDb->isNotEmpty()) {
            $lines = $directorsFromDb->map(function ($t) {
                $lavozim = trim((string) $t->lavozim);
                $staj = (int) ($t->experience_years ?? 0);
                $stajText = $staj > 0 ? "{$staj} yil staj" : '';
                $parts = array_filter([$lavozim, $stajText]);
                $detail = implode(' • ', $parts);

                return "👤 **{$t->full_name}**".($detail !== '' ? "\n   💼 {$detail}" : '');
            })->implode("\n\n");

            return "🏫 **{$schoolName}** maktabi rahbariyati:\n\n"
                .$lines
                ."\n\n✨ Batafsil ma'lumot 'Maktab haqida' bo'limida berilgan.";
        }

        // Faqat locale dan olingan nom bor
        if ($localeDirector !== null) {
            return "🏫 **{$schoolName}** direktorligi — **{$localeDirector}** tomonidan boshqariladi.\n"
                ."✨ Batafsil 'Maktab haqida' bo'limiga o'ting.";
        }

        // Hech narsa topilmagan
        return "🏫 **{$schoolName}** direktorlari haqida hozircha ma'lumot kiritilmagan.\n"
            ."Admin panelida ustozlar bo'limiga lavozim qo'shing. 😊";
    }

    private function matchSchoolProfileData(string $message): ?string
    {
        $q = $this->cleanMessage($message);
        $isSchoolDataIntent = Str::contains($q, [
            'maktab haqida', 'maktab ichida', 'ichidagi malumot', 'ichidagi ma\'lumot',
            'maktab malumot', 'maktab ma\'lumot', 'pasport', 'boshqaruv', 'school info',
            'maktab statistika', 'umumiy malumot',
        ]);

        if (! $isSchoolDataIntent) {
            return null;
        }

        $schoolName = SiteSetting::get('school_name', (string) __('public.layout.school_name'));
        $schoolPhone = SiteSetting::get('school_phone', '+998 71 123 45 67');
        $schoolEmail = SiteSetting::get('school_email', 'info@school81.uz');
        $schoolAddress = SiteSetting::get('school_address', (string) __('public.about.quick_facts.0.value'));
        $directorName = $this->extractDirectorNameFromLocale();

        $teachers = Teacher::where('is_active', true)->count();
        $students = User::query()->whereHas('roleRelation', fn ($q) => $q->where('name', User::ROLE_USER))->count();
        $courses = Course::where('status', Course::STATUS_PUBLISHED)->count();
        $posts = Post::count();
        $events = CalendarEvent::where('event_date', '>=', now())->count();

        $directorLine = $directorName !== null
            ? "• 🎓 Direktor: **{$directorName}**"
            : "• 🎓 Direktor: ma'lumot kiritilmagan";

        return "🏫 **{$schoolName}** — to'liq ma'lumot:\n\n"
            ."{$directorLine}\n"
            ."• 👨‍🏫 Faol ustozlar: **{$teachers} ta**\n"
            ."• 🎓 Ro'yxatdagi o'quvchilar: **{$students} ta**\n"
            ."• 📚 Faol kurslar: **{$courses} ta**\n"
            ."• 📰 Yangiliklar: **{$posts} ta**\n"
            ."• 📅 Yaqin tadbirlar: **{$events} ta**\n\n"
            ."📞 Tel: {$schoolPhone} | 📧 Email: {$schoolEmail}\n"
            ."📍 Manzil: {$schoolAddress}";
    }

    private function extractDirectorNameFromLocale(): ?string
    {
        $locationText = (string) __('public.about.cards.location_text');
        if ($locationText === '') {
            return null;
        }

        if (preg_match('/muassasasiga\\s+(.+?)\\s+rahbarlik\\s+qiladi/iu', $locationText, $matches)) {
            return trim((string) ($matches[1] ?? '')) ?: null;
        }

        return null;
    }

    /**
     * Intent-aware analytics answers:
     * - totals
     * - today/this week/this month windows
     * - growth vs previous period
     * - most/least requested courses
     * - mixed multi-metric prompts
     */
    private function matchAnalyticalData(string $message): ?string
    {
        $q = $this->cleanMessage($message);

        $wantsCount = Str::contains($q, ['nechta', 'qancha', 'soni', 'statistika', 'jami', 'umumiy', 'miqdor'])
            || preg_match('/\b\d+\s*ta\b/u', $q) === 1;
        $wantsGrowth = Str::contains($q, ['osish', 'o\'sish', 'kopay', 'ko\'pay', 'kamay', 'dinamika', 'taqqos', 'solishtir']);
        $wantsRanking = preg_match('/\b(eng kop|eng ko\'p|top|mashhur|popular|least|eng kam)\b/i', $q) === 1;

        if (! $wantsCount && ! $wantsGrowth && ! $wantsRanking) {
            return null; // Tahliliy maqsad (intent) yo'q
        }

        $entities = $this->extractRequestedEntities($q);
        $period = $this->extractRequestedPeriod($q);

        if ($entities === [] && ! $wantsRanking) {
            return null;
        }

        if ($wantsRanking) {
            $rankingAnswer = $this->buildCoursePopularityAnswer($q, $period);
            if ($rankingAnswer !== null) {
                return $rankingAnswer;
            }
        }

        if ($entities === []) {
            return "Savolingizni biroz aniqlashtirib bering: qaysi ko'rsatkich kerak (kurslar, ustozlar, o'quvchilar, yangiliklar, imtihonlar)?";
        }

        $lines = [];
        foreach ($entities as $entity) {
            $count = $this->countEntityForPeriod($entity, $period);
            $label = $this->entityLabel($entity);
            $lines[] = "• {$label}: {$count} ta";

            if ($wantsGrowth) {
                $previousCount = $this->countEntityForPreviousPeriod($entity, $period);
                $delta = $count - $previousCount;
                $trend = $delta > 0 ? "o'sgan" : ($delta < 0 ? 'kamaygan' : "o'zgarmagan");
                $lines[] = "  ↳ Oldingi davrga nisbatan: {$trend} ({$delta})";
            }
        }

        $periodLabel = $this->periodLabel($period);

        return "📊 {$periodLabel} bo'yicha natijalar:\n".implode("\n", $lines);
    }

    private function extractRequestedEntities(string $q): array
    {
        $map = [
            'courses' => ['kurs', 'dars', 'fan'],
            'teachers' => ['ustoz', 'oqituvchi', 'o\'qituvchi', 'teacher', 'domla', 'muallim'],
            'students' => ['oquvchi', 'o\'quvchi', 'user', 'foydalanuvchi', 'talaba'],
            'posts' => ['yangilik', 'post', 'maqola', 'elon', 'e\'lon'],
            'exams' => ['imtihonlar', 'imtihon soni', 'examlar', 'exam soni'],
            'results' => ['imtihon', 'natija', 'test', 'result'],
            'enrollments' => ['royxat', 'ro\'yxat', 'ariza', 'enroll', 'qabul'],
        ];

        $entities = [];
        foreach ($map as $entity => $keywords) {
            foreach ($keywords as $kw) {
                if (Str::contains($q, $kw)) {
                    $entities[] = $entity;
                    break;
                }
            }
        }

        if ($entities === [] && Str::contains($q, ['nechta', 'qancha', 'jami', 'umumiy'])) {
            $entities = ['courses', 'teachers', 'students', 'posts'];
        }

        return array_values(array_unique($entities));
    }

    private function extractRequestedPeriod(string $q): string
    {
        if (Str::contains($q, ['bugun', 'hozir bugun', 'today'])) {
            return 'today';
        }
        if (Str::contains($q, ['kecha', 'yesterday'])) {
            return 'yesterday';
        }
        if (Str::contains($q, ['hafta', 'this week', 'shu hafta'])) {
            return 'week';
        }
        if (Str::contains($q, ['oy', 'this month', 'shu oy'])) {
            return 'month';
        }

        return 'all';
    }

    private function countEntityForPeriod(string $entity, string $period): int
    {
        [$start, $end] = $this->resolvePeriodRange($period);
        $query = match ($entity) {
            'courses' => Course::query(),
            'teachers' => Teacher::query()->where('is_active', true),
            'students' => User::query()->whereHas('roleRelation', fn ($q) => $q->where('name', User::ROLE_USER)),
            'posts' => Post::query(),
            'exams' => Exam::query(),
            'results' => Result::query(),
            'enrollments' => CourseEnrollment::query(),
            default => null,
        };

        if (! $query) {
            return 0;
        }

        if ($start && $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }

        return (int) $query->count();
    }

    private function countEntityForPreviousPeriod(string $entity, string $period): int
    {
        if (! in_array($period, ['today', 'yesterday', 'week', 'month'], true)) {
            return 0;
        }

        [$start, $end] = $this->resolvePreviousPeriodRange($period);
        $query = match ($entity) {
            'courses' => Course::query(),
            'teachers' => Teacher::query()->where('is_active', true),
            'students' => User::query()->whereHas('roleRelation', fn ($q) => $q->where('name', User::ROLE_USER)),
            'posts' => Post::query(),
            'exams' => Exam::query(),
            'results' => Result::query(),
            'enrollments' => CourseEnrollment::query(),
            default => null,
        };

        if (! $query) {
            return 0;
        }

        return (int) $query->whereBetween('created_at', [$start, $end])->count();
    }

    private function resolvePeriodRange(string $period): array
    {
        return match ($period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            default => [null, null],
        };
    }

    private function resolvePreviousPeriodRange(string $period): array
    {
        return match ($period) {
            'today' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'yesterday' => [now()->subDays(2)->startOfDay(), now()->subDays(2)->endOfDay()],
            'week' => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            'month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            default => [now()->startOfDay(), now()->endOfDay()],
        };
    }

    private function buildCoursePopularityAnswer(string $q, string $period): ?string
    {
        $isCourseRelated = Str::contains($q, ['kurs', 'dars', 'fan', 'enroll', 'ariza', 'qabul', 'mashhur', 'popular', 'eng kop', 'eng ko\'p', 'eng kam']);
        if (! $isCourseRelated) {
            return null;
        }

        [$start, $end] = $this->resolvePeriodRange($period);

        $enrollmentQuery = CourseEnrollment::query()
            ->selectRaw('course_id, COUNT(*) as total')
            ->groupBy('course_id')
            ->with('course:id,title');

        if ($start && $end) {
            $enrollmentQuery->whereBetween('created_at', [$start, $end]);
        }

        $rows = $enrollmentQuery->orderByDesc('total')->take(3)->get();
        if ($rows->isEmpty()) {
            return "Bu davrda kurslar bo'yicha ariza ma'lumotlari yetarli emas.";
        }

        $least = $rows->sortBy('total')->first();
        $top = $rows->first();
        $periodLabel = $this->periodLabel($period);

        return "📈 {$periodLabel} bo'yicha kurslar faolligi:\n"
            .'• Eng mashhur: '.($top?->course?->title ?? 'Noma\'lum kurs')." ({$top->total} ta ariza)\n"
            .'• Nisbatan kam: '.($least?->course?->title ?? 'Noma\'lum kurs')." ({$least->total} ta ariza)\n"
            ."• Top-3 ro'yxat: ".$rows->map(fn ($r) => ($r->course?->title ?? 'Noma\'lum')." ({$r->total})")->implode(', ');
    }

    private function entityLabel(string $entity): string
    {
        return match ($entity) {
            'courses' => 'Kurslar',
            'teachers' => 'Faol ustozlar',
            'students' => 'O\'quvchilar',
            'posts' => 'Yangiliklar',
            'exams' => 'Imtihonlar',
            'results' => 'Imtihon natijalari',
            'enrollments' => 'Kurs arizalari',
            default => 'Ko\'rsatkich',
        };
    }

    private function periodLabel(string $period): string
    {
        return match ($period) {
            'today' => 'Bugun',
            'yesterday' => 'Kecha',
            'week' => 'Shu hafta',
            'month' => 'Shu oy',
            default => 'Umumiy',
        };
    }

    private function matchExamAssistantQuery(string $message, ?object $user = null): ?string
    {
        $q = $this->normalizeSearchText($message);

        if (! Str::contains($q, ['imtihon', 'exam', 'natija', 'ball', 'qayta topshir'])) {
            return null;
        }

        if ($user && method_exists($user, 'isParent') && $user->isParent()) {
            return "Ota-ona akkaunti bilan imtihon topshirib bo'lmaydi. Farzandingiz natijalarini ko'rish uchun o'quvchi akkauntidan foydalaning.";
        }

        if ($user && method_exists($user, 'canManageExams') && $user->canManageExams() && Str::contains($q, ['yarat', 'tuz', 'qo\'sh', 'savol'])) {
            return "Sizning rolingizda imtihon yaratish va savollar qo'shish mumkin. **Mening imtihonlarim** sahifasidan yangi imtihon oching va savollarni to'ldiring.";
        }

        if (Str::contains($q, ['faol', 'ochiq', 'aktiv'])) {
            $activeExams = Exam::query()->where('is_active', true)->orderByDesc('id')->take(5)->get();
            if ($activeExams->isEmpty()) {
                return 'Hozircha faol imtihonlar topilmadi.';
            }

            $lines = $activeExams->map(function (Exam $exam): string {
                $date = $exam->availableFromLabel();

                return 'вЂў '.$exam->title.($date ? " ({$date} dan)" : '');
            })->implode("\n");

            return "Hozir faol yoki ochilishi belgilangan imtihonlar:\n{$lines}";
        }

        if ($user && Str::contains($q, ['oxirgi', 'natijam', 'o\'tdim', 'otdim', 'yiqildim', 'o\'tgan', 'o\'tmagan', 'pass', 'fail'])) {
            $lastResult = Result::query()
                ->where('user_id', $user->id)
                ->with('exam')
                ->latest('submitted_at')
                ->latest('id')
                ->first();

            if (! $lastResult) {
                return "Sizda hozircha saqlangan imtihon natijasi yo'q.";
            }

            $status = $lastResult->passed === true
                ? "O'tgan"
                : ($lastResult->passed === false ? 'Yiqilgan' : 'Tekshiruvda');

            $points = $lastResult->points_max
                ? ($lastResult->points_earned.' / '.$lastResult->points_max.' ball')
                : (($lastResult->score ?? 0).' ta to\'g\'ri javob');

            return "Oxirgi natijangiz: **{$lastResult->exam?->title}**.\n"
                ."- Holat: **{$status}**\n"
                ."- Ko'rsatkich: **{$points}**\n"
                ."- Batafsilini profil natijalari bo'limida ko'rasiz.";
        }

        if (Str::contains($q, ['qayta topshir', 'yana topshir'])) {
            return "Hozirgi tizimda foydalanuvchi uchun bitta imtihon bo'yicha odatda bitta urinish saqlanadi. Agar urinish allaqachon yaratilgan bo'lsa, tizim sizni mavjud sessiya yoki natijaga qaytaradi. Qayta topshirish kerak bo'lsa, o'qituvchi yoki admin bilan kelishish kerak bo'ladi.";
        }

        return null;
    }

    private function matchCourseCatalogQuery(string $message): ?string
    {
        $q = $this->normalizeCourseIntentText($message);

        if (! $this->isCourseCatalogQuestion($q)) {
            return null;
        }

        if (! Schema::hasTable('courses')) {
            return null;
        }

        $courses = Course::query()
            ->select($this->availableColumnsForTable('courses', [
                'id',
                'teacher_id',
                'title',
                'title_en',
                'price',
                'price_en',
                'duration',
                'duration_en',
                'start_date',
                'status',
                'created_at',
            ]))
            ->when(Schema::hasTable('teachers'), function ($query): void {
                $query
                    ->whereHas('teacher', fn ($teacherQuery) => $teacherQuery->where('is_active', true))
                    ->with(['teacher' => function ($teacherQuery): void {
                        $teacherQuery->select($this->availableColumnsForTable('teachers', [
                            'id',
                            'full_name',
                            'subject',
                            'subject_en',
                            'is_active',
                        ]));
                    }]);
            })
            ->where('status', Course::STATUS_PUBLISHED)
            ->latest('id')
            ->take(8)
            ->get();

        if ($courses->isEmpty()) {
            return "Hozircha saytda nashr qilingan faol kurslar topilmadi. Yangi kurslar qo'shilsa, ular **Kurslar** sahifasida chiqadi: ".route('courses');
        }

        $lines = $courses
            ->values()
            ->map(fn (Course $course, int $index): string => $this->formatCourseCatalogLine($course, $index + 1))
            ->implode("\n");

        return "**Hozir saytda nashr qilingan kurslar:**\n"
            ."{$lines}\n\n"
            ."To'liq ma'lumot va yozilish uchun **Kurslar** sahifasiga o'ting: ".route('courses');
    }

    private function isCourseCatalogQuestion(string $q): bool
    {
        if ($q === '' || ! $this->hasCourseWord($q)) {
            return false;
        }

        if (Str::contains($q, [
            'kurs och',
            'kurs yarat',
            'ochish ruxsat',
            'tasdiqlash kodi',
            'publish code',
            'kursni tasdiq',
            'kim kurs och',
            'kurs ochgan',
            'ustoz kurs',
            'teacher kurs',
        ])) {
            return false;
        }

        if (Str::contains($q, ['qanday yozil', 'kursga yozilish', 'arizam', 'holati'])) {
            return false;
        }

        return Str::contains($q, [
            'kurslar',
            'kurslarni',
            'qaysi kurs',
            'qanaqa kurs',
            'nima kurs',
            'mavjud kurs',
            'bor kurs',
            'kurs bor',
            'royxat',
            'ro\'yxat',
            'sanab',
            'korsat',
            'ko\'rsat',
            'list',
            'hammasi',
        ])
            || in_array($q, ['kurs', 'kurslar'], true);
    }

    private function hasCourseWord(string $q): bool
    {
        return preg_match('/\bkurs[\p{L}\']*\b/u', $q) === 1
            || preg_match('/\bcourses?\b/u', $q) === 1;
    }

    private function formatCourseCatalogLine(Course $course, int $number): string
    {
        $title = $this->safeAiText($this->localizedModelText($course, 'title'), 120);
        $teacherName = $this->safeAiText((string) $course->teacher?->full_name, 80);
        $duration = $this->safeAiText($this->localizedModelText($course, 'duration'), 80);
        $price = $this->safeAiText($this->localizedModelText($course, 'price'), 80);
        $startDate = $course->start_date
            ? Carbon::parse($course->start_date)->format('d.m.Y')
            : '';

        $details = array_values(array_filter([
            $teacherName !== '' ? "Ustoz: {$teacherName}" : null,
            $duration !== '' ? "Davomiyligi: {$duration}" : null,
            $price !== '' ? "Narxi: {$price}" : null,
            $startDate !== '' ? "Boshlanishi: {$startDate}" : null,
        ]));

        return "{$number}. **{$title}**"
            .($details !== [] ? "\n   ".implode(' | ', $details) : '');
    }

    private function localizedModelText(object $model, string $field): string
    {
        if (function_exists('localized_model_value')) {
            return localized_model_value($model, $field);
        }

        return (string) data_get($model, $field, '');
    }

    private function safeAiText(string $text, int $limit): string
    {
        return Str::limit(Str::squish(strip_tags($text)), $limit, '');
    }

    private function availableColumnsForTable(string $table, array $columns): array
    {
        if (! Schema::hasTable($table)) {
            return $columns;
        }

        return array_values(array_filter(
            $columns,
            static fn (string $column): bool => Schema::hasColumn($table, $column)
        ));
    }

    private function matchCourseAssistantQuery(string $message, ?object $user = null): ?string
    {
        $q = $this->normalizeCourseIntentText($message);

        if ($courseByTitle = $this->matchPublishedCourseByTitle($message)) {
            return $courseByTitle;
        }

        if (! Str::contains($q, ['kurs', 'enroll', 'yozil', 'ariza', 'teacher kurs', 'ustoz kurs'])) {
            return null;
        }

        if ($user && method_exists($user, 'isTeacher') && $user->isTeacher() && Str::contains($q, ['och', 'yarat', 'manage', 'boshqar'])) {
            if (! $user->hasReachedCourseOpenLimit() && $user->hasCourseOpenApproval()) {
                return "Sizga kurs ochish ruxsati berilgan. Endi kurs formasini ochib, ma'lumotlarni to'ldirib nashr qilishingiz mumkin.";
            }

            if ($user->hasReachedCourseOpenLimit()) {
                return "Teacher akkaunti bilan hozircha bitta kurs yaratish limiti qo'llanadi. Siz bu limitga yetgansiz.";
            }

            return "Kurs ochishdan oldin profildagi **Kurs ochish ruxsati** bo'limidan admin ruxsatini so'rang. Ustoz kartasiga bog'lash shart emas.";
        }

        if ($courseCatalog = $this->matchCourseCatalogQuery($message)) {
            return $courseCatalog;
        }

        if (Str::contains($q, ['qaysi kurs', 'kurslar bor', 'nima kurs'])) {
            $courses = Course::query()
                ->where('status', Course::STATUS_PUBLISHED)
                ->with(['teacher:id,full_name', 'creator:id,name,first_name,last_name'])
                ->latest('id')
                ->take(5)
                ->get();

            if ($courses->isEmpty()) {
                return "Hozircha nashr etilgan kurslar yo'q.";
            }

            $lines = $courses->map(function (Course $course): string {
                return 'вЂў '.$course->title.($course->teacher ? ' вЂ” '.$course->teacher->full_name : '');
            })->implode("\n");

            return "Hozir mavjud kurslardan ba'zilari:\n{$lines}\n\nTo'liq ro'yxat kurslar sahifasida bor.";
        }

        if (Str::contains($q, ['qaysi ustoz kurs', 'ustoz kurs och', 'kim kurs ochgan'])) {
            $courses = Course::query()
                ->where('status', Course::STATUS_PUBLISHED)
                ->with(['teacher:id,full_name', 'creator:id,name,first_name,last_name'])
                ->latest('id')
                ->take(8)
                ->get()
                ->filter(fn (Course $course) => $course->teacher !== null);

            if ($courses->isEmpty()) {
                return 'Hozircha ustozlarga biriktirilgan nashr etilgan kurs topilmadi.';
            }

            $lines = $courses->map(fn (Course $course) => 'вЂў '.$course->teacher->full_name.' вЂ” '.$course->title)->implode("\n");

            return "Kurs ochgan ustozlardan ba'zilari:\n{$lines}";
        }

        if ($user && Str::contains($q, ['arizam', 'holati', 'statusim', 'yozilganmanmi'])) {
            $latestEnrollment = CourseEnrollment::query()
                ->where('user_id', $user->id)
                ->with('course:id,title')
                ->latest('id')
                ->first();

            if (! $latestEnrollment) {
                return 'Siz hali kursga ariza yubormagansiz. Kurslar sahifasidan kerakli kursni tanlab yozilishingiz mumkin.';
            }

            $status = match ($latestEnrollment->status) {
                CourseEnrollment::STATUS_APPROVED => 'Tasdiqlangan',
                CourseEnrollment::STATUS_REJECTED => 'Rad etilgan',
                default => 'Kutilmoqda',
            };

            return "Oxirgi kurs arizangiz: **{$latestEnrollment->course?->title}**.\n"
                ."- Holat: **{$status}**\n"
                ."- Batafsilini profilingizdagi kurslar blokida ko'rasiz.";
        }

        if (Str::contains($q, ['qanday yozil', 'qanday kiraman', 'kursga yozilish'])) {
            return "Kursga yozilish uchun **Kurslar** sahifasiga kiring, kerakli kursni tanlang va yozilish formasini yuboring. Ariza yuborilgach, kurs egasi uni ko'rib chiqadi.";
        }

        return null;
    }

    private function matchPublishedCourseByTitle(string $message): ?string
    {
        $q = $this->normalizeSearchText($message);
        if ($q === '' || mb_strlen($q) < 3) {
            return null;
        }

        $genericTokens = [
            'kurs', 'kurslar', 'kusr', 'kusrlar', 'dars', "o'quv", 'oqish', 'fan',
            'fanlar', 'royxat', "ro'yxat", 'qaysi', 'nima', 'bor',
        ];

        $tokens = array_values(array_filter(
            array_slice($this->meaningfulTokens($q), 0, 6),
            fn (string $token): bool => ! in_array($token, $genericTokens, true) && mb_strlen($token) >= 3
        ));

        if ($tokens === [] && mb_strlen($q) < 5) {
            return null;
        }

        $candidates = Course::query()
            ->where('status', Course::STATUS_PUBLISHED)
            ->with('teacher:id,full_name')
            ->where(function ($query) use ($q, $tokens): void {
                $query->whereRaw('LOWER(title) LIKE ?', ['%'.$q.'%']);
                foreach ($tokens as $token) {
                    $query->orWhereRaw('LOWER(title) LIKE ?', ['%'.$token.'%']);
                }
            })
            ->latest('id')
            ->take(10)
            ->get();

        if ($candidates->isEmpty()) {
            return null;
        }

        $contentWords = $this->meaningfulTokens($q);
        $best = $candidates
            ->map(function (Course $course) use ($q, $contentWords): array {
                $title = $this->normalizeSearchText((string) $course->title);
                $score = $this->teacherNameMatchScore($q, $title, $contentWords);
                if (Str::contains($title, $q) || Str::contains($q, $title)) {
                    $score += 25;
                }

                return ['course' => $course, 'score' => min(100, $score)];
            })
            ->sortByDesc('score')
            ->first();

        if (! $best || ($best['score'] ?? 0) < 58) {
            return null;
        }

        /** @var Course $course */
        $course = $best['course'];
        $teacher = $course->teacher?->full_name ? "Ustoz: **{$course->teacher->full_name}**\n" : '';

        return "Topildi: **{$course->title}**\n"
            .$teacher
            ."Kurs haqida batafsil ma'lumotni **Kurslar** bo'limida ko'rishingiz mumkin.";
    }

    /**
     * Matches topics to actual database entities with typo tolerance.
     */
    private function matchDynamicData(string $message, ?object $user = null): ?string
    {
        $q = $this->normalizeCourseIntentText($message);
        $qClean = $this->cleanMessage($q);

        if ($examAssistant = $this->matchExamAssistantQuery($message, $user)) {
            return $examAssistant;
        }

        if ($courseAssistant = $this->matchCourseAssistantQuery($message, $user)) {
            return $courseAssistant;
        }

        // 0. Ustozning kimligi (ism-familya) yoki lavozim bo'yicha qidiruv
        if ($teacherIdentity = $this->matchTeacherIdentityQuery($q, $qClean)) {
            return $teacherIdentity;
        }

        // 1. User Results
        if ($this->isMatch($q, $qClean, ['natija', 'ball', 'bal', 'imtihonim', 'score', 'imtihon natija', 'ochko', 'foiz'])) {
            if (! $user) {
                return "Sizning natijalaringizni ko'rish uchun avval tizimga kiring. 😊";
            }
            $lastResult = Result::where('user_id', $user->id)->with('exam')->latest()->first();
            if ($lastResult) {
                $passed = $lastResult->passed;
                $status = $passed === true ? "o'tdingiz ✅" : ($passed === false ? 'yeta olmadingiz ❌' : 'natija tekshirilmoqda ⏳');
                $points = $lastResult->points_earned !== null
                    ? " ({$lastResult->points_earned}/{$lastResult->points_max} ball)"
                    : '';

                return "Sizning oxirgi imtihoningiz: **{$lastResult->exam->title}**{$points}.\n"
                    ."Natijangiz: **{$lastResult->score}%** — {$status}.\n"
                    ."Batafsil ma'lumotni 'Profil' bo'limida ko'rishingiz mumkin. 🎓";
            }

            return "Siz hali imtihon topshirmagansiz. Imtihon bo'limiga o'tib sinab ko'ring! 📝";
        }

        // 2. User Profile
        if ($this->isMatch($q, $qClean, ['men kimman', 'ismim nima', 'profilim', 'akkauntim', 'mening ismim', 'mening rolim'])) {
            if (! $user) {
                return "Siz tizimga kirmagansiz. Iltimos, ro'yxatdan o'ting! 😊";
            }

            return "Sizning ismingiz **{$user->first_name} {$user->last_name}**. "
                ."Siz saytimizda **{$user->role_label}** maqomiga egasiz. ✨";
        }

        // 3. Courses — kengaytirilgan sinonimlar
        if ($this->isMatch($q, $qClean, ['kurs', 'dars', "o'quv", 'fanlar', 'kusrlar', 'kurslar', 'o\'rganish', 'dastur', 'program'])) {
            $courses = Course::where('status', 'published')->latest()->take(5)->get();
            if ($courses->isNotEmpty()) {
                $list = $courses->map(fn ($c) => "• {$c->title}")->implode("\n");

                return "Hozirgi faol kurslarimiz:\n{$list}\n\nBatafsil: 'Kurslar' bo'limidan ko'rishingiz mumkin. ✅";
            }

            return "Hozircha nashr etilgan kurslar yo'q. Tez orada yangi kurslar qo'shiladi! 😊";
        }

        // 4. Teachers — kengaytirilgan sinonimlar
        if ($this->isMatch($q, $qClean, ['ustoz', "o'qituvchi", 'domla', 'muallim', 'teacher', 'pedagog', 'o\'qtuvchi', 'o\'qi'])) {
            $teachers = Teacher::where('is_active', true)->latest()->take(6)->get();
            if ($teachers->isNotEmpty()) {
                $list = $teachers->map(fn ($t) => "• {$t->full_name}".($t->subject ? " — {$t->subject}" : ''))->implode("\n");

                return "Bizning tajribali ustozlarimizdan ba'zilari:\n{$list}\n\nTo'liq ro'yxat: 'Ustozlar' sahifasida. 👨‍🏫";
            }

            return "Hozircha o'qituvchilar ma'lumoti kiritilmagan. Keyinroq tekshirib ko'ring! ✨";
        }

        // 5. News/Events — kengaytirilgan sinonimlar
        if ($this->isMatch($q, $qClean, ['yangilik', 'tadbir', 'nima gap', "e'lon", 'post', 'xabar', 'yangililar', 'voqea', 'maqola'])) {
            $posts = Post::latest()->take(3)->pluck('title')->toArray();
            $events = CalendarEvent::where('event_date', '>=', now())->orderBy('event_date')->take(3)->pluck('title')->toArray();

            if (empty($posts) && empty($events)) {
                return "Hozircha yangi xabarlar va tadbirlar yo'q. Yana bir oz kutib ko'ring! ✨";
            }

            $res = '';
            if (! empty($posts)) {
                $res .= "📰 **So'nggi yangiliklar:**\n• ".implode("\n• ", $posts)."\n\n";
            }
            if (! empty($events)) {
                $res .= "📅 **Yaqin kunlardagi tadbirlar:**\n• ".implode("\n• ", $events);
            }

            return trim($res);
        }

        return null;
    }

    private function matchTeacherIdentityQuery(string $q, string $qClean): ?string
    {
        $teachers = Teacher::query()
            ->where('is_active', true)
            ->select(['full_name', 'subject', 'lavozim', 'experience_years', 'toifa'])
            ->get();

        if ($teachers->isEmpty()) {
            return null;
        }

        // "ustoz", "domla", "muallim" kabi umumiy so'zlarni qidiruvdan ajratib tur
        $genericTeacherWords = ['ustoz', 'domla', 'muallim', 'teacher', 'oqituvchi', 'pedagog',
            'o\'qituvchi', 'o\'qituvchilar', 'ustozlar'];
        $hasOnlyGenericWord = false;
        $hasSpecificName = false;

        // Agar so'rovda ism bo'lmaydigan so'z bo'lsa (3+ harf, lekin ustoz/domla emas), u aniq ism deb hisoblaymiz
        $words = preg_split('/\s+/u', mb_strtolower(trim($q))) ?: [];
        // Savol so'zlari (ism emas) — contentWords dan chiqariladi
        $stopWords = [
            'kim', 'kimlar', 'kimdir', 'u', 've', 'va', 'ni', 'ga', 'da', 'dan',
            'haqida', 'haqida', 'menga', 'ber', 'bering', 'qiladi', 'ishlaydi',
            'qaysi', 'ustoz', 'domla', 'muallim', 'teacher', 'oqituvchi',
            'ustozlar', 'iltimos', 'ayting', 'malumot', 'malumotlar', 'lumi',
            'bildir', 'kors', 'ayt', 'bilsam', 'lavozim', 'lavozimida',
        ];
        $contentWords = array_values(array_filter($words, fn ($w) => mb_strlen($w) >= 3
            && ! in_array(mb_strtolower($w), $stopWords, true)
        ));

        if (count($contentWords) === 0) {
            $hasOnlyGenericWord = true;
        } else {
            $hasSpecificName = true;
        }

        $normalizedQ = $this->normalizeForTeacherLookup($q);

        $isWhoQuestion = Str::contains($qClean, [
            'kim', 'kimligi', 'kim ekan', 'haqida', 'lavozimi', 'fani', 'qaysi fan', 'malumot', 'ma\'lumot',
        ]);
        $isRoleQuestion = Str::contains($q, [
            'lavozimda kim', 'lavozimda kimlar', 'kim ishlaydi', 'kimlar ishlaydi',
            'vazifada kim', 'o\'qituvchi lavozim', 'ishlagan',
        ]);

        // ──────────────────────────────────────────────────────────────────────
        // 1) ANIQ ISM QIDIRISH (eng muhim qism — token + Levenshtein fuzzy)
        // ──────────────────────────────────────────────────────────────────────
        if ($hasSpecificName) {
            $matchedByName = null;
            $bestNameScore = 0;

            foreach ($teachers as $teacher) {
                $normalizedTeacherName = $this->normalizeForTeacherLookup((string) $teacher->full_name);
                if ($normalizedTeacherName === '') {
                    continue;
                }

                $score = $this->teacherNameMatchScore($normalizedQ, $normalizedTeacherName, $contentWords);
                if ($score > $bestNameScore) {
                    $bestNameScore = $score;
                    $matchedByName = $teacher;
                }
            }

            // Threshold: ism bo'yicha savol bo'lsa 40+, aks holda 70+
            $minScore = ($isWhoQuestion) ? 40 : 70;
            if ($matchedByName && $bestNameScore >= $minScore) {
                return $this->formatTeacherCard($matchedByName);
            }
        }

        // ──────────────────────────────────────────────────────────────────────
        // 2) LAVOZIM BO'YICHA QIDIRUV (masalan: "o'qituvchilar" yoki "psixolog")
        // ──────────────────────────────────────────────────────────────────────
        $matchedRole = null;
        $bestRoleScore = 0;

        foreach ($teachers as $teacher) {
            $role = trim((string) $teacher->lavozim);
            if ($role === '') {
                continue;
            }

            $normalizedRole = $this->normalizeForTeacherLookup($role);

            // Fuzzy + token matching role uchun
            foreach ($contentWords as $cw) {
                if (Str::contains($normalizedRole, $cw) || Str::contains($cw, $normalizedRole)) {
                    if ($bestRoleScore < 90) {
                        $bestRoleScore = 90;
                        $matchedRole = $role;
                    }
                    break;
                }
                similar_text($cw, $normalizedRole, $pct);
                if ($pct > $bestRoleScore) {
                    $bestRoleScore = (int) round($pct);
                    $matchedRole = $role;
                }
            }
        }

        // Lavozim qidiruvi: minimum 45% mos kelishi kerak
        if (! $matchedRole || $bestRoleScore < 45) {
            // Agar faqat umumiy ustoz so'zi bo'lsa — null qaytaramiz (matchDynamicData() hal qiladi)
            return null;
        }

        $byRole = $teachers
            ->filter(fn ($t) => mb_strtolower(trim((string) $t->lavozim)) === mb_strtolower($matchedRole))
            ->sortByDesc('experience_years')
            ->values();

        if ($byRole->isEmpty()) {
            return "Bu lavozim bo'yicha faol ustoz topilmadi.";
        }

        // Limitlash: 10 ta dan ko'p chiqmasin
        $limit = 10;
        $total = $byRole->count();
        $shown = $byRole->take($limit);

        $lines = $shown->map(function ($t) {
            $staj = (int) ($t->experience_years ?? 0);
            $stajText = $staj > 0 ? "🕐 {$staj} yil staj" : '';
            $fan = trim((string) $t->subject);
            $fanText = $fan !== '' ? "• 📖 {$fan}" : '';
            $detail = array_filter([$stajText, $fanText]);

            return "👤 **{$t->full_name}**".(! empty($detail) ? ' — '.implode(' ', $detail) : '');
        })->implode("\n");

        $footer = $total > $limit
            ? "\n\n📋 Jami **{$total} ta** ustoz. To'liq ro'yxat: 'Ustozlar' sahifasida 👨‍🏫"
            : "\n\n✨ To'liq ma'lumot: 'Ustozlar' sahifasiga o'ting.";

        return "🏫 **{$matchedRole}** lavozimidagi ustozlar:\n\n{$lines}{$footer}";
    }

    /**
     * Bitta ustoz uchun chiroyli "card" formati.
     */
    private function formatTeacherCard(object $teacher): string
    {
        $name = trim((string) $teacher->full_name);
        $lavozim = trim((string) $teacher->lavozim);
        $fan = trim((string) $teacher->subject);
        $staj = (int) ($teacher->experience_years ?? 0);
        $toifa = trim((string) $teacher->toifa);

        $lines = ["👨‍🏫 **{$name}** haqida ma'lumot:", ''];

        if ($lavozim !== '') {
            $lines[] = "💼 Lavozim: {$lavozim}";
        }
        if ($fan !== '') {
            $lines[] = "📖 Fani: {$fan}";
        }
        if ($staj > 0) {
            $lines[] = "🕐 Staj: {$staj} yil";
        }
        if ($toifa !== '') {
            $lines[] = "🏅 Toifa: {$toifa}";
        }

        if (count($lines) === 2) {
            // Faqat ism bor, qo'shimcha ma'lumot yo'q
            $lines[] = "ℹ️ Qo'shimcha ma'lumot hali kiritilmagan.";
        }

        return implode("\n", $lines);
    }

    private function normalizeForTeacherLookup(string $text): string
    {
        $text = mb_strtolower(trim($text));
        // Maxsus belgilarni olib tashla, lekin harflarni saqla
        $text = preg_replace('/[^\p{L}\s]+/u', ' ', $text) ?? $text;

        return Str::squish($text);
    }

    /**
     * Ustoz nomini fuzzy scoring bilan solishtirish.
     * Levenshtein (imlo xatolari) + token overlap + similar_text kombinatsiyasi.
     *
     * @param  string[]  $contentWords  Savoldan ajratilgan mazmunli tokenlar
     */
    private function teacherNameMatchScore(string $query, string $candidate, array $contentWords = []): int
    {
        if ($query === '' || $candidate === '') {
            return 0;
        }

        // To'liq mos kelish
        if ($query === $candidate) {
            return 100;
        }

        // Substring mos kelish
        if (Str::contains($query, $candidate) || Str::contains($candidate, $query)) {
            return 95;
        }

        $candidateTokens = array_values(array_filter(preg_split('/\s+/u', $candidate) ?: []));

        // ── Token darajasida Levenshtein fuzzy matching ──
        $tokenHits = 0;
        foreach ($contentWords as $qToken) {
            if (mb_strlen($qToken) < 3) {
                continue;
            }
            foreach ($candidateTokens as $ct) {
                // 1. Boshlang'ich mos kelish
                if (Str::startsWith($ct, $qToken) || Str::startsWith($qToken, $ct)) {
                    $tokenHits += 2; // katta og'irlik
                    break;
                }
                // 2. Levenshtein — 1-2 harf xatosi
                $maxErr = mb_strlen($qToken) <= 5 ? 1 : 2;
                if (levenshtein($qToken, $ct) <= $maxErr) {
                    $tokenHits++;
                    break;
                }
                // 3. Substring mos kelish
                if (Str::contains($ct, $qToken) || Str::contains($qToken, $ct)) {
                    $tokenHits++;
                    break;
                }
            }
        }

        // Token score: contentWords bo'yicha (birinchi darajali signal)
        $tokenScore = count($contentWords) > 0
            ? (int) round(($tokenHits / (count($contentWords) * 2)) * 100)
            : 0;

        // similar_text: butun matn bo'yicha (qo'shimcha signal, shovqin bor)
        // Faqat contentWords bo'sh bo'lganida yoki tokenScore past bo'lganda ishlatiladi
        if ($tokenScore >= 40) {
            // Token mos kelish yetarli — fuzzy ni ishlatmaymiz
            return $tokenScore;
        }

        // Faqat candidate tokenlarini query tokenlari bilan solishtiramiz
        $candidateStr = implode(' ', $candidateTokens);
        $queryTokensStr = implode(' ', array_filter(preg_split('/\s+/u', $query) ?: []));
        similar_text($queryTokensStr, $candidateStr, $pct);
        $fuzzyScore = (int) round($pct);

        return max($tokenScore, $fuzzyScore);
    }

    /**
     * Helper to check if message matching target keywords (with fuzzy support).
     */
    private function isMatch(string $q, string $qClean, array $keywords): bool
    {
        foreach ($keywords as $kw) {
            $kw = mb_strtolower(trim($kw));
            if (Str::contains($q, $kw)) {
                return true;
            }

            similar_text($qClean, $this->cleanMessage($kw), $percent);
            if ($percent >= 75) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gemini API call with retry and fallback logic.
     */
    private function callGemini(string $message, ?object $user, array $conversationContext = []): array
    {
        $conversationContext = $this->finalizeConversationContext($message, $conversationContext);
        $resolvedMessage = (string) ($conversationContext['resolved_message'] ?? $message);
        $apiKey = (string) config('services.gemini.key');
        $model = (string) config('services.gemini.model', 'gemini-1.5-flash');

        if ($apiKey === '') {
            return [
                'success' => true,
                'text' => "Men asosan maktab saytining ichki yordamchisiman.\n\n"
                    ."Quyidagi mavzularda yordam bera olaman:\n"
                    ."- Maktab, ustozlar, kurslar va aloqa bo'limlari\n"
                    ."- Imtihonlar, natijalar va taqvim\n"
                    ."- Saytdan foydalanish bo'yicha yo'l-yo'riq\n"
                    ."- Oddiy hisob-kitoblar\n\n"
                    ."Maktabdan tashqari keng va global mavzular uchun mo'ljallanmaganman.",
                'source' => 'no_gemini_key',
            ];
            $fallbackText = "Kechirasiz, hozircha bu savolga aniq javob bera olmayman. ✨\n\nLekin men quyidagi mavzularda yordam bera olaman:\n"
                ."• Maktab haqida ma'lumotlar 🏫\n"
                ."• Eng so'nggi yangiliklar va tadbirlar 📅\n"
                ."• Kurslar va ustozlar haqida 👨‍🏫\n"
                ."• Imtihon natijalaringizni ko'rsatish 🎓\n\n"
                ."GEMINI_API_KEY sozlanmagani uchun tashqi AI o‘chiq. Admin bilim bazasi va sayt ichki ma’lumotlari ishlaydi.\n"
                ."Iltimos, savolingizni aniqroq yozing yoki kerakli bo'limga o'ting! 😊";

            return ['success' => true, 'text' => $fallbackText, 'source' => 'no_gemini_key'];
        }

        $systemInstruction = $this->buildSystemInstruction($user);
        $contents = [];

        foreach (array_slice($conversationContext['history'] ?? [], -6) as $item) {
            $text = trim((string) ($item['text'] ?? ''));
            if ($text === '') {
                continue;
            }

            $contents[] = [
                'role' => ($item['role'] ?? 'user') === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $text]],
            ];
        }

        $currentPrompt = $message;
        if (($conversationContext['context_applied'] ?? false) && $resolvedMessage !== trim($message)) {
            $currentPrompt = "Joriy savol: {$message}\nKontekst bo'yicha izohlangan savol: {$resolvedMessage}";
        }

        $contents[] = ['role' => 'user', 'parts' => [['text' => $currentPrompt]]];

        $maxRetries = 2;
        $retryCount = 0;

        while ($retryCount <= $maxRetries) {
            try {
                $response = Http::timeout(45)->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'system_instruction' => ['parts' => [['text' => $systemInstruction]]],
                    'contents' => $contents,
                    'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 1024],
                ]);

                if ($response->successful()) {
                    $aiText = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if ($aiText) {
                        return ['success' => true, 'text' => $this->handleTicketCreation($aiText), 'source' => 'gemini'];
                    }
                }

                if ($response->status() === 429) {
                    $retryCount++;
                    sleep(2 * $retryCount); // Linear backoff

                    continue;
                }
                break;
            } catch (\Exception $e) {
                $retryCount++;
                sleep(1);
            }
        }

        // Ultimate Fallback: Smart local response if Gemini fails
        $localFallback = $this->matchDynamicData($resolvedMessage, $user);

        if ($localFallback) {
            return ['success' => true, 'text' => $localFallback, 'source' => 'local_fallback'];
        }

        return [
            'success' => true,
            'text' => "Men asosan maktab saytining ichki yordamchisiman.\n\n"
                ."Quyidagi mavzularda yordam bera olaman:\n"
                ."- Maktab, ustozlar, kurslar va aloqa bo'limlari\n"
                ."- Imtihonlar, natijalar va taqvim\n"
                ."- Saytdan foydalanish bo'yicha yo'l-yo'riq\n"
                ."- Oddiy hisob-kitoblar\n\n"
                ."Maktabdan tashqari keng va global mavzular uchun mo'ljallanmaganman.",
            'source' => 'ultimate_fallback',
        ];

        $fallbackText = "Kechirasiz, hozircha bu savolga aniq javob bera olmayman. ✨\n\nLekin men quyidagi mavzularda yordam bera olaman:\n"
            ."• Maktab haqida ma'lumotlar 🏫\n"
            ."• Eng so'nggi yangiliklar va tadbirlar 📅\n"
            ."• Kurslar va ustozlar haqida 👨‍🏫\n"
            ."• Imtihon natijalaringizni ko'rsatish 🎓\n\n"
            ."Iltimos, savolingizni aniqroq yozing yoki kerakli bo'limga o'ting! 😊";

        return ['success' => true, 'text' => $fallbackText, 'source' => 'ultimate_fallback'];
    }

    private function buildSystemInstruction(?object $user): string
    {
        $tz = (string) config('app.timezone', 'UTC');
        $now = Carbon::now($tz);
        $schoolName = SiteSetting::get('school_name', (string) __('public.layout.school_name'));
        $phone = SiteSetting::get('school_phone', '');
        $address = SiteSetting::get('school_address', '');
        $email = SiteSetting::get('school_email', '');
        $credits = $this->siteCreditsPayload();
        $siteCreditsList = collect($credits['members'])
            ->map(fn ($member) => "- {$member['name']}".($member['date'] !== '' ? " ({$member['date']})" : ''))
            ->implode("\n");
        if ($siteCreditsList === '') {
            $siteCreditsList = "- 10-E sinf o'quvchilari jamoasi";
        }
        $knowledgeSnippets = $this->knowledgeSnippetsForPrompt();

        // Maktab statistikasi
        $teacherCount = Teacher::where('is_active', true)->count();
        $studentCount = \App\Models\User::whereHas('roleRelation', fn ($q) => $q->where('name', \App\Models\User::ROLE_USER))->count();
        $courseCount = Course::where('status', Course::STATUS_PUBLISHED)->count();

        // O'qituvchilar ro'yxati (top 8)
        $teachersList = Teacher::where('is_active', true)
            ->latest('id')
            ->take(8)
            ->get()
            ->map(fn ($t) => "- {$t->full_name}".($t->subject ? " ({$t->subject})" : ''))
            ->implode("\n");

        // Nashr etilgan kurslar (top 6)
        $coursesList = Course::where('status', Course::STATUS_PUBLISHED)
            ->latest('id')
            ->take(6)
            ->get()
            ->map(fn ($c) => "- {$c->title}")
            ->implode("\n");

        // Foydalanuvchi konteksti
        $userContext = '';
        if ($user) {
            $userContext = "\n\n=== FOYDALANUVCHI ==="
                ."\nIsm: {$user->first_name} {$user->last_name}"
                ."\nRol: {$user->role_label}"
                ."\nFoydalanuvchiga ism bilan murojaat qiling.";
        }

        return <<<PROMPT
Sen {$schoolName} maktabi veb-saytining ichki AI yordamchisisiz.

=== ASOSIY QO'LLANMA ===
1. FAQAT O'ZBEK TILIDA javob ber.
1a. Agar keyingi ko'rsatmalarda zid joy bo'lsa, ushbu bandlarni ustun qo'y: sen GLOBAL AI emassan.
1b. Asosiy vazifang - maktab, sayt bo'limlari, kurslar, ustozlar, imtihonlar, taqvim, aloqa, profil va admin jarayonlari bo'yicha yordam berish.
1c. Juda sodda hisob-kitoblar (masalan 2+2 yoki 12/3) bo'lsa qisqa javob berishing mumkin.
1d. Maktabdan tashqari keng va global mavzularda uzun javob bermagin; foydalanuvchiga bu saytning ichki yordamchisi ekaningni ayt.
2. Har qanday savolga javob berishga harakat qil — maktab haqida ham, umumiy bilim (matematika, fizika, biologiya, tarix, geografiya, ingliz tili va boshqa fanlar) haqida ham.
3. Javoblar qisqa, aniq va foydali bo'lsin. Emojilar qo'sh. ✨
4. Agar maktabga oid ma'lumot so'ralsa, avvalo quyidagi ma'lumotlardan foydalan.
5. Bilmagan narsani taxmin qilib javob berma — "Bu haqda aniq ma'lumotim yo'q" de.

=== MAKTAB MA'LUMOTLARI ===
Maktab nomi: {$schoolName}
Telefon: {$phone}
Manzil: {$address}
Email: {$email}
Faol o'qituvchilar: {$teacherCount} ta
Ro'yxatdagi o'quvchilar: {$studentCount} ta
Faol kurslar: {$courseCount} ta

=== O'QITUVCHILAR (faollar) ===
{$teachersList}

=== MAVJud KURSLAR ===
{$coursesList}

=== SAYT MUALLIFLARI VA JAMOA ===
{$credits['intro']}
{$siteCreditsList}

=== SAYT BO'LIMLARI ===
- Yangiliklar: post va e'lonlar
- Ustozlar: faol o'qituvchilar profillari
- Kurslar: kurslar, arizalar va tasdiqlash jarayoni
- Imtihonlar: testlar, natijalar, matnli javoblarni baholash
- Taqvim: maktab tadbirlari
- Aloqa: murojaatlar
- Profil: foydalanuvchi ma'lumotlari, natijalar, kurs arizalari
- Admin panel: content, inbox, ta'lim, foydalanuvchilar va sozlamalar

=== ADMIN AI BILIM BAZASI ===
{$knowledgeSnippets}

=== HOZIRGI VAQT ===
Sana: {$now->format('d.m.Y')}, {$now->translatedFormat('l')}
Vaqt: {$now->format('H:i')} (Toshkent vaqti){$userContext}

=== MUHIM ===
- Maktab haqidagi savollarga yuqoridagi ma'lumotlardan foydalanib javob ber.
- Sayt muallifi yoki kim ishtirok etgani so'ralsa, SAYT MUALLIFLARI VA JAMOA bo'limidagi ismlarni aniq ayt.
- Admin AI bilim bazasidagi javob savolga mos kelsa, avvalo o'sha javobga tayan.
- Maktabdan tashqari keng va global mavzularda javobni cheklagin; kerak bo'lsa foydalanuvchiga bu saytning ichki yordamchisi ekaningni eslat.
- Umumiy bilim savollarga (masalan: "Pythagoras teoremasi?", "Suv formulasi?") — oddiy, tushunarli javob ber.
- Siyosat, zararli kontent, noqonuniy narsalar haqida javob berma.
- Javob 5-6 jumladan oshmasin (oddiy suhbat uchun).
PROMPT;
    }

    private function handleTicketCreation(string $text): string
    {
        if (Str::contains($text, '[CREATE_TICKET:')) {
            $description = Str::between($text, '[CREATE_TICKET:', ']');
            $ticket = ContactMessage::create([
                'name' => auth()->check() ? auth()->user()->name : 'AI System',
                'email' => auth()->check() ? auth()->user()->email : 'ai@81-maktab.uz',
                'message' => 'AI Murojaat: '.$description,
            ]);

            return Str::of($text)->before('[CREATE_TICKET:')->trim()->value()."\n\n📌 №{$ticket->id} raqamli murojaat yaratildi! ✅";
        }

        return $text;
    }
}
