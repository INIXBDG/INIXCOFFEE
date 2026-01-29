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
            
            // Kolom ID tanpa constraint foreign key
            $table->unsignedBigInteger('user_id')->index(); 
            
            // Data Sertifikat
            $table->string('nama_sertifikat');
            $table->string('penyedia');
            $table->date('tanggal_ujian');
            $table->date('tanggal_berlaku_dari');
            $table->date('tanggal_berlaku_sampai')->nullable();
            $table->decimal('harga', 15, 2);
            $table->string('vendor');

            // Data Approval (Education Manager)
            $table->string('status_approval')->default('pending'); // pending, approved, rejected
            $table->unsignedBigInteger('approved_by')->nullable()->index(); // ID User Education Manager
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sertifikasis');
    }
};