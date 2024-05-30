<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Asientos;
use App\Models\TicketsAsientos;
use App\Models\Eventos;
use App\Models\Partidos;

class Tickets extends Model
{
    use HasFactory;

    public $timestamps = true;

    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'updated_date';

    protected $fillable = [
        'id_registro_caja',
        'id_method_payment',
        'fecha',
        'horario',
        'lugar',
        'temporada',
        'eventos_id',
        'type_reservation',
        'users_id',
        'user_sender_id',
        'user_receiver_id',
        'zona',
        'fila',
        'total',
        'code',
        'status',
        'payed',
        'type_payment',
        'type_ticket',
        'is_generate_for_seat',
        'type_agreement'
    ];

    protected $casts = [
        'temporada' => 'boolean',
        'is_generate_for_seat'=> 'boolean',
        'type_payment' => 'integer'
    ];

    protected $hidden = ['users_id'];

    public function user() {
        return $this->belongsTo(User::class,'users_id');
    }

    public function asientos() {
        return $this->hasMany(TicketsAsientos::class)->with('precio');
    }

    public function evento() {
     return $this->belongsTo(Partidos::class,'eventos_id');
    }
    public function codigo() {
        return $this->hasOne('App\Models\Imagenes', 'rel_id')->where('rel_type','codigo');
    }

     public function codigos() {
        return $this->hasMany('App\Models\Imagenes', 'rel_id')->where('rel_type','codigo');
    }

    public function ticket(){
        return $this->hasMany(TicketsAsientos::class,'id','tickets_id');
    }

    public function conteo(){
        return $this->hasMany(TicketsAsientos::class);
    }

    /**
     *
     * ZurielDA
     *
     * Get the registroCaja that owns the Tickets
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function registroCaja()
    {
        return $this->belongsTo(RegistroCajas::class, 'id', 'id_registro_caja');
    }

    /**
     *
     * ZurielDA
     *
     * Get the metodoCobro associated with the Tickets
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function metodoCobro()
    {
        return $this->hasOne(MetodosCobro::class, 'id', 'id_method_payment');
    }

    /**
     *
     * ZurielDA
     *
     * Get all of the asientosCambiados for the Tickets
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function asientosCambiados()
    {
        return $this->hasMany(TicketsCambio::class, 'id_ticket', 'id');
    }

    /**
     *
     * ZurielDA
     *
     * Get all of the asientosSinPrecio for the Tickets
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function asientoTicket()
    {
        return $this->hasMany(TicketsAsientos::class);
    }


    /**
     *
     * ZurielDA
     *
     * Get the partido that owns the Tickets
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partido()
    {
        return $this->belongsTo(Partidos::class, 'eventos_id', 'id');
    }


    /**
     *
     * ZurielDA
     *
     * Get all of the abonados for the Tickets
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function abonados()
    {
        return $this->hasMany(Abonados::class, 'id_ticket', 'id');
    }

}
