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
        Schema::create('kdramas', function (Blueprint $table) {
            $table->id();
            $table->string('tmdb_id')->unique();
            $table->string('name'); // Titre FR (prioritaire)
            $table->string('en_name')->nullable(); // Titre EN
            $table->string('original_name')->nullable(); // Titre OR
            $table->text('overview')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('backdrop_path')->nullable();
            $table->string('first_air_date')->nullable();
            $table->float('vote_average')->default(0);
            $table->integer('vote_count')->default(0);
            $table->json('genres')->nullable();
            $table->json('origin_country')->nullable();
            $table->string('status')->nullable();
            $table->string('original_language')->nullable();
            $table->integer('number_of_episodes')->nullable();
            $table->integer('number_of_seasons')->nullable();
            $table->string('last_air_date')->nullable();
            $table->json('credits')->nullable();
            $table->json('similar')->nullable();
            $table->json('translations')->nullable(); // Stockage des versions FR/EN/DE
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kdramas');
    }
};
