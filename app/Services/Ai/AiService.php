<?php

namespace App\Services\Ai;

use App\Models\AiKnowledge;
use App\Models\CalendarEvent;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Exam;
use App\Models\Post;
use App\Models\User;
use App\Models\Teacher;
use App\Models\ContactMessage;
use App\Models\Result;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AiService
{
    private const GEMINI_CALLS_PER_MINUTE_SOFT_LIMIT = 14;

    /**
     * Main entry point for generating a response.
     */
    public function generateResponse(string $userMessage, ?object $user = null): array
    {
        $message = trim($userMessage);
        
        // 0. Smart analytics (intent-based, not keyword-locked)
        if ($analytics = $this->matchAnalyticalData($message)) {
            return ['success' => true, 'text' => $analytics, 'source' => 'analytics_data'];
        }

        // 0.1 School profile / director / internal data summary
        if ($schoolData = $this->matchSchoolProfileData($message)) {
            return ['success' => true, 'text' => $schoolData, 'source' => 'school_profile'];
        }

        // 1. Try Local Machine/Static Knowledge (Greetings, Persona)
        if ($static = $this->matchStaticKnowledge($message)) {
            return ['success' => true, 'text' => $static, 'source' => 'static_knowledge'];
        }

        // 2. Universal Site Stats & Contact (New)
        if ($universal = $this->matchUniversalData($message)) {
            return ['success' => true, 'text' => $universal, 'source' => 'universal_data'];
        }

        // 3. Try Dynamic Data (Personal Results, Courses, Teachers) - Priority for "My Results"
        if ($dynamic = $this->matchDynamicData($message, $user)) {
            return ['success' => true, 'text' => $dynamic, 'source' => 'dynamic_data'];
        }

        // 4. Try Database Knowledge Base (Admin Managed Q&A)
        if ($dbResult = $this->matchDatabaseKnowledge($message)) {
            return ['success' => true, 'text' => $dbResult, 'source' => 'database_knowledge'];
        }

        // 5. Fallback to Gemini API
        return $this->callGemini($message, $user);
    }

    /**
     * Hardcoded static knowledge and small talk.
     */
    private function matchStaticKnowledge(string $message): ?string
    {
        $q = $this->cleanMessage($message);

        // Greetings & Persona
        if (Str::contains($q, ['salom', 'assalom', 'qalay', 'ishlar', 'hayrli tun', 'hayrli kun', 'keling'])) {
            return "Assalomu alaykum! Men 81-IDUM saytining aqlli yordamchisiman. Sizga qanday yordam bera olaman? 😊🚀";
        }

        if (Str::contains($q, ['kim yasagan', 'muallif', 'kim yaratgan', 'saytni kim'])) {
            $siteCreditsIntro = (string) __('public.about.site_credits_intro');
            $siteCredits = trans('public.about.site_credits_members');
            $names = [];
            if (is_array($siteCredits)) {
                foreach ($siteCredits as $member) {
                    if ($name = trim((string) ($member['name'] ?? ''))) $names[] = $name;
                }
            }
            return $siteCreditsIntro . ' Mualliflar: ' . implode(', ', $names) . '.';
        }

        if (Str::contains($q, ['admin', 'boshqaruvchi', 'kim yuritadi', 'mas\'ul', 'yordamchi'])) {
            return "Hozirgi paytda saytni Xabibullayev Shamsiddin boshqaradi. Qolgan hamkorlar moderator va editor sifatida yordam beradi. ✨";
        }

        if (Str::contains($q, ['rahmat', 'katta rahmat', 'minnatdor', 'bor bo\'ling'])) {
            return "Arziydi! Sizga yordam berganimdan xursandman. Savollaringiz bo'lsa, har doim tayyorman! 😊✅";
        }

        if (Str::contains($q, ['qandaysan', 'yaxshimi', 'tuzukmi', 'kimsan', 'nima qilasan'])) {
            return "Men 81-IDUM saytining virtual yordamchisiman. Maktab, darslar, yangiliklar va natijalaringiz haqida savollarga javob bera olaman. ✨🚀";
        }

        return null;
    }

    /**
     * Advanced fuzzy matching in the ai_knowledges table.
     */
    private function matchDatabaseKnowledge(string $message): ?string
    {
        $q = mb_strtolower(trim($message));
        $qClean = $this->cleanMessage($q);
        
        $knowledges = AiKnowledge::where('is_active', true)->get();
        $matches = [];

        foreach ($knowledges as $item) {
            $bestScore = 0;
            
            // 1. Compare against question_name (Fuzzy)
            similar_text($qClean, $this->cleanMessage($item->question_name), $percent);
            $bestScore = max($bestScore, $percent);

            // 2. Compare against keywords (Weighted)
            $keywords = explode(',', (string) $item->keywords);
            foreach ($keywords as $kw) {
                $kw = mb_strtolower(trim($kw));
                if ($kw === '') continue;

                // Exact substring match gets a huge boost!
                if (Str::contains($q, $kw)) {
                    $bestScore = max($bestScore, 90);
                }

                similar_text($qClean, $this->cleanMessage($kw), $kwPercent);
                $bestScore = max($bestScore, $kwPercent);
            }

            if ($bestScore >= 60) {
                $matches[] = [
                    'score' => $bestScore,
                    'answer' => $item->answer_text
                ];
            }
        }

        if (!empty($matches)) {
            usort($matches, fn($a, $b) => $b['score'] <=> $a['score']);
            return $matches[0]['answer'];
        }

        return null;
    }

    /**
     * Cleans noise words while PRESERVING intent words (qachon, kim, nima).
     */
    private function cleanMessage(string $text): string
    {
        $noise = [
            'savolim', 'bor', 'edi', 'ayting', 'bilasiz', 'haqida', 'bering', 'yana',
            'iltimos', 'qanaqa', 'qanday',
            '?', '!', '.', ',', '...', '-', ':', ';'
        ];
        
        $text = mb_strtolower($text);
        foreach ($noise as $n) {
            $text = str_replace($n, '', $text);
        }
        
        return Str::squish($text);
    }

    /**
     * Matches general site statistics and contact info.
     */
    private function matchUniversalData(string $message): ?string
    {
        $q = mb_strtolower(trim($message));
        $qClean = $this->cleanMessage($q);

        // 1. School Statistics
        if ($this->isMatch($q, $qClean, ['qancha', 'necha kishi', 'nechta', 'soni', 'statistika'])) {
            $teachers = Teacher::count();
            $users = \App\Models\User::count();
            $results = Result::count();
            
            return "Bizning maktabimiz haqida qisqacha ma'lumotlar:\n"
                . "• Ustozlarimiz soni: **{$teachers} ta** 👨‍🏫\n"
                . "• Ro'yxatdan o'tgan o'quvchilar: **{$users} ta** 🎓\n"
                . "• Topshirilgan imtihonlar: **{$results} ta** ✅\n"
                . "Biz doimo o'sib bormoqdamiz! 🚀";
        }

        // 2. Contact & Location
        if ($this->isMatch($q, $qClean, ['telefon', 'raqam', 'nomer', 'manzil', 'lokatsiya', 'qayerda', 'aloqa'])) {
            return "Biz bilan bog'lanish uchun:\n"
                . "📞 Telefon: **+998 71 123 45 67**\n"
                . "📍 Manzil: **Toshkent viloyati, Zangiota tumani**\n"
                . "Batafsil ma'lumotni 'Aloqa' sahifasidan olishingiz mumkin. ✨";
        }

        // 3. School Identity / Principal
        if ($this->isMatch($q, $qClean, ['direktor', 'maktab haqida', 'idum nima', '81'])) {
            $schoolName = (string) __('public.layout.school_name');
            return "**{$schoolName}** - bu zamonaviy ta'lim texnologiyalari va tajribali ustozlar jamlangan ilm maskani. ✨\n"
                . "Direktor va ma'muriyat haqida ma'lumot 'Maktab ma'muriyati' bo'limida keltirilgan. 😊";
        }

        return null;
    }

    private function matchSchoolProfileData(string $message): ?string
    {
        $q = $this->cleanMessage($message);
        $isSchoolDataIntent = Str::contains($q, [
            'direktor', 'rahbar', 'maktab haqida', 'maktab ichi', 'ichidagi malumot', 'ichidagi ma\'lumot',
            'maktab malumot', 'maktab ma\'lumot', 'pasport', 'boshqaruv', 'school info',
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
            ? "• Maktab direktori: {$directorName}"
            : "• Maktab direktori: hozircha admin panelda alohida saqlanmagan";

        return "🏫 {$schoolName} bo'yicha ichki ma'lumotlar:\n"
            . "{$directorLine}\n"
            . "• Faol ustozlar: {$teachers} ta\n"
            . "• Ro'yxatdan o'tgan o'quvchilar: {$students} ta\n"
            . "• Faol kurslar: {$courses} ta\n"
            . "• Yangiliklar: {$posts} ta\n"
            . "• Yaqin tadbirlar: {$events} ta\n"
            . "• Telefon: {$schoolPhone}\n"
            . "• Email: {$schoolEmail}\n"
            . "• Manzil: {$schoolAddress}";
    }

    private function extractDirectorNameFromLocale(): ?string
    {
        $locationText = (string) __('public.about.cards.location_text');
        if ($locationText === '') {
            return null;
        }

        if (preg_match("/muassasasiga\\s+(.+?)\\s+rahbarlik\\s+qiladi/iu", $locationText, $matches)) {
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

        $entities = $this->extractRequestedEntities($q);
        $period = $this->extractRequestedPeriod($q);
        $wantsGrowth = Str::contains($q, ['osish', 'o\'sish', 'kopay', 'ko\'pay', 'kamay', 'dinamika', 'taqqos', 'solishtir']);
        $wantsRanking = Str::contains($q, ['eng kop', 'eng ko\'p', 'top', 'mashhur', 'popular', 'least', 'eng kam']);

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
                $trend = $delta > 0 ? "o'sgan" : ($delta < 0 ? "kamaygan" : "o'zgarmagan");
                $lines[] = "  ↳ Oldingi davrga nisbatan: {$trend} ({$delta})";
            }
        }

        $periodLabel = $this->periodLabel($period);
        return "📊 {$periodLabel} bo'yicha natijalar:\n" . implode("\n", $lines);
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
            . "• Eng mashhur: " . ($top?->course?->title ?? 'Noma\'lum kurs') . " ({$top->total} ta ariza)\n"
            . "• Nisbatan kam: " . ($least?->course?->title ?? 'Noma\'lum kurs') . " ({$least->total} ta ariza)\n"
            . "• Top-3 ro'yxat: " . $rows->map(fn ($r) => ($r->course?->title ?? 'Noma\'lum') . " ({$r->total})")->implode(', ');
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

    /**
     * Matches topics to actual database entities with typo tolerance.
     */
    private function matchDynamicData(string $message, ?object $user = null): ?string
    {
        $q = mb_strtolower(trim($message));
        $qClean = $this->cleanMessage($q);

        // 1. User Results
        if ($this->isMatch($q, $qClean, ['natija', 'bal', 'imtihonim', 'score', 'imtihon natija'])) {
            if (!$user) return "Sizning natijalaringizni ko'rish uchun avval tizimga kiring. 😊";

            $lastResult = Result::where('user_id', $user->id)->with('exam')->latest()->first();
            if ($lastResult) {
                $status = $lastResult->passed ? "o'tdingiz ✅" : "yeta olmadingiz ❌";
                return "Sizning oxirgi imtihoningiz: **{$lastResult->exam->title}**.\nNatijangiz: **{$lastResult->score}%**. Siz ushbu imtihondan {$status}.\nBatafsil ma'lumotni 'Profil' bo'limida ko'rishingiz mumkin. 🎓";
            }
            return "Siz hali imtihon topshirmagansiz yoki natijalaringiz hali chiqmagan. 📝";
        }

        // 2. User Profile
        if ($this->isMatch($q, $qClean, ['men kimman', 'ismim nima', 'profilim', 'akkauntim'])) {
            if (!$user) return "Siz tizimga kirmagansiz. Iltimos, ro'yxatdan o'ting! 😊";
            return "Sizning ismingiz **{$user->first_name} {$user->last_name}**. Siz saytimizda **{$user->role_label}** maqomiga egasiz. ✨";
        }

        // 3. Courses
        if ($this->isMatch($q, $qClean, ['kurs', 'dars', 'o\'quv', 'fanlar', 'kusrlar'])) {
            $courses = Course::where('status', 'published')->latest()->take(3)->get();
            if ($courses->isNotEmpty()) {
                $list = $courses->map(fn($c) => "• {$c->title}")->implode("\n");
                return "Hozirgi mavjud kurslarimiz:\n{$list}\nBatafsil ma'lumotni 'Kurslar' bo'limidan olishingiz mumkin. ✅";
            }
            return "Hozircha saytida kurslar haqida ma'lumot yo'q. Tez orada yangi kurslar qo'shiladi! 😊";
        }

        // 4. Teachers
        if ($this->isMatch($q, $qClean, ['ustoz', 'o\'qituvchi', 'domla', 'muallim', 'teacher'])) {
            $teachers = Teacher::where('is_active', true)->latest()->take(5)->get();
            if ($teachers->isNotEmpty()) {
                $list = $teachers->map(fn($t) => "• {$t->full_name} ({$t->subject})")->implode("\n");
                return "Bizning ba'zi tajribali ustozlarimiz:\n{$list}\nTo'liq ro'yxatni 'Ustozlar' sahifasida ko'rishingiz mumkin. 👨‍🏫👩‍🏫";
            }
            return "Hozircha o'qituvchilar haqida ma'lumot kiritilmagan. Keyinroq tekshirib ko'ring! ✨";
        }

        // 5. News/Events
        if ($this->isMatch($q, $qClean, ['yangilik', 'tadbir', 'nima gap', 'e\'lon', 'post'])) {
            $posts = Post::latest()->take(2)->pluck('title')->toArray();
            $events = CalendarEvent::where('event_date', '>=', now())->orderBy('event_date')->take(2)->pluck('title')->toArray();
            
            if (empty($posts) && empty($events)) {
                return "Hozircha yangi xabarlar va tadbirlar yo'q. Bizni kuzatishda davom eting! ✨";
            }

            $res = "So'nggi yangiliklarimiz:\n";
            if (!empty($posts)) $res .= "• " . implode("\n• ", $posts) . "\n";
            if (!empty($events)) $res .= "Yaqin kunlardagi tadbirlar:\n• " . implode("\n• ", $events);
            
            return $res;
        }

        return null;
    }

    /**
     * Helper to check if message matching target keywords (with fuzzy support).
     */
    private function isMatch(string $q, string $qClean, array $keywords): bool
    {
        foreach ($keywords as $kw) {
            $kw = mb_strtolower(trim($kw));
            if (Str::contains($q, $kw)) return true;
            
            similar_text($qClean, $this->cleanMessage($kw), $percent);
            if ($percent >= 75) return true;
        }
        return false;
    }

    /**
     * Gemini API call with retry and fallback logic.
     */
    private function callGemini(string $message, ?object $user): array
    {
        $apiKey = (string) config('services.gemini.key');
        $model = (string) config('services.gemini.model', 'gemini-1.5-flash');

        if ($apiKey === '') {
            return ['success' => false, 'error' => 'API Key missing'];
        }

        $systemInstruction = $this->buildSystemInstruction($user);
        $contents = [['role' => 'user', 'parts' => [['text' => $message]]]];

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
        $localFallback = $this->matchDynamicData($message, $user);
        
        if ($localFallback) {
            return ['success' => true, 'text' => $localFallback, 'source' => 'local_fallback'];
        }

        $fallbackText = "Kechirasiz, hozircha bu savolga aniq javob bera olmayman. ✨\n\nLekin men quyidagi mavzularda yordam bera olaman:\n"
            . "• Maktab haqida ma'lumotlar 🏫\n"
            . "• Eng so'nggi yangiliklar va tadbirlar 📅\n"
            . "• Kurslar va ustozlar haqida 👨‍🏫\n"
            . "• Imtihon natijalaringizni ko'rsatish 🎓\n\n"
            . "Iltimos, savolingizni aniqroq yozing yoki kerakli bo'limga o'ting! 😊";

        return ['success' => true, 'text' => $fallbackText, 'source' => 'ultimate_fallback'];
    }

    private function buildSystemInstruction(?object $user): string
    {
        $schoolName = (string) __('public.layout.school_name');
        $userContext = $user ? "Foydalanuvchi ismi: {$user->first_name}. Unga ism bilan murojaat qiling.\n" : "";
        
        return "Siz {$schoolName} saytining aqlli yordamchisiz. Faqat o'zbek tilida, qisqa va emojilar bilan javob bering. 😊✨\n" . $userContext;
    }

    private function handleTicketCreation(string $text): string
    {
        if (Str::contains($text, '[CREATE_TICKET:')) {
            $description = Str::between($text, '[CREATE_TICKET:', ']');
            $ticket = ContactMessage::create([
                'name' => auth()->check() ? auth()->user()->name : 'AI System',
                'email' => auth()->check() ? auth()->user()->email : 'ai@81-maktab.uz',
                'message' => "AI Murojaat: " . $description,
            ]);
            return Str::of($text)->before('[CREATE_TICKET:')->trim()->value() . "\n\n📌 №{$ticket->id} raqamli murojaat yaratildi! ✅";
        }
        return $text;
    }
}
