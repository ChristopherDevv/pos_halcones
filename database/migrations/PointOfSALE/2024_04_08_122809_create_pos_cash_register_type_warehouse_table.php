<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePosCashRegisterTypeWarehouseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pos_cash_register_type_warehouse', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pos_cash_register_type_id');
            $table->foreign('pos_cash_register_type_id', 'pos_cash_type_id_foreign')->references('id')->on('pos_cash_register_types')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('pos_product_warehouse_id');
            $table->foreign('pos_product_warehouse_id', 'pos_prod_warehouse_id_foreign')->references('id')->on('pos_product_warehouses')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('pos_cash_register_type_warehouse');
    }
}
