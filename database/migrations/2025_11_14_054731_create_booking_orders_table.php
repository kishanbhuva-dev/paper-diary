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
        Schema::create('booking_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('propertyId')->constrained('property');
            $table->foreignId('userId')->constrained('users');
            $table->foreignId('resourceTypeId')->constrained('resource_types');
            $table->dateTime('arrivalDateTime');
            $table->dateTime('departureDateTime');
            $table->integer('adult');
            $table->integer('children')->default(0);
            $table->decimal('price', 15, 2);
            $table->decimal('cost', 15, 2)->default(0);
            $table->enum('status', [ 'pending','confirm', 'cancelled'])->default('pending');
            $table->enum('paymentStatus', ['paid', 'unpaid', 'failed', 'cancelled', 'confirm'])->default('unpaid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_orders');
    }
};
