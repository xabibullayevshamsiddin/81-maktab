<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesTurnstile;
use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\Teacher;
use App\Models\TeacherComment;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\Course;
use App\Models\Exam;

class HomeController extends Controller
{
    use ValidatesTurnstile;

    public function home()
    {
        $posts = Cache::remember(cache_key_home_posts(), now()->addMinutes(5), function () {
            return Post::query()
                ->select([
                    'id',
                    'category_id',
                    'title',
                    'title_en',
                    'short_content',
                    'short_content_en',
                    'image',
                    'slug',
                    'views',
                    'post_kind',
                    'video_path',
                    'video_url',
                    'created_at',
                ])
                ->with(['category:id,name,name_en'])
                ->withCount(['comments'])
                ->latest()
                ->take(3)
                ->get();
        });

        $featuredTeacherId = Cache::remember(cache_key_home_featured_teacher(), now()->addMinutes(10), function () {
            return Teacher::query()
                ->where('is_active', true)
                ->whereNotNull('image')
                ->where('image', '!=', '')
                ->inRandomOrder()
                ->value('id');
        });

        $featuredTeacher = null;
        if ($featuredTeacherId) {
            $featuredTeacher = Teacher::query()
                ->select([
                    'id',
                    'full_name',
                    'slug',
                    'subject',
                    'subject_en',
                    'lavozim',
                    'lavozim_en',
                    'toifa',
                    'toifa_en',
                    'image',
                    'experience_years',
                    'is_active',
                ])
                ->where('id', $featuredTeacherId)
                ->where('is_active', true)
                ->whereNotNull('image')
                ->where('image', '!=', '')
                ->first();
        }

        if (! $featuredTeacher) {
            $featuredTeacher = Teacher::query()
                ->select([
                    'id',
                    'full_name',
                    'slug',
                    'subject',
                    'subject_en',
                    'lavozim',
                    'lavozim_en',
                    'toifa',
                    'toifa_en',
                    'image',
                    'experience_years',
                    'is_active',
                ])
                ->where('is_active', true)
                ->whereNotNull('image')
                ->where('image', '!=', '')
                ->inRandomOrder()
                ->first();
        }

        $postKindLabels = config('post_kinds', []);

        SEOMeta::setTitle('Bosh sahifa');
        SEOMeta::setDescription('81-IDUM maktab sayti — yangiliklar, o\'qituvchilar, kurslar va imtihonlar.');
        OpenGraph::setUrl(route('home'));

        return view('home', compact('posts', 'featuredTeacher', 'postKindLabels'));
    }

    public function about()
    {
        return view('about');
    }

    public function courses()
    {
        return view('courses');
    }

    public function post()
    {
        return view('post');
    }

    public function teacher()
    {
        return view('teacher');
    }

    public function teacherShow()
    {
        $comments = TeacherComment::query()
            ->whereNull('parent_id')
            ->with(['replies' => function ($query) {
                $query->latest();
            }])
            ->latest()
            ->get();

        return view('teacherShow', compact('comments'));
    }

    public function contact(Request $request)
    {
        $conversation = null;

        return view('contact', compact('conversation'));
    }

    public function privacyPolicy()
    {
        return view('privacy-policy');
    }

    public function terms()
    {
        return view('terms');
    }

