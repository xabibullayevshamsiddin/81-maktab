<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class HomeController extends Controller
{
    public function home()
    {
        $posts = Post::with('category')
            ->withCount(['comments', 'likes'])
            ->latest()
            ->take(3)
            ->get();

        return view('home', compact('posts'));
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

     public function contact(){
        return view('contact');
    }
}

