<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::update("UPDATE settings SET is_sensitive = 1 WHERE `key` LIKE '%key%' OR `key` LIKE '%secret%' OR `key` LIKE '%password%'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::update('UPDATE settings SET is_sensitive = 0');
    }
};
