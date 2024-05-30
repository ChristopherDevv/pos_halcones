<?php

namespace App\Models\PointOfSALE;

use App\Models\PointOfSale\WarehouseProductCatalog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClothingSize extends Model
{
    use HasFactory;

    protected $table = 'clothing_sizes';
    protected $fillable = [
        'name',
        'abbreviation',
        'description',
        'is_active'
    ];

    public function warehouse_product_catalogs()
    {
        return $this->belongsToMany(WarehouseProductCatalog::class, 'warehouse_product_clothing_size', 'clothing_size_id', 'warehouse_product_catalog_id')->withTimestamps();
    }

    public function warehouse_product_size_inventories()
    {
        return $this->hasMany(WarehouseProductSizeInventory::class);
    }

    public function global_inventories()
    {
        return $this->hasMany(GlobalInventory::class);
    }
}
