<?php

namespace App\Models\Interfaces;

abstract class TipoDeTicket
{
    const REGULAR  = 0;
    const BOLETO_VIP  = 1;
    const ABONO = 2;
    const ABONO_VIP = 3;

    public static function TYPETICKE($code)
    {
        switch ($code) {
            case 0:
                    return "Regular";
                break;
            case 1:
                    return "Boleto Vip";
                break;
            case 2:
                    return "Abono";
                break;
            case 3:
                return "Abono Vip";
                break;

            default:
                    return "Desconocido";
                break;
        }
    }
}
