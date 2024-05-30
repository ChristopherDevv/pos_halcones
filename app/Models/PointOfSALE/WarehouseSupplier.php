<?php

namespace App\Models\PointOfSale;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseSupplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'last_name',
        'company_name',
        'email',
        'phone_number',
        'address',
        'city',
        'state',
        'country',
        'zip_code',
        'description'
    ];

    public function warehouse_transaction_acknowledgments()
    {
        return $this->hasMany(WarehouseTransactionAcknowledgment::class);
    }

}
