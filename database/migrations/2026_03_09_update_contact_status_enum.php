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
        // SQLite doesn't support changing enum, so we need to do it differently
        Schema::table('contact_messages', function (Blueprint $table) {
            // Drop the old enum
            $table->dropColumn('status');
        });

        Schema::table('contact_messages', function (Blueprint $table) {
            // Add the new enum with more states
            $table->enum('status', ['pending', 'read', 'in_progress', 'resolved', 'spam'])
                  ->default('pending')
                  ->after('error_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('contact_messages', function (Blueprint $table) {
            $table->enum('status', ['pending', 'read', 'resolved', 'spam'])
                  ->default('pending')
                  ->after('error_message');
        });
    }
};
