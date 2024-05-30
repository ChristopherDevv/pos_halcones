<?php

namespace App\Models\Interfaces;

abstract class TipoDePagos
{
    const EFECTIVO  = 1;
    const TARJETA  = 2;
    const CORTESIA = 3;


    public static function TYPEPAYMENT($code)
    {
        switch ($code) {
            case 1:
                    return "Efectivo";
                break;
            case 2:
                    return "Tarjeta";
                break;
            case 3:
                    return "Cortesía";
                break;

            default:
                    return "Desconocido";
                break;
        }
    }
}
