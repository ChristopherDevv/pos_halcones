<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partidos extends Model
{
    use HasFactory;

    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'updated_date';
    const STATUS = [0,1,2];

    protected $fillable = [
        'id',
        'id_match_season',
        'descripcion',
        'fecha',
        'horario',
        'lugar',
        'status',
        'titulo'
    ];

    public function images()
    {
        return $this->hasMany('App\Models\Imagenes', 'rel_id')->where('rel_type','partidos');
    }
    public function image()
    {
        return $this->hasOne('App\Models\Imagenes', 'rel_id')->where('rel_type','partidos');
    }

    public function activos()
    {
        return $this->hasMany(TicketsAsientos::class,'tickets_id','id');
    }

    /**
     *
     * ZurielDA
     *
     * Get all of the ticket for the Partidos
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets()
    {
        return $this->hasMany(Tickets::class, 'eventos_id', 'id');
    }

    /**
     *
     * ZurielDA
     *
     * Get the temporada that owns the Partidos
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function temporada()
    {
        return $this->belongsTo(TemporadaPartido::class, 'id_match_season', 'id');
    }


    /**
     *
     * ZurielDA
     *
     * Get all of the sorteoPartido for the Partidos
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sorteoPartido()
    {
        return $this->hasMany(SorteoPartido::class, 'id_match', 'id');
    }

    /**
     *
     * ZurielDA
     *
     * The abonados that belong to the Partidos
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function abonados()
    {
        return $this->belongsToMany(Abonados::class, 'abono_partido', 'id_match', 'id_subscribers');
    }

}
