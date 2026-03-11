<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Consolidates ratings: adds column to watchlist_items, migrates data, drops old table
     */
    public function up(): void
    {
        // Step 1: Add rating column to watchlist_items (if not already present)
        if (!Schema::hasColumn('watchlist_items', 'rating')) {
            Schema::table('watchlist_items', function (Blueprint $table) {
                $table->unsignedTinyInteger('rating')->nullable()->after('is_watched');
            });
        }

        // Step 2: Migrate data from ratings table to watchlist_items.rating (if ratings table exists)
        if (Schema::hasTable('ratings')) {
            DB::statement('
                UPDATE watchlist_items wi
                SET rating = (
                    SELECT r.rating FROM ratings r
                    WHERE r.user_id = wi.user_id AND r.tmdb_id = wi.tmdb_id
                    LIMIT 1
                )
                WHERE EXISTS (
                    SELECT 1 FROM ratings r
                    WHERE r.user_id = wi.user_id AND r.tmdb_id = wi.tmdb_id
                )
            ');

            // Step 3: Drop the old ratings table - no longer needed
            Schema::dropIfExists('ratings');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert: remove rating column and recreate ratings table
        if (Schema::hasColumn('watchlist_items', 'rating')) {
            Schema::table('watchlist_items', function (Blueprint $table) {
                $table->dropColumn('rating');
            });
        }

        // Recreate the old ratings table if needed
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('tmdb_id');
            $table->unsignedTinyInteger('rating');
            $table->timestamps();

            $table->unique(['user_id', 'tmdb_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};