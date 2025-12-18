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
        Schema::table('booking_orders', function (Blueprint $table) {
            $table->text('guestFullName')->nullable()->after('arrivalDateTime');
            $table->string('guestEmail')->nullable()->after('guestFullName');
            $table->string('guestPhone')->nullable()->after('guestEmail');
            $table->text('guestAddress')->nullable()->after('guestPhone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_orders', function (Blueprint $table) {
            $table->dropColumn('guestAddress');
            $table->dropColumn('guestPhone');
            $table->dropColumn('guestEmail');
            $table->dropColumn('guestFullName');
        });
    }
};
