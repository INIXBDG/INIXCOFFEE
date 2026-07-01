<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perhitungan_tunjangan_h_r_s', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');

            $table->unsignedTinyInteger('bulan');
            $table->unsignedSmallInteger('tahun');
            $table->string('periode')->virtualAs("CONCAT(LPAD(bulan, 2, '0'), '/', tahun)");

            $table->bigInteger('gaji_pokok')->default(0);
            $table->bigInteger('salary_bpjstk')->default(0);
            $table->bigInteger('umk_bandung')->default(2100000);

            $table->json('tunjangan_detail')->nullable();
            $table->bigInteger('total_tunjangan')->default(0);

            $table->bigInteger('jht_perusahaan')->default(0);
            $table->bigInteger('jkm_perusahaan')->default(0);
            $table->bigInteger('jkk_perusahaan')->default(0);
            $table->bigInteger('jp_perusahaan')->default(0);
            $table->bigInteger('total_bpjstk_perusahaan')->default(0);

            $table->bigInteger('jht_karyawan')->default(0);
            $table->bigInteger('jp_karyawan')->default(0);
            $table->bigInteger('total_bpjstk_karyawan')->default(0);

            $table->bigInteger('bpjs_kes_perusahaan')->default(0);
            $table->bigInteger('bpjs_kes_karyawan')->default(0);

            $table->bigInteger('total_bpjs_perusahaan')->default(0);
            $table->bigInteger('total_bpjs_karyawan')->default(0);

            $table->bigInteger('potongan_pph21')->default(0);
            $table->bigInteger('potongan_kasbon')->default(0);
            $table->bigInteger('potongan_denda')->default(0);
            $table->bigInteger('potongan_lain')->default(0);
            $table->bigInteger('total_potongan_lain')->default(0);

            $table->bigInteger('thp_kotor')->default(0);
            $table->bigInteger('thp_bersih')->default(0);
            $table->bigInteger('total_biaya_perusahaan')->default(0);

            $table->enum('status', ['draft', 'calculated', 'approved', 'paid', 'cancelled'])->default('draft');
            $table->text('catatan')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['karyawan_id', 'bulan', 'tahun'], 'idx_periode_karyawan');
            $table->index(['status', 'tahun', 'bulan'], 'idx_status_periode');

            $table->unique(['karyawan_id', 'bulan', 'tahun'], 'unique_periode_karyawan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perhitungan_tunjangan_h_r_s');
    }
};