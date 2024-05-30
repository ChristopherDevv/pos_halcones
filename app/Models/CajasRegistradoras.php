<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CajasRegistradoras extends Model
{
    /**
     *
     * ZurielDA
     *
     */

    use HasFactory;

    protected $table = 'cajas_registradoras';

    protected $fillable = [
        "id",
        "id_sucursal",
        "status",
        "name",
        "description",
        "created_at",
        "updated_at"
    ];

    /**
     * Get the sucursal that owns the CajasRegistradoras
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sucursal()
    {
        return $this->belongsTo(Sucursales::class, 'id', 'id_sucursal');
    }


    /**
     * Get all of the registrosCajas for the CajasRegistradoras
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function registrosCajas()
    {
        return $this->hasMany(RegistroCajas::class, 'id_caja_registradora', 'id');
    }

    /**
     * Get all of the registrosCajas for the CajasRegistradoras
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function registrosCajasActivas()
    {
        return $this->hasMany(RegistroCajas::class, 'id_caja_registradora', 'id')->where('status','=', 'Activo');
    }

    /**
     * Get all of the registrosCajas for the CajasRegistradoras
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function registrosCajasInactivas()
    {
        return $this->hasMany(RegistroCajas::class, 'id_caja_registradora', 'id')->where('status','=', 'Inactivo');
    }

}
