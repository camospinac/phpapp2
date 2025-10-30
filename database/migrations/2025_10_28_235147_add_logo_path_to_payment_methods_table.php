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
    Schema::table('payment_methods', function (Blueprint $table) {
        // La ruta donde guardaremos 'logos/nequi.png', etc.
        $table->string('logo_path')->nullable()->after('account_details');
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            //
        });
    }
};
