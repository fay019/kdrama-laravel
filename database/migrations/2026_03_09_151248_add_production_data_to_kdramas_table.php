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
            if (!Schema::hasColumn('kdramas', 'production_companies')) {
                $table->json('production_companies')->nullable()->after('credits');
            }
            if (!Schema::hasColumn('kdramas', 'networks')) {
                $table->json('networks')->nullable()->after('production_companies');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kdramas', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('kdramas', 'production_companies')) {
                $columns[] = 'production_companies';
            }
            if (Schema::hasColumn('kdramas', 'networks')) {
                $columns[] = 'networks';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
