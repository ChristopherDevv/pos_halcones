<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Codigos extends Model
{
    /**
     *
     * ZurielDA
     *
     */

    use HasFactory;

    protected $table = 'codigos';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
        'id',
        'idUser',
        'idCodeDiscount',
        'code',
        'status',
    ];

    /**
     * Get the codigoDescuento that owns the codigos
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function codigoDescuento()
    {
        return $this->belongsTo(CodigosDescuento::class, 'id', 'idCodeDiscount');
    }

    /**
     * Get the producto that owns the CodigosDescuento
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'idUser');
    }

}
