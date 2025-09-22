<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kwitansis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->unique(); // 1 kwitansi hanya untuk 1 invoice
            $table->date('tanggal_cetak')->nullable();
            $table->string('dicetak_oleh')->nullable();
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kwitansis');
    }
};

