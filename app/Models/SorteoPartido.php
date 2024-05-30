<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SorteoPartido extends Model
{
    /**
     *
     * ZurielDA
     *
     */

    use HasFactory;

    protected $table = 'sorteo_partido';

    protected $fillable = [
        "id",
        "id_raffle",
        "id_match",
        "initial_date",
        "finished_date",
        "description",
        "created_at",
        "updated_at"
    ];

    /**
     * Get the sorteo that owns the SorteoPartido
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sorteo()
    {
        return $this->belongsTo(Sorteo::class, 'id_raffle', 'id');
    }

    /**
     * Get all of the evidenciaSorteoPartido for the SorteoPartido
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function evidenciaSorteoPartido()
    {
        return $this->hasMany(EvidenciaSorteoPartido::class, 'id_raffle_match', 'id');
    }

    /**
     * Get the partido that owns the SorteoPartido
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partido()
    {
        return $this->belongsTo(Partidos::class, 'id_match', 'id');
    }

}
