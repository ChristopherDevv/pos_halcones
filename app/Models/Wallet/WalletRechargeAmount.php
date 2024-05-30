<?php

namespace App\Models\Wallet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletRechargeAmount extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'description',
        'is_active'
    ];
}
