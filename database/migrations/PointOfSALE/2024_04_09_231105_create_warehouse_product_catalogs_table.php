<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWarehouseProductCatalogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouse_product_catalogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_unit_measurement_id')->constrained('pos_unit_measurements')->nullable();

            $table->integer('user_seller_id')->nullable();
            $table->foreign('user_seller_id')->references('id')->on('users');
            $table->foreignId('clothing_category_id')->constrained('clothing_categories')->nullable();
            $table->string('name');
            $table->integer('unit_measurement_quantity')->nullable();
            $table->boolean('is_clothing')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('sales_code')->unique();
            $table->string('description')->nullable();

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
        Schema::dropIfExists('warehouse_product_catalogs');
    }
}
