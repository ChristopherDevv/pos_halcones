<?php

namespace App\Models;

use App\Models\PointOfSale\WarehouseProductCatalog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Imagenes extends Model
{
    use HasFactory;


    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'updated_date';

    protected $table = 'images';

    protected $hidden = [
        'status',
        "updated_date",
        "creation_date"
    ];

    protected $fillable = [
        'id',
        'warehouse_product_catalog_id',
        'uri_path',
        'rel_id',
        'rel_type',
        'name'
    ];

    public function warehouse_product_catalog()
    {
        return $this->belongsTo(WarehouseProductCatalog::class);
    }
}
