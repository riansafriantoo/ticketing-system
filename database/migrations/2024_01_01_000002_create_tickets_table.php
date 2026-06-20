<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('subject', 255);
            $table->text('description');

            $table->string('status', 20)->default('open');
            $table->string('priority', 20)->default('medium');
            $table->string('category', 30)->default('other');
            $table->string('case_type', 30)->default('hardware');

            $table->foreignId('requester_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('assignee_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('sla_due_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->boolean('sla_breached')->default(false);

            $table->softDeletes();
            $table->timestamps();

            // Composite indexes for common filter patterns
            $table->index(['status', 'priority']);
            $table->index(['status', 'created_at']);
            $table->index(['requester_id', 'status']);
            $table->index(['assignee_id', 'status']);
            $table->index('sla_due_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};