<?php

namespace App\Models\PointOfSale;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseProductUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_product_inventory_id',
        'user_seller_id',
        'previous_price',
        'price_entered',
        'new_price',
        'previous_stock',
        'stock_entered',
        'new_stock',
        'reason'
    ];

    public function warehouse_product_inventory()
    {
        return $this->belongsTo(WarehouseProductInventory::class);
    }

    public function user_seller()
    {
        return $this->belongsTo(User::class);
    }
}
