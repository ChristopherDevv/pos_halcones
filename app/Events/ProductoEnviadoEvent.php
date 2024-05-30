<?php
namespace App\Events;
use App\Events\BaseEvent;
class ProductoEnviadoEvent extends BaseEvent{

    protected $isPublic = false;
    protected $data  = [
        'title' => 'Producto enviado',
        'subtitle' => 'Se ha enviado correctamente su producto',
        'data' => []
    ];
    protected $user = null;
    protected $channel = 'seguimiento-pedidos';
    protected $listen = 'envio-pedido';

    public function __construct()
    {
        parent::__construct($this->isPublic, $this->data, $this->user, $this->channel, $this->listen);
    }

}
