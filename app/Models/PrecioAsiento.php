<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrecioAsiento extends Model
{

    /**
     *
     * ZurielDA
     *
     */

    use HasFactory;

    protected $table = 'precio_asiento';

    protected $fillable = [
        "id",
        "price",
        "description",
        "created_at",
        "updated_at",
    ];

}
