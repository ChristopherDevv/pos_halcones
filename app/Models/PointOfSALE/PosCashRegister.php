<?php

namespace App\Models\PointOfSale;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosCashRegister extends Model
{
    use HasFactory;
    protected $fillable = [
        'cash_register_type_id',
        'user_cashier_opening_id',
        'user_cashier_closing_id',
        'stadium_location_id',
        'description',
        'is_open',
        'opening_balance',
        'current_balance',
        'closing_balance',
        'opening_time',
        'closing_time',
    ];

    public function pos_product_warehouse()
    {
        return $this->belongsTo(PosProductWarehouse::class);
    }

    public function pos_cash_register_type()
    {
        return $this->belongsTo(PosCashRegisterType::class);
    }

    public function user_cashier_opening()
    {
        return $this->belongsTo(User::class, 'user_cashier_opening_id');
    }

    public function user_cashier_closing()
    {
        return $this->belongsTo(User::class, 'user_cashier_closing_id');
    }

    public function stadium_location()
    {
        return $this->belongsTo(StadiumLocation::class);
    }

    public function pos_tickets()
    {
        return $this->hasMany(PosTicket::class);
    }

    public function pos_cash_register_movements()
    {
        return $this->hasMany(PosCashRegisterMovement::class);
    }

    public function combo_sales()
    {
        return $this->hasMany(ComboSale::class);
    }

    
}
