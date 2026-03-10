<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_metadata', function (Blueprint $table) {
            $table->id();

            // Author Info
            $table->string('author_name')->nullable();
            $table->text('author_bio')->nullable();
            $table->string('author_email')->nullable();
            $table->string('author_avatar')->nullable();

            // Site Info
            $table->string('site_name')->nullable();
            $table->string('site_tagline')->nullable();
            $table->text('site_footer_text')->nullable();
            $table->string('site_copyright')->nullable();

            // SEO
            $table->string('meta_description', 160)->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('og_type')->default('website');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_metadata');
    }
};
