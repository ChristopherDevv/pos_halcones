<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWarehouseProductUpdatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouse_product_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_product_inventory_id')->constrained('warehouse_product_inventories');

            $table->integer('user_seller_id')->nullable();
            $table->foreign('user_seller_id')->references('id')->on('users');

            $table->decimal('previous_price', 14, 4);
            $table->decimal('price_entered', 14, 4);
            $table->decimal('new_price', 14, 4);

            $table->integer('previous_stock');
            $table->integer('stock_entered');
            $table->integer('new_stock');

            $table->string('reason');

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
        Schema::dropIfExists('warehouse_product_updates');
    }
}
