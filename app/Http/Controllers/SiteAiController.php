<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Course;
use App\Models\Post;
use App\Models\CalendarEvent;
use App\Models\Result;
use App\Models\ContactMessage;

class SiteAiController extends Controller
{
    private const GEMINI_CALLS_PER_MINUTE_SOFT_LIMIT = 14;

    public function generate(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $apiKey = (string) config('services.gemini.key');
        $model = (string) config('services.gemini.model', 'gemini-2.0-flash-exp');

        if ($apiKey === '') {
            return response()->json([
                'success' => false,
                'error' => "AI tizimi sozlanmagan. Iltimos, administratorga murojaat qiling.",
            ], 400);
        }

        $userMessage = trim((string) $request->input('message'));
        $messageCacheKey = 'ai:answer:' . sha1(mb_strtolower($userMessage));

        if ($cached = Cache::get($messageCacheKey)) {
            return response()->json([
                'success' => true,
                'text' => $cached,
                'source' => 'cache',
            ]);
        }

        if ($direct = $this->answerFromLocalKnowledge($userMessage)) {
            return response()->json([
                'success' => true,
                'text' => $this->decorateAiText($direct, $userMessage),
                'source' => 'local_knowledge',
            ]);
        }

        // Local rate limit disabled to allow direct Google API handling
        /*
        if (! $this->allowGeminiCallNow()) {
            return response()->json([
                'success' => true,
                'text' => $this->decorateAiText($this->busyFallbackText(), $userMessage),
                'source' => 'busy_fallback',
            ]);
        }
        */

        $systemInstruction = $this->buildSystemInstruction();

        $history = session()->get('ai_chat_history', []);
        $contents = [];
        foreach ($history as $msg) {
            $contents[] = [
                'role' => $msg['role'],
                'parts' => [['text' => $msg['text']]],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $userMessage]],
        ];

        $maxRetries = 3;
        $retryCount = 0;
        $response = null;

