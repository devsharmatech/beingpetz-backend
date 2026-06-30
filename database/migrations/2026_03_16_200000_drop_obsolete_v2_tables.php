<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Drop the obsolete V2-specific tables that have been consolidated into V1 tables.
     */
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        Schema::dropIfExists('v2_post_tags');
        Schema::dropIfExists('v2_post_videos');
        Schema::dropIfExists('v2_post_images');
        Schema::dropIfExists('v2_engagement_shares');
        Schema::dropIfExists('v2_engagement_likes');
        Schema::dropIfExists('v2_friend_request_logs');
        Schema::dropIfExists('v2_reposts'); // RECHECK: User said "other also". Reposts were also separate.
        Schema::dropIfExists('v2_comments');
        Schema::dropIfExists('v2_posts');

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback
    }
};
