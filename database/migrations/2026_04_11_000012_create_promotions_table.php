<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['percentage', 'fixed_amount', 'buy_x_get_y', 'bundle']);
            $table->decimal('discount_value', 10, 2);
            $table->decimal('min_purchase', 10, 2)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'inactive', 'scheduled', 'expired'])->default('scheduled');
            $table->json('applicable_items')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};