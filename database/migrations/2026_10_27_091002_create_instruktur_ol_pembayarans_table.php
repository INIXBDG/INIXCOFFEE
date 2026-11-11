<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instruktur_ol_pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instruktur_ol_id')->constrained('instruktur_ols')->cascadeOnDelete();
            $table->unsignedBigInteger('rkm_id')->nullable()->index();
            $table->string('judul');
            $table->unsignedBigInteger('harga');               
            $table->boolean('kali_pax')->default(false);    
            $table->unsignedInteger('pax')->nullable();         
            $table->unsignedBigInteger('total');                
            $table->date('tanggal_bayar');
            $table->string('keterangan', 500)->nullable();
            $table->string('bukti_path')->nullable();
            $table->string('bukti_nama_asli')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instruktur_ol_pembayarans');
    }
};