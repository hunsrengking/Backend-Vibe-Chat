<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\GuestRepositoryInterface::class,
            \App\Repositories\GuestRepository::class
        );
        $this->app->bind(
            \App\Repositories\PostRepositoryInterface::class,
            \App\Repositories\PostRepository::class
        );
        $this->app->bind(
            \App\Repositories\CommentRepositoryInterface::class,
            \App\Repositories\CommentRepository::class
        );
        $this->app->bind(
            \App\Repositories\ChatRepositoryInterface::class,
            \App\Repositories\ChatRepository::class
        );
        $this->app->bind(
            \App\Repositories\GroupChatRepositoryInterface::class,
            \App\Repositories\GroupChatRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
