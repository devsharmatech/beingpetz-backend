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
        Schema::create('adoption_listings', function (Blueprint $table) {
            $table->id();
            
            // Who’s offering the pet
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('pet_name');
            $table->string('slug')->unique();  
            $table->string('pet_type');
            $table->string('breed')->nullable();
            $table->enum('gender', ['male','female','unknown'])->default('unknown');
            $table->date('dob')->nullable();
            $table->text('description')->nullable();

            $table->text('about_pet')->nullable();        
            $table->boolean('is_healthy')->default(true);  
            $table->boolean('vaccination_done')->default(false);

            // Location & contact
            $table->string('location')->nullable();       
            $table->string('latitude')->nullable();       
            $table->string('longitude')->nullable();       
            $table->string('contact_phone');
            $table->string('contact_email');

            $table->string('featured_image')->nullable();
            $table->enum('status', ['available','pending','adopted'])
                  ->default('available');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adoption_listings');
    }
};
