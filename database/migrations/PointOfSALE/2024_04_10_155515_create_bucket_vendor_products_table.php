<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBucketVendorProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bucket_vendor_products', function (Blueprint $table) {
            $table->id();

            $table->integer('user_bucket_vendor_id')->nullable();
            $table->foreign('user_bucket_vendor_id')->references('id')->on('users');
            
            $table->foreignId('pos_product_warehouse_id')->constrained('pos_product_warehouses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bucket_vendor_products');
    }
}
