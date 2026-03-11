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
        Schema::table('site_metadata', function (Blueprint $table) {
            $table->string('favicon_path')->nullable()->after('og_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_metadata', function (Blueprint $table) {
            $table->dropColumn('favicon_path');
        });
    }
};
