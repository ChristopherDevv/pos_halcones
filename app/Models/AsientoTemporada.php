<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsientoTemporada extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $table = 'asiento_temporada';

    protected $fillable = [
        'id',
        'id_seat',
        'id_season',
        'status',
        'lastStatus'
    ];
}
