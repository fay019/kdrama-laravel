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
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('subject');
            $table->longText('message');

            // Attachments
            $table->string('attachment_path')->nullable();
            $table->string('attachment_original_name')->nullable();
            $table->string('attachment_mime')->nullable();
            $table->bigInteger('attachment_size')->nullable();

            $table->boolean('email_sent')->default(false);
            $table->text('error_message')->nullable();

            // Status and timestamps (merged from other migrations)
            $table->enum('status', ['pending', 'read', 'in_progress', 'resolved', 'spam'])->default('pending');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('email_sent');
            $table->index('created_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
