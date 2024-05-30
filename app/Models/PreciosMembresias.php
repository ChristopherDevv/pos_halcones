<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreciosMembresias extends Model
{
    /**
     *
     * ZurielDA
     *
     */

    use HasFactory;

    protected $table = 'precios_membresias';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */

    protected $fillable = [
        'idPrice',
        'idMemberShip',
        'status',
        'creation_at',
        'updated_at',
    ];

    /**
     * Get the precioMembresia associated with the PreciosMembresias
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function precioMembresia()
    {
        return $this->hasOne(PrecioMembresia::class, 'id', 'idPrice');
    }


}
