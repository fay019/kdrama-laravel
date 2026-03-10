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
        Schema::create('availabilities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('content_id')->nullable(false);
            $table->unsignedInteger('platform_id')->nullable(false);

            $table->string('region', 2)->nullable(false);
            $table->boolean('is_available')->default(true);
            $table->enum('watch_type', ['free', 'rent', 'buy', 'subscription'])->default('subscription');

            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 3)->nullable();

            $table->string('deep_link', 1000)->nullable();

            $table->timestamp('synced_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['content_id', 'platform_id', 'region']);
            $table->foreign('content_id')->references('id')->on('contents')->onDelete('cascade');
            $table->foreign('platform_id')->references('id')->on('platforms')->onDelete('cascade');

            $table->index(['platform_id', 'region', 'is_available']);
            $table->index('synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};
