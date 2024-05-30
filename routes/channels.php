<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/
Broadcast::channel('public-channel-echo', function () {
    return true;
});

Broadcast::channel('seguimiento-pedidos', function () {
    return true;
});

Broadcast::channel('seguimiento-pedidos', function () {
    return true;
});
Broadcast::channel('seguimiento-pedidos.{id}', function ($user, $id) {
    if($user && $id) {
        return (int) $user->id === (int) $id;
    }else {
        return false;
    }
});

