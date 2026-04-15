<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->foreignId('ingredient_batch_id')->nullable()->constrained('ingredient_batches')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            // FK to waste_records will be added in phase 2 after waste_records table exists.
            $table->unsignedBigInteger('waste_record_id')->nullable();
            $table->foreignId('stock_adjustment_id')->nullable()->constrained('stock_adjustments')->nullOnDelete();
            $table->enum('movement_type', [
                'purchase',
                'sale',
                'waste',
                'adjustment_increase',
                'adjustment_decrease',
                'correction',
            ]);
            $table->string('source_type')->nullable();
            $table->string('source_id')->nullable();
            $table->decimal('quantity_before', 12, 2);
            $table->decimal('quantity_change', 12, 2);
            $table->decimal('quantity_after', 12, 2);
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['ingredient_id', 'created_at']);
            $table->index(['movement_type', 'created_at']);
            $table->index(['source_type', 'source_id']);
            $table->index('waste_record_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
