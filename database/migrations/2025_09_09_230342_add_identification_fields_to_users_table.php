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
            // Creamos un array con los tipos de documento permitidos
            $documentTypes = [
                'TARJETA IDENTIDAD',
                'CEDULA CIUDANIA',
                'CEDULA EXTRANJERIA',
                'PASAPORTE'
            ];

            // Añadimos las nuevas columnas después de 'apellidos'
            $table->enum('identification_type', $documentTypes)->nullable()->after('apellidos');
            $table->string('identification_number')->nullable()->after('identification_type');
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
