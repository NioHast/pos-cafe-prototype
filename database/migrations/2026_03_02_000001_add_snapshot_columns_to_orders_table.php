<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add denormalized snapshot columns for immutable transaction history.
     * - Customer snapshot (name, type) for audit trail
     * - Financial breakdown (subtotal, discount, tax, grand_total)
     * - Void audit trail (reason, notes, timestamp, who)
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Customer snapshot
            $table->string('customer_name')->nullable()->after('customer_id');
            $table->string('customer_type', 20)->default('guest')->after('customer_name');

            // Financial breakdown
            $table->decimal('subtotal', 12, 2)->default(0)->after('total_price');
            $table->decimal('discount_total', 12, 2)->default(0)->after('subtotal');
            $table->decimal('tax_amount', 12, 2)->default(0)->after('discount_total');
            $table->decimal('grand_total', 12, 2)->default(0)->after('tax_amount');

            // Void audit trail
            $table->string('void_reason')->nullable()->after('payment_method');
            $table->text('void_notes')->nullable()->after('void_reason');
            $table->timestamp('voided_at')->nullable()->after('void_notes');
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete()->after('voided_at');

            // Indexes for reporting
            $table->index('customer_type');
            $table->index('voided_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['voided_by']);
            $table->dropIndex(['customer_type']);
            $table->dropIndex(['voided_at']);
            $table->dropColumn([
                'customer_name',
                'customer_type',
                'subtotal',
                'discount_total',
                'tax_amount',
                'grand_total',
                'void_reason',
                'void_notes',
                'voided_at',
                'voided_by',
            ]);
        });
    }
};
