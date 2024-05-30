<?php

namespace App\Models\PointOfSale;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosTicketCancelation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_cashier_id',
        'pos_ticket_id',
        'total_amount',
        'reason'
    ];

    public function user_cashier()
    {
        return $this->belongsTo(User::class, 'user_cashier_id');
    }

    public function pos_ticket()
    {
        return $this->belongsTo(PosTicket::class);
    }

    public function pos_cash_register_movement()
    {
        return $this->hasOne(PosCashRegisterMovement::class);
    }

}
