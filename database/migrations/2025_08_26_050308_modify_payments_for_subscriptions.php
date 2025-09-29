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
        Schema::table('payments', function (Blueprint $table) {
            // Añadimos la nueva columna para la suscripción
            $table->foreignId('subscription_id')->after('id')->constrained('subscriptions')->onDelete('cascade');

            // Hacemos las columnas user_id y plan_id innecesarias y las eliminamos
            $table->dropForeign(['user_id']);
            $table->dropForeign(['plan_id']);
            $table->dropColumn(['user_id', 'plan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            //
        });
    }
};
