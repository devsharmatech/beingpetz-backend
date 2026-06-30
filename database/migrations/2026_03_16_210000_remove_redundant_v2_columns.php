<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Remove redundant user_id columns that were added to V1 tables,
     * as we are now reusing the existing parent_id columns for V2 unification.
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            if (Schema::hasColumn('comments', 'user_id')) {
                $table->dropColumn('user_id');
            }
        });

        Schema::table('likes', function (Blueprint $table) {
            if (Schema::hasColumn('likes', 'user_id')) {
                $table->dropColumn('user_id');
            }
        });

        Schema::table('shares', function (Blueprint $table) {
            if (Schema::hasColumn('shares', 'user_id')) {
                $table->dropColumn('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback
    }
};
