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
        Schema::create('job_history', function (Blueprint $table) {
            $table->id();
            $table->string('job_class'); // e.g., App\Jobs\SyncPopularActors
            $table->string('queue')->default('default');
            $table->text('payload'); // Serialized job data
            $table->integer('attempts')->default(1);
            $table->text('output')->nullable(); // Job output/logs
            $table->enum('status', ['completed', 'failed'])->default('completed');
            $table->text('exception')->nullable(); // Error message if failed
            $table->integer('duration_seconds')->nullable(); // How long job took
            $table->json('metadata')->nullable(); // Custom job metadata (e.g., actors_synced, dramas_processed)
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_history');
    }
};
