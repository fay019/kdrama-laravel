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
        Schema::create('export_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('format')->default('pdf'); // pdf or csv
            $table->integer('item_count')->default(0); // nombre d'items exportés
            $table->integer('file_size')->default(0); // taille en bytes
            $table->string('cache_hash')->nullable(); // hash du cache
            $table->boolean('was_cached')->default(false); // si c'était du cache
            $table->integer('generation_time')->default(0); // temps en millisecondes
            $table->json('filters')->nullable(); // paramètres de filtrage
            $table->timestamps();

            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index('user_id');
            $table->index('format');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_logs');
    }
};