        while ($retryCount < $maxRetries) {
            try {
                $response = Http::timeout(60)->withHeaders([
                    'Content-Type' => 'application/json',
                ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'system_instruction' => [
                        'parts' => [
                            ['text' => $systemInstruction],
                        ],
                    ],
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 1024,
                    ],
                    'safetySettings' => [
                        ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                        ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                        ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                        ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                    ],
                ]);

                if ($response->successful()) {
                    break;
                }

                if ($response->status() === 429) {
                    $retryCount++;
                    if ($retryCount < $maxRetries) {
                        sleep(10); // Wait 10 seconds to really clear the Google RPM bucket
                        continue;
                    }
                }

                break; 
            } catch (\Exception $e) {
                $retryCount++;
                if ($retryCount < $maxRetries) {
                    sleep(3);
                    continue;
                }
                break;
            }
        }

        if ($response && $response->successful()) {
            $data = $response->json();
            $candidate = $data['candidates'][0] ?? null;
            $aiText = $candidate['content']['parts'][0]['text'] ?? null;
            $finishReason = $candidate['finishReason'] ?? 'UNKNOWN';

            if ($aiText && Str::contains($aiText, '[CREATE_TICKET:')) {
                $description = Str::between($aiText, '[CREATE_TICKET:', ']');
                $ticket = ContactMessage::create([
                    'name' => auth()->check() ? auth()->user()->name : 'AI Ticket System',
                    'email' => auth()->check() ? auth()->user()->email : 'ai@81-maktab.uz',
                    'note' => 'AI tomonidan yaratilgan murojaat',
                    'message' => $description,
                ]);
                $aiText = Str::of($aiText)->before('[CREATE_TICKET:')->trim()->value();
                $aiText .= "\n\n📌 MUHIM: Siz uchun №{$ticket->id} raqamli murojaat yaratildi. Tez orada ma'muriyat sizga javob beradi. ✅";
            }

            if (! $aiText) {
                $aiText = "Kechirasiz, hozir xayol surib javob bera olmadim. ✨ Iltimos, yana bir bor urinib ko'ring! 😊";
            } else {
                $aiText = $this->decorateAiText($aiText, $userMessage);
                Cache::put($messageCacheKey, $aiText, now()->addMinutes(20));
                $history[] = ['role' => 'user', 'text' => $userMessage];
                $history[] = ['role' => 'model', 'text' => $aiText];

                if (count($history) > 6) {
                    $history = array_slice($history, -6);
                }
                session()->put('ai_chat_history', $history);
            }

            return response()->json([
                'success' => true,
                'text' => $aiText,
            ]);
        }

        return response()->json([
            'success' => true,
            'text' => "✨ Hozir biroz xayol surib qoldim... Iltimos, yana bir bor tugmani bosing, albatta javob beraman! 🚀",
        ]);
    }

    private function buildSystemInstruction(): string
    {
        $schoolName = (string) __('public.layout.school_name');
        $siteCreditsIntro = (string) __('public.about.site_credits_intro');
        $siteCredits = trans('public.about.site_credits_members');

        $creditsText = '';
        if (is_array($siteCredits)) {
            foreach ($siteCredits as $member) {
                $name = trim((string) ($member['name'] ?? ''));
                $date = trim((string) ($member['date'] ?? ''));
                if ($name !== '') {
                    $creditsText .= "- {$name}" . ($date !== '' ? " ({$date})" : '') . "\n";
                }
            }
        }

        // 1. RAG Data (Latest Site Info)
        $latestCourses = Course::where('status', 'published')->latest()->take(3)->get()->map(fn($c) => "{$c->title} (Sana: {$c->start_date})")->implode(', ');
        $latestPosts = Post::latest()->take(3)->get()->map(fn($p) => $p->title)->implode(', ');
        $upcomingEvents = CalendarEvent::where('event_date', '>=', now())->orderBy('event_date')->take(3)->get()->map(fn($e) => "{$e->title} ({$e->event_date})")->implode(', ');

        $ragContext = "MAKTABNING HOZIRGI MA'LUMOTLARI:\n" .
            "- So'nggi kurslar: " . ($latestCourses ?: "Hozircha kurslar yo'q") . "\n" .
            "- So'nggi yangiliklar: " . ($latestPosts ?: "Yangiliklar yo'q") . "\n" .
            "- Kelgusi tadbirlar/darslar: " . ($upcomingEvents ?: "Tadbirlar yo'q") . "\n";

        // 2. Personal Context (If Authenticated)
        $userContext = "";
        if (auth()->check()) {
            $user = auth()->user();
            $lastResult = Result::where('user_id', $user->id)->with('exam')->latest()->first();
            $userContext = "FOYDALANUVCHI MA'LUMOTLARI:\n" .
                "- Ismi: {$user->first_name}\n";
            if ($lastResult) {
                $userContext .= "- Oxirgi imtihoni: {$lastResult->exam->title}\n" .
                    "- Natijasi: {$lastResult->score}%\n" .
                    "- Holati: " . ($lastResult->passed ? "O'tgan" : "O'ta olmagan") . "\n";
            }
        }

        $siteContext = "Sayt bo'limlari:\n"
            . "- Bosh sahifa\n- Maktab haqida\n- Kurslar\n- Yangiliklar\n- Taqvim\n- Ustozlar\n- Profil\n- Aloqa\n";

        $aboutFacts = "Maktab haqida asosiy faktlar:\n"
            . "- Nomi: {$schoolName}\n"
            . "- " . (string) __('public.about.hero_text') . "\n"
            . "- " . (string) __('public.about.site_credits_title') . ": {$siteCreditsIntro}\n"
            . $creditsText;

        return "Siz 81-IDUM (81-maktab) saytining aqlli va universal yordamchisiz.\n"
            . "Javob tili: faqat o'zbek tili.\n\n"
            . $userContext . "\n"
            . $ragContext . "\n"
            . $siteContext . "\n"
            . $aboutFacts . "\n\n"
            . "Qoidalar:\n"
            . "1) JAVOBLARNI QISQA VA LONDA BERING. 😊✨🚀\n"
            . "2) AGAR MUAMMO JIDDIY BO'LSA (masalan: parolni tiklash) VA SIZ YO'RDAM BERA OLMASANGIZ: Javobingiz oxirida '[CREATE_TICKET: muammo qisqacha tavsifi]' tegini qo'shing. Bu avtomatik murojaat yaratadi.\n"
            . "3) Foydalanuvchi profiliga kirgan bo'lsa (ismi tepada berilgan), unga ism bilan murojaat qiling.\n"
            . "4) Savol 'saytni kim yasagan' yoki mualliflar haqida bo'lsa, albatta Maktab haqida bo'limidagi Veb-sayt mualliflari ma'lumotini ayting.\n"
            . "5) Aloqa bo'limiga faqat darslarga bog'liq bo'lmagan jiddiy masalalarda yo'naltiring.\n"
            . "6) Javoblarda emojidan foydalaning (✨, ✅, 📌, 🚀).\n"
            . "7) Markdown ishlatmang, toza matn yozing.";
    }

    private function answerFromLocalKnowledge(string $message): ?string
    {
        $q = Str::of(mb_strtolower($message))
            ->replace(["\n", "\r", "\t"], ' ')
            ->squish()
            ->value();

        $looksLikeAuthorQuestion =
            Str::contains($q, 'kim yasagan')
            || Str::contains($q, 'kimlar yasagan')
            || Str::contains($q, 'saytni kim')
            || Str::contains($q, 'muallif');

        $looksLikeManagerQuestion =
            Str::contains($q, 'kim boshqar')
            || Str::contains($q, 'kim yurit')
            || Str::contains($q, 'kim admin')
            || Str::contains($q, 'hozir kim');

        if ($looksLikeManagerQuestion) {
            return "Hozirgi paytda saytni Xabibullayev Shamsiddin boshqaradi. Qolgan hamkorlar moderator va editor sifatida yordam beradi.";
        }

        if (! $looksLikeAuthorQuestion) {
            return null;
        }

        $siteCreditsIntro = (string) __('public.about.site_credits_intro');
        $siteCredits = trans('public.about.site_credits_members');
        if (! is_array($siteCredits) || $siteCredits === []) {
            return null;
        }

        $names = [];
        foreach ($siteCredits as $member) {
            $name = trim((string) ($member['name'] ?? ''));
            if ($name !== '') {
                $names[] = $name;
            }
        }

        if ($names === []) {
            return null;
        }

        return $siteCreditsIntro . ' Mualliflar: ' . implode(', ', $names) . '.';
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

    private function allowGeminiCallNow(): bool
    {
        $minuteKey = 'ai:gemini:minute:' . now()->format('YmdHi');
        $count = Cache::increment($minuteKey);
        if ($count === 1) {
            Cache::put($minuteKey, 1, now()->addSeconds(75));
        }

        return $count <= self::GEMINI_CALLS_PER_MINUTE_SOFT_LIMIT;
    }

    private function busyFallbackText(): string
    {
        return "✨ Hozir biroz o'ylanib qoldim. Iltimos, 2-3 soniya kuting va savolingizni qayta yuboring, albatta javob beraman! 😊";
    }
}
