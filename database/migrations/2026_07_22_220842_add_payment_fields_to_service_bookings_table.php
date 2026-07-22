<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_bookings', function (Blueprint $table) {
            // Payment fields tightly coupled with booking
            $table->string('payment_status')->default('pending')->after('total_amount'); // pending, paid, failed, refunded
            $table->string('payment_method')->nullable()->after('payment_status');       // razorpay, upi, cash, card
            $table->string('payment_gateway')->nullable()->after('payment_method');      // razorpay, stripe, etc.
            $table->string('transaction_id')->nullable()->after('payment_gateway');      // Gateway transaction ID
            $table->string('payment_gateway_order_id')->nullable()->after('transaction_id'); // Gateway order ID
            $table->text('notes')->nullable()->after('payment_gateway_order_id');        // Customer notes/special instructions
            $table->string('pet_id')->nullable()->after('notes');                        // Pet for whom service is booked
        });
    }

    public function down(): void
    {
        Schema::table('service_bookings', function (Blueprint $table) {
            $table->dropColumn([
                'payment_status', 'payment_method', 'payment_gateway',
                'transaction_id', 'payment_gateway_order_id', 'notes', 'pet_id'
            ]);
        });
    }
};
