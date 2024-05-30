<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePosCategorySubcategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pos_category_subcategory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_product_category_id')->constrained('pos_product_categories');
            $table->foreignId('pos_product_subcategory_id')->constrained('pos_product_subcategories');
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
        Schema::dropIfExists('pos_category_subcategory');
    }
}
