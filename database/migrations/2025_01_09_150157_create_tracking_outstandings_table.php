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
        Schema::create('tracking_outstandings', function (Blueprint $table) {
            $table->id();
            $table->string('id_outstanding')->nullable();  
            $table->string('invoice')->nullable();  
            $table->string('faktur_pajak')->nullable();  
            $table->string('dokumen_tambahan')->nullable();  
            $table->string('konfir_cs')->nullable();  
            $table->string('tracking_dokumen')->nullable();
            $table->string('no_resi')->nullable();
            $table->string('konfir_pic')->nullable();
            $table->string('pembayaran')->nullable();
            $table->string('status_resi')->nullable();
            $table->string('status_pic')->nullable();
            $table->timestamps();
        });
    }
    //CATATAN : JIKA MENGUPDATE UBAH DI CONTROLLER OUTSTANDINGNYA JUGA AGAR SELARAS

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_outstandings');
    }
};
