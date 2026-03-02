<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add denormalized snapshot columns to order_items for immutable transaction history.
     * Also fix menu_id FK from CASCADE to SET NULL (menu deletion shouldn't destroy order history).
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Snapshot menu data at transaction time
            $table->string('product_name')->after('menu_id');
            $table->decimal('price', 10, 2)->after('product_name');
            $table->decimal('base_price', 10, 2)->after('price');

            // Snapshot promo data
            $table->decimal('discount_amount', 10, 2)->default(0)->after('base_price');
            $table->string('discount_name')->nullable()->after('discount_amount');

            // Calculated line total: (price × quantity) - discount_amount
            $table->decimal('line_total', 10, 2)->after('discount_name');
        });

        // Fix menu_id FK: cascade → set null (preserving order history if menu deleted)
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['menu_id']);
            $table->foreignId('menu_id')->nullable()->change();
            $table->foreign('menu_id')->references('id')->on('menu')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore original FK behavior
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['menu_id']);
            $table->foreignId('menu_id')->nullable(false)->change();
            $table->foreign('menu_id')->references('id')->on('menu')->cascadeOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'product_name',
                'price',
                'base_price',
                'discount_amount',
                'discount_name',
                'line_total',
            ]);
        });
    }
};
