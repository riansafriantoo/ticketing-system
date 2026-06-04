<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_tag', 20)->unique();
            $table->string('name', 255);
            $table->text('description')->nullable();

            // Classification
            $table->string('category', 30)->default('other');
            $table->string('status', 20)->default('active');

            // Hardware details
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('serial_number', 100)->nullable()->unique();

            // Financial
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 12, 2)->nullable();
            $table->date('warranty_expiry')->nullable();

            // Location & assignment
            $table->string('location', 150)->nullable();
            $table->foreignId('assigned_to')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();

            $table->text('notes')->nullable();
            $table->string('image')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Indexes for common filter patterns
            $table->index(['status', 'category']);
            $table->index(['assigned_to', 'status']);
            $table->index('warranty_expiry');
            $table->index('purchase_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};