<?php
namespace App\Events;
class PedidoAtendidoEvent extends BaseEvent {

    protected $isPublic = true;
    protected $data  = [
        'title' => 'Producto atentido',
        'subtitle' => 'Su producto a sido atendido correctamente',
        'data' => []
    ];
    protected $user = null;
    protected $channel = 'seguimiento-pedidos';
    protected $listen = 'envio-atendido';

    public function __construct()
    {
        parent::__construct($this->isPublic, $this->data, $this->user, $this->channel, $this->listen);
    }


}
