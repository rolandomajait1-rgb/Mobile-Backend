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
        // Skip in testing environment to avoid duplicate index errors
        if (app()->environment('testing')) {
            return;
        }

        // Articles table indexes for faster queries
        Schema::table('articles', function (Blueprint $table) {
            if (!$this->indexExists('articles', 'articles_status_index')) {
                $table->index('status');
            }
            if (!$this->indexExists('articles', 'articles_published_at_index')) {
                $table->index('published_at');
            }
            if (!$this->indexExists('articles', 'articles_status_published_at_index')) {
                $table->index(['status', 'published_at']);
            }
            if (!$this->indexExists('articles', 'articles_category_id_index')) {
                $table->index('category_id');
            }
            if (!$this->indexExists('articles', 'articles_author_id_index')) {
                $table->index('author_id');
            }
        });

        // Article interactions indexes
        Schema::table('article_interactions', function (Blueprint $table) {
            if (!$this->indexExists('article_interactions', 'amber_article_interactions_article_id_user_id_type_index')) {
                $table->index(['article_id', 'user_id', 'type']);
            }
            if (!$this->indexExists('article_interactions', 'amber_article_interactions_user_id_type_index')) {
                $table->index(['user_id', 'type']);
            }
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            if (!$this->indexExists('users', 'users_role_index')) {
                $table->index('role');
            }
        });
    }

    /**
     * Check if an index exists
     */
    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $schemaManager = $connection->getDoctrineSchemaManager();
        $doctrineTable = $schemaManager->listTableDetails($connection->getTablePrefix() . $table);
        
        return $doctrineTable->hasIndex($index);
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
