<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\Teacher;
use App\Models\TeacherComment;

class HomeController extends Controller
{
    public function home()
    {
        $posts = Post::with('category')
            ->withCount(['comments', 'likes'])
            ->latest()
            ->take(3)
            ->get();

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

        $featuredTeacher = Teacher::query()
            ->where('is_active', true)
            ->inRandomOrder()
            ->first();

        return view('home', compact('posts', 'likedPostIds', 'featuredTeacher'));
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
