<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return response([
        'message' => 'Welcome to ' . env('APP_NAME') . ' Api',
    ]);
});

Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminController::class, 'login'])->middleware(['throttle:adminLogin']);
    Route::middleware(['auth:admins'])->group(function () {
        Route::post('/logout', [AdminController::class, 'logout']);
        Route::post('/borrowed-returned', [AdminController::class, 'borrowedReturnend']);
        Route::get('/borroweds-expires-in/{duration}', [AdminController::class, 'borrowedsExpiresIn']);
        Route::get('/user-costs/{id}', [AdminController::class, 'userCosts']);
        Route::post('/cost-paid', [AdminController::class, 'costPaid']);
        Route::get('/users', [AdminController::class, 'indexUsers']);
        Route::post('/deactivate-user', [AdminController::class, 'deactivateUser']);
        Route::post('/activate-user', [AdminController::class, 'activateUser']);
    });
});

Route::prefix('user')->group(function () {
    Route::post('/signup', [UserController::class, 'signup']);
    Route::post('/resend-otp', [UserController::class, 'resendOtp'])->middleware(['throttle:resendOtp']);
    Route::post('/verify-email', [UserController::class, 'verifyEmail'])->middleware(['throttle:verifyEmail']);
    Route::post('/login', [UserController::class, 'login'])->middleware(['throttle:userLogin']);
    Route::post('/reset-password', [UserController::class, 'resetPassword']);

    Route::middleware(['auth:users'])->group(function () {
        Route::post('/set-password', [UserController::class, 'setPassword']);
        Route::post('/logout', [UserController::class, 'logout']);
        Route::get('/profile', [UserController::class, 'profile']);
        Route::post('/borrow', [UserController::class, 'borrow']);
    });
});

Route::prefix('books')->group(function () {
    Route::get('/', [BookController::class, 'index']);
    Route::get('/{id}', [BookController::class, 'show']);
    Route::middleware(['auth:admins'])->group(function () {
        Route::post('/', [BookController::class, 'store']);
        Route::post('/{id}', [BookController::class, 'update']);
        Route::delete('/{id}', [BookController::class, 'destroy']);
    });
});
