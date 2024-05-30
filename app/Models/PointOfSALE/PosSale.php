<?php

namespace App\Models\PointOfSale;

use App\Models\Wallet\WalletAccount;
use App\Models\Wallet\WalletTransaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_account_id',
        'pos_sales_receivable_id',
        'bucket_vendor_product_id',
        'is_bucketvendor_sale',
        'total_amount',
        'total_amount_payable',
        'global_combo_id',
        'is_combo_sale',
        'combos_quantity',
        'paid_with_courtesy'
    ];

    public function wallet_account()
    {
        return $this->belongsTo(WalletAccount::class);
    }

    public function warehouse_product_inventories()
    {
        return $this->belongsToMany(WarehouseProductInventory::class, 'pos_sale_product_inventory', 'pos_sale_id', 'warehouse_product_inventory_id')
        ->withPivot('quantity', 'quantity_if_removed_product', 'original_quantity')->withTimestamps();
    }

    public function pos_tickets()
    {
        return $this->hasMany(PosTicket::class);
    }

    public function wallet_transaction()
    {
        return $this->hasOne(WalletTransaction::class);
    }

    public function global_combo()
    {
        return $this->belongsTo(GlobalCombo::class);
    }

    public function pos_sales_receivable()
    {
        return $this->belongsTo(PosSalesReceivable::class);
    }

    public function combo_sales()
    {
        return $this->hasMany(ComboSale::class);
    }

    public function products_for_bucketvendor()
    {
        return $this->belongsTo(ProductsForBucketvendor::class);
    }
    
}
