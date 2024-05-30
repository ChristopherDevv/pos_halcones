<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionBloqueo extends Model
{
    public $zona;
    public $tipoBloqueo;
    public $excludeZonas;
    public $excludeSecciones;
    public $excludesFilas;
    public $exludes;
}
