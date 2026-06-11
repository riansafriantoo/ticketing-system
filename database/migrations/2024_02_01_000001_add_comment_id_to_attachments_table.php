<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            // nullable — existing ticket-level attachments have no comment
            $table->foreignId('comment_id')
                  ->nullable()
                  ->after('ticket_id')
                  ->constrained('comments')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Comment::class);
            $table->dropColumn('comment_id');
        });
    }
};