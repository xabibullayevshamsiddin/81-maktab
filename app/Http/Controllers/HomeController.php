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

        $posts = Post::query()
            ->where('title', 'like', "%{$q}%")
            ->orWhere('title_en', 'like', "%{$q}%")
            ->orWhere('short_content', 'like', "%{$q}%")
            ->orWhere('content', 'like', "%{$q}%")
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
            ->where(function ($query) use ($q) {
                $query->where('full_name', 'like', "%{$q}%")
                    ->orWhere('subject', 'like', "%{$q}%")
                    ->orWhere('lavozim', 'like', "%{$q}%");
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
            ->where('status', 'active')
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                    ->orWhere('title_en', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
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
                'image' => $course->cover_image ? app_storage_asset($course->cover_image) : null,
            ];
        }
        
        $exams = Exam::query()
            ->where('is_active', true)
            ->where('title', 'like', "%{$q}%")
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
}
