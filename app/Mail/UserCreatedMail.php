<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;

    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    public function build()
    {
        return $this->subject('🎉 Welcome to Being Petz - Your Account Credentials')
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->view('emails.user-created')
                    ->with([
                        'user' => $this->user,
                        'password' => $this->password,
                    ]);
    }
}