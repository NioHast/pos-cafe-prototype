<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_name')->after('id');
            $table->string('customer_phone', 20)->after('customer_name');
            $table->enum('payment_method', ['cash', 'qris'])->nullable()->after('total_amount');
            $table->string('payment_proof', 500)->nullable()->after('payment_method');
            $table->dropColumn(['status', 'payment_status']);
        });

        // Drop customer_id FK separately (PostgreSQL requires constraint drop first)
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'menunggu_bayar_cash',
                'menunggu_konfirmasi_qris',
                'dikonfirmasi',
                'diproses',
                'siap',
                'selesai',
                'ditolak_qris',
            ])->default('pending')->after('cashier_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'customer_phone', 'payment_method', 'payment_proof', 'status']);
            $table->unsignedBigInteger('customer_id')->nullable()->after('id');
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');
            $table->enum('status', ['pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled'])->default('pending');
        });
    }
};
