<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add V2-specific columns to existing V1 tables.
     * All new columns are NULLABLE so V1 APIs continue working unchanged.
     */
    public function up(): void
    {
        // --- POSTS TABLE ---
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'posted_by_type')) {
                $table->string('posted_by_type', 20)->nullable()->after('parent_id');
            }
            if (!Schema::hasColumn('posts', 'posted_by_id')) {
                $table->unsignedBigInteger('posted_by_id')->nullable()->after('posted_by_type');
            }
            if (!Schema::hasColumn('posts', 'media_urls')) {
                $table->json('media_urls')->nullable()->after('featured_video');
            }
            if (!Schema::hasColumn('posts', 'status')) {
                $table->string('status', 20)->nullable()->default('active')->after('media_urls');
            }
            if (!Schema::hasColumn('posts', 'moderation_reason')) {
                $table->text('moderation_reason')->nullable()->after('status');
            }
        });

        // --- COMMENTS TABLE ---
        Schema::table('comments', function (Blueprint $table) {
            if (!Schema::hasColumn('comments', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('post_id');
            }
            if (!Schema::hasColumn('comments', 'commented_by_type')) {
                $table->string('commented_by_type', 20)->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('comments', 'commented_by_id')) {
                $table->unsignedBigInteger('commented_by_id')->nullable()->after('commented_by_type');
            }
            if (!Schema::hasColumn('comments', 'status')) {
                $table->string('status', 20)->nullable()->default('active')->after('comment');
            }
            if (!Schema::hasColumn('comments', 'moderation_reason')) {
                $table->text('moderation_reason')->nullable()->after('status');
            }
            if (!Schema::hasColumn('comments', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // --- LIKES TABLE ---
        Schema::table('likes', function (Blueprint $table) {
            if (!Schema::hasColumn('likes', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('post_id');
            }
            if (!Schema::hasColumn('likes', 'liked_by_type')) {
                $table->string('liked_by_type', 20)->nullable()->default('parent')->after('parent_id');
            }
            if (!Schema::hasColumn('likes', 'liked_by_id')) {
                $table->unsignedBigInteger('liked_by_id')->nullable()->after('liked_by_type');
            }
        });

        // --- SHARES TABLE ---
        Schema::table('shares', function (Blueprint $table) {
            if (!Schema::hasColumn('shares', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('post_id');
            }
            if (!Schema::hasColumn('shares', 'shared_by_type')) {
                $table->string('shared_by_type', 20)->nullable()->default('parent')->after('parent_id');
            }
            if (!Schema::hasColumn('shares', 'shared_by_id')) {
                $table->unsignedBigInteger('shared_by_id')->nullable()->after('shared_by_type');
            }
            if (!Schema::hasColumn('shares', 'platform')) {
                $table->string('platform', 50)->nullable()->after('shared_by_id');
            }
        });

        // --- FRIEND REQUESTS TABLE ---
        Schema::table('friend_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('friend_requests', 'responded_at')) {
                $table->timestamp('responded_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('friend_requests', 'message')) {
                $table->text('message')->nullable()->after('responded_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['posted_by_type', 'posted_by_id', 'media_urls', 'status', 'moderation_reason']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'commented_by_type', 'commented_by_id', 'status', 'moderation_reason']);
            $table->dropSoftDeletes();
        });

        Schema::table('likes', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'liked_by_type', 'liked_by_id']);
        });

        Schema::table('shares', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'shared_by_type', 'shared_by_id', 'platform']);
        });

        Schema::table('friend_requests', function (Blueprint $table) {
            $table->dropColumn(['responded_at', 'message']);
        });
    }
};
