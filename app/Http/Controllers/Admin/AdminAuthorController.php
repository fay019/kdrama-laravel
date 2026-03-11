<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteMetadata;
use App\Models\SocialLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminAuthorController extends Controller
{
    public function edit()
    {
        $metadata = SiteMetadata::firstOrCreate(
            [],
            [
                'site_name' => config('app.name'),
                'og_type' => 'website',
            ]
        );

        $socialLinks = SocialLink::orderBy('order')->get();

        return view('admin.author.edit', [
            'metadata' => $metadata,
            'socialLinks' => $socialLinks,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'author_name' => 'nullable|string|max:255',
            'author_bio' => 'nullable|string|max:1000',
            'author_email' => 'nullable|email',
            'author_avatar' => 'nullable|image|max:2048',
            'site_name' => 'nullable|string|max:255',
            'site_tagline' => 'nullable|string|max:255',
            'site_footer_text' => 'nullable|string|max:1000',
            'site_copyright' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|image|max:2048',
            'og_type' => 'nullable|in:website,blog,article',
        ]);

        $metadata = SiteMetadata::firstOrCreate([]);

        // Handle avatar upload
        if ($request->hasFile('author_avatar')) {
            if ($metadata->author_avatar && Storage::disk('public')->exists($metadata->author_avatar)) {
                Storage::disk('public')->delete($metadata->author_avatar);
            }
            $path = $request->file('author_avatar')->store('avatars', 'public');
            $validated['author_avatar'] = $path;
        }

        // Handle OG image upload
        if ($request->hasFile('og_image')) {
            if ($metadata->og_image && Storage::disk('public')->exists($metadata->og_image)) {
                Storage::disk('public')->delete($metadata->og_image);
            }
            $path = $request->file('og_image')->store('og-images', 'public');
            $validated['og_image'] = $path;
        }

        $metadata->update($validated);

        // Handle social links
        if ($request->has('social_links')) {
            SocialLink::truncate();

            $newOrder = 0;
            foreach ($request->input('social_links', []) as $link) {
                // Skip empty links
                if (empty($link['platform']) || empty($link['url'])) {
                    continue;
                }

                // Auto-add https:// if URL doesn't have http:// or https://
                $url = $link['url'];
                if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://') && !str_starts_with($url, 'mailto:')) {
                    $url = 'https://' . $url;
                }

                SocialLink::create([
                    'platform' => $link['platform'],
                    'url' => $url,
                    'icon' => $link['icon'] ?? null,
                    'order' => $newOrder,
                    'is_visible' => isset($link['is_visible']) ? (bool)$link['is_visible'] : true,
                ]);

                $newOrder++;
            }
        }

        return redirect()->route('admin.author.edit')->with('success', __('admin.author_updated'));
    }

    public function reorderSocialLinks(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:social_links,id',
        ]);

        $ids = $request->input('ids');

        // Update order for each social link based on position in array
        foreach ($ids as $index => $id) {
            SocialLink::find($id)->update(['order' => $index]);
        }

        return response()->json(['success' => true, 'message' => __('admin.social_links_reordered')]);
    }

    public function saveSocialLinks(Request $request)
    {
        $request->validate([
            'social_links' => 'nullable|array',
            'social_links.*.platform' => 'required|string|max:50',
            'social_links.*.url' => 'required|url',
            'social_links.*.icon' => 'nullable|string|max:50',
        ]);

        // Delete old links and create new ones
        SocialLink::truncate();

        if ($request->input('social_links')) {
            foreach ($request->input('social_links') as $order => $link) {
                SocialLink::create([
                    'platform' => $link['platform'],
                    'url' => $link['url'],
                    'icon' => $link['icon'] ?? null,
                    'order' => $order,
                ]);
            }
        }

        return redirect()->route('admin.author.edit')->with('success', __('admin.social_links_updated'));
    }
}
