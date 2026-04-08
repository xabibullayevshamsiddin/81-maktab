<?php

namespace App\Http\Controllers;

use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Http\Request;
use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\Teacher;
use App\Models\TeacherComment;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
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
                ->withCount(['comments', 'likes'])
                ->latest()
                ->take(3)
                ->get();
        });

        $likedPostIds = collect();
        if (auth()->check() && auth()->user()->isActive()) {
            $ids = $posts->pluck('id');
            if ($ids->isNotEmpty()) {
                $likedPostIds = PostLike::query()
                    ->where('user_id', auth()->id())
                    ->whereIn('post_id', $ids)
                    ->pluck('post_id');
            }
        }

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
                    'bio',
                    'bio_en',
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

        return view('home', compact('posts', 'likedPostIds', 'featuredTeacher', 'postKindLabels'));
    }

     public function about(){
        return view('about');
    }

     public function courses(){
        return view('courses');
    }

    public function post(){
        return view('post');
    }

     public function teacher(){
        return view('teacher');
    }

     public function teacherShow(){
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

        ContactMessage::query()->create($validated);

        return redirect()
            ->route('contact')
            ->with('success', 'Xabaringiz qabul qilindi. Tez orada siz bilan bog‘lanamiz.');
    }
}
