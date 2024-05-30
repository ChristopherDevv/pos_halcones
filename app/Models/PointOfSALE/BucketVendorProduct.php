<?php

namespace App\Models\PointOfSale;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BucketVendorProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_bucket_vendor_id',
        'pos_product_warehouse_id'
    ];

    public function user_bucket_vendor()
    {
        return $this->belongsTo(User::class);
    }

    public function pos_product_warehouse()
    {
        return $this->belongsTo(PosProductWarehouse::class);
    }

    public function warehouse_product_inventories()
    {
        return $this->belongsToMany(WarehouseProductInventory::class, 'product_inventory_bucket_vendor', 'bucket_vendor_product_id', 'warehouse_product_inventory_id')->withPivot('stock_received', 'stock_sold', 'stock_returned', 'current_stock')->withTimestamps();
    }

    public function pos_tickets()
    {
        return $this->hasMany(PosTicket::class);
    }
    
}
