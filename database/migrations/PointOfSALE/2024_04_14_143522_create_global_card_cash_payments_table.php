<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalCardCashPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('global_card_cash_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('global_type_card_payment_id')->constrained('global_type_card_payments')->nullable(); // hacerlo nullable manualmente
            $table->decimal('amount_received', 14, 4);
            $table->decimal('amount_change_given', 14, 4);
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
        Schema::dropIfExists('global_card_cash_payments');
    }
}
