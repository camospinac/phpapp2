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
        Schema::table('plans', function (Blueprint $table) {
            // Para saber qué fórmula usar: 'percentage_based' o 'fixed_plus_final'
            $table->string('calculation_type')->default('percentage_based')->after('description');
            // Para guardar el 15% del Plan Básico
            $table->decimal('fixed_percentage', 5, 2)->nullable()->after('calculation_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['calculation_type', 'fixed_percentage']);
        });
    }
};
