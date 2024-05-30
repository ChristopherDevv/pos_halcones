<?php

namespace App\Models\PointOfSale;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StadiumLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'address',
        'city',
        'state',
        'country',
        'zip_code',
        'phone',
        'email'
    ];

    public function pos_product_warehouses()
    {
        return $this->hasMany(PosProductWarehouse::class);
    }

    public function pos_cash_registers()
    {
        return $this->hasMany(PosCashRegister::class);
    }

    public function global_inventories()
    {
        return $this->hasMany(GlobalInventory::class);
    }
}
