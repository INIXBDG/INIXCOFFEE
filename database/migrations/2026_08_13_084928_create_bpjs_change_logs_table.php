<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bpjs_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perhitungan_id')->constrained('perhitungan_tunjangan_h_r_s')->onDelete('cascade');
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
            $table->unsignedTinyInteger('bulan');
            $table->unsignedSmallInteger('tahun');
            $table->string('field_name');
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['perhitungan_id', 'karyawan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bpjs_change_logs');
    }
};