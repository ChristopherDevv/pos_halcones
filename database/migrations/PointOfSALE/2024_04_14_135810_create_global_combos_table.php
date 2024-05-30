<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalCombosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('global_combos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_product_warehouse_id')->constrained('pos_product_warehouses');
            $table->string('name')->unique();
            $table->decimal('sale_price', 14, 4);
            $table->integer('permitted_products');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
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
        Schema::dropIfExists('global_combos');
    }
}
