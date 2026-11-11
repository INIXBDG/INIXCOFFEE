<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Target & bobot per tahun
        Schema::create('analysis_finance_settings', function (Blueprint $t) {
            $t->id();
            $t->unsignedSmallInteger('tahun')->unique();
            $t->decimal('target_tagihan', 6, 2)->default(95);
            $t->decimal('target_pencairan', 6, 2)->default(100);
            $t->unsignedSmallInteger('target_ide')->default(2);
            $t->decimal('bobot_tagihan', 5, 2)->default(40);
            $t->decimal('bobot_pencairan', 5, 2)->default(35);
            $t->decimal('bobot_ide', 5, 2)->default(25);
            $t->timestamps();
        });

        // Internal Process: override manual per bulan (null = pakai data tagihan)
        Schema::create('analysis_finance_internal_processes', function (Blueprint $t) {
            $t->id();
            $t->unsignedSmallInteger('tahun');
            $t->unsignedTinyInteger('bulan');
            $t->unsignedInteger('pengajuan_barang')->nullable();
            $t->unsignedInteger('bukti_kas')->nullable();
            $t->text('catatan')->nullable();
            $t->timestamps();
            $t->unique(['tahun', 'bulan']);
        });

        // Learning & Growth: daftar inisiatif
        Schema::create('analysis_finance_initiatives', function (Blueprint $t) {
            $t->id();
            $t->unsignedSmallInteger('tahun')->index();
            $t->unsignedSmallInteger('no')->default(1);
            $t->string('initiative');
            $t->text('description')->nullable();
            $t->unsignedTinyInteger('analysis_target')->default(1); // triwulan 1-4
            $t->string('estimated_impact')->nullable();
            $t->boolean('is_implemented')->default(false);
            $t->timestamps();
        });

        // Rekap triwulan: override manual (null = hitung otomatis)
        Schema::create('analysis_finance_quarterly_reports', function (Blueprint $t) {
            $t->id();
            $t->unsignedSmallInteger('tahun');
            $t->unsignedTinyInteger('quarter');
            $t->decimal('nilai_persen', 7, 2)->nullable();
            $t->decimal('skor_kinerja', 7, 2)->nullable();
            $t->string('status', 30)->nullable();
            $t->text('catatan')->nullable();
            $t->text('keterangan')->nullable();
            $t->timestamps();
            $t->unique(['tahun', 'quarter']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_finance_quarterly_reports');
        Schema::dropIfExists('analysis_finance_initiatives');
        Schema::dropIfExists('analysis_finance_internal_processes');
        Schema::dropIfExists('analysis_finance_settings');
    }
};