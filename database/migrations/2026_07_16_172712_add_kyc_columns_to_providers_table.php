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
        Schema::table('providers', function (Blueprint $table) {
            $table->string('kyc_status')->default('pending'); // pending, partially_verified, verified, rejected
            $table->text('kyc_message')->nullable();
            $table->json('kyc_documents')->nullable(); // Additional documents
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn(['kyc_status', 'kyc_message', 'kyc_documents']);
        });
    }
};
