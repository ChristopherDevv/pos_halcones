<?php

namespace App\Models\PointOfSale;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsForBucketvendor extends Model
{
    use HasFactory;

    protected $table = 'products_for_bucketvendors';

    protected $fillable = [
        'bucketvendor_name',
        'bucketvendor_last_name',
        'bucketvendor_phone',
        'is_active',
    ];

    public function warehouse_product_inventories()
    {
        return $this->belongsToMany(WarehouseProductInventory::class, 'warehouse_product_bucketvendor', 'products_for_bucketvendor_id', 'warehouse_product_inventory_id')
        ->withPivot('id', 'created_at', 'quantity', 'is_paid')->withTimestamps();
    }

    public function pos_sales()
    {
        return $this->hasMany(PosSale::class);
    }

}
