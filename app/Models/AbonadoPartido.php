<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbonadoPartido extends Model
{
    /**
     *
     * ZurielDA
     *
     */

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    use HasFactory;

    protected $table = 'abono_partido';

    protected $fillable = [
            "id",
            "id_subscribers",
            "id_match",
            "creation_date"
    ];

    /**
     * Get the partido that owns the AbonadoPartido
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partido()
    {
        return $this->belongsTo(Partidos::class, 'id', 'id_match');
    }


    /**
     * Get the abonado that owns the AbonadoPartido
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function abonado()
    {
        return $this->belongsTo(Abonados::class, 'id', 'id_subscribers');
    }

}
