<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrecioMembresia extends Model
{
    /**
     *
     * ZurielDA
     *
     */

    use HasFactory, SoftDeletes;

    protected $table = 'precio_membresia';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */

    protected $fillable = [
        'id',
        'price',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime:d-m-Y',
        'updated_at' => 'datetime:d-m-Y',
    ];


}
