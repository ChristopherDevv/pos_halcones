<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioMembresia extends Model
{
    /**
     *
     * ZurielDA
     *
     */

    use HasFactory;

    protected $table = 'usuario_membresia';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */

    protected $fillable = [
        'idUser',
        'idMemberShip',
        'status',
        'creation_at',
        'updated_at',
        'finished_at',
        'numberControl'
    ];

    /**
     * Get the user that owns the UsuarioMembresia
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'idUser', 'id');
    }


    /**
     * Get the membresia associated with the UsuarioMembresia
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function membresia()
    {
        return $this->hasOne(Membresia::class, 'id', 'idMemberShip');
    }



}
