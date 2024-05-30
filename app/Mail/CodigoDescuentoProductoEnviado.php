<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\CaIlluminaterbon;

class CodigoDescuentoProductoEnviado extends Mailable
{
    use Queueable, SerializesModels;

    public $discount;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($discount)
    {
        $this-> discount = $discount;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('Email.codigoDescuentoProductoEnviado') ->with([ 'discount' => $this-> discount ]);
    }
}
