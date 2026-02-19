<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->string('author_name')->nullable()->after('content');
            $table->text('author_link')->nullable()->after('author_name');
            $table->timestamp('published_at')->nullable()->after('author_link');
        });
    }

    public function down()
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn(['author_name', 'author_link', 'published_at']);
        });
    }
};