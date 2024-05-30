<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodigosDescuento extends Model
{

    /**
     *
     * ZurielDA
     *
     */

    use HasFactory;

    protected $table = 'codigos_descuento';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
        'id',
        'idProduct',
        'minimumPurchase',
        'numberUses',
        'uniqueCode',
        'discount',
        'status',
        'creation_at',
        'finished_at',
    ];

    /**
     * Get the producto that owns the CodigosDescuento
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function producto()
    {
        return $this->belongsTo(Productos::class, 'id', 'idProduct');
    }

    /**
     * Get all of the codigos for the CodigosDescuento
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function codigos()
    {
        return $this->hasMany(Codigos::class, 'idCodeDiscount', 'id');
    }

    /**
     * Get the user that owns the CodigosDescuento
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(OrdenesCodigos::class, 'idCodeDiscount', 'id');
    }

}
