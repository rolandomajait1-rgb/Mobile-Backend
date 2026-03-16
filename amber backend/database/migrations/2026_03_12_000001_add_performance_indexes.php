<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Articles table indexes for faster queries
        Schema::table('articles', function (Blueprint $table) {
            $table->index('status');
            $table->index('published_at');
            $table->index(['status', 'published_at']);
            $table->index('category_id');
            $table->index('author_id');
        });

        // Article interactions indexes
        Schema::table('article_interactions', function (Blueprint $table) {
            $table->index(['article_id', 'user_id', 'type']);
            $table->index(['user_id', 'type']);
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['published_at']);
            $table->dropIndex(['status', 'published_at']);
            $table->dropIndex(['category_id']);
            $table->dropIndex(['author_id']);
        });

        Schema::table('article_interactions', function (Blueprint $table) {
            $table->dropIndex(['article_id', 'user_id', 'type']);
            $table->dropIndex(['user_id', 'type']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
        });
    }
};
