<?php
namespace App\Http\Controllers\api;

use App\Events\BaseEvent;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use JWTAuth;


class BaseEvenController extends Controller{

    protected $event;

    public function __construct($data = null){

    }

    protected function send() {
        $user = JWTAuth::parseToken()->authenticate();
        if($user) {
            $this->event->setUser($user);
        }
        event($this->event);
    }
}
