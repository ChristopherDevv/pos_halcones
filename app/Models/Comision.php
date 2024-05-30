<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comision extends Model
{

    /**
     *
     * ZurielDA
     *
     */

    use HasFactory;

    protected $table = 'comision';

    protected $fillable = [
        "id",
        "id_method_payment",
        "status",
        "payment_limit",
        "comission",
        "created_at",
        "updated_at",
    ];


    /**
     * Get the metodosCobro that owns the Comision
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function metodosCobro()
    {
        return $this->belongsTo(MetodosCobro::class, 'id', 'id_method_payment');
    }

}
