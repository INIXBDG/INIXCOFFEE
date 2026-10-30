<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bonus_tahunans')) {
            Schema::create('bonus_tahunans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
                $table->integer('tahun')->index();
                $table->integer('bulan')->default(12)->index();
                $table->string('jabatan')->nullable();
                $table->decimal('basic_salary', 15, 2)->default(0);
                $table->string('rekening_maybank')->nullable();
                $table->string('status_karyawan')->nullable();
                $table->date('tanggal_bergabung')->nullable();
                $table->integer('masa_kerja_bulan')->default(0);
                $table->string('masa_kerja_teks')->nullable();
                $table->string('skema_bonus', 30)->default('standar'); // 'standar', '2x', '4x', 'custom'
                $table->decimal('multiplier', 8, 2)->default(1.00);
                $table->decimal('nominal_bonus', 15, 2)->default(0);
                $table->text('catatan')->nullable();
                $table->boolean('is_locked')->default(false);
                $table->timestamps();

                $table->unique(['karyawan_id', 'tahun', 'bulan'], 'unique_bonus_karyawan_periode');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bonus_tahunans');
    }
};
