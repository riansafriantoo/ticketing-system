<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Comments ──────────────────────────────────────────────────────────
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->boolean('is_internal')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['ticket_id', 'created_at']);
        });

        // ── Attachments ───────────────────────────────────────────────────────
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('original_name', 255);
            $table->string('stored_name', 500);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->string('disk', 20)->default('public');
            $table->timestamps();

            $table->index('ticket_id');
        });

        // ── Activity log ─────────────────────────────────────────────────────
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 60);
            $table->json('meta')->nullable();
            $table->timestamp('created_at');

            $table->index(['ticket_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('comments');
    }
};