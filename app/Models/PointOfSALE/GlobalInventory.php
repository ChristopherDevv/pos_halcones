<?php

namespace App\Models\PointOfSale;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_product_catalog_id',
        'stadium_location_id',
        'pos_product_warehouse_id',
        'clothing_size_id',
        'current_stock',
        'purchase_price',
        'sale_price',
        'discount_sale_price'
    ];

    public function warehouse_product_catalog()
    {
        return $this->belongsTo(WarehouseProductCatalog::class);
    }

    public function stadium_location()
    {
        return $this->belongsTo(StadiumLocation::class);
    }

    public function global_inventory_transactions()
    {
        return $this->hasMany(GlobalInventoryTransaction::class);
    }

    public function warehouse_product_inventories()
    {
        return $this->hasMany(WarehouseProductInventory::class);
    }

    public function pos_product_warehouse()
    {
        return $this->belongsTo(PosProductWarehouse::class);
    }

    public function clothing_size()
    {
        return $this->belongsTo(ClothingSize::class);
    }
}
