<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_notifications_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('notifiable_id')->nullable();
            $table->string('type'); // info, alert, promo, etc.
            $table->string('title');
            $table->text('message');
            $table->string('image')->nullable();
            $table->json('audience')->nullable(); // Store audience filters
            $table->boolean('is_read')->default(false);
            $table->timestamp('scheduled_at')->nullable();
            $table->boolean('is_sent')->default(false);
            $table->boolean('status')->default(true); // enabled/disabled
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}