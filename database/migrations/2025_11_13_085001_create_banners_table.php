<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('link')->nullable(); // Banner click link
            $table->string('mobile_image')->nullable(); // Image for mobile devices
            $table->string('desktop_image')->nullable(); // Image for desktop devices
            $table->integer('sort')->default(0); // Sorting order
            $table->timestamps(); // created_at and updated_at
        });
    }

   
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};