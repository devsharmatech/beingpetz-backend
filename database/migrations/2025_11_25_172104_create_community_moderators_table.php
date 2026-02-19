<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_community_moderators_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('community_moderators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['moderator', 'admin'])->default('moderator');
            $table->timestamps();
            
            $table->unique(['community_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('community_moderators');
    }
};