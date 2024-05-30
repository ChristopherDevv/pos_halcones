<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PedidoEnviadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = 'Confirmación de envio';


    public $folio;
    public $paqueteria;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($paqueteria,$folio)
    {
        $this->folio = $folio;
        $this->paqueteria = $paqueteria;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('Email.pedidoEnviadoMail')->with([
                'folio' => $this->folio,
                'paqueteria' => $this->paqueteria
        ]);
    }
}
