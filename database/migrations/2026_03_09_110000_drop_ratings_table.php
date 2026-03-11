<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate ratings from ratings table to watchlist_items.rating column
        DB::statement('
            UPDATE watchlist_items wi
            SET rating = (
                SELECT r.rating
                FROM ratings r
                WHERE r.user_id = wi.user_id
                AND r.tmdb_id = wi.tmdb_id
                LIMIT 1
            )
            WHERE EXISTS (
                SELECT 1 FROM ratings r
                WHERE r.user_id = wi.user_id
                AND r.tmdb_id = wi.tmdb_id
            )
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert: copy ratings back to ratings table if needed
        // For now, just set all ratings to null
        DB::table('watchlist_items')->update(['rating' => null]);
    }
};
