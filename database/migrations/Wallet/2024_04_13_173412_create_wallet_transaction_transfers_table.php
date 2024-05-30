<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletTransactionTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_transaction_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_transaction_id')->constrained('wallet_transactions')->onDelete('cascade');
            $table->foreignId('sender_wallet_account_id')->constrained('wallet_accounts')->onDelete('cascade');
            $table->foreignId('receiver_wallet_account_id')->constrained('wallet_accounts')->onDelete('cascade');
            $table->string('description')->nullable();
            $table->decimal('sender_balance_before_transaction', 14, 4)->nullable();
            $table->decimal('sender_balance_after_transaction', 14, 4)->nullable();
            $table->decimal('receiver_balance_before_transaction', 14, 4)->nullable();
            $table->decimal('receiver_balance_after_transaction', 14, 4)->nullable();
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
        Schema::dropIfExists('wallet_transaction_transfers');
    }
}
