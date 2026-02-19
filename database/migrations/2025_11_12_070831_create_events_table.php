<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('admin_id')->constrained()->onDelete('cascade');
                $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
                $table->string('title');
                $table->string('slug', 191)->unique();
                $table->text('description');
                $table->date('event_date')->nullable();
                $table->string('location')->nullable();
                $table->string('image')->nullable();
                $table->timestamps();
            });
        }
        // If table exists, we assume it has the correct structure
        // You can create a separate migration to add missing columns if needed
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};