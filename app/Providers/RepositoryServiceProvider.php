<?php

namespace App\Providers;

use App\Repositories\CategoryRepository;
use App\Repositories\CommentRepository;
use App\Repositories\PostRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostRepository::class, function () {
            return new PostRepository;
        });

        $this->app->singleton(CategoryRepository::class, function () {
            return new CategoryRepository;
        });

        $this->app->singleton(UserRepository::class, function () {
            return new UserRepository;
        });

        $this->app->singleton(CommentRepository::class, function () {
            return new CommentRepository;
        });
    }

    public function boot(): void
    {
        //
    }
}
