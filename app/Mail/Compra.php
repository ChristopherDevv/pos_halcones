<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Compra extends Mailable
{
    use Queueable, SerializesModels;

    public $pedido;

    public $correo;

    public $subject = 'Confirmación de compra';
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($order,$correo)
    {
        $this->pedido = $order;
        $this->correo = $correo;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('Email.confirmarCompra')->with([
            'pedido' => $this->pedido
        ]);
    }
}
