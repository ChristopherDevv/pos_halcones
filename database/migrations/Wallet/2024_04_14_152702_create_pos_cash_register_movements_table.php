<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePosCashRegisterMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pos_cash_register_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_cash_register_id')->constrained('pos_cash_registers');
            $table->foreignId('pos_movement_type_id')->constrained('pos_movement_types');
            $table->foreignId('pos_ticket_id')->constrained('pos_tickets')->nullable(); // hacerlo nullable manualmente
            $table->foreignId('pos_ticket_cancelation_id')->constrained('pos_ticket_cancelations')->nullable(); // hacerlo nullable manualmente
            $table->integer('user_manager_id')->nullable();
            $table->foreign('user_manager_id')->references('id')->on('users');
            $table->boolean('is_active')->default(false);
            $table->decimal('previous_balance', 14, 4);
            $table->decimal('movement_amount', 14, 4);
            $table->decimal('new_balance', 14, 4);
            $table->string('reason');
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
        Schema::dropIfExists('pos_cash_register_movements');
    }
}
