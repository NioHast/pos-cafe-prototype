<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applied_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('promotion_id')->nullable()->constrained('promotions')->nullOnDelete();
            $table->enum('discount_type', ['percentage', 'fixed_amount', 'buy_x_get_y', 'bundle']);
            $table->decimal('discount_value', 10, 2);
            $table->decimal('discount_amount', 10, 2);
            $table->timestamps();

            $table->index('order_id');
            $table->index('promotion_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applied_promotions');
    }
};