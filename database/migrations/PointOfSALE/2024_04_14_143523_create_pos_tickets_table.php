<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePosTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pos_tickets', function (Blueprint $table) {
            $table->id();

            $table->integer('user_cashier_id')->nullable();
            $table->foreign('user_cashier_id')->references('id')->on('users');

            $table->foreignId('pos_cash_register_id')->constrained('pos_cash_registers');
            $table->foreignId('global_payment_type_id')->constrained('global_payment_types');
            $table->foreignId('global_card_cash_payment_id')->constrained('global_card_cash_payments')->nullable(); 
            $table->foreignId('pos_ticket_status_id')->constrained('pos_ticket_statuses');
            $table->foreignId('pos_sale_id')->constrained('pos_sales');
            $table->foreignId('bucket_vendor_product_id')->constrained('bucket_vendor_products')->nullable(); // hacerlo nullable manualmente
            $table->decimal('total_amount', 14, 4);
            $table->integer('sale_folio')->nullable();
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
        Schema::dropIfExists('pos_tickets');
    }
}
