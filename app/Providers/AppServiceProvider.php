<?php

namespace App\Providers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Response::macro('tooManyAttempts', function() {
            return Response::make([
                'message' => 'Too many attempts.',
            ], 429);
        });

        Response::macro('notFound', function(string $title) {
            return response([
                'message' => $title . ' not found.',
            ], 404);
        });
    }
}
