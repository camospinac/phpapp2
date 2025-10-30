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
        Schema::table('winners', function (Blueprint $table) {
            // 1. Añadimos las nuevas columnas
            $table->string('nombre_completo')->after('id');
            $table->string('cedula')->after('nombre_completo')->nullable(); // 'nullable' por si no la quieres obligatoria

            // 2. Eliminamos la columna de usuario
            // (Asegúrate de que el nombre de la FK sea 'winners_user_id_foreign')
            $table->dropForeign('winners_user_id_foreign');
            $table->dropColumn('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('winners', function (Blueprint $table) {
            // El proceso inverso
            $table->dropColumn('nombre_completo');
            $table->dropColumn('cedula');

            $table->char('user_id', 36)->after('id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
