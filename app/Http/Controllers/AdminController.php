<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\BookUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function login(Request $request) {
        $fields = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        $admin = Admin::where('username', $fields['username'])->first();

        if(!$admin || !Hash::check($fields['password'], $admin->password)) {
            return response([
                'message' => 'Wrong credentials.',
            ], 401);
        }

        //Delete admin expired tokens
        $tokens = $admin->tokens;

        foreach ($tokens as $token) {
            $deleteToken = now()->gt($token->expires_at);
            if ($deleteToken) {
                $token->delete();
            }
        }

        //Only allow limited number of tokens for each admin
        if ($tokens->count() >= env('ADMIN_TOKENS_NUMBER')) {
            $fisrtTokenId = $admin->tokens()->first()->id;
            $admin->tokens()->where('id', $fisrtTokenId)->delete();
        }

        $token = $admin->createToken('token', ['*'], now()->addHours(env('ADMIN_TOKENS_EXPIRES_AFTER')))->plainTextToken;

        return response([
            'message' => "Logged in successfully!",
            'token' => $token,
        ]);
    }

    public function logout() {
        Auth::user()->currentAccessToken()->delete();

        return response([
            'message' => 'Logged out successfully!',
        ]);
    }

    public function borrowedReturnend(Request $request) {
        $fields = $request->validate([
            'user_id' => ['required', 'numeric'],
            'book_id' => ['required', 'numeric'],
        ]);

        $user = User::find($fields['user_id']);

        //Checking user_id and book_id are matching a user record in database or not
        $borrowed = $user->books()->wherePivotNull('returned_at')->where('book_id', $fields['book_id'])->first();

        if(!$borrowed) {
            return response([
                'message' => 'There isn\'t any items with provided Ids.',
            ], 404);
        }

        //Check the return has delay or not for determining cost
        $cost = 0;

        if(now()->gt($borrowed->pivot->expires_at)) {
            $expiration_date = Carbon::parse($borrowed->pivot->expires_at);

            $daysPassed = today()->diffInDays($expiration_date);

            $cost = $daysPassed * env('USER_EXPIRED_BORROW_COST_AFTER');
        }

        //Check the ruturn should be paid or not
        $paid = null;

        if($cost) {
            $paid = 0;
        }

        BookUser::where('id', $borrowed->pivot->id)->update(['returned_at' => today(), 'cost' => $cost, 'paid' => $paid]);

        return response([
            'messgae' => 'Book return added successfully!',
        ]);
    }

    public function borrowedsExpiresIn(string $duration) {
        $startDate = today()->addDays($duration);

        $borroweds = BookUser::where('expires_at', '<=', $startDate)->get();

        return response($borroweds);
    }

    public function userCosts(string $id) {
        $user = User::find($id);

        if(!$user) {
            return response()->notFound('User');
        }

        //Get related pivot table items when the borrowed should be paid and not paid before
        $books = $user->books()->wherePivotNotNull('paid')->wherePivotNotIn('paid', [1])->get();

        //Claculate usert total costs
        $totalCosts = 0;

        foreach($books as $book) {
            $totalCosts += $book->pivot->cost;
        }

        return response([
            'books' => $books,
            'totalCosts' => floatVal($totalCosts),
        ]);
    }

    public function costPaid(Request $request) {
        $fields = $request->validate([
            'borrowed_id' => ['required', 'numeric']
        ]);

        $borrowed = BookUser::find($fields['borrowed_id']);

        if(!$borrowed) {
            return response()->notFound('Borrowed');
        }

        if(is_null($borrowed->paid)) {
            return response([
                'message' => 'Borrowed is can\'t be paid beacause it\'s cost is 0',
            ]);
        }

        if(!$borrowed->returned_at) {
            return response([
                'message' => 'Please first add that borrowed is returned'
            ], 406);
        }

        if($borrowed->paid) {
            return response([
                'message' => 'Borrowed paid before.',
            ], 409);
        }

        $borrowed->update([
            'paid' => true,
        ]);

        return response([
            'message' => 'Cost paid added successfully!',
        ]);
    }

    public function indexUsers() {
        $users = User::all();

        return response($users);
    }

    public function deactivateUser(Request $request) {
        $fields = $request->validate([
            'user_id' => ['required', 'numeric'],
        ]);

        $user = User::find($fields['user_id']);

        if(!$user) {
            return response()->notFound('User');
        }

        //Delete all user tokes
        $user->tokens()->delete();

        $user->update([
            'deactivated' => true,
        ]);

        return response([
            'message' => 'User deactivated successfully!',
        ]);
    }

    public function activateUser(Request $request) {
        $fields = $request->validate([
            'user_id' => ['required', 'numeric'],
        ]);

        $user = User::find($fields['user_id']);

        if(!$user) {
            return response()->notFound('User');
        }

        $user->update([
            'deactivated' => false,
        ]);

        return response([
            'message' => 'User activated successfully!',
        ]);
    }
}
