<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Models\User;
use App\Models\Asientos;
use APP\Models\Partidos;

use Illuminate\Support\Facades\DB;



class TicketsAsientos extends Pivot
{

    public $timestamps = false;


    protected $fillable = [
        'id_seat_price',
        'id_seat_price_subcription',
        'tickets_id',
        'zona',
        'fila',
        'code',
        'eventos_id',
        'status',
        'id_grupo',
        'tipo_grupo',
        'folio',
        'change',
        'qr'
    ];

    public function precio() {
        return $this->hasOne(Asientos::class,'code','code');
    }

    public function activos(){
        return $this->hasOne(Partidos::class,'id','eventos_id');
    }

    public function ticket(){
        return $this->belongsTo(Tickets::class,'tickets_id','id');
    }


    /**
     *
     * ZurielDA
     *
     * Get the asiento associated with the TicketsAsientos
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function asiento()
    {
        return $this->hasOne(Asientos::class, 'code','code');
    }


    /**
     *
     * ZurielDA
     *
     * Get the precioAsiento associated with the TicketsAsientos
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function precioAsiento()
    {
        return $this->hasOne(PreciosAsientos::class, 'id', 'id_seat_price');
    }

    /**
     *
     * ZurielDA
     *
     * Get the precioAsiento associated with the TicketsAsientos
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function precioAsientoAbono()
    {
        return $this->hasOne(PreciosAsientos::class, 'id', 'id_seat_price_subcription');
    }

    /**
     * Get the grupo associated with the TicketsAsientos
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function grupo()
    {
        return $this->hasOne(GruposAsientos::class, 'id', 'id_grupo');
    }
}
