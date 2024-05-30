<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Events\PedidoAtendidoEvent;

class PedidoAtendidoEventCtrl extends BaseEvenController
{
    protected  $event;

    public function __construct()
    {
        $this->event = new PedidoAtendidoEvent();
    }
}
