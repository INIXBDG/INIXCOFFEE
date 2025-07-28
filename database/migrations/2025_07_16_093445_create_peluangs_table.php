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
        Schema::create('peluangs', function (Blueprint $table) {
            $table->id();
            $table->integer('id_contact');
            $table->text('id_sales');
            $table->text('judul');
            $table->text('deskripsi');
            $table->decimal('jumlah', 15 ,2); // Ekspetasi pendapatan dari peluang penjualan
            $table->enum('tahap', ['hitam', 'biru', 'merah']);
            $table->date('tanggal_tutup_diharapkan'); // Ekspetasi waktu selesai
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peluangs');
    }
};
