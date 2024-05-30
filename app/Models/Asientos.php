<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asientos extends Model
{
    use HasFactory;

    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'updated_date';

    protected $fillable = [
        'id',
        'zona',
        'fila',
        'code',
        'eventos_id',
        'precio',
        'precio_abono',
        'type',
        'idGrupo',
        'section_seat'
    ];


    protected $hidden =[
        'users_id','creation_date','updated_date'
    ];


    /**
     *
     * ZurielDA
     *
     * The preciosAsientos that belong to the Asientos
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function preciosAsientos()
    {
        return $this->belongsToMany(PrecioAsiento::class, 'precios_asientos','id_seat', 'id_seat_price')->withPivot('id','status','typePrice','id_season');
    }

    /**
     *
     * ZurielDA
     *
     * The preciosAsientos that belong to the Asientos
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function preciosAsientosActivos()
    {
        return $this->belongsToMany(PrecioAsiento::class, 'precios_asientos','id_seat', 'id_seat_price')->withPivot('id','status','typePrice','id_season')->where('status','=','Activo');
    }

    /**
     *
     * ZurielDA
     *
     * The preciosAsientos that belong to the Asientos
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function preciosAsientosInactivos()
    {
        return $this->belongsToMany(PrecioAsiento::class, 'precios_asientos','id_seat', 'id_seat_price')->withPivot('id','status','typePrice','id_season')->where('status','=','Inactivo');
    }

    /**
     *
     * ZurielDA
     *
     * Get all of the asientoTemporada for the Asientos
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function asientoTemporada()
    {
        return $this->hasMany(AsientoTemporada::class, 'id_seat', 'id');
    }
}
