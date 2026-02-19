<?php
// database/migrations/2024_01_01_000000_create_settings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->default('general');
            $table->string('key', 191)->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text'); // text, textarea, email, number, boolean, select
            $table->text('options')->nullable(); // for select type
            $table->string('label');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
};