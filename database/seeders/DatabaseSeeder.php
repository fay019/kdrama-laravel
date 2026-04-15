<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed site metadata and social links
        $this->call(SiteMetadataSeeder::class);

        // Create default admin user
        User::firstOrCreate(
            ['email' => 'admin@kdrama.local'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'is_admin' => true,
                'preferred_language' => 'fr',
                'preferred_region' => 'fr',
            ]
        );

        // Create test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'is_admin' => false,
        ]);

        // Create default settings
        $defaultSettings = [
            // Site settings
            ['key' => 'site_name', 'value' => 'KDrama Hub', 'group' => 'site', 'label' => 'Site Name'],
            ['key' => 'site_description', 'value' => 'Your favorite Korean dramas hub', 'group' => 'site', 'label' => 'Site Description'],
            ['key' => 'site_links', 'value' => '{}', 'group' => 'site', 'label' => 'Site Links (JSON)'],

            // API settings - Streaming (RapidAPI)
            ['key' => 'rapidapi_key', 'value' => env('RAPIDAPI_KEY', ''), 'group' => 'api_streaming', 'label' => 'RapidAPI Key (Streaming)'],
            ['key' => 'rapidapi_host', 'value' => env('RAPIDAPI_HOST', ''), 'group' => 'api_streaming', 'label' => 'RapidAPI Host'],
            ['key' => 'rapidapi_cache_hours', 'value' => '24', 'group' => 'api_streaming', 'label' => 'RapidAPI Cache Duration (hours)'],

            // API settings - Watchmode
            ['key' => 'watchmode_api_key', 'value' => env('WATCHMODE_API_KEY', ''), 'group' => 'api_watchmode', 'label' => 'Watchmode API Key'],
            ['key' => 'watchmode_cache_hours', 'value' => '24', 'group' => 'api_watchmode', 'label' => 'Watchmode Cache Duration (hours)'],
            ['key' => 'watchmode_enabled', 'value' => 'false', 'group' => 'api_watchmode', 'label' => 'Enable Watchmode'],

            // API settings - TMDB
            ['key' => 'tmdb_api_key', 'value' => env('TMDB_API_KEY', ''), 'group' => 'api_tmdb', 'label' => 'TMDB API Key'],

            // API settings - General
            ['key' => 'api_source_priority', 'value' => 'env_first', 'group' => 'api_general', 'label' => 'API Source Priority (env_first or db_first)'],
            ['key' => 'streaming_provider_priority', 'value' => 'rapidapi', 'group' => 'api_general', 'label' => 'Streaming Provider Priority (rapidapi or watchmode)'],
        ];

        foreach ($defaultSettings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
