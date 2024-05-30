<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sorteo extends Model
{
    /**
     *
     * ZurielDA
     *
     */

    use HasFactory;

    protected $table = 'sorteo';

    protected $fillable = [
            "id",
            "type",
            "name",
            "description",
            "rules",
            "status",
            "method_raffle",
            "matchNecesary",
            "matchNecesaryParticipate",
            "totalMatch",
            "initial_date",
            "finished_date",
            "created_at",
            "updated_at"
        ];


        /**
         * Get all of the multimedia for the Sorteo
         *
         * @return \Illuminate\Database\Eloquent\Relations\HasMany
         */
        public function multimedia()
        {
            return $this->hasMany(MultimediaSorteo::class, 'id_raffle', 'id');
        }

        /**
         * The sorteoUsuario that belong to the Sorteo
         *
         * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
         */
        public function sorteoUsuario()
        {
            return $this->belongsToMany(User::class, 'sorteo_usuario', 'id_raffle', 'id_user');
        }

        /**
         * Get all of the sorteoPartido for the Sorteo
         *
         * @return \Illuminate\Database\Eloquent\Relations\HasMany
         */
        public function sorteoPartido()
        {
            return $this->hasMany(SorteoPartido::class, 'id_raffle', 'id');
        }
}
