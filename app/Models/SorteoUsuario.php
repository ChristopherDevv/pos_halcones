<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SorteoUsuario extends Model
{
    /**
     *
     * ZurielDA
     *
     */

    use HasFactory;

    protected $table = 'sorteo_usuario';

    protected $fillable = [
        "id",
        "id_raffle",
        "id_user",
        "code",
        "status",
        "created_at",
        "updated_at"
    ];

    /**
     * Get the sorteo that owns the SorteoUsuario
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sorteo()
    {
        return $this->belongsTo(Sorteo::class, 'id_raffle', 'id');
    }


    /**
     * Get the user that owns the SorteoUsuario
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    /**
     * Get all of the evidenciaSorteoPartido for the SorteoUsuario
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function evidenciaSorteoPartido()
    {
        return $this->hasMany(EvidenciaSorteoPartido::class, 'id_raffle_user', 'id');
    }

}
