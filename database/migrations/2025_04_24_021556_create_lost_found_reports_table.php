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
        Schema::create('lost_found_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('phone');
            $table->enum('report_type', ['lost', 'found']);
            $table->string('pet_type');        
            $table->enum('pet_gender', ['male','female','unknown'])->default('unknown');
            $table->string('breed')->nullable();
            $table->date('pet_dob')->nullable();   
            $table->text('about_pet')->nullable();  
            
            // When & where
            $table->string('location')->nullable();   
            $table->dateTime('occurred_at')->nullable();
            $table->json('images')->nullable();
            $table->enum('status', ['open','resolved','cancelled'])
                  ->default('open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lost_found_reports');
    }
};
