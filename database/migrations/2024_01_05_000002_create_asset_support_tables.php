<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Assignment history ────────────────────────────────────────────────
        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at');
            $table->timestamp('returned_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['asset_id', 'returned_at']);
            $table->index(['user_id', 'returned_at']);
        });

        // ── Maintenance / service log ─────────────────────────────────────────
        Schema::create('asset_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 30)->default('service'); // repair|service|upgrade|inspection
            $table->text('description');
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('vendor', 150)->nullable();
            $table->date('performed_at');
            $table->date('next_maintenance_at')->nullable();
            $table->timestamps();

            $table->index(['asset_id', 'performed_at']);
        });

        // ── Link ticket ↔ asset ───────────────────────────────────────────────
        // (asset_id column on existing tickets table)
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('asset_id')
                  ->nullable()
                  ->after('category')
                  ->constrained('assets')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', fn ($t) => $t->dropForeignIdFor(\App\Models\Asset::class));
        Schema::dropIfExists('asset_maintenances');
        Schema::dropIfExists('asset_assignments');
    }
};