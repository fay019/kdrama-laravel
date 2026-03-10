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
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('platforms');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('availabilities');
        Schema::dropIfExists('content_genres');
        Schema::dropIfExists('content_origins');
        Schema::dropIfExists('titles');
        Schema::dropIfExists('genres');
        Schema::dropIfExists('origins');
        Schema::dropIfExists('sync_logs');
        Schema::dropIfExists('contents');

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
