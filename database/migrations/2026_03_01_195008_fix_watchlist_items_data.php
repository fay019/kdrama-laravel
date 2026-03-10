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
        // Clear all data and start fresh
        // Since we're in development, this is safe
        DB::table('watchlist_items')->truncate();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to restore data since we truncated
    }
};
