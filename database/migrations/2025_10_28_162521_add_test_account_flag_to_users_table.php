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
            // Añadimos la columna. Por defecto, todos son 'false' (usuarios reales)
            $table->boolean('es_cuenta_prueba')->default(false)->after('rol');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('es_cuenta_prueba');
        });
    }
};
