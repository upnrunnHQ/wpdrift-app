<?php

namespace App\Listeners;

use App\Events\UserSignUp;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mail;
use App\Mail\NewUserWelcome;
use App\Mail\NewUserAdminNotify;
use Config;

class UserSignUpListner
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserSignUp  $event
     * @return void
     */
    public function handle(UserSignUp $event)
    {
        // Send email to admins
        $admin_emails = Config::get('app.admin_email');
        foreach ($admin_emails as $admin_email) {
            // send email to Admin Accounts
            Mail::to($admin_email)->send(new NewUserAdminNotify($event->user));
        }

        Mail::to($event->user->email)->send(new NewUserWelcome($event->user));
    }
}
