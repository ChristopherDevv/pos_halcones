<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Becas extends Model
{
    use HasFactory;
    protected $table = 'becas';
    protected $primaryKey = 'idBeca';
    public $timestamps = true;

    protected $fillabled= [
        'idBeca',
        'nombre_beca',
        'comentarios',
        'estatus',
        'created_at',
        'updated_at'
    ];
}
