<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resultados extends Model
{
    use HasFactory;

    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'updated_date';

    protected $fillable = [
        'equipoUno',
        'resultE1Q1',
        'resultE1Q2',
        'resultE1Q3',
        'resultE1Q4',
        'resultE1PTS',
        'imgEquipoUno',
        'equipoDos',
        'resultE2Q1',
        'resultE2Q2',
        'resultE2Q3',
        'resultE2Q4',
        'resultE2PTS',
        'imgEquipoDos'
    ];
}
