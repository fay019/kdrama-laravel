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
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->enum('status', ['pending', 'read', 'resolved', 'spam'])->default('pending')->after('error_message');
            $table->timestamp('read_at')->nullable()->after('status');
            $table->timestamp('resolved_at')->nullable()->after('read_at');
            $table->index('status');
            $table->index('read_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropColumn(['status', 'read_at', 'resolved_at']);
            $table->dropIndex(['status', 'read_at']);
        });
    }
};
