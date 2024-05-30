<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePosProductWarehousesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pos_product_warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stadium_location_id')->constrained('stadium_locations');

            $table->integer('user_manager_id')->nullable();
            $table->foreign('user_manager_id')->references('id')->on('users');

            $table->string('name');
            $table->string('description')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
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
        Schema::dropIfExists('pos_product_warehouses');
    }
}
