<?php

namespace App\Models\PointOfSALE;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClothingCategory extends Model
{
    use HasFactory;

    protected $table = 'clothing_categories';
    protected $fillable = [
        'name',
        'description',
        'is_active'
    ];

    public function warehouse_product_catalogs()
    {
        return $this->hasMany(WarehouseProductCatalog::class);
    }
}
