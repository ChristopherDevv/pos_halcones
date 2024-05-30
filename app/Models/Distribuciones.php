<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distribuciones extends Model
{
    use HasFactory;

    protected $fillable = [
        'rango',
        'bloqueados',
        'permitidos'
    ];

}
