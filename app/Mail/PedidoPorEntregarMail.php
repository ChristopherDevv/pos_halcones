<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PedidoPorEntregarMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = 'Pedido listo para recoger';

    private  $direccion;
    private  $sucursal;
    private  $folio;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($sucursal,$folio)
    {
        $this->sucursal = $sucursal;
        $this->folio = $folio;
        $this->direccion = $this->buildDireccion($sucursal->direccion);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('Email.pedidoPorEntregar')->with([
            'sucursal' => $this->sucursal,
            'folio' => $this->folio,
            'direccion' => $this->buildDireccion($this->sucursal->direccion)
        ]);
    }
    private function buildDireccion($direccion) {
        $dir = '';
        $dir = $dir.$direccion->calle.',';
        $dir = $dir.'#'.$direccion->numExt.' ';
        if($direccion->numInt != '0') {
            $dir = $dir.',Num Int: '.$direccion->numInt;
        }
        $dir = $dir.', Estado: '. $direccion->estado->nombre;
        $dir = $dir.', Municipio: '.$direccion->municipio->nombre;
        $dir = $dir.', Localidad: '.$direccion->ciudad->nombre;
        $dir = $dir.', Colonia: ' . $direccion->colonia;
        return $dir;
    }
}
