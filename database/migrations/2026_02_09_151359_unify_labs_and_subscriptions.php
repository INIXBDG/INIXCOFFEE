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
        if (Schema::hasTable('labs')) {
            Schema::table('labs', function (Blueprint $table) {
                if (!Schema::hasColumn('labs', 'tipe')) {
                    // Menambahkan kolom Tipe untuk pembeda
                    $col = $table->enum('tipe', ['one-time', 'subscription'])
                        ->default('one-time')
                        ->comment('One-time: Sekali beli. Subscription: Berlangganan/Bisa dipakai ulang.');
                    if (Schema::hasColumn('labs', 'nama_labs')) {
                        $col->after('nama_labs');
                    }
                }

                if (!Schema::hasColumn('labs', 'merk')) {
                    // Menambahkan kolom 'merk' (sebelumnya hanya ada di subs)
                    $col = $table->string('merk')->nullable();
                    if (Schema::hasColumn('labs', 'nama_labs')) {
                        $col->after('nama_labs');
                    }
                }

                if (!Schema::hasColumn('labs', 'is_active')) {
                    // Menambahkan status aktif/tidak (untuk keperluan reuse)
                    $col = $table->boolean('is_active')->default(true);
                    if (Schema::hasColumn('labs', 'status')) {
                        $col->after('status');
                    }
                }
            });
        }

        // 2. Bersihkan Tabel Pengajuan (Hapus ketergantungan ke Subs)
        if (Schema::hasTable('pengajuan_lab_subs')) {
            Schema::table('pengajuan_lab_subs', function (Blueprint $table) {
                if (Schema::hasColumn('pengajuan_lab_subs', 'id_subs')) {
                    $table->dropColumn('id_subs');
                }

                if (Schema::hasColumn('pengajuan_lab_subs', 'subs_snapshot')) {
                    $table->dropColumn('subs_snapshot');
                }

                if (!Schema::hasColumn('pengajuan_lab_subs', 'jenis_transaksi')) {
                    // Tambah penanda apakah ini pengadaan baru atau pakai aset lama
                    $col = $table->enum('jenis_transaksi', ['baru', 'existing', 'pembaharuan'])
                        ->default('baru')
                        ->comment('Baru: Beli dari vendor. Existing: Pakai lab langganan kantor. Pembaharuan: Pembaruan lab yang sudah ada.');
                    if (Schema::hasColumn('pengajuan_lab_subs', 'id_labs')) {
                        $col->after('id_labs');
                    }
                }
            });
        }

        // 3. Hapus Tabel Subscriptions lama hanya jika belum menggunakan skema baru (kolom 'tipe')
        if (Schema::hasTable('subscriptions') && !Schema::hasColumn('subscriptions', 'tipe')) {
            Schema::dropIfExists('subscriptions');
        }
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
