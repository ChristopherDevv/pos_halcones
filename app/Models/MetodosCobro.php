<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetodosCobro extends Model
{

    /**
     *
     * ZurielDA
     *
     */


    use HasFactory;

    protected $table = 'metodos_cobro';

    protected $fillable = [
        "id",
        "name",
        "description",
        "deadlines",
        "created_at",
        "updated_at",
    ];


    /**
     * Get all of the comisiones for the MetodosCobro
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comisiones()
    {
        return $this->hasMany(Comision::class, 'id_method_payment', 'id');
    }

    /**
     * Get all of the comisiones for the MetodosCobro
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comisionesActivas()
    {
        return $this->hasMany(Comision::class, 'id_method_payment', 'id')->where('status','=','Activo');
    }

    /**
     * Get all of the comisiones for the MetodosCobro
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comisionesInactivas()
    {
        return $this->hasMany(Comision::class, 'id_method_payment', 'id')->where('status','=','Inactivo');
    }

    /**
     * Get all of the tickets for the MetodosCobro
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets()
    {
        return $this->hasMany(Comment::class, 'id_method_payment', 'id');
    }


}
