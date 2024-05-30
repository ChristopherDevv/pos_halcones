<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class OrdersProductos extends Pivot
{
    protected $table = 'orders_productos';

    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'updated_date';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    protected $fillable = [
        'id',
        'cant',
        'orders_id',
        'productos_id',
        'updated_date',
        'creation_date',
        'tallas_id',
        'priceProduct',
        'discountApplied'
    ];

    public function producto() {
        return $this->belongsTo(Productos::class,'productos_id')->with(['categorias','images']);
    }

    /**
     * Get the descuento associated with the OrdersProductos
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function descuentos()
    {
        return $this->hasMany(DescuentoProducto::class, 'idOrderProduct', 'id');
    }
}
