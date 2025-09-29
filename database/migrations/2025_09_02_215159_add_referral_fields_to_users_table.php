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
        Schema::table('users', function (Blueprint $table) {
            // Para guardar el código de referido ÚNICO de este usuario
            $table->string('referral_code', 8)->unique()->nullable()->after('rol');

            // Para guardar el ID del usuario que lo refirió a él
            $table->foreignUuid('referred_by_id')->nullable()->after('referral_code')->constrained('users')->onDelete('set null');
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
