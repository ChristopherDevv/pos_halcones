<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('global_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_product_catalog_id')->constrained('warehouse_product_catalogs');
            $table->foreignId('stadium_location_id')->constrained('stadium_locations');
            $table->foreignId('pos_product_warehouse_id')->constrained('pos_product_warehouses');
            $table->foreignId('clothing_size_id')->constrained('clothing_sizes')->nullable();
            $table->integer('current_stock');
            $table->decimal('purchase_price', 14, 4);
            $table->decimal('sale_price', 14, 4);
            $table->decimal('discount_sale_price', 14, 4);
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
        Schema::dropIfExists('global_inventories');
    }
}
