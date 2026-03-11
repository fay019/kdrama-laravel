<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteMetadata extends Model
{
    protected $fillable = [
        'author_name',
        'author_bio',
        'author_email',
        'author_avatar',
        'site_name',
        'site_tagline',
        'site_footer_text',
        'site_copyright',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
        'og_image',
        'og_type',
        'favicon_path',
    ];

    public function socialLinks()
    {
        return $this->hasMany(SocialLink::class)->orderBy('order');
    }
}
