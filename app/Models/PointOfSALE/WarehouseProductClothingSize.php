<?php

namespace App\Models\PointOfSALE;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseProductClothingSize extends Model
{
    use HasFactory;

    protected $table = 'warehouse_product_clothing_size';
    protected $fillable = [
        'warehouse_product_catalog_id',
        'clothing_size_id',
    ];

    public function warehouse_product_catalog()
    {
        return $this->belongsTo(WarehouseProductCatalog::class);
    }

    public function clothing_size()
    {
        return $this->belongsTo(ClothingSize::class);
    }
}
