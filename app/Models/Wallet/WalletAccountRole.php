<?php

namespace App\Models\Wallet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletAccountRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    public function wallet_accounts()
    {
        return $this->belongsToMany(WalletAccount::class, 'account_role_wallet_account', 'wallet_account_role_id', 'wallet_account_id')->withTimestamps();
    }
}
