<?php

namespace App\Models\PointOfSale;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_cashier_id',
        'pos_cash_register_id',
        'global_payment_type_id',
        'pos_ticket_status_id',
        'pos_sale_id',
        'bucket_vendor_product_id',
        'total_amount',
        'sale_folio'
    ];

    public function user_cashier()
    {
        return $this->belongsTo(User::class, 'user_cashier_id');
    }

    public function pos_cash_register()
    {
        return $this->belongsTo(PosCashRegister::class);
    }

    public function global_payment_type()
    {
        return $this->belongsTo(GlobalPaymentType::class);
    }

    public function pos_ticket_status()
    {
        return $this->belongsTo(PosTicketStatus::class);
    }

    public function pos_sale()
    {
        return $this->belongsTo(PosSale::class);
    }

    public function bucket_vendor_product()
    {
        return $this->belongsTo(BucketVendorProduct::class);
    }

    public function global_card_cash_payment()
    {
        return $this->belongsTo(GlobalCardCashPayment::class);
    }

    public function pos_ticket_cancelation()
    {
        return $this->hasOne(PosTicketCancelation::class);
    }

    public function pos_product_cancelations()
    {
        return $this->hasMany(PosProductCancelation::class);
    }

    public function pos_cash_register_movements()
    {
        return $this->hasMany(PosCashRegisterMovement::class);
    }

}

