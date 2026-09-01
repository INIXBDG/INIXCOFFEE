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
        // 1. Tabel Subscriptions Master
        if (!Schema::hasTable('subscriptions')) {
            Schema::create('subscriptions', function (Blueprint $table) {
                $table->id();
                $table->string('kode_karyawan')->nullable();
                $table->string('nama_subs');
                $table->string('merk')->nullable();
                $table->enum('tipe', ['one-time', 'subscription'])->default('subscription');
                $table->text('desc')->nullable();
                $table->string('subs_url')->nullable();
                $table->string('access_code')->nullable();
                $table->integer('duration_minutes')->nullable();
                $table->string('mata_uang')->default('Rupiah');
                $table->decimal('harga', 15, 2)->default(0);
                $table->decimal('kurs', 15, 2)->default(1);
                $table->decimal('harga_rupiah', 15, 2)->default(0);
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->enum('status', ['active', 'pending', 'expired'])->default('pending');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Tabel Pivot Subscription - Materi
        if (!Schema::hasTable('subscription_materi')) {
            Schema::create('subscription_materi', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('subscription_id');
                $table->unsignedBigInteger('materi_id');
                $table->timestamps();

                $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
                $table->foreign('materi_id')->references('id')->on('materis')->onDelete('cascade');
            });
        }

        // 3. Tabel Pengajuan Subs
        if (!Schema::hasTable('pengajuan_subs')) {
            Schema::create('pengajuan_subs', function (Blueprint $table) {
                $table->id();
                $table->string('kode_karyawan');
                $table->unsignedBigInteger('id_subs')->nullable();
                $table->unsignedBigInteger('id_rkm')->nullable();
                $table->enum('jenis_transaksi', ['baru', 'existing', 'pembaharuan'])->default('baru');
                $table->unsignedBigInteger('id_tracking')->nullable();
                $table->string('invoice')->nullable();
                $table->json('subs_snapshot')->nullable();
                $table->timestamps();
            });
        }

        // 4. Tabel Tracking Pengajuan Subs
        if (!Schema::hasTable('tracking_pengajuan_subs')) {
            Schema::create('tracking_pengajuan_subs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_pengajuan_subs');
                $table->text('tracking');
                $table->timestamp('tanggal')->useCurrent();
                $table->timestamps();

                $table->foreign('id_pengajuan_subs')->references('id')->on('pengajuan_subs')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_pengajuan_subs');
        Schema::dropIfExists('pengajuan_subs');
        Schema::dropIfExists('subscription_materi');
        Schema::dropIfExists('subscriptions');
    }
};
