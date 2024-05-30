<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvidenciaSorteoPartido extends Model
{
    /**
     *
     * ZurielDA
     *
     */

    use HasFactory;

    protected $table = 'evidencia_sorteo_partido';

    protected $fillable = [
        "id",
        "id_raffle_match",
        "id_raffle_user",
        "created_at",
        "updated_at"
    ];


    /**
     * Get the sorteoPartido that owns the CajasRegistradoras copy 5
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sorteoPartido()
    {
        return $this->belongsTo(SorteoPartido::class, 'id_raffle_match', 'id');
    }

    /**
     * Get the user that owns the CajasRegistradoras copy 5
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sorteoUsuario()
    {
        return $this->belongsTo(SorteoUsuario::class, 'id_raffle_user', 'id');
    }

    /**
     * Get all of the codigoEvidenciaSorteoPartido for the EvidenciaSorteoPartido
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function codigoEvidenciaSorteoPartido()
    {
        return $this->hasMany(CodigoEvidenciaSorteoPartido::class, 'id_evidence_raffle_match', 'id');
    }

    /**
     * Get all of the multimediaEvidenciaSorteoPartido for the EvidenciaSorteoPartido
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function multimediaEvidenciaSorteoPartido()
    {
        return $this->hasMany(MultimediaEvidenciaSorteoPartido::class, 'id_evidence_raffle_match', 'id');
    }

}
