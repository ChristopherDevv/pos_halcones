<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Descuentos extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
        'id',
        'idUser',
        'idProduct',
        'idCategory',
        'idSubCategory',
        'idMemberShip',
        'discount',
        'status',
        'reason',
        'creation_date',
        'finished_date'
    ];

    /**
     * Get the user that owns the Descuentos
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'idUser');
    }


    /**
     * Get the producto that owns the Descuentos
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function producto()
    {
        return $this->belongsTo(Productos::class, 'idProduct', 'id');
    }


    /**
     * Get the categorias that owns the Descuentos
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function categorias()
    {
        return $this->belongsTo(Categorias::class, 'idCategory', 'id');
    }

    /**
     * Get the subCategorias that owns the Descuentos
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subCategorias()
    {
        return $this->belongsTo(Categorias::class, 'idSubCategory', 'id');
    }

}
