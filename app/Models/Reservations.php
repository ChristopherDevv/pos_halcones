<?php

namespace App\Models;

use App\Models\Partidos;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TicketsAsientos;

class Reservations extends Model
{
    use HasFactory;

    protected $fillable = [
        'eventos_id',
        'created_by',
        'motivo',
        'tickets_id',
        'status',
        'payed'
    ];
    protected $casts = [
        'payed' => 'boolean'
    ];

    public function partido() {
        return $this->belongsTo(Partidos::class,'eventos_id')->with('image');
    }
    public function ticket() {
        return $this->belongsTo(Tickets::class,'tickets_id')->with('asientos');
    }

    public function createdBy() {
        return $this->belongsTo(User::class,'created_by');
    }
}
