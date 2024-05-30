<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePosSaleProductInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pos_sale_product_inventory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_product_inventory_id');
            $table->foreign('warehouse_product_inventory_id', 'pos_sale_prod_inv_inventory_id_foreign')->references('id')->on('warehouse_product_inventories');
            $table->unsignedBigInteger('pos_sale_id');
            $table->foreign('pos_sale_id', 'pos_sale_prod_inv_sale_id_foreign')->references('id')->on('pos_sales');
            $table->integer('quantity');
            $table->integer('quantity_if_removed_product')->nullable();
            $table->integer('original_quantity')->nullable();
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
        Schema::dropIfExists('pos_sale_product_inventory');
    }
}
