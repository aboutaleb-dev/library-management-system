<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    public static $text = null;

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });

        RateLimiter::for('adminLogin', function (Request $request) {
            return Limit::perMinute(env('ADMIN_LOGIN_ROUTE_LIMIT_AFTER'))->by($request->email ?: $request->user()?->id ?: $request->ip())->response(function() {
                return response()->tooManyAttempts();
            });
        });

        RateLimiter::for('verifyEmail', function (Request $request) {
            return Limit::perMinute(env('USER_VERIFY_EMAIL_ROUTE_LIMIT_AFTER'))->by($request->email ?: $request->user()?->id ?: $request->ip())->response(function() {
                return response()->tooManyAttempts();
            });
        });

        RateLimiter::for('resendOtp', function (Request $request) {
            return Limit::perMinutes(env('USER_RESED_OTP_ROUTE_LIMIT_AFTER_MINUTES'), env('USER_RESED_OTP_ROUTE_LIMIT_AFTER_TIMES'))->by($request->email ?: $request->user()?->id ?: $request->ip())->response(function() {
                return response()->tooManyAttempts();
            });
        });

        RateLimiter::for('userLogin', function (Request $request) {
            return Limit::perMinute(env('USER_LOGIN_ROUTE_LIMIT_AFTER'))->by($request->email ?: $request->user()?->id ?: $request->ip())->response(function() {
                return response()->tooManyAttempts();
            });
        });
    }
}
