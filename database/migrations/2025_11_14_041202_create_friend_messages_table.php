<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('friend_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id');
            $table->enum('message_type', ['text', 'image', 'video', 'audio'])->default('text');
            $table->text('message_text')->nullable();
            $table->string('media_path')->nullable();
            $table->boolean('is_seen')->default(false);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes for better performance
            $table->index(['sender_id', 'receiver_id']);
            $table->index('is_seen');
            $table->index('created_at');
        });
    }

  
    public function down(): void
    {
        Schema::dropIfExists('friend_messages');
    }
};