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
        Schema::table('ranks', function (Blueprint $table) {
            $table->dropColumn('reward_amount'); // Remove the old fixed amount
            $table->decimal('reward_percentage', 5, 2)->default(10)->after('reward_description'); // Add the new percentage field
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ranks', function (Blueprint $table) {
            //
        });
    }
};
