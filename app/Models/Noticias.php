<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Noticias extends Model
{
    use HasFactory;

    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'updated_date';
    
    protected $fillable = [
        'titulo','horario','descripcion','fecha','urlVideo','lugar','type','urlYoutube'
    ];

    public function images()
    {
        return $this->hasMany('App\Models\Imagenes', 'rel_id')->where('rel_type','noticias');
    }
}
