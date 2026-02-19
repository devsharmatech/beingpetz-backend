<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('adbanners', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('sort');
            $table->date('end_date')->nullable()->after('start_date');
            $table->string('section')->default('services')->after('end_date'); // services, adoption, lost_found
        });
    }

    public function down()
    {
        Schema::table('adbanners', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date', 'section']);
        });
    }
};