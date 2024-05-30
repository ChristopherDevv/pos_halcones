<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdersMembresias extends Model
{

    use HasFactory;

    /**
     *
     * ZurielDA
     *
     */

    protected $table = 'orders_membresias';

    protected $fillable = [
        'id',
        'idOrders',
        'idMembresia',
        'price',
        'benefit',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'created_at' => 'datetime:d-m-Y',
        'updated_date' => 'datetime:d-m-Y',
    ];

    /**
     *
     * Get the membresia associated with the OrdersMembresias
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function membresia()
    {
        return $this->hasOne(Membresia::class, 'id', 'idMembresia');
    }

    /**
     * Get the order that owns the OrdersMembresias
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Orders::class, 'id', 'idOrders');
    }

}
