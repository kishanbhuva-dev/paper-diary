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
        Schema::create('property', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ownerId')->constrained('users');
            $table->string('propertyName');
            $table->string('email')->nullable();
            $table->longText('address');
            $table->longText('address2')->nullable();
            $table->string('country')->nullable();
            $table->string('county')->nullable();
            $table->string('city')->nullable();
            $table->string('postcode')->nullable();
            $table->string('phone')->nullable();
            $table->string('telephone')->nullable();
            $table->string('latitude');
            $table->string('longitude');
            $table->time('arrivalTime')->nullable();
            $table->time('departureTime')->nullable();
            $table->boolean('status')->default(false);
            $table->boolean('isIcal')->default(false);
            $table->longText('description')->nullable();
            $table->integer('visitorCount')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property');
    }
};
