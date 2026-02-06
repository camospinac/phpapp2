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
    Schema::table('withdrawals', function (Blueprint $table) {
        // Redefinimos la columna para incluir 'rejected'
        // Si usas ENUM, asegúrate de poner TODOS los estados actuales + el nuevo
        $table->enum('status', ['pending', 'completed', 'rejected'])->default('pending')->change();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            //
        });
    }
};
