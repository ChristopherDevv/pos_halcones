<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Egresos extends Model
{
    use HasFactory;
    protected $table = 'egresos';
    public $timestamps = true;

    protected $fillable = [
        'numero_referencia',
        'id_tipo_egreso',
        'concepto',
        'monto',
        'idUsuario',
        'estatus',
        'created_at',
        'updated_at'
    ];
}
