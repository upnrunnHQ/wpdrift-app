<?php

namespace App\Listeners;

use App\Events\StoreCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mail;
use App\Mail\NewStoreNotifyUser;
use App\Mail\NewStoreNotifyAdmin;
use Config;
use Illuminate\Support\Facades\Auth;

class StoreCreatedListener
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
     * @param  StoreCreated  $event
     * @return void
     */
    public function handle(StoreCreated $event)
    {
        // Send email to admins
        $admin_emails = Config::get('app.admin_email');
        foreach ($admin_emails as $admin_email) {
            // send email to Admin Accounts
            Mail::to($admin_email)->send(new NewStoreNotifyAdmin($event->store));
        }

        Mail::to(Auth::user()->email)->send(new NewStoreNotifyUser($event->store));
    }
}
