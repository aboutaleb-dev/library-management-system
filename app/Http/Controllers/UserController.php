<?php

namespace App\Http\Controllers;

use App\Mail\SendOtp;
use App\Models\Book;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function signup(Request $request)
    {
        $fields = $request->validate([
            'email' => ['required', 'email', 'unique:users,email', 'max:' . env('USER_SIGNUP_STRING_FILEDS_MAX_LENGHT')],
        ]);

        //Check user sent otp before or not
        $otp = Otp::where('email', $fields['email'])->first();

        //User didn't sent otp or otp expired
        if (!$otp || !now()->lt($otp->expires_at)) {
            $code = random_int(10000, 99999);

            Otp::updateOrCreate(
                ['email' => $fields['email']],
                ['code' => Hash::make($code), 'expires_at' => now()->addMinutes(env('USER_OTP_EXPIRES_AFTER'))],
            );

            Mail::to($fields['email'])->send(new SendOtp($code));
        }

        return response([
            'message' => 'OTP sent successfully!',
        ]);
    }

    public function resendOtp(Request $request)
    {
        $fields = $request->validate([
            'email' => ['required', 'email', 'max:' . env('USER_SIGNUP_STRING_FILEDS_MAX_LENGHT')],
        ]);

        $code = random_int(10000, 99999);

        Otp::updateOrCreate(
            ['email' => $fields['email']],
            ['code' => Hash::make($code), 'expires_at' => now()->addMinutes(env('USER_OTP_EXPIRES_AFTER'))],
        );

        Mail::to($fields['email'])->send(new SendOtp($code));

        return response([
            'message' => 'OTP sent again successfully!',
        ]);
    }

    public function verifyEmail(Request $request)
    {
        $fields = $request->validate([
            'email' => ['required', 'email', 'max:' . env('USER_SIGNUP_STRING_FILEDS_MAX_LENGHT')],
            'code' => ['required', 'numeric'],
        ]);

        $otp = Otp::where('email', $fields['email'])->first();

        //Delete otps that user may sent before and expired
        if ($otp && now()->gt($otp->expires_at)) {
            $otp->delete();
        }

        if (!$otp || !Hash::check($fields['code'], $otp->code)) {
            return response([
                'message' => 'ٌWrong credentials.',
            ], 401);
        }

        $user = User::firstOrCreate([
            'email' => $fields['email'],
        ]);

        //Delete set password tokens that user may sent before
        $user->tokens()->where('name', 'set-password-token')->delete();

        $token = $user->createToken('set-password-token', ['*'], now()->addHours(env('USER_TOKENS_EXPIRES_AFTER')))->plainTextToken;

        return response([
            'message' => 'User verified successfully!',
            'token' => $token,
        ]);
    }

    public function setPassword(Request $request)
    {
        $fields = $request->validate([
            'password' => ['required', 'min:8', 'regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-])/'],
        ]);

        $user = Auth::user();

        //Delete all user tokens
        $user->tokens()->delete();

        $user->update([
            'password' => Hash::make($fields['password']),
        ]);

        return response([
            'message' => 'Password set successfully!'
        ]);
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => ['required', 'email', 'max:' . env('USER_SIGNUP_STRING_FILEDS_MAX_LENGHT')],
            'password' => ['required'],
        ]);


        $user = User::where('email', $fields['email'])->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'message' => 'ٌWrong credentials.',
            ], 401);
        }

        if($user->deactivated) {
            return response([
                'message' => 'User is deactivated.'
            ], 403);
        }

        //Delete user expired tokens
        $tokens = $user->tokens;

        foreach ($tokens as $token) {
            $deleteToken = now()->gt($token->expires_at);
            if ($deleteToken) {
                $token->delete();
            }
        }

        //Only allow limited number of tokens for each user
        if ($tokens->count() >= env('USER_TOKENS_NUMBER')) {
            $fisrtTokenId = $user->tokens()->first()->id;
            $user->tokens()->where('id', $fisrtTokenId)->delete();
        }

        $token = $user->createToken('token', ['*'], now()->addHours(env('USER_TOKENS_EXPIRES_AFTER')))->plainTextToken;

        return response([
            'message' => 'Logged in successfully!',
            'token' => $token,
        ]);
    }

    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();

        return response([
            'message' => 'Logged out successfully!',
        ]);
    }

    public function profile()
    {
        $user = Auth::user()->with('books:id,name,writer,image')->first();

        return response($user);
    }

    public function borrow(Request $request)
    {
        $fields = $request->validate([
            'book_id' => ['required', 'numeric'],
        ]);

        $user = Auth::user();

        //Check user can borrow a new book or not by comparing user books number with the number provided
        $count = $user->books()->count();

        if($count >= env('USER_BORROW_NUMBER')) {
            return response([
                'message' => 'Each user can only borrow ' . env('USER_BORROW_NUMBER') . ' books at the same time',
            ], 403);
        }

        $book = Book::find($fields['book_id']);

        //Check user borrowed this book_id or not
        if (!$book) {
            return response()->notFound('Book');
        }

        //Check this book_id has stock or not
        if ($book->stock <= 0) {
            return response([
                'message' => 'This book is not available.',
            ]);
        }

        //Check user borrowed this book_id or not
        $borrowed = $user->books()->where(
            'book_id',
            $book->id
        )->wherePivotNull('returned_at')->first();

        if ($borrowed) {
            return response([
                'message' => 'User already borrowed this book.',
            ], 409);
        }

        //ُSubtract this book stock number by 1
        $book->update([
            'stock' => $book->stock - 1,
        ]);

        $user->books()->attach($book->id, ['expires_at' => today()->addDays(env('USER_BORROW_TIME') + 1)->format('Y-m-d')]);

        return response([
            'message' => 'User borrow added successfully!',
        ]);
    }
}
