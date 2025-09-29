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
    Schema::table('subscriptions', function (Blueprint $table) {
        // Para el consecutivo por usuario
        $table->unsignedInteger('sequence_id')->nullable()->after('plan_id');
        // Para guardar la ganancia calculada
        $table->decimal('profit_amount', 15, 2)->nullable()->after('initial_investment');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            //
        });
    }
};
