<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UbicacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject ='Nuevo registro de ubicación';
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('soporte_hdx@halconesdexalapa.com.mx')->markdown('Email.nuevaUbicacion');
    }
}
