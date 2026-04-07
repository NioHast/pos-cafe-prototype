<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Migrate existing data to new 3-status values
        DB::statement("UPDATE orders SET status = 'pending'  WHERE status IN ('menunggu_bayar_cash', 'menunggu_konfirmasi_qris', 'ditolak_qris')");
        DB::statement("UPDATE orders SET status = 'diproses' WHERE status IN ('dikonfirmasi', 'siap')");
        // 'pending', 'diproses', 'selesai' stay unchanged

        // 2. Replace the status column with the new 3-value enum
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'diproses', 'selesai'])
                  ->default('pending')
                  ->after('cashier_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('status');
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
};
