<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DescuentoProducto extends Model
{
    use HasFactory;

    protected $table = 'descuento_producto';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
        'id',
        'idOrderProduct',
        'idDiscount',
    ];

    /**
     * Get the descuento associated with the DescuentoProducto
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function descuento()
    {
        return $this->hasOne(Descuentos::class, 'id', 'idDiscount');
    }

}
