<?php

namespace App\Models;


use App\Models\Productos;
use App\Models\User;
use App\Models\OrdersMembresias;
use App\Models\OrdenesCodigos;
use App\Models\Directions;
use App\Models\Sucursales;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;



class Orders extends Model
{
    use HasFactory;

    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'updated_date';

    public  $fillable = [
        'id',
        'total',
        'cant_total',
        'status',
        'users_id',
        'atended_by',
        'personaOtro',
        'isPersonaOtro',
        'directions_id',
        'num_control',
        'num_seguimiento',
        'type_origin',
        'type_payment',
        'num_transaccion',
        'num_transaccion_client',
        'paqueterias_id',
        'sucursales_id',
        'is_reserved_for_pick',
        'motiveCoutersy'
    ];

    protected $casts = [
        'creation_date' => 'datetime:d-m-Y',
        'updated_date' => 'datetime:d-m-Y',
        'isPersonaOtro' => 'boolean',
        'is_reserved_for_pick' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'users_id');
    }

    public function atendedBy()
    {
        return $this->belongsTo(User::class,'atended_by');
    }

    public function  direccion()
    {
        return $this->belongsTo(Directions::class,'directions_id');
    }

    public function productos()
    {
        return $this->belongsToMany(Productos::class)-> withPivot('id','priceProduct','discountApplied')-> select('productos.*',
        'orders_productos.tallas_id as talla',
        'orders_productos.productos_id as producto_comprado',
        DB::raw('(select title from tallas where id = orders_productos.tallas_id) as titulo_talla'),
        DB::raw('(select abrev from tallas where id = orders_productos.tallas_id) as abreviacion_talla'))->with(['categorias.padre','images']);
    }

    public function paqueteria()
    {
        return $this->belongsTo(Paqueterias::class,'paqueterias_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursales::class,'sucursales_id')->with('direccion');
    }

    /**
     *
     * ZurielDA
     *
     * Get all of the ordenesCodigosDescuento for the Orders
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ordenesCodigosDescuento()
    {
        return $this->hasMany(OrdenesCodigos::class, 'idOrder', 'id');
    }

    /**
     *
     * ZurielDA
     *
     * Get the membresia associated with the Orders
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function membresia()
    {
        return $this->hasOne(OrdersMembresias::class, 'idOrders', 'id');
    }

}
