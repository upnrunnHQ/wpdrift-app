<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use App\User;

class OAuthError extends Mailable
{
    use Queueable, SerializesModels;
    public $store, $error, $call_response;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($store, $error, $call_response)
    {
        $this->store = $store;
        $this->error = $error;
        $this->call_response = $call_response;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $user = User::findOrFail(Auth::user()->id);
        return $this->markdown('emails.errors.oauth-error', 
            [
                'user' => $user 
            ]
        )->subject('Store Authorization Error!');
    }
}
