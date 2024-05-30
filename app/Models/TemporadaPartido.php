<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TemporadaPartido extends Model
{
    /**
     *
     * ZurielDA
     *
     */

    use HasFactory;
    use SoftDeletes;

    protected $table = 'temporada_partido';

    protected $fillable = [
        "id",
        "status",
        "name",
        "description",
        "created_at",
        "updated_at",
        "deleted_at"
    ];

    /**
     * Get all of the partidos for the TemporadaPartido
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function partidos()
    {
        return $this->hasMany(Partidos::class, 'id_match_season', 'id');
    }

}
