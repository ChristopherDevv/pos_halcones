<?php

namespace App\Models\Ubicacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Ubicacion\Localidades;

class Municipios extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable =  [
        'estado_id',
        'clave',
        'nombre',
        'activo'
    ];
    protected $casts = [
    'activo' => 'boolean'
    ];

    public function localidades(){
        return $this->hasMany(Localidades::class, 'municipio_id');
    }

}
