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
            $table->string('business_name')->nullable();
            $table->string('legal_name')->nullable();
            $table->json('services')->nullable();
            $table->string('area')->nullable();
            $table->string('experience_years')->nullable();
            $table->decimal('start_pricing', 10, 2)->nullable();
            $table->decimal('consultation_fee', 10, 2)->nullable();
            $table->json('accepted_pet_types')->nullable();
            $table->json('accepted_pet_sizes')->nullable();
            $table->string('emergency_contact_number')->nullable();
            $table->json('weekly_schedule')->nullable();
            $table->string('primary_gov_doc')->nullable();
            $table->string('alternate_id_doc')->nullable();
            $table->json('proof_of_expertise')->nullable();
            $table->json('work_gallery')->nullable();
            $table->string('video_walkthrough')->nullable();
            $table->boolean('dpdp_consent')->default(false);
            $table->json('service_specific_data')->nullable();
            
            // service_id is currently NOT NULL, let's change it to nullable because we use 'services' JSON
            $table->unsignedBigInteger('service_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn([
                'business_name',
                'legal_name',
                'services',
                'area',
                'experience_years',
                'start_pricing',
                'consultation_fee',
                'accepted_pet_types',
                'accepted_pet_sizes',
                'emergency_contact_number',
                'weekly_schedule',
                'primary_gov_doc',
                'alternate_id_doc',
                'proof_of_expertise',
                'work_gallery',
                'video_walkthrough',
                'dpdp_consent',
                'service_specific_data'
            ]);
        });
    }
};
