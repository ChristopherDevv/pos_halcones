<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GruposAsientos extends Model
{
    use HasFactory;
    protected $table = 'grupos_asiento';

    public $timestamps = false;

    protected $fillable = [
        'grupo',
        'nombre',
        'descripcion',
        'tipo_grupo'
    ];
}
