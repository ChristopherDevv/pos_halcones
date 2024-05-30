<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketsCambio extends Model
{
    use HasFactory;

    /**
     *
     * ZurielDA
     *
     */
    protected $table = 'tickets_cambiado';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;


    protected $fillable = [
        'id',
        'id_ticket_seat',
        'id_ticket'
    ];

    /**
     * Get the tickets t
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tickets()
    {
        return $this->belongsTo(Tickets::class, 'id', 'id_ticket');
    }

    /**
     * Get the ticketAsiento associated with the TicketsCambio
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ticketAsiento()
    {
        return $this->hasOne(TicketsAsientos::class, 'id', 'id_ticket_seat');
    }

}
