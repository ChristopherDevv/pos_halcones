<?php

namespace App\Models\PointOfSale;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalTypeCardPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active'
    ];

    public function global_card_cash_payments()
    {
        return $this->hasMany(GlobalCardCashPayment::class);
    }

    public function warehouse_transaction_acknowledgments()
    {
        return $this->hasMany(WarehouseTransactionAcknowledgment::class);
    }
}
