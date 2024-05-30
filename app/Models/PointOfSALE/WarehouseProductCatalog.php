<?php

namespace App\Models\PointOfSale;

use App\Models\Imagenes;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseProductCatalog extends Model
{
    use HasFactory;

    protected $table = 'warehouse_product_catalogs';

    protected $fillable = [
        'pos_unit_measurement_id',
        'user_seller_id',
        'clothing_category_id',
        'name',
        'unit_measurement_quantity',
        'is_clothing',
        'is_active',
        'sales_code',
        'description',
    ];

    public function pos_unit_measurement()
    {
        return $this->belongsTo(PosUnitMeasurement::class);
    }

    public function user_seller()
    {
        return $this->belongsTo(User::class, 'user_seller_id');
    }

    public function clothing_sizes()
    {
        return $this->belongsToMany(ClothingSize::class, 'warehouse_product_clothing_size', 'warehouse_product_catalog_id', 'clothing_size_id')->withTimestamps();
    }

    public function clothing_category()
    {
        return $this->belongsTo(ClothingCategory::class);
    }

    public function warehouse_product_inventories()
    {
        return $this->hasMany(WarehouseProductInventory::class);
    }

    public function pos_product_subcategories()
    {
        return $this->belongsToMany(PosProductSubcategory::class, 'pos_subcategory_product_catalog', 'warehouse_product_catalog_id', 'pos_product_subcategory_id')->withTimestamps();
    }

    public function global_inventories()
    {
        return $this->hasMany(GlobalInventory::class);
    }

    public function images()
    {
        return $this->hasMany(Imagenes::class);
    }

    public function warehouse_product_size_inventories()
    {
        return $this->hasMany(WarehouseProductSizeInventory::class);
    }
}
