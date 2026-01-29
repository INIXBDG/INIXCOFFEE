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
        Schema::create('kondisi_kendaraans', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->enum('jenis_kendaraan', ['Innova', 'H1']);

            // Kondisi Fisik
            $table->boolean('fisik_baik');
            $table->boolean('bersih');
            $table->boolean('wiper_baik');
            $table->boolean('klakson_baik');
            $table->boolean('lampu_baik');
            $table->boolean('tekanan_ban_baik');
            $table->boolean('ban_baik');
            $table->boolean('ban_cadangan_lengkap');
            $table->boolean('setir_pedal_baik');
            $table->text('catatan_kondisi')->nullable();

            // Mesin
            $table->boolean('oli_baik');
            $table->boolean('radiator_baik');
            $table->boolean('air_wiper_baik');
            $table->boolean('minyak_rem_baik');
            $table->boolean('aki_baik');
            $table->text('catatan_mesin')->nullable();

            // Dokumen & Perlengkapan
            $table->boolean('dokumen_lengkap');
            $table->boolean('jas_hujan_ada');
            $table->boolean('pengharum_ada');
            $table->text('catatan_perlengkapan')->nullable();

            // Fasilitas
            $table->boolean('ac_baik');
            $table->boolean('audio_baik');
            $table->boolean('charger_ada');
            $table->boolean('air_minum_ada');
            $table->boolean('tisu_ada');
            $table->boolean('hand_sanitizer_ada');
            $table->text('catatan_fasilitas')->nullable();

            // BBM & Tol
            $table->boolean('bbm_cukup');
            $table->boolean('etol_aktif');
            $table->date('tanggal_pemeriksaan');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kondisi_kendaraans');
    }
};
