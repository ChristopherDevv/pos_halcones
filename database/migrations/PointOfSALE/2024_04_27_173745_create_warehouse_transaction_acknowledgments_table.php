<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWarehouseTransactionAcknowledgmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouse_transaction_acknowledgments', function (Blueprint $table) {
            $table->id();
            $table->integer('user_manager_id')->nullable();
            $table->foreign('user_manager_id')->references('id')->on('users');
            $table->unsignedBigInteger('warehouse_supplier_id')->nullable();
            $table->foreign('warehouse_supplier_id', 'ws_id_foreign')->references('id')->on('warehouse_suppliers');
            $table->unsignedBigInteger('global_payment_type_id')->nullable();
            $table->foreign('global_payment_type_id', 'gpt_id_foreign')->references('id')->on('global_payment_types');
            $table->unsignedBigInteger('global_type_card_payment_id')->nullable();
            $table->foreign('global_type_card_payment_id', 'gtcp_id_foreign')->references('id')->on('global_type_card_payments');
            $table->unsignedBigInteger('global_card_cash_payment_id')->nullable();
            $table->foreign('global_card_cash_payment_id', 'gccp_id_foreign')->references('id')->on('global_card_cash_payments');
            $table->unsignedBigInteger('inventory_transaction_type_id');
            $table->foreign('inventory_transaction_type_id', 'itt_id_foreign')->references('id')->on('inventory_transaction_types');
            $table->unsignedBigInteger('pos_product_warehouse_id');
            $table->foreign('pos_product_warehouse_id', 'ppw_id_foreign')->references('id')->on('pos_product_warehouses');
            $table->string('acknowledgment_key')->unique();
            $table->boolean('is_active')->default(true);
            $table->string('reason')->nullable();
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
        Schema::dropIfExists('warehouse_transaction_acknowledgments');
    }
}
