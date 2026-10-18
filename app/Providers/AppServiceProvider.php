<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\JudgmentRepositoryInterface;
use App\Interfaces\SearchRepositoryInterface;
use App\Repositories\JudgmentRepository;
use App\Repositories\SearchRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SearchRepositoryInterface::class,SearchRepository::class,);
        $this->app->bind(JudgmentRepositoryInterface::class, JudgmentRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
