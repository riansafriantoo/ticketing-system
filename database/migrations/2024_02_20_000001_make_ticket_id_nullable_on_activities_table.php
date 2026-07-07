<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Makes activities.ticket_id nullable.
 *
 * REQUIRED for the new user_deactivated / user_reactivated activity
 * log entries, which are account-level events not tied to any single
 * ticket. If ticket_id is currently a required foreign key, inserting
 * one of these entries will fail with a database constraint violation
 * the first time an admin deactivates a user.
 *
 * Skip this migration only if you've confirmed ticket_id is already
 * nullable in your activities table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            // Drop and re-add as nullable — SQLite/MySQL both require
            // dropping the existing foreign key constraint first if one
            // exists, then re-adding it as nullable.
            $table->foreignId('ticket_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->foreignId('ticket_id')->nullable(false)->change();
        });
    }
};