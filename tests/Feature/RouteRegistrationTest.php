<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteRegistrationTest extends TestCase
{
    public function test_split_web_route_files_are_loaded(): void
    {
        $this->assertTrue(Route::has('home'));
        $this->assertTrue(Route::has('login'));
        $this->assertTrue(Route::has('contact'));
        $this->assertTrue(Route::has('ai.chat'));
        $this->assertTrue(Route::has('profile.exams.index'));
        $this->assertTrue(Route::has('admin.exams.index'));

        $this->assertSame('/', route('home', [], false));
        $this->assertSame('/ai-chat', route('ai.chat', [], false));
        $this->assertSame('/profile/exams', route('profile.exams.index', [], false));
        $this->assertSame('/admin/exams', route('admin.exams.index', [], false));
    }
}
