<?php

namespace App\Models\PointOfSale;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosTicketStatus extends Model
{
    use HasFactory;

    protected $table = 'pos_ticket_statuses';

    protected $fillable = [
        'name',
        'description',
        'color'
    ];

    public function pos_tickets()
    {
        return $this->hasMany(PosTicket::class);
    }

}
