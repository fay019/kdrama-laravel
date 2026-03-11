<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Setting::updateOrCreate(
            ['key' => 'adsense_client_id'],
            [
                'value' => '',
                'group' => 'general',
                'label' => 'AdSense Client ID'
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Setting::where('key', 'adsense_client_id')->delete();
    }
};
