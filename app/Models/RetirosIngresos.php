<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetirosIngresos extends Model
{
    use HasFactory;

    protected $fillable = [
        'productos_id',
        'cant',
        'talla_id',
        'motivo',
        'old_cant',
        'tipo'
    ];

    public function  producto(){
        return $this->belongsTo(Productos::class,'productos_id');
    }

    public function talla() {
        return $this->belongsTo(Tallas::class,'talla_id');
    }
}
