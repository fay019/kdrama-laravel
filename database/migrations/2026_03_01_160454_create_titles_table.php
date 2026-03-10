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
        Schema::create('titles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('content_id')->nullable(false);

            $table->string('language', 5)->nullable(false);
            $table->string('title', 500)->nullable(false);
            $table->string('original_title', 500)->nullable();
            $table->string('tagline', 500)->nullable();
            $table->longText('overview')->nullable();
            $table->enum('overview_source', ['native', 'fallback_en'])->default('native');

            $table->timestamps();

            $table->unique(['content_id', 'language']);
            $table->foreign('content_id')->references('id')->on('contents')->onDelete('cascade');
            $table->index('language');
            $table->fullText(['title', 'overview']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('titles');
    }
};
