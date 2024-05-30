<?php

namespace App\Http\Controllers\api;

use App\Events\ProductoEnviadoEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductoEnviadoEventController extends BaseEvenController {

    protected  $event;

    public function __construct()
    {
        $this->event = new ProductoEnviadoEvent();
    }

}
