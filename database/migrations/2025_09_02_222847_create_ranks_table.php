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
        Schema::create('ranks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // "Bronce", "Plata", etc.
            $table->integer('required_referrals'); // 10, 25, 50, etc.
            $table->string('reward_description');
            $table->string('reward_type')->default('cash_bonus');
            $table->decimal('reward_amount', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ranks');
    }
};
