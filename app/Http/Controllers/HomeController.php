<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function home(){
        return view('home');
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

