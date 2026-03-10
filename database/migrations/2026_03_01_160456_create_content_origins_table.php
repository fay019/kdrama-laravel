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
        Schema::create('content_origins', function (Blueprint $table) {
            $table->unsignedBigInteger('content_id')->nullable(false);
            $table->unsignedInteger('origin_id')->nullable(false);
            $table->boolean('is_primary')->default(false);

            $table->primary(['content_id', 'origin_id']);
            $table->foreign('content_id')->references('id')->on('contents')->onDelete('cascade');
            $table->foreign('origin_id')->references('id')->on('origins')->onDelete('cascade');
            $table->index('is_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_origins');
    }
};
