<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
        $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('plan_id')->constrained();
        $table->decimal('amount', 10, 2); // El monto calculado
        $table->integer('percentage'); // El porcentaje usado (5, 10, 15...)
        $table->string('status')->default('pending'); // 'pending', 'paid', etc.
        $table->date('payment_due_date');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
