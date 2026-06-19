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
        Schema::create('log_gajis', function (Blueprint $table) {
            $table->id();
            $table->integer('id_karyawan');
            $table->string('gaji')->nullable();
            $table->year('tahun');
            $table->tinyInteger('bulan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_gajis');
    }
};
