<?php

namespace App\Models\PointOfSale;

use App\Models\User;
use App\Models\Wallet\SuperAdminWalletTransaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosProductWarehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'stadium_location_id',
        'user_manager_id',
        'name',
        'description',
        'email',
        'phone',
    ];

    public function pos_cash_registers()
    {
        return $this->hasMany(PosCashRegister::class);
    }

    public function stadium_location()
    {
        return $this->belongsTo(StadiumLocation::class);
    }

    public function user_manager()
    {
        return $this->belongsTo(User::class);
    }

    public function pos_cash_register_types()
    {
        return $this->belongsToMany(PosCashRegisterType::class, 'pos_cash_register_type_warehouse')->withTimestamps();
    }

    public function pos_product_categories()
    {
        return $this->belongsToMany(PosProductCategory::class, 'pos_warehouse_category', 'pos_warehouse_id', 'pos_product_category_id')->withTimestamps();
    }

    public function warehouse_product_inventories()
    {
        return $this->hasMany(WarehouseProductInventory::class);
    }

    public function bucket_vendor_products()
    {
        return $this->hasMany(BucketVendorProduct::class);
    }

    public function super_admin_wallet_transactions()
    {
        return $this->hasMany(SuperAdminWalletTransaction::class);
    }

    public function global_inventories()
    {
        return $this->hasMany(GlobalInventory::class);
    }

    public function warehouse_product_size_inventories()
    {
        return $this->hasMany(WarehouseProductSizeInventory::class);
    }

    public function combo_sales()
    {
        return $this->hasMany(ComboSale::class);
    }

    public function global_combos()
    {
        return $this->hasMany(GlobalCombo::class);
    }

    public function warehouse_transaction_acknowledgments()
    {
        return $this->hasMany(WarehouseTransactionAcknowledgment::class);
    }
}
