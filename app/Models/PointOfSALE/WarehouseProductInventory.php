<?php

namespace App\Models\PointOfSale;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseProductInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'pos_product_warehouse_id',
        'warehouse_product_catalog_id',
        'global_inventory_id',
        'sale_price',
        'discount_sale_price',
        'stock',
        'is_active'
    ];

    public function warehouse_product_catalog() 
    {
        return $this->belongsTo(WarehouseProductCatalog::class);
    }

    public function pos_product_warehouse()
    {
        return $this->belongsTo(PosProductWarehouse::class);
    }

    public function global_inventory()
    {
        return $this->belongsTo(GlobalInventory::class);
    }

    public function warehouse_product_updates()
    {
        return $this->hasMany(WarehouseProductUpdate::class);
    }

    public function bucket_vendor_products()
    {
        return $this->belongsToMany(BucketVendorProduct::class, 'product_inventory_bucket_vendor', 'warehouse_product_inventory_id', 'bucket_vendor_product_id')->withPivot('stock_received', 'stock_sold', 'stock_returned', 'current_stock')->withTimestamps();
    }

    public function pos_sales()
    {
        return $this->belongsToMany(PosSale::class, 'pos_sale_product_inventory', 'warehouse_product_inventory_id', 'pos_sale_id')
        ->withPivot('quantity', 'quantity_if_removed_product', 'original_quantity')->withTimestamps();
    }

    public function pos_product_cancelations()
    {
        return $this->hasMany(PosProductCancelation::class);
    }

    public function products_for_bucketvendors()
    {
        return $this->belongsToMany(ProductsForBucketvendor::class, 'warehouse_product_bucketvendor', 'warehouse_product_inventory_id', 'products_for_bucketvendor_id')
        ->withPivot('id', 'created_at', 'quantity', 'is_paid')->withTimestamps();
    }

    
}
