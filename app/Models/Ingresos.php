<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingresos extends Model
{
    use HasFactory;
    protected $table = 'ingresos';
    public $timestamps = true;

    protected $fillable = [
        'numero_referencia',
        'id_tipo_ingreso',
        'concepto',
        'monto',
        'idUsuario',
        'estatus',
        'created_at',
        'updated_at'
    ];
}
