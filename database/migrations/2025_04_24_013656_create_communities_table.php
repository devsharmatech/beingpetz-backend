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
        Schema::create('communities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique()->nullable();
            $table->text('description')->nullable();
            $table->string('type');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('profile')->nullable();
            $table->string('cover_image')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communities');
    }
};
