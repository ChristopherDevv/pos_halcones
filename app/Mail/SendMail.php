<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMail extends Mailable
{
    use Queueable, SerializesModels;
    public $token;

    public $image;


    public $subject = 'Recuperación de contraseña';


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($token,$image)
    {
        $this->token = $token;
        $this->image = $image;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('Email.resetPassword')->with([
            'token' => $this->token
        ]);
    }
}