    public function storeContact(Request $request)
    {
        $this->validateTurnstile($request);
        $user = $request->user();

        $rules = [
            'note' => ['nullable', 'string', 'max:2000'],
            'message' => ['required', 'string', 'max:5000'],
        ];

        if (! $user) {
            $rules['name'] = ['required', 'string', 'max:120'];
            $rules['email'] = ['required', 'email:rfc,dns', 'max:255'];
            $rules['phone'] = uz_phone_rules();
        }

        $validated = $request->validate($rules, [
            'phone.regex' => uz_phone_validation_message(),
        ]);

        $resolvedName = $user
            ? trim((string) ($user->name ?: ($user->first_name.' '.$user->last_name)))
            : trim((string) ($validated['name'] ?? ''));

        $resolvedEmail = $user
            ? (string) $user->email
            : (string) ($validated['email'] ?? '');

        $resolvedPhone = $user
            ? uz_phone_format((string) $user->phone)
            : uz_phone_format((string) ($validated['phone'] ?? ''));

        $data = [
            'name' => sanitize_plain_text($resolvedName),
            'email' => $resolvedEmail,
            'phone' => $resolvedPhone,
            'note' => isset($validated['note']) && $validated['note'] !== ''
                ? sanitize_plain_text($validated['note'])
                : null,
            'message' => sanitize_plain_text($validated['message']),
        ];

        ContactMessage::query()->create($data);

        $msg = 'Xabaringiz qabul qilindi. Tez orada siz bilan bog‘lanamiz.';

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => $msg]);
        }

        return redirect()
            ->route('contact')
            ->with('success', $msg);
    }

    public function globalSearch(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if (empty($q)) {
            if ($request->expectsJson()) {
                return response()->json(['results' => []]);
            }

            return view('search-results', ['q' => $q, 'results' => []]);
        }

        $results = $this->collectGlobalSearchResults($q);

        if ($request->expectsJson()) {
            return response()->json(['results' => $results]);
        }

        return view('search-results', compact('q', 'results'));
    }

    private function collectGlobalSearchResults(string $q): array
    {
        $results = [];
        $q = trim($q);
        $searchTerms = $this->buildSearchTerms($q);
        if ($searchTerms === []) {
            return [];
        }

        $posts = Post::query()
            ->where(function ($query) use ($searchTerms): void {
                foreach ($searchTerms as $term) {
                    $query->orWhere('title', 'like', "%{$term}%")
                        ->orWhere('title_en', 'like', "%{$term}%")
                        ->orWhere('short_content', 'like', "%{$term}%")
                        ->orWhere('content', 'like', "%{$term}%");
                }
            })
            ->latest()
            ->take(10)
            ->get();
            
        foreach ($posts as $post) {
            $results[] = [
                'type' => 'post',
                'title' => localized_model_value($post, 'title'),
                'description' => localized_model_value($post, 'short_content') ?: strip_tags(localized_model_value($post, 'content')),
                'url' => route('post.show', $post->slug),
                'image' => $post->image ? app_storage_asset($post->image) : null,
            ];
        }

        $teachers = Teacher::query()
            ->where('is_active', true)
            ->where(function ($query) use ($searchTerms): void {
                foreach ($searchTerms as $term) {
                    $query->orWhere('full_name', 'like', "%{$term}%")
                        ->orWhere('subject', 'like', "%{$term}%")
                        ->orWhere('lavozim', 'like', "%{$term}%");
                }
            })
            ->latest()
            ->take(10)
            ->get();
            
        foreach ($teachers as $teacher) {
            $results[] = [
                'type' => 'teacher',
                'title' => $teacher->full_name,
                'description' => localized_model_value($teacher, 'lavozim') ?: localized_model_value($teacher, 'subject'),
                'url' => route('teacher.show', $teacher->slug),
                'image' => $teacher->image ? app_storage_asset($teacher->image) : null,
            ];
        }

        $courses = Course::query()
            ->where('status', Course::STATUS_PUBLISHED)
            ->where(function ($query) use ($searchTerms): void {
                foreach ($searchTerms as $term) {
                    $query->orWhere('title', 'like', "%{$term}%")
                        ->orWhere('title_en', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                }
            })
            ->latest()
            ->take(10)
            ->get();
            
        foreach ($courses as $course) {
            $results[] = [
                'type' => 'course',
                'title' => localized_model_value($course, 'title'),
                'description' => strip_tags(localized_model_value($course, 'description')),
                'url' => route('courses.show', $course->id),
                'image' => $course->image ? app_storage_asset($course->image) : null,
            ];
        }
        
        $exams = Exam::query()
            ->where('is_active', true)
            ->where(function ($query) use ($searchTerms): void {
                foreach ($searchTerms as $term) {
                    $query->orWhere('title', 'like', "%{$term}%");
                }
            })
            ->latest()
            ->take(10)
            ->get();
            
        foreach ($exams as $exam) {
            $results[] = [
                'type' => 'exam',
                'title' => $exam->title,
                'description' => strip_tags((string)$exam->description),
                'url' => route('exam.start.page', $exam->id),
                'image' => null,
            ];
        }

        return array_values($results);
    }

    private function buildSearchTerms(string $q): array
    {
        $q = $this->normalizeGlobalSearchText($q);
        if ($q === '') {
            return [];
        }

        if ($this->isStandaloneGlobalNumericOrDateQuery($q)) {
            return [];
        }

        $tokens = preg_split('/\s+/u', $q) ?: [];
        $tokens = array_values(array_filter(
            $tokens,
            fn (string $token): bool => $this->isSearchableGlobalToken($token)
        ));

        if ($tokens === []) {
            return [];
        }

        $terms = [];
        $phrase = Str::squish(implode(' ', $tokens));
        if ($phrase !== '') {
            $terms[] = $phrase;
        }

        foreach ($tokens as $token) {
            $terms[] = $token;
        }

        if (Str::contains($q, 'kusr')) {
            $terms[] = str_replace('kusr', 'kurs', $q);
        }
        if (Str::contains($q, 'kurs')) {
            $terms[] = str_replace('kurs', 'kusr', $q);
        }

        return array_values(array_unique(array_filter($terms)));
    }

    private function normalizeGlobalSearchText(string $text): string
    {
        $text = Str::lower(trim($text));
        $text = str_replace(['`', '‘', '’', 'ʼ', 'ʻ', '´'], "'", $text);
        $text = str_replace(['o‘', 'o’', 'g‘', 'g’'], ["o'", "o'", "g'", "g'"], $text);
        $text = preg_replace('/[^\p{L}\p{N}\']+/u', ' ', $text) ?? $text;

        return Str::squish($text);
    }

    private function isStandaloneGlobalNumericOrDateQuery(string $q): bool
    {
        if (preg_match('/^[\d\s.,:;+\-*\/()%]+$/u', $q) === 1) {
            return true;
        }

        $monthNames = 'yanvar|fevral|mart|aprel|may|iyun|iyul|avgust|sentabr|sentyabr|oktabr|oktyabr|noyabr|dekabr';

        return preg_match('/^\d{1,2}\s+('.$monthNames.')(?:\s+\d{2,4})?$/u', $q) === 1
            || preg_match('/^('.$monthNames.')\s+\d{1,2}(?:\s+\d{2,4})?$/u', $q) === 1;
    }

    private function isSearchableGlobalToken(string $token): bool
    {
        $token = trim($token);
        if ($token === '' || mb_strlen($token) < 3) {
            return in_array($token, ['it', 'ai', 'js'], true);
        }

        $stopWords = [
            'men', 'menga', 'meni', 'sen', 'siz', 'biz', 'ular', 'shu', 'bu', 'ana',
            'kim', 'nima', 'qanday', 'qanaqa', 'qaysi', 'qayerda', 'qayer', 'qachon',
            'necha', 'qancha', 'haqida', 'kerak', 'iltimos', 'ayt', 'ayting', 'ber',
            'bering', 'bor', 'yoq', 'yo\'q', 'ham', 'va', 'yoki', 'bilan', 'uchun',
            'asosda', 'asosida', 'bo\'yicha', 'boyicha',
            'davomiyligi', 'davomiligi', 'davomligi', 'muddati', 'muddat',
            'boshlanishi', 'boshlanish', 'boshlanadi', 'boshlaydi', 'boshlash',
            'tugashi', 'tugaydi', 'tugash', 'narxi', 'sana', 'sanasi', 'vaqti',
            'foydasiz', 'dali',
        ];
        $fuzzyStopWords = [
            'asosda', 'asosida', 'boyicha',
            'davomiyligi', 'davomiligi', 'davomligi',
            'boshlanishi', 'boshlanish', 'boshlanadi',
            'muddati', 'muddat', 'narxi', 'sanasi', 'vaqti',
        ];

        if (in_array($token, $stopWords, true) || $this->isApproximateGlobalStopToken($token, $fuzzyStopWords)) {
            return false;
        }

        foreach (['narx', 'davom', 'boshlan', 'muddat', 'sana', 'vaqt', 'asos'] as $metadataPrefix) {
            if (str_starts_with($token, $metadataPrefix)) {
                return false;
            }
        }

        if (preg_match('/^\d+$/u', $token) === 1) {
            return false;
        }

        $plain = str_replace("'", '', $token);
        if (in_array($plain, ['php', 'css', 'html', 'sql'], true)) {
            return true;
        }

        return preg_match('/[aeiouo\'ʻ]/u', $token) === 1;
    }

    private function isApproximateGlobalStopToken(string $token, array $stopWords): bool
    {
        if (mb_strlen($token) < 5) {
            return false;
        }

        foreach ($stopWords as $stopWord) {
            if (abs(mb_strlen($token) - mb_strlen($stopWord)) > 1) {
                continue;
            }

            $maxErrors = mb_strlen($stopWord) >= 8 ? 2 : 1;
            if (levenshtein($token, $stopWord) <= $maxErrors) {
                return true;
            }
        }

        return false;
    }
}
