<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalInventoryTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('global_inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('global_inventory_id');
            $table->foreign('global_inventory_id', 'git_gi_id_foreign')->references('id')->on('global_inventories');
            $table->unsignedBigInteger('inventory_transaction_type_id');
            $table->foreign('inventory_transaction_type_id', 'git_itt_id_foreign')->references('id')->on('inventory_transaction_types');
            $table->unsignedBigInteger('warehouse_transaction_acknowledgment_id')->nullable();
            $table->foreign('warehouse_transaction_acknowledgment_id', 'git_wta_id_foreign')->references('id')->on('warehouse_transaction_acknowledgments');
            $table->integer('previous_stock')->nullable();
            $table->integer('stock_movement')->nullable();
            $table->integer('new_stock')->nullable();
            $table->decimal('previous_sale_price', 14, 4)->nullable();
            $table->decimal('new_sale_price', 14, 4)->nullable();
            $table->decimal('previous_discount_price', 14, 4)->nullable();
            $table->decimal('new_discount_price', 14, 4)->nullable();
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
        Schema::dropIfExists('global_inventory_transactions');
    }
}
