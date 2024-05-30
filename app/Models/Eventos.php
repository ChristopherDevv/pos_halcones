<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Imagenes;
use App\Models\User;
use App\Models\EventosUsers;
use Illuminate\Database\Eloquent\Casts\AsCollection;


class Eventos extends Model
{
    use HasFactory;

    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'updated_date';

    protected $fillable = [
        'descripcion',
        'fecha','lugar','status','titulo','type','urlVideo','urlYoutube', 'horario'
    ];

    protected $casts = [
        'images' => AsCollection::class
    ];
    public function images()
    {
        return $this->hasMany('App\Models\Imagenes', 'rel_id')->where('rel_type','evento');
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->using(EventosUsers::class);
    }
}
