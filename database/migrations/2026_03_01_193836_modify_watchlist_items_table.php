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
        Schema::table('watchlist_items', function (Blueprint $table) {
            // Drop old status column if it exists
            if (Schema::hasColumn('watchlist_items', 'status')) {
                $table->dropColumn('status');
            }

            // Add new boolean columns
            $table->boolean('is_in_watchlist')->default(true);
            $table->boolean('is_watched')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('watchlist_items', function (Blueprint $table) {
            // Drop new columns
            if (Schema::hasColumn('watchlist_items', 'is_in_watchlist')) {
                $table->dropColumn('is_in_watchlist');
            }
            if (Schema::hasColumn('watchlist_items', 'is_watched')) {
                $table->dropColumn('is_watched');
            }

            // Re-add status column
            $table->enum('status', ['plan_to_watch', 'watching', 'completed', 'dropped'])->default('plan_to_watch');
        });
    }
};
