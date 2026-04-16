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
        Schema::table('kdramas', function (Blueprint $table) {
            $table->boolean('adult_only')->default(false)->after('original_language')->comment('Marked for adult content exclusion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kdramas', function (Blueprint $table) {
            $table->dropColumn('adult_only');
        });
    }
};
