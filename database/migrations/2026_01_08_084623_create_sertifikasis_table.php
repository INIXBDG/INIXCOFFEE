<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sertifikasis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('nama_sertifikat');
            // $table->string('penyedia');
            $table->date('tanggal_ujian')->nullable();
            $table->date('tanggal_berlaku_dari')->nullable();
            $table->date('tanggal_berlaku_sampai')->nullable();
            $table->text('keterangan')->nullable();
            $table->decimal('harga', 15, 2);
            $table->string('vendor');
            $table->string('status_approval')->default('pending');
            $table->unsignedBigInteger('id_pengajuan_barang')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable()->index();
            $table->timestamp('approved_at')->nullable();
            $table->string('bukti_sertifikasi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sertifikasis');
    }
};
