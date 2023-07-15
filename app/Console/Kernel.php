<?php

namespace App\Console;

use App\Mail\ExpiredBorrow;
use App\Models\Book;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {   
        //Delete expired tokens and otps
        $schedule->call(function() {
            DB::table('personal_access_tokens')->where('expires_at', '<', now())->delete();
            DB::table('otps')->where('expires_at', '<', now())->delete();
        })->everyFourHours();

        //Mail expiration notices
        $schedule->call(function () {
            $expired_borrowes = DB::table('book_user')->where([
                ['expiration_email_sent', 0],
                ['expires_at', '<=', today()->addDays(env('USER_EXPIRED_BORROW_EMAIL_SEND_BEFORE') + 1)]
            ])->get();
            foreach($expired_borrowes as $expired_borrow) {
                $user = User::find($expired_borrow->user_id);
                $book = Book::find($expired_borrow->book_id);

                Mail::to($user->email)->send(new ExpiredBorrow($book->name, env('USER_EXPIRED_BORROW_EMAIL_SEND_BEFORE')));

                DB::table('book_user')->where('id', $expired_borrow->id)->update([
                    'expiration_email_sent' => true
                ]);
            }
        })->everySixHours();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
