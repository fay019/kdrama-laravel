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
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('sync_type', 50)->nullable(false);
            $table->enum('status', ['pending', 'running', 'success', 'failed'])->default('pending');

            $table->integer('items_total')->default(0);
            $table->integer('items_synced')->default(0);
            $table->integer('items_failed')->default(0);

            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('next_sync_at')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['sync_type', 'status']);
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};
