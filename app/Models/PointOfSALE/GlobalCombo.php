<?php

namespace App\Models\PointOfSale;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalCombo extends Model
{
    use HasFactory;

    protected $fillable = [
        'pos_product_warehouse_id',
        'name',
        'sale_price',
        'permitted_products',
        'description',
        'is_active'
    ];

    public function pos_product_warehouse()
    {
        return $this->belongsTo(PosProductWarehouse::class);
    }

    public function combo_sales()
    {
        return $this->hasMany(ComboSale::class);
    }

    public function pos_sales()
    {
        return $this->hasMany(PosSale::class);
    }
}
