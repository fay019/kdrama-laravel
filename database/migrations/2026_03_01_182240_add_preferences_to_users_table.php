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
        Schema::table('users', function (Blueprint $table) {
            // Only add columns if they don't already exist
            if (!Schema::hasColumn('users', 'preferred_language')) {
                $table->string('preferred_language')->default('fr')->after('password');
            }
            if (!Schema::hasColumn('users', 'preferred_region')) {
                $table->string('preferred_region')->default('fr')->after('preferred_language');
            }
            if (!Schema::hasColumn('users', 'is_admin')) {
                $table->boolean('is_admin')->default(false)->after('preferred_region');
            }
            if (!Schema::hasColumn('users', 'is_public')) {
                $table->boolean('is_public')->default(true)->after('is_admin');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['preferred_language', 'preferred_region', 'is_admin', 'is_public']);
        });
    }
};
