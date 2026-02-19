<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('blogs')) {
            Schema::create('blogs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('admin_id')->constrained()->onDelete('cascade');
                $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
                $table->string('title');
                $table->text('short_description');
                $table->longText('content');
                $table->string('image')->nullable();
                $table->string('slug', 191)->unique(); // 191 characters max
                $table->timestamps();
                
                $table->index('slug');
                $table->index('admin_id');
                $table->index('category_id');
            });
        } else {
            // Handle existing table
            Schema::table('blogs', function (Blueprint $table) {
                $columns = [
                    'admin_id' => function() use ($table) {
                        if (!Schema::hasColumn('blogs', 'admin_id')) {
                            $table->foreignId('admin_id')->constrained()->onDelete('cascade')->after('id');
                        }
                    },
                    'category_id' => function() use ($table) {
                        if (!Schema::hasColumn('blogs', 'category_id')) {
                            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null')->after('admin_id');
                        }
                    },
                    'short_description' => function() use ($table) {
                        if (!Schema::hasColumn('blogs', 'short_description')) {
                            $table->text('short_description')->after('title');
                        }
                    },
                    'content' => function() use ($table) {
                        if (!Schema::hasColumn('blogs', 'content')) {
                            $table->longText('content')->after('short_description');
                        }
                    },
                    'image' => function() use ($table) {
                        if (!Schema::hasColumn('blogs', 'image')) {
                            $table->string('image')->nullable()->after('content');
                        }
                    },
                    'slug' => function() use ($table) {
                        if (!Schema::hasColumn('blogs', 'slug')) {
                            $table->string('slug', 191)->unique()->after('image');
                        } else {
                            // Check if unique constraint exists and remove it
                            $indexes = DB::select('SHOW INDEX FROM blogs WHERE Column_name = "slug" AND Key_name != "PRIMARY"');
                            foreach ($indexes as $index) {
                                if ($index->Non_unique == 0) {
                                    $table->dropUnique($index->Key_name);
                                }
                            }
                            // Modify existing slug column
                            $table->string('slug', 191)->unique()->change();
                        }
                    }
                ];

                foreach ($columns as $column => $action) {
                    $action();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};