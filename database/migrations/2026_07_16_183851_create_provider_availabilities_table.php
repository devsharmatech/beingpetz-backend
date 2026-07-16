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
        Schema::create('provider_availabilities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id');
            // If date is null, it acts as the "Default Operational Shifts" for this provider
            // If date is provided, it acts as an override for that specific date
            $table->date('date')->nullable(); 
            $table->boolean('is_blocked')->default(false); // Used for "Set vacation dates and block scheduling slots"
            $table->boolean('morning_active')->default(true); // 08:00 AM - 12:00 PM
            $table->boolean('afternoon_active')->default(true); // 12:00 PM - 05:00 PM
            $table->boolean('evening_active')->default(false); // 05:00 PM - 09:00 PM
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_availabilities');
    }
};
