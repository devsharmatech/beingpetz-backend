<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('sort');
            $table->date('end_date')->nullable()->after('start_date');
            $table->boolean('is_active')->default(true)->after('end_date');
        });
    }

    public function down()
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date', 'is_active']);
        });
    }
};