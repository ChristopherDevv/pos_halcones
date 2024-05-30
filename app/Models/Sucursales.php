<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \App\Models\Directions;

class Sucursales extends Model
{
    use HasFactory;

    protected $table = 'sucursales';

    protected $fillable = [
        "id",
        "titulo",
        "created_at",
        "updated_at",
        "status",
    ];

    public function  direccion() {
        return $this->hasOne(Directions::class,'users_id')->with(['estado','municipio','ciudad']);
    }


    /**
     *
     * ZurielDA
     *
     * Get all of the cajasRegistradoras for the Sucursales
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cajasRegistradoras()
    {
        return $this->hasMany(CajasRegistradoras::class, 'id_sucursal', 'id');
    }

    /**
     *
     * ZurielDA
     *
     * Get all of the cajasRegistradoras for the Sucursales
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cajasRegistradorasActivas()
    {
        return $this->hasMany(CajasRegistradoras::class, 'id_sucursal', 'id')->where('status', '=', 'Activo');
    }

    /**
     *
     * ZurielDA
     *
     * Get all of the cajasRegistradoras for the Sucursales
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cajasRegistradorasInactivas()
    {
        return $this->hasMany(CajasRegistradoras::class, 'id_sucursal', 'id')->where('status', '=', 'Inactiva');
    }

}
