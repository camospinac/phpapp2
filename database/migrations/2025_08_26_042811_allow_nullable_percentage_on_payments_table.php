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
            // Hacemos que la columna 'percentage' acepte valores nulos
            $table->integer('percentage')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // La revertimos para que no acepte nulos (para los rollbacks)
            $table->integer('percentage')->nullable(false)->change();
        });
    }
};
