<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroCajas extends Model
{

    /**
     *
     * ZurielDA
     *
     */

    use HasFactory;

    protected $table = 'registro_cajas';

    protected $fillable = [
        "id",
        "id_responsible",
        "id_caja_registradora",
        "cash_received",
        "finaly_money",
        "cash_outflow",
        "sell_total",
        "cash_diference",
        "created_at",
        "updated_at",
        'status'
    ];


    /**
     * Get the cajaRegistradora that owns the RegistroCajas
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cajaRegistradora()
    {
        return $this->belongsTo(CajasRegistradoras::class, 'id', 'id_caja_registradora');
    }

    /**
     *
     * Se duda si esta relacion se ocupe, revisarlo.
     *
     * Get the user that owns the RegistroCajas
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_responsible', 'id');
    }

    /**
     * Get the user associated with the RegistroCajas
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function responsable()
    {
        return $this->hasOne(User::class, 'id', 'id_responsible');
    }

    /**
     * Get all of the tickets for the RegistroCajas
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets()
    {
        return $this->hasMany(Tickets::class, 'id_registro_caja', 'id');
    }

}
