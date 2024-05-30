<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MultimediaEvidenciaSorteoPartido extends Model
{
    /**
     *
     * ZurielDA
     *
     */

    use HasFactory;

    protected $table = 'multimedia_evidencia_sorteo_partido';

    protected $fillable = [
        "id",
        "id_evidence_raffle_match",
        "status",
        "name",
        "type",
        "created_at",
        "updated_at"
    ];


    /**
     * Get the evidenciaSorteoPartido that owns the CajasRegistradoras copy
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function evidenciaSorteoPartido()
    {
        return $this->belongsTo(EvidenciaSorteoPartido::class, 'id', 'id_evidence_raffle_match');
    }


}
