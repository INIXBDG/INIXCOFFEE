<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lsps', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('jenis', ['LSP 1', 'LSP 3', 'LPK']);
            $table->string('bidang')->nullable();
            $table->string('based')->nullable();
            $table->string('nomor_pks_mou')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_berakhir')->nullable();
            $table->string('pic')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->enum('status', ['Aktif', 'Nonaktif', 'Berakhir'])->default('Aktif');
            $table->unsignedInteger('karyawan')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lsps');
    }
};
