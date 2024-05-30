<?php

namespace App\Models\Wallet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransactionTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_transaction_id',
        'sender_wallet_account_id',
        'receiver_wallet_account_id',
        'description',
        'sender_balance_before_transaction',
        'sender_balance_after_transaction',
        'receiver_balance_before_transaction',
        'receiver_balance_after_transaction'
    ];

    public function wallet_transaction()
    {
        return $this->belongsTo(WalletTransaction::class);
    }

    public function sender_wallet_account()
    {
        return $this->belongsTo(WalletAccount::class, 'sender_wallet_account_id');
    }

    public function receiver_wallet_account()
    {
        return $this->belongsTo(WalletAccount::class, 'receiver_wallet_account_id');
    }

}
