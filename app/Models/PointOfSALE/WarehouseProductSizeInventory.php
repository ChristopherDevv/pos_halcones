<?php

namespace App\Models\PointOfSALE;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseProductSizeInventory extends Model
{
    use HasFactory;

    protected $table = 'warehouse_product_size_inventories';

    protected $fillable = [
        'pos_product_warehouse_id',
        'warehouse_product_catalog_id',
        'clothing_size_id',
        'current_stock',
        'purchase_price',
        'sale_price',
        'discount_sale_price',
    ];

    public function warehouse_product_catalog()
    {
        return $this->belongsTo(WarehouseProductCatalog::class);
    }

    public function clothing_size()
    {
        return $this->belongsTo(ClothingSize::class);
    }

    public function pos_product_warehouse()
    {
        return $this->belongsTo(PosProductWarehouse::class);
    }

    
}
