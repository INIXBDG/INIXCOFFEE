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
        Schema::create('analysis_annual_reports', function (Blueprint $table) {
            $table->id();
            $table->year('year')->unique();
            $table->text('description')->nullable();
            $table->json('file_paths')->nullable(); // Untuk menyimpan banyak file
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_annual_reports');
    }
};
