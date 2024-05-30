<?php

namespace App\Models\PointOfSale;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosUnitMeasurement extends Model
{
    use HasFactory;

    protected $table = 'pos_unit_measurements';

    protected $fillable = [
        'name',
        'description',
        'abbreviation'
    ];

    public function warehouse_product_catalogs()
    {
        return $this->hasMany(WarehouseProductCatalog::class);
    }
    
}
