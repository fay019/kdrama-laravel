<?php

namespace Database\Seeders;

use App\Models\SiteMetadata;
use App\Models\SocialLink;
use Illuminate\Database\Seeder;

class SiteMetadataSeeder extends Seeder
{
    public function run(): void
    {
        // Create or update site metadata
        $metadata = SiteMetadata::firstOrCreate([], [
            'author_name' => 'Moussouni',
            'author_bio' => 'Passionate K-Drama enthusiast dedicated to helping fans discover and rate their favorite Korean dramas with detailed streaming availability.',
            'author_email' => 'admin@moussouni.dev',
            'author_avatar' => 'avatars/avatar.png',
            'site_name' => 'Moussouni',
            'site_tagline' => 'Discover & Rate Korean Dramas',
            'site_footer_text' => 'Your ultimate K-Drama companion. Discover, track, and rate your favorite Korean dramas with real-time streaming availability.',
            'site_copyright' => '© 2026 Moussouni. All rights reserved.',
            'meta_description' => 'Discover and rate Korean dramas with real-time streaming availability. Build your watchlist and find where to watch your favorite K-Dramas.',
            'meta_keywords' => 'kdrama, korean drama, watch kdrama, kdrama list, korean series, drama ratings, streaming',
            'og_title' => 'Moussouni - Discover & Rate K-Dramas',
            'og_description' => 'Your ultimate platform to discover, track, and rate Korean dramas with real-time streaming information.',
            'og_type' => 'website',
        ]);

        // Clear existing social links
        SocialLink::truncate();

        // Add social links
        $socialLinks = [
            ['platform' => 'Twitter', 'url' => 'https://x.com/moussouni', 'icon' => 'share', 'order' => 0, 'is_visible' => true],
            ['platform' => 'GitHub', 'url' => 'https://github.com', 'icon' => 'link', 'order' => 1, 'is_visible' => true],
            ['platform' => 'Email', 'url' => 'mailto:admin@moussouni.dev', 'icon' => 'envelope', 'order' => 2, 'is_visible' => true],
        ];

        foreach ($socialLinks as $link) {
            SocialLink::create($link);
        }
    }
}
