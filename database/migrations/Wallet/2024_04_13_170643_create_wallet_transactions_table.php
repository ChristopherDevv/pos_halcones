<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('origin_wallet_account_id')->constrained('wallet_accounts');
            $table->foreignId('destination_wallet_account_id')->constrained('wallet_accounts')->nullable();
            $table->foreignId('wallet_transaction_type_id')->constrained('wallet_transaction_types')->nullable();
            $table->foreignId('wallet_transaction_status_id')->constrained('wallet_transaction_statuses')->nullable();
            $table->foreignId('global_payment_type_id')->constrained('global_payment_types')->nullable(); /* haceRlo nullable manualmente */
            $table->foreignId('global_card_cash_payment_id')->constrained('global_card_cash_payments')->nullable(); /* haceRlo nullable manualmente */
            $table->foreignId('pos_sale_id')->constrained('pos_sales')->nullable(); /* haceRlo nullable manualmente */
            $table->foreignId('seller_wallet_account_id')->constrained('wallet_accounts')->nullable();
            $table->decimal('amount', 14, 4)->nullable();
            $table->string('description')->nullable();
            $table->decimal('balance_account_before_transaction', 14, 4)->nullable();
            $table->decimal('balance_account_after_transaction', 14, 4)->nullable();
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
        Schema::dropIfExists('wallet_transactions');
    }
}
