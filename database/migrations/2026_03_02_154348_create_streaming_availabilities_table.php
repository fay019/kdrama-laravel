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
        Schema::create('streaming_availabilities', function (Blueprint $table) {
            $table->id();
            $table->string('tmdb_id')->index();
            $table->string('type')->default('tv'); // 'tv' or 'movie'
            $table->string('region')->default('fr');
            $table->json('data'); // Stocke les plateformes parsées
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();

            $table->unique(['tmdb_id', 'type', 'region']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('streaming_availabilities');
    }
};
