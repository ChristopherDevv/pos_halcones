<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MultimediaSorteo extends Model
{
    /**
     *
     * ZurielDA
     *
     */

    use HasFactory;

    protected $table = 'multimedia_sorteo';

    protected $fillable = [
        "id",
        "id_raffle",
        "name",
        "type",
        "created_at",
        "updated_at"
    ];

    /**
     * Get the sorteo that owns the MultimediaSorteo
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sorteo()
    {
        return $this->belongsTo(Sorteo::class, 'id', 'id_raffle');
    }



}
