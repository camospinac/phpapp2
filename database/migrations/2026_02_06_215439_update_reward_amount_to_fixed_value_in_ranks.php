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
        Schema::table('ranks', function (Blueprint $table) {
            // 1. Renombramos la columna
            $table->renameColumn('reward_percentage', 'reward_amount');
        });

        Schema::table('ranks', function (Blueprint $table) {
            // 2. Cambiamos el tipo y la longitud para que aguante montos grandes
            $table->decimal('reward_amount', 15, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ranks', function (Blueprint $table) {
            // El camino de regreso por si algo falla
            $table->renameColumn('reward_amount', 'reward_percentage');
            $table->decimal('reward_percentage', 5, 2)->default(0)->change();
        });
    }
};
