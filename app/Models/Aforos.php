<?php

namespace App\Models;

use App\Http\casts\Json;
use App\Models\Distribuciones;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;


class Aforos extends Model
{
    use HasFactory;

    protected $fillable = [
        'aforo',
        'distribucion',
        'partido',
        'configs',
        'status'
    ];
    protected $casts = [
        'configs' => Json::class
    ];

    public  function partido() {
        return $this->belongsTo(Partidos::class,'partido');
    }
    public function distribucionInf() {
        return $this->belongsTo(Distribuciones::class,'distribucion');
    }

}
