<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Ubicacion\Estados;
use App\Models\Ubicacion\Municipios;
use App\Models\Ubicacion\Localidades;
use App\Models\User;
use Illuminate\Support\Collection as BaseCollection;


class Directions extends Model
{
    use HasFactory;

    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'updated_date';

    protected $casts = [
      'is_query_for_cp' => 'boolean'
    ];
    protected $fillable = [
        'calle',
        'numInt',
        'numExt',
        'colonia',
        'estados_id',
        'localidades_id',
        'municipios_id',
        'cp',
        'status',
        'users_id',
        'numTel',
        'is_query_for_cp',
        'referncias',
        'tipo_asentamiento',
        'asentamiento',
        'municipiOtro',
        'estadOtro',
        'ciudadOtro',
        'pais'
    ];
    public function estado() {
        return $this->belongsTo(Estados::class,'estados_id');
    }
    public function municipio() {
        return $this->belongsTo(Municipios::class,'municipios_id');
    }
    public function ciudad() {
        return $this->belongsTo(Localidades::class,'localidades_id');
    }
    public function user() {
        return $this->belongsTo(User::class,'users_id');
    }

}
