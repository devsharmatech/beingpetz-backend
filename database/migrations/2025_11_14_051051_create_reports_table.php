<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('report_by');
            $table->unsignedBigInteger('post_id')->nullable();
            $table->enum('type', ['post', 'comment', 'pet', 'profile', 'community', 'message']);
            $table->unsignedBigInteger('comment_id')->nullable();
            $table->unsignedBigInteger('community_id')->nullable();
            $table->unsignedBigInteger('pet_id')->nullable();
            $table->unsignedBigInteger('profile_id')->nullable();
            $table->unsignedBigInteger('message_id')->nullable();
            $table->text('reason')->nullable();
            $table->enum('status',['pending','reviewed','resolved','rejected'])->default('pending');
            
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('report_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->foreign('comment_id')->references('id')->on('comments')->onDelete('cascade');
            $table->foreign('community_id')->references('id')->on('communities')->onDelete('cascade');
            $table->foreign('pet_id')->references('id')->on('pets')->onDelete('cascade');
            $table->foreign('profile_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('message_id')->references('id')->on('community_messages')->onDelete('cascade');
        });
    }

   
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};