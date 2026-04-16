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
        Schema::create('actors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tmdb_id')->unique();
            $table->string('name');
            $table->string('en_name')->nullable();
            $table->text('biography')->nullable();
            $table->string('profile_path')->nullable();
            $table->date('birthday')->nullable();
            $table->string('birthplace')->nullable();
            $table->json('known_for')->nullable();
            $table->integer('tv_credits_count')->default(0);
            $table->float('popularity')->default(0);
            $table->json('external_ids')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index('tmdb_id');
            $table->index('tv_credits_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actors');
    }
};
