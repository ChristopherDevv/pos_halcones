<?php

namespace App\Models;

use App\Models\PointOfSale\BucketVendorProduct;
use App\Models\PointOfSale\GlobalInventoryTransaction;
use App\Models\PointOfSale\PosCashRegister;
use App\Models\PointOfSale\PosCashRegisterMovement;
use App\Models\PointOfSale\PosProductCancelation;
use App\Models\PointOfSale\PosProductWarehouse;
use App\Models\PointOfSale\PosTicket;
use App\Models\PointOfSale\PosTicketCancelation;
use App\Models\PointOfSale\WarehouseProductUpdate;
use App\Models\PointOfSale\WarehouseTransactionAcknowledgment;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Roles;
use App\Models\Wallet\WalletAccount;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'updated_date';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'nombre',
        'correo',
        'password',
        'apellidoP',
        'apellidoM',
        'sexo',
        'id_rol',
        'curp',
        'profession',
        'fechaN',
        'convenio',
        'webaccess',
        'taquilla'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'remember_token','updated_date'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'roles'=> AsCollection::class
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }



     public function getEmailForPasswordReset() {
        return $this->correo;
    }
     public function roles()
    {
        return $this->belongsToMany(Roles::class);
    }

    // public function avatar()
    // {
    //     return $this->hasOne('App\Models\Imagenes', 'rel_id')->where('rel_type','usuario');
    // }

    // ZurielDA
    public function avatar()
    {
        return $this->hasMany('App\Models\Imagenes', 'rel_id')->where('rel_type','usuario');
    }


    /**
     * ZurielDA
     *
     * Get all of the orders for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany(Orders::class,'atended_by' ,'id');
    }

    /**
     *
     * ZurielDA
     *
     * Get all of the codigosDescuento for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function codigos()
    {
        return $this->hasMany(Codigos::class, 'idUser', 'id');
    }

    /**
     *
     * ZurielDA
     *
     * Get all of the usuarioMembresia for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usuarioMembresias()
    {
        return $this->hasMany(UsuarioMembresia::class, 'idUser', 'id')->select(['idUser','idMemberShip','finished_at','numberControl'])->where('status','=', 'Activo');
    }


    /**
     *
     * ZurielDA
     *
     * Get all of the registrosCajas for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function registrosCajas()
    {
        return $this->hasMany(RegistroCajas::class, 'id_responsible', 'id');
    }

    /**
     * Get all of the sorteos for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sorteos()
    {
        return $this->hasMany(SorteoUsuario::class, 'id_user', 'id');
    }

    /**
     *
     * ZurielDA
     *
     * Get all of the direcciones for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function direcciones()
    {
        return $this->hasMany(Directions::class, 'users_id', 'id');
    }

    /** 
     * Christoper Patiño
    */
    public function pos_product_warehouses()
    {
        return $this->hasMany(PosProductWarehouse::class);
    }

    public function pos_cash_registers()
    {
        return $this->hasMany(PosCashRegister::class);
    }

    public function pos_cash_register_active()
    {
        return $this->hasMany(PosCashRegister::class,'user_cashier_opening_id', 'id')->where('is_open', 1);
    }

    public function warehouse_product_updates()
    {
        return $this->hasMany(WarehouseProductUpdate::class);
    }

    public function bucket_vendor_products()
    {
        return $this->hasMany(BucketVendorProduct::class);
    }

    public function pos_tickets()
    {
        return $this->hasMany(PosTicket::class, 'user_cashier_id');
    }

    public function pos_ticket_cancelations()
    {
        return $this->hasMany(PosTicketCancelation::class, 'user_cashier_id');
    }

    public function pos_product_cancelations()
    {
        return $this->hasMany(PosProductCancelation::class, 'user_cashier_id');
    }

    public function wallet_account()
    {
        return $this->hasOne(WalletAccount::class);
    }
    
    public function warehouse_transaction_acknowledgments()
    {
        return $this->hasMany(WarehouseTransactionAcknowledgment::class);
    }

    public function pos_cash_register_movements()
    {
        return $this->hasMany(PosCashRegisterMovement::class);
    }
}
