<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreciosAsientos extends Model
{

    /**
     *
     * ZurielDA
     *
    */

    use HasFactory;

    protected $table = 'precios_asientos';

    protected $fillable = [
        "id",
        "id_seat",
        "id_seat_price",
        "id_season",
        "status",
        "typePrice",
        "created_at",
        "updated_at",
    ];

    /**
     * Get the precioAsiento associated with the PreciosAsientos
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function precioAsiento()
    {
        return $this->hasOne(PrecioAsiento::class, 'id', 'id_seat_price');
    }


    /**
     * Get all of the ticketAsiento for the PreciosAsientos
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ticketAsientoPorPrecio()
    {
        return $this->hasMany(TicketsAsientos::class, 'id_seat_price', 'id');
    }

    /**
     * Get all of the ticketAsiento for the PreciosAsientos
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ticketAsientoPorAbono()
    {
        return $this->hasMany(TicketsAsientos::class, 'id_seat_price_subcription', 'id');
    }

}
