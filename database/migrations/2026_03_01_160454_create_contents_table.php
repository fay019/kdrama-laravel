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
        Schema::create('contents', function (Blueprint $table) {
            $table->bigIncrements('id');

            // External IDs
            $table->integer('tmdb_id')->unique()->nullable(false);
            $table->string('imdb_id', 20)->nullable();

            // Type & status
            $table->enum('type', ['tv', 'movie'])->nullable(false);
            $table->string('status', 50)->nullable();

            // Visual metadata
            $table->string('poster_path', 255)->nullable();
            $table->string('backdrop_path', 255)->nullable();

            // Ratings
            $table->decimal('vote_average', 3, 1)->default(0);
            $table->integer('vote_count')->default(0);
            $table->decimal('popularity', 10, 3)->default(0);

            // Dates
            $table->date('first_air_date')->nullable();
            $table->date('last_air_date')->nullable();
            $table->date('release_date')->nullable();

            // Series/Movie info
            $table->integer('number_of_seasons')->nullable();
            $table->integer('number_of_episodes')->nullable();
            $table->string('episode_run_time', 100)->nullable();
            $table->integer('runtime')->nullable();

            // Adult content flag
            $table->boolean('is_adult')->default(false);

            // Sync timestamps
            $table->timestamp('tmdb_updated_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('type');
            $table->index('vote_average');
            $table->index('popularity');
            $table->index('first_air_date');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
