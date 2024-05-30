<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;


class Membresia extends Model
{
    /**
     *
     * ZurielDA
     *
     */

    use HasFactory, SoftDeletes;

    protected $table = 'membresia';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */

    protected $fillable = [
        'id',
        'name',
        'description',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'created_at' => 'datetime:d-m-Y',
        'updated_at' => 'datetime:d-m-Y',
    ];


    /**
     * The usuariosMembresia that belong to the Membresia
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function usuariosMembresia()
    {
        return $this->belongsToMany(UsuarioMembresia::class, 'usuario_membresia', 'idMemberShip', 'id');
    }

    /**
     * Get all of the preciosMembresia for the Membresia
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function preciosMembresia()
    {
        return $this->hasMany(PreciosMembresias::class, 'idMemberShip', 'id');
    }


    /**
     * Get all of the imagenes for the Membresia
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function imagenes()
    {
        return $this->hasMany('App\Models\Imagenes', 'rel_id')->where('rel_type','membresia');
    }

    /**
     * Get all of the discount for the Membresia
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function discount()
    {
        return $this->hasMany(Descuentos::class, 'idMemberShip', 'id')->where('status','=','Activo');
    }

}
