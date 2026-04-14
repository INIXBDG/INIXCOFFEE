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
        // 1. Modifikasi Tabel Labs (Master Data Utama)
        Schema::table('labs', function (Blueprint $table) {
            // Menambahkan kolom Tipe untuk pembeda
            $table->enum('tipe', ['one-time', 'subscription'])
                  ->default('one-time')
                  ->after('nama_labs')
                  ->comment('One-time: Sekali beli. Subscription: Berlangganan/Bisa dipakai ulang.');

            // Menambahkan kolom 'merk' (sebelumnya hanya ada di subs)
            $table->string('merk')->nullable()->after('nama_labs');

            // Menambahkan status aktif/tidak (untuk keperluan reuse)
            $table->boolean('is_active')->default(true)->after('status');
        });

        // 2. Bersihkan Tabel Pengajuan (Hapus ketergantungan ke Subs)
        Schema::table('pengajuan_lab_subs', function (Blueprint $table) {
            // Hapus Foreign Key & Kolom ID Subs jika ada
            // Pastikan cek nama foreign key di database Anda, biasanya: pengajuan_lab_subs_id_subs_foreign
            // $table->dropForeign(['id_subs']);
            $table->dropColumn('id_subs');

            // Hapus snapshot subs lama
            $table->dropColumn('subs_snapshot');

            // Tambah penanda apakah ini pengadaan baru atau pakai aset lama
            $table->enum('jenis_transaksi', ['baru', 'existing', 'pembaharuan'])
                  ->default('baru')
                  ->after('id_labs')
                  ->comment('Baru: Beli dari vendor. Existing: Pakai lab langganan kantor. Pembaharuan: Pembaruan lab yang sudah ada.');
        });

        // 3. Hapus Tabel Subscriptions (Karena sudah tidak dipakai)
        Schema::dropIfExists('subscriptions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan struktur lama (Opsional, sesuaikan jika perlu rollback)
        Schema::table('labs', function (Blueprint $table) {
            $table->dropColumn(['tipe', 'merk', 'is_active']);
        });

        Schema::table('pengajuan_lab_subs', function (Blueprint $table) {
            $table->unsignedBigInteger('id_subs')->nullable();
            $table->json('subs_snapshot')->nullable();
            $table->dropColumn('jenis_transaksi');
        });
    }
};
