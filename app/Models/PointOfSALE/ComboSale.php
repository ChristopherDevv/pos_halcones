<?php

namespace App\Models\PointOfSale;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComboSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'global_combo_id',
        'pos_product_warehouse_id',
        'pos_cash_register_id',
        'pos_sale_id',
        'sale_count', 
        'is_canceled'
    ];

    public function global_combo()
    {
        return $this->belongsTo(GlobalCombo::class);
    }

    public function pos_product_warehouse()
    {
        return $this->belongsTo(PosProductWarehouse::class);
    }

    public function pos_cash_register()
    {
        return $this->belongsTo(PosCashRegister::class);
    }

    public function pos_sale()
    {
        return $this->belongsTo(PosSale::class);
    }
}
