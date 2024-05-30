<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePosWarehouseCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pos_warehouse_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_warehouse_id')->constrained('pos_product_warehouses');
            $table->foreignId('pos_product_category_id')->constrained('pos_product_categories');
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
        Schema::dropIfExists('pos_warehouse_category');
    }
}
