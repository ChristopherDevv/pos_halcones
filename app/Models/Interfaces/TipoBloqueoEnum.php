<?php

namespace App\Models\Interfaces;

abstract class TipoBloqueoEnum
{
    const BLOQUEO_ZONA  = 1;
    const BLOQUEO_FILA  = 2;
    const BLOQUEO_SECCION = 3;
    const BLOQUEO_ASIENTOS = 4;
}
