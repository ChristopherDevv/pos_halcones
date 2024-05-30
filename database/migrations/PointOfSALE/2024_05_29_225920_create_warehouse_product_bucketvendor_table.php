<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWarehouseProductBucketvendorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouse_product_bucketvendor', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('warehouse_product_inventory_id')->nullable();
            $table->foreign('warehouse_product_inventory_id', 'wpi_id_foreign')->references('id')->on('warehouse_product_inventories');

            $table->unsignedBigInteger('products_for_bucketvendor_id')->nullable();
            $table->foreign('products_for_bucketvendor_id', 'pfb_id_foreign')->references('id')->on('products_for_bucketvendors');

            $table->integer('quantity');
            $table->boolean('is_paid')->default(false);
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
        Schema::dropIfExists('warehouse_product_bucketvendor');
    }
}
