<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelatihans', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('user_id')->index();
            
            $table->string('nama_pelatihan');
            $table->string('penyedia');
            
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            
            $table->text('keterangan')->nullable();
            $table->decimal('harga', 15, 2);

            $table->string('status_approval')->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable()->index();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelatihans');
    }
};