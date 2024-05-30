<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductInventoryBucketVendorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_inventory_bucket_vendor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_product_inventory_id');
            $table->foreign('warehouse_product_inventory_id', 'prod_inv_bucket_vendor_inventory_id_foreign')->references('id')->on('warehouse_product_inventories');
            $table->unsignedBigInteger('bucket_vendor_product_id');
            $table->foreign('bucket_vendor_product_id', 'prod_inv_bucket_vendor_product_id_foreign')->references('id')->on('bucket_vendor_products');
            $table->integer('stock_received');
            $table->integer('stock_sold');
            $table->integer('stock_returned');
            $table->integer('current_stock');
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
        Schema::dropIfExists('product_inventory_bucket_vendor');
    }
}
