<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tallas extends Model
{
    use HasFactory;

    protected  $fillable = [
        'descripcion',
        'title',
        'abrev'
    ];

    protected $hidden = ['created_at','updated_at'];


}
