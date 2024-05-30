<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
           StadiumLocationSeeder::class,
           PosProductWarehouseSeeder::class,
           PosCashRegisterTypeSeeder::class,
           PosMovementTypeSeeder::class,
           GlobalPaymentTypeSeeder::class,
           GlobalCardPaymentTypeSeeder::class,
           PosTicketStatusSeeder::class,
           PosProductCategorySeeder::class,
           PosProductSubcategorySeeder::class,
           PosUnitMeasurementSeeder::class,
           ClothingSizeSeeder::class,
           ClothingCategorySeeder::class,

           WalletCurrencySeeder::class,
           WalletExchangeRateSeeder::class,
           WalletAccountRoleSeeder::class,
           WalletTransactionStatusSeeder::class,
           WalletTransactionTypeSeeder::class,
           WalletRechargeAmountSeeder::class,
           InventoryTransactionTypeSeeder::class,
           GlobalComboSeeder::class,
           WarehouseSupplierSeeder::class,
           ProductsForBucketvendorSeeder::class,
        ]);
    }
}
