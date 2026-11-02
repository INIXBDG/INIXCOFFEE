<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pph21_laporans', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('bulan');
            $table->unsignedSmallInteger('tahun');

            $table->timestamp('lapor_kortex_at')->nullable();
            $table->foreignId('lapor_kortex_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('kode_billing_nomor', 100)->nullable();
            $table->string('kode_billing_file')->nullable();
            $table->timestamp('kode_billing_at')->nullable();
            $table->foreignId('kode_billing_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('bukti_bayar_file')->nullable();
            $table->timestamp('bukti_bayar_at')->nullable();
            $table->foreignId('bukti_bayar_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->unique(['bulan', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pph21_laporans');
    }
};