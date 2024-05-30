<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePosSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pos_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_account_id')->constrained('wallet_accounts')->nullable(); // hacerlo nullable manualmente
            $table->foreignId('global_combo_id')->constrained('global_combos')->nullable(); // hacerlo nullable manualmente
            $table->foreignId('pos_sales_receivable_id')->constrained('pos_sales_receivables')->nullable(); // hacerlo nullable manualmente
            $table->unsignedBigInteger('products_for_bucketvendor_id')->nullable();
            $table->foreign('products_for_bucketvendor_id', 'pfb_idd_foreign')->references('id')->on('products_for_bucketvendors');
            $table->boolean('is_bucketvendor_sale')->default(false);
            $table->decimal('total_amount', 14, 4); 
            $table->decimal('total_amount_payable', 14, 4)->nullable();
            $table->boolean('is_combo_sale')->default(false);
            $table->integer('combos_quantity')->nullable();
            $table->boolean('paid_with_courtesy')->default(false);
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
        Schema::dropIfExists('pos_sales');
    }
}
