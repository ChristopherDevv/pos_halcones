<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePosCashRegistersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pos_cash_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_product_warehouse_id')->constrained('pos_product_warehouses');
            $table->foreignId('pos_cash_register_type_id')->constrained('pos_cash_register_types');
            
            $table->integer('user_cashier_opening_id')->nullable();
            $table->foreign('user_cashier_opening_id')->references('id')->on('users');

            $table->integer('user_cashier_closing_id')->nullable();
            $table->foreign('user_cashier_closing_id')->references('id')->on('users');

            $table->foreignId('stadium_location_id')->nullable()->constrained();
            $table->string('description')->nullable();
            $table->boolean('is_open')->default(false); 
            $table->decimal('opening_balance', 14, 4)->nullable();
            $table->decimal('current_balance', 14, 4)->nullable();
            $table->decimal('closing_balance', 14, 4)->nullable();
            $table->dateTime('opening_time')->nullable();
            $table->dateTime('closing_time')->nullable();

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
        Schema::dropIfExists('pos_cash_registers');
    }
}
