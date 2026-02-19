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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained('pets')->onDelete('cascade');
            $table->string('slug')->unique();
            $table->text('content')->nullable();
            $table->boolean('is_public')->default(true);
            $table->string('featured_image')->nullable();
            $table->string('featured_video')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
