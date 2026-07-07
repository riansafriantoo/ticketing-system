<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The notification_logs table is the idempotency layer for the
     * email notification pipeline.
     *
     * HOW IT WORKS:
     * Every listener computes a "fingerprint" — a hash unique to that
     * specific notification event (e.g. "ticket 42 changed status to
     * resolved"). Before sending, it attempts to INSERT a row with that
     * fingerprint. The unique constraint on fingerprint makes the second
     * INSERT fail if the fingerprint already exists, meaning the email
     * was already sent. The listener catches that exception and skips
     * sending silently.
     *
     * This is bulletproof regardless of WHY duplicates are occurring —
     * duplicate event dispatch, duplicate listener registration,
     * auto-discovery conflicts, stale queue jobs — none of those can
     * cause a duplicate email if the fingerprint is already in this table.
     */
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('fingerprint')->unique(); // the idempotency key
            $table->string('event_type');            // e.g. "ticket_created"
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->json('recipient_emails');        // audit: who was notified
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->index(['ticket_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};