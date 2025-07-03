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
        Schema::create('inventaris', function (Blueprint $table) {
            $table->id();
            $table->string('idbarang')->unique(); // Nomor inventaris otomatis, e.g., INV-0001
            $table->string('name'); // Nama barang, e.g., "Laptop"
            $table->string('kodebarang'); // diambil dari nama barang "LP"
            $table->text('merk_kode_seri_hardware')->nullable(); // Merk / Kode Seri / Kode Hardware
            $table->integer('qty')->default(1); // QTY barang
            $table->string('satuan')->default('unit'); // satuan nya e.g., 'Unit / Box'
            $table->enum('type', ['E', 'NE']); // Tipe barang
            $table->decimal('harga_beli', 15, 2); // Harga beli
            $table->decimal('total_harga', 15, 2); // Total harga keseluruhan
            $table->date('waktu_pembelian'); // Tanggal beli
            $table->text('pengguna')->nullable(); // Karyawan yang menggunakan
            $table->text('ruangan')->nullable(); // Ruangan lokasi barang
            $table->enum('kondisi', ['baik', 'rusak', 'kurang layak'])->default('baik'); // Kondisi barang
            $table->text('deskripsi')->nullable(); // Deskripsi tambahan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventaris');
    }
};
