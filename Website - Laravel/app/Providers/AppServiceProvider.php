<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\QuestionGeneratorService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(QuestionGeneratorService::class, function ($app) {
            return new QuestionGeneratorService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
