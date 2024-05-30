<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWarehouseProductInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouse_product_inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pos_product_warehouse_id');
            $table->foreign('pos_product_warehouse_id', 'warehouse_prod_warehouse_id_foreign')->references('id')->on('pos_product_warehouses');
            $table->unsignedBigInteger('warehouse_product_catalog_id');
            $table->foreign('warehouse_product_catalog_id', 'warehouse_prod_cat_id_foreign')->references('id')->on('warehouse_product_catalogs');
            $table->foreignId('global_inventory_id')->constrained('global_inventories');
            $table->decimal('sale_price', 14, 4);
            $table->decimal('discount_sale_price', 14, 4);
            $table->integer('stock');
            $table->boolean('is_active');
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
        Schema::dropIfExists('warehouse_product_inventories');
    }
}
