<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tiposIngreso extends Model
{
    use HasFactory;
    protected $table = "tipos_ingreso";
    public $timesamps = true;

    protected $fillable = [
        'tipo',
        'idUser',
        'estatus',
        'created_at',
        'updated_at'
    ];
}
