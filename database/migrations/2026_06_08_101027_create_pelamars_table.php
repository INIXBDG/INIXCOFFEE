<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pelamars', function (Blueprint $table) {
            $table->id();

            // Data Pribadi
            $table->string('nama_lengkap');
            $table->string('email')->unique();
            $table->string('no_telepon', 20)->nullable();
            $table->string('domisili')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('pendidikan_terakhir')->nullable();
            $table->string('jurusan')->nullable();
            $table->string('institusi')->nullable();
            $table->decimal('ipk', 3, 2)->nullable();

            // Data Lamaran
            $table->string('divisi')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('detail_jabatan')->nullable();
            $table->date('tanggal_melamar')->default(now());
            $table->string('sumber_lamaran')->nullable();
            $table->enum('tahap_rekrutmen', ['applied', 'screening', 'interview', 'offer', 'hired', 'rejected'])->default('applied');
            $table->boolean('status_aktif')->default(true);

            // Keahlian & Pengalaman
            $table->json('keahlian')->nullable();
            $table->unsignedTinyInteger('pengalaman_tahun')->nullable();
            $table->unsignedBigInteger('gaji_diharapkan')->nullable();

            // Dokumen
            $table->string('cv_path')->nullable();
            $table->string('portofolio_path')->nullable();
            $table->string('foto_path')->nullable();

            // Interview
            $table->dateTime('jadwal_interview')->nullable();
            $table->enum('metode_interview', ['online', 'offline', 'phone'])->nullable();
            $table->string('link_meeting')->nullable();
            $table->string('lokasi_interview')->nullable();
            $table->string('interviewer')->nullable();
            $table->string('tahap_interview')->nullable();

            // Offer
            $table->unsignedBigInteger('gaji_ditawarkan')->nullable();
            $table->unsignedBigInteger('tunjangan_makan')->nullable();
            $table->unsignedBigInteger('tunjangan_transport')->nullable();
            $table->date('tanggal_mulai_kerja')->nullable();
            $table->enum('status_kepegawaian', ['probation', 'pkwt', 'pkwtt'])->nullable();
            $table->text('benefit_lainnya')->nullable();
            $table->dateTime('tanggal_offer_dikirim')->nullable();
            $table->enum('status_offer', ['pending', 'accepted', 'rejected'])->nullable();

            // Evaluasi
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('catatan_hr')->nullable();
            $table->text('catatan_internal')->nullable();
            $table->string('alasan_penolakan')->nullable();

            // Onboarding
            $table->string('nik_karyawan')->nullable();
            $table->string('atasan_langsung')->nullable();
            $table->json('checklist_onboarding')->nullable();

            // Talent Pool
            $table->boolean('simpan_talent_pool')->default(false);
            $table->text('talent_pool_catatan')->nullable();

            // Relasi
            $table->foreignId('karyawan_id')->nullable()->constrained('karyawans')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            // Index untuk performa query
            $table->index('tahap_rekrutmen');
            $table->index('divisi');
            $table->index('jabatan');
            $table->index('tanggal_melamar');
            $table->index('simpan_talent_pool');
            $table->index('status_aktif');
        });

        Schema::create('pelamar_riwayats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelamar_id')->constrained('pelamars')->cascadeOnDelete();
            $table->string('tahap_dari')->nullable();
            $table->string('tahap_ke')->nullable();
            $table->string('aksi');
            $table->text('keterangan')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->string('oleh')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('pelamar_id');
            $table->index('aksi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelamar_riwayats');
        Schema::dropIfExists('pelamars');
    }
};
