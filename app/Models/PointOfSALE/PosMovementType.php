<?php

namespace App\Models\PointOfSale;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosMovementType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description'
    ];

    public function pos_cash_register_movements()
    {
        return $this->hasMany(PosCashRegisterMovement::class);
    }
    
}
