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
        Schema::create('analytics_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('job_name');
            $table->enum('frequency', ['daily', 'weekly', 'monthly']);
            $table->dateTime('last_run')->nullable();
            $table->dateTime('next_run');
            $table->enum('status', ['active', 'paused', 'failed'])->default('active');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_schedules');
    }
};
