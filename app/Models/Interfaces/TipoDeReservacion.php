<?php

namespace App\Models\Interfaces;

abstract class TipoDeReservacion
{
    const Taquilla  = 'taquilla';
    const App =  'evento';

    public static function TYPERESERVATION($code)
    {
        switch ($code) {
            case 'taquilla':
                    return "taquilla";
                break;
            case 'evento':
                    return "app";
                break;
            default:
                    return "Desconocido";
                break;
        }
    }
}
