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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre interno (ej: "Promo Navidad")
            $table->string('title'); // Título que ve el usuario en el modal
            $table->text('content'); // Contenido del modal
            $table->string('image_url')->nullable(); // Imagen para el modal
            $table->boolean('is_active')->default(false); // Switch para activarla/desactivarla
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
