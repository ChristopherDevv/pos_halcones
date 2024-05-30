<?php

namespace App\Models\Ubicacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Ubicacion\Municipios;

class Estados extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'clave',
        'nombre',
        'abrev',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    public function municipios() {
        return $this->hasMany(Municipios::class,'estado_id');
    }
}
