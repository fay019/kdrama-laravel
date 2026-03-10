<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Setting;
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

            // API settings
            ['key' => 'tmdb_api_key', 'value' => env('TMDB_API_KEY', ''), 'group' => 'api', 'label' => 'TMDB API Key'],
            ['key' => 'rapidapi_key', 'value' => env('RAPIDAPI_KEY', ''), 'group' => 'api', 'label' => 'RapidAPI Key'],
            ['key' => 'rapidapi_host', 'value' => env('RAPIDAPI_HOST', ''), 'group' => 'api', 'label' => 'RapidAPI Host'],
            ['key' => 'api_source_priority', 'value' => 'env_first', 'group' => 'api', 'label' => 'API Source Priority (env_first or db_first)'],
        ];

        foreach ($defaultSettings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
