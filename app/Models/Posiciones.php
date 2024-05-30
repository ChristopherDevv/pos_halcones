<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Posiciones extends Model
{
    use HasFactory;

    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'updated_date';

    protected $fillable = [
        'zona',
        'equipo',
        'jj',
        'jg',
        'jp',
        'puntos'
    ];
    protected $hidden = ['creation_date', 'updated_date'];

     public function imgEquipo()
    {
        return $this->hasOne('App\Models\Imagenes', 'rel_id')->where('rel_type','posiciones');
    }
}
