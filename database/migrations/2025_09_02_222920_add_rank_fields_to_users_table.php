<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            // Para saber qué rango tiene el usuario
            $table->foreignId('rank_id')->nullable()->after('rol')->constrained('ranks')->onDelete('set null');

            // Para contar eficientemente los referidos
            $table->unsignedInteger('referral_count')->default(0)->after('rank_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
