<?php

namespace App\Listeners;

use Soved\Laravel\Gdpr\Events\GdprDownloaded;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mail;
use App\Mail\GdprDownloadedUser;
use App\Mail\GdprDownloadedAdmin;
use Config;
use Illuminate\Support\Facades\Auth;

class GdprDownloadedListener
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
     * @param  GdprDownloaded  $event
     * @return void
     */
    public function handle(GdprDownloaded $event)
    {
        // Send email to admins
        $admin_emails = Config::get('app.admin_email');
        foreach ($admin_emails as $admin_email) {
            // send email to Admin Accounts
            Mail::to($admin_email)->send(new GdprDownloadedAdmin($event->user));
        }

        Mail::to(Auth::user()->email)->send(new GdprDownloadedUser($event->user));
    }
}
