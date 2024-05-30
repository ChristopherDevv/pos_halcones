<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Abonados extends Model
{
    /**
     *
     * ZurielDA
     *
     */

    use HasFactory;

    protected $table = 'abonados';

    protected $fillable = [
            "id",
            "id_ticket",
            "id_ticket_seat",
            "holder",
            "name",
            "paternalSurname",
            "maternalSurname",
            'created_at',
            'updated_at'
    ];

    /**
     * Get the ticket that owns the Abonados
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ticket()
    {
        return $this->belongsTo(Tickets::class, 'id', 'id_ticket');
    }

    /**
     * Get the ticketAsiento associated with the Abonados
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ticketAsiento()
    {
        return $this->hasOne(TicketsAsientos::class, 'id', 'id_ticket_seat');
    }

    /**
     * The partidos that belong to the Abonados
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function partidos()
    {
        return $this->belongsToMany(Partidos::class, 'abono_partido', 'id_subscribers', 'id_match')->withPivot('creation_date');
    }

}
