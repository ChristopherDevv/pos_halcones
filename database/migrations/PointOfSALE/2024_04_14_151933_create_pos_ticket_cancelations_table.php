<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePosTicketCancelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pos_ticket_cancelations', function (Blueprint $table) {
            $table->id();

            $table->integer('user_cashier_id')->nullable();
            $table->foreign('user_cashier_id')->references('id')->on('users');

            $table->foreignId('pos_ticket_id')->constrained('pos_tickets')->nullable();
            $table->decimal('total_amount', 14, 4);
            $table->string('reason')->nullable();
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
        Schema::dropIfExists('pos_ticket_cancelations');
    }
}
