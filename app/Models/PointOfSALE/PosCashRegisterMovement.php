<?php

namespace App\Models\PointOfSale;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosCashRegisterMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'pos_cash_register_id',
        'pos_movement_type_id',
        'pos_ticket_id',
        'pos_ticket_cancelation_id',
        'user_manager_id',
        'is_active',
        'previous_balance',
        'movement_amount',
        'new_balance',
        'reason',
    ];

    public function pos_cash_register()
    {
        return $this->belongsTo(PosCashRegister::class);
    }

    public function pos_movement_type()
    {
        return $this->belongsTo(PosMovementType::class);
    }

    public function pos_ticket()
    {
        return $this->belongsTo(PosTicket::class);
    }

    public function pos_ticket_cancelation()
    {
        return $this->belongsTo(PosTicketCancelation::class);
    }

    public function pos_product_cancelations()
    {
        return $this->hasMany(PosProductCancelation::class);
    }

    public function user_manager()
    {
        return $this->belongsTo(User::class);
    }

}
