<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComboSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('combo_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('global_combo_id')->constrained('global_combos');
            $table->foreignId('pos_product_warehouse_id')->constrained('pos_product_warehouses');
            $table->foreignId('pos_cash_register_id')->constrained('pos_cash_registers');
            $table->foreignId('pos_sale_id')->constrained('pos_sales');
            $table->integer('sale_count');
            $table->boolean('is_canceled')->default(false);
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
        Schema::dropIfExists('combo_sales');
    }
}
