<?php

namespace App\Models\Wallet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletCurrency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'symbol',
        'image_file',
        'is_active'
    ];

    public function wallet_exchange_rates()
    {
        return $this->hasMany(WalletExchangeRate::class, 'from_wallet_currency_id');
    }

    public function wallet_accounts()
    {
        return $this->hasMany(WalletAccount::class);
    }
}
