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
        // Initialize order for all settings by group
        $groups = DB::table('settings')->distinct()->pluck('group');

        foreach ($groups as $group) {
            $settings = DB::table('settings')
                ->where('group', $group)
                ->orderBy('id')
                ->get();

            foreach ($settings as $index => $setting) {
                DB::table('settings')
                    ->where('id', $setting->id)
                    ->update(['order' => $index + 1]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->update(['order' => 0]);
    }
};
