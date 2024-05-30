<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletExchangeRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_wallet_currency_id')->constrained('wallet_currencies');
            $table->foreignId('to_wallet_currency_id')->constrained('wallet_currencies');
            $table->decimal('rate', 14, 4);
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
        Schema::dropIfExists('wallet_exchange_rates');
    }
}
