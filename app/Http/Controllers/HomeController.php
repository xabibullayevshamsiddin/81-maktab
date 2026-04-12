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
                ->inRandomOrder()
                ->value('id');
        });

        $featuredTeacher = $featuredTeacherId
            ? Teacher::query()
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
                ->find($featuredTeacherId)
            : null;

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

    public function contact()
    {
        return view('contact');
    }

    public function storeContact(Request $request)
    {
        $this->validateTurnstile($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => uz_phone_rules(),
            'note' => ['nullable', 'string', 'max:2000'],
            'message' => ['required', 'string', 'max:5000'],
        ], [
            'phone.regex' => uz_phone_validation_message(),
        ]);
        $validated['phone'] = uz_phone_format($validated['phone']);
        $validated['name'] = sanitize_plain_text($validated['name']);
        $validated['note'] = isset($validated['note']) && $validated['note'] !== ''
            ? sanitize_plain_text($validated['note'])
            : null;
        $validated['message'] = sanitize_plain_text($validated['message']);

        ContactMessage::query()->create($validated);

        $msg = 'Xabaringiz qabul qilindi. Tez orada siz bilan bog‘lanamiz.';

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => $msg]);
        }

        return redirect()
            ->route('contact')
            ->with('success', $msg);
    }
}
