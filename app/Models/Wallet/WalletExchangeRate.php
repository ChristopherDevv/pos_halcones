<?php

namespace App\Models\Wallet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletExchangeRate extends Model
{
    use HasFactory;

    protected $table = 'wallet_exchange_rates';

    protected $fillable = [
        'from_wallet_currency_id',
        'to_wallet_currency_id',
        'rate',
    ];

    public function from_wallet_currency()
    {
        return $this->belongsTo(WalletCurrency::class);
    }

    public function to_wallet_currency()
    {
        return $this->belongsTo(WalletCurrency::class);
    }

}
