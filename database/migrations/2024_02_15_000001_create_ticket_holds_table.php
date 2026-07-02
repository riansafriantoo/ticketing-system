<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Add hold tracking columns to tickets ─────────────────────────────
        Schema::table('tickets', function (Blueprint $table) {
            // Timestamp of when the CURRENT on-hold period began.
            // NULL when the ticket is not currently on hold.
            $table->timestamp('hold_started_at')
                  ->nullable()
                  ->after('resolution_duration_minutes')
                  ->comment('Set when status becomes on_hold, cleared when it leaves on_hold.');

            // Running total of all hold time across the ticket's life, in minutes.
            // Updated every time a hold period ends (status leaves on_hold).
            $table->unsignedInteger('total_hold_minutes')
                  ->default(0)
                  ->after('hold_started_at')
                  ->comment('Cumulative minutes spent in on_hold status, across all hold periods.');
        });

        // ── Create ticket_holds table — full audit history of every hold ────
        //
        // Why a separate table instead of just the two columns above?
        // The columns give us a fast "current state" check, but a real
        // helpdesk needs the FULL history: when was it held, by whom,
        // why, and for how long — visible in the ticket's activity timeline
        // and reportable later (e.g. "average hold duration by reason").
        Schema::create('ticket_holds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('held_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('held_at');
            $table->timestamp('resumed_at')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable()
                  ->comment('Set when resumed_at is filled in. NULL while hold is still active.');
            $table->string('reason', 255)->nullable();
            $table->timestamps();

            $table->index(['ticket_id', 'resumed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_holds');

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['hold_started_at', 'total_hold_minutes']);
        });
    }
};
