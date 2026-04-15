<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_ingredient_usages', function (Blueprint $table) {
            $table->id();
            $table->date('usage_date');
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->string('ingredient_name');
            $table->string('unit', 20);
            $table->decimal('jumlah_digunakan', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['usage_date', 'ingredient_id']);
            $table->index('usage_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_ingredient_usages');
    }
};
