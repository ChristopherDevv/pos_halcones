<?php

namespace App\Models\PointOfSale;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosCashRegisterType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'cash_register_number',
    ];

    public function pos_product_warehouses()
    {
        return $this->belongsToMany(PosProductWarehouse::class, 'pos_cash_register_type_warehouse')->withTimestamps();
    }

    public function pos_cash_registers()
    {
        return $this->hasMany(PosCashRegister::class);
    }

    public function pos_tickets()
    {
        return $this->hasMany(PosTicket::class);
    }
}
