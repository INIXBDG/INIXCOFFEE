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
        Schema::create('thr_periods', function (Blueprint $table) {
            $table->id();
            $table->string('nama_periode');
            $table->integer('tahun')->index();
            $table->date('tanggal_jatuh_tempo');
            $table->integer('min_masa_kerja_bulan')->default(1);
            $table->integer('full_masa_kerja_bulan')->default(12);
            $table->string('status', 20)->default('draft');
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('thr_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thr_period_id')->constrained('thr_periods')->onDelete('cascade');
            $table->unsignedBigInteger('karyawan_id')->index();
            $table->string('nama_lengkap');
            $table->string('jabatan')->nullable();
            $table->string('status_karyawan')->nullable();
            $table->string('rekening_maybank')->nullable();
            $table->date('tanggal_join')->nullable();
            $table->integer('masa_kerja_tahun')->default(0);
            $table->integer('masa_kerja_bulan')->default(0);
            $table->integer('masa_kerja_hari')->default(0);
            $table->decimal('total_masa_kerja_bulan', 8, 2)->default(0);
            $table->string('masa_kerja_teks')->nullable();
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('nilai_thr_sistem', 15, 2)->default(0);
            $table->decimal('nilai_thr_final', 15, 2)->default(0);
            $table->boolean('is_manual')->default(false);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['thr_period_id', 'karyawan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thr_items');
        Schema::dropIfExists('thr_periods');
    }
};
