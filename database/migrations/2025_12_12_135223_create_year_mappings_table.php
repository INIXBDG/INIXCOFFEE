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
        Schema::create('year_mappings', function (Blueprint $table) {
            $table->id();
            $table->year('year')->index();              // Tahun (2025)
            $table->tinyInteger('quarter')->index();    // Triwulan (1-4)
            $table->tinyInteger('month');               // Bulan (1-12)
            $table->string('theme');                    // Tema Fixed
            $table->date('planned_date');               // Tanggal D-Day
            $table->integer('duration_minutes')->default(120);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('year_mappings');
    }
};
