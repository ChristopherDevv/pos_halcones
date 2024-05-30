<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuperAdminWalletTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('super_admin_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('super_admin_wallet_account_id');
            $table->foreign('super_admin_wallet_account_id', 'super_admin_wallet_acc_id_foreign')->references('id')->on('wallet_accounts');
            $table->unsignedBigInteger('pos_product_warehouse_id');
            $table->foreign('pos_product_warehouse_id', 'super_admin_wallet_prod_wh_id_foreign')->references('id')->on('pos_product_warehouses');
            $table->unsignedBigInteger('wallet_transaction_id')->nullable();
            $table->foreign('wallet_transaction_id', 'super_admin_wallet_trans_id_foreign')->references('id')->on('wallet_transactions');
            $table->string('description')->nullable();
            $table->decimal('amount', 14, 4)->nullable();
            $table->decimal('balance_account_before_transaction', 14, 4)->nullable();
            $table->decimal('balance_account_after_transaction', 14, 4)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('super_admin_wallet_transactions');
    }
}
