<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePosProductCancelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pos_product_cancelations', function (Blueprint $table) {
            $table->id();

            $table->integer('user_cashier_id')->nullable();
            $table->foreign('user_cashier_id')->references('id')->on('users');

            $table->foreignId('pos_ticket_id')->constrained('pos_tickets')->nullable();
            $table->foreignId('warehouse_product_inventory_id')->constrained('warehouse_product_inventories')->nullable();
            $table->foreignId('wallet_transaction_id')->constrained('wallet_transactions')->nullable();
            $table->foreignId('pos_cash_register_movement_id')->constrained('pos_cash_register_movements')->nullable(); 
            $table->integer('quantity');
            $table->decimal('total_amount', 14, 4);
            $table->text('reason')->nullable();
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
        Schema::dropIfExists('pos_product_cancelations');
    }
}
