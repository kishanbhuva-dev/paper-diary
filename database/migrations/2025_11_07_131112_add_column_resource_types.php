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
        Schema::table('resource_types', function (Blueprint $table) {
            $table->enum('slot', ['hourly', 'day', 'monthly'])->default('day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resource_types', function (Blueprint $table) {
            $table->dropColumn('slot');
        });
    }
};
