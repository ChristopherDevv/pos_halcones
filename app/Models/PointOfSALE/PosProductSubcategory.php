<?php

namespace App\Models\PointOfSale;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosProductSubcategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image_file'
    ];

    public function pos_product_categories()
    {
        return $this->belongsToMany(PosProductCategory::class, 'pos_category_subcategory', 'pos_product_subcategory_id', 'pos_product_category_id')->withTimestamps();
    }

    public function warehouse_product_catalogs()
    {
        return $this->belongsToMany(WarehouseProductCatalog::class, 'pos_subcategory_product_catalog', 'pos_product_subcategory_id', 'warehouse_product_catalog_id')->withTimestamps();
    }
}
