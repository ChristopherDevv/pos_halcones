<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePosSubcategoryProductCatalogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pos_subcategory_product_catalog', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pos_product_subcategory_id');
            $table->foreign('pos_product_subcategory_id', 'pos_subcat_prod_cat_subcat_id_foreign')->references('id')->on('pos_product_subcategories');
            $table->unsignedBigInteger('warehouse_product_catalog_id');
            $table->foreign('warehouse_product_catalog_id', 'pos_subcat_prod_cat_warehouse_id_foreign')->references('id')->on('warehouse_product_catalogs');
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
        Schema::dropIfExists('pos_subcategory_product_catalog');
    }
}
