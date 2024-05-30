<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Categorias;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use App\Http\casts\Json;

class Productos extends Model
{
    use HasFactory;

    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'updated_date';

    protected $fillable = [
        'id',
        'title',
        'price',
        'purchasePrice',
        'description',
        'stock',
        'status',
        'categorias_id'
    ];

    public function categorias()
    {
        return $this->belongsTo(Categorias::class);
    }

    public function images() {
        return $this->hasMany('App\Models\Imagenes', 'rel_id')->where('rel_type','productos');
    }

    public function tallas() {
        return $this->belongsToMany(Tallas::class)->addSelect('*','productos_tallas.tallas_id as id','productos_tallas.tallas_id as value','productos_tallas.cant as cantidad_tallas')->where('cant','>',0);
    }

    public function tallasF() {
        return $this->belongsToMany(Tallas::class)->addSelect('*','productos_tallas.tallas_id as id','productos_tallas.tallas_id as value','productos_tallas.cant as cantidad_tallas');
    }

    /**
     * ZurielDA
     *
     * Get all discount associated with the Productos for all people
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function discount()
    {
        return $this->hasMany(Descuentos::class, 'idProduct', 'id')->where([['status','=','Activo'],['idMemberShip', '=', null]]);
    }

    /**
     * ZurielDA
     *
     * Get all discount associated with the Productos for all people
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function discountMemberShip()
    {
        return $this->hasMany(Descuentos::class, 'idProduct', 'id')->where('status','=','Activo');
    }

    /**
     * ZurielDA
     *
     * Get all of the codigosDescuentos for the Productos
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function codigosDescuentos()
    {
        return $this->hasMany(Comment::class, 'idProduct', 'id');
    }

}
