<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\casts\Json;
use Illuminate\Support\Facades\DB;
use App\Models\Imagenes;

class Categorias extends Model
{
    use HasFactory;

    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'updated_date';

    protected $fillable = [
        'id',
        'value',
        'title',
        'padre_id'
    ];
    protected $hidden = [
        'updated_date',
        'creation_date'
    ];
    public function subcategories() {
        return $this->hasMany(Categorias::class,'padre_id');
    }

    public function image() {
        return $this->hasOne(Imagenes::class,'rel_id')->where('rel_type','categoria');
    }

    public function productos() {
        return $this->hasMany(Productos::class)->where('stock','>',0)->with('images');
    }
    public function padre() {
        return $this->belongsTo(Categorias::class,'padre_id');
    }

    /**
     * Get all of the descuentoCategorias for the Categorias for all people
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function descuentoCategorias()
    {
        return $this->hasMany(Descuentos::class, 'idCategory', 'id')->where([['status', '=','Activo'], ['idMemberShip', '=', null]]);
    }

    /**
     * Get all of the descuentoCategorias for the Categorias for all people and membership
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function descuentoCategoriasMemberShip()
    {
        return $this->hasMany(Descuentos::class, 'idCategory', 'id')->where('status', '=','Activo');
    }

   /**
    * Get all of the descuentoSubCategoria for the Categorias for all people
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
   public function descuentoSubCategoria()
   {
       return $this->hasMany(Descuentos::class, 'idSubCategory', 'id')->where([['status', '=','Activo'],['idMemberShip', '=', null]]);
   }

   /**
    * Get all of the descuentoSubCategoria for the Categorias for all people and membership
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function descuentoSubCategoriaMemberShip()
    {
        return $this->hasMany(Descuentos::class, 'idSubCategory', 'id')->where('status', '=','Activo');
    }
}
