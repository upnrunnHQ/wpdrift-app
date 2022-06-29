<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Store;
use Illuminate\Support\Facades\Auth;
use App\User;

class EDDSetupNotifyUser extends Mailable
{
    use Queueable, SerializesModels;
    public $store;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $user = User::findOrFail(Auth::user()->id);
        return $this->markdown('emails.store.eddsetupnotifyuser', ['user' => $user]);
    }
}
