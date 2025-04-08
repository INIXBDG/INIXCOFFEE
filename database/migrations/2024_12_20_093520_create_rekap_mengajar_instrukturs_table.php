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
        Schema::create('rekap_mengajar_instrukturs', function (Blueprint $table) {
            $table->id();
            $table->string('id_rkm');  
            $table->string('id_instruktur');  
            $table->string('feedback');  
            $table->string('pax');  
            $table->string('level');  
            $table->string('durasi');  
            $table->date('tanggal_awal');
            $table->date('tanggal_akhir');
            $table->string('bulan');  
            $table->string('tahun');  
            $table->string('poin_durasi')->nullable();  
            $table->string('poin_pax')->nullable();  
            $table->string('tunjangan_feedback')->nullable(); 
            $table->string('total_tunjangan')->nullable();  
            $table->string('status')->nullable();   
            $table->text('keterangan')->nullable();  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_mengajar_instrukturs');
    }
};
