<?php

namespace App\Models\PointOfSale;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosProductCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image_file'
    ];

    public function pos_product_warehouses()
    {
        return $this->belongsToMany(PosProductWarehouse::class, 'pos_warehouse_category', 'pos_product_category_id', 'pos_warehouse_id')->withTimestamps();
    }

    public function pos_product_subcategories()
    {
        return $this->belongsToMany(PosProductSubcategory::class, 'pos_category_subcategory', 'pos_product_category_id', 'pos_product_subcategory_id')->withTimestamps();
    }
}
