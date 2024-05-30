<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenesCodigos extends Model
{

    /**
     *
     * ZurielDA
     *
     */

    use HasFactory;

    protected $table = 'ordenes_codigos_descuentos';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
        'id',
        'idOrder',
        'idCodeDiscount',
    ];


    /**
     * Get the order that owns the OrdenesCodigos
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orden()
    {
        return $this->belongsTo(Orders::class, 'id', 'idOrder');
    }


    /**
     * Get the codigoDescuentos associated with the OrdenesCodigos
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function codigoDescuento()
    {
        return $this->hasOne(CodigosDescuento::class, 'id', 'idCodeDiscount');
    }


}
