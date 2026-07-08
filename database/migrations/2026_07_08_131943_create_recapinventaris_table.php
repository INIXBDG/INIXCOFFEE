<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('recap_inventaris', function (Blueprint $table) {
            $table->id();
            $table->string('idbarang')->nullable();
            $table->string('name'); 
            $table->string('kategori'); 
            $table->integer('qty')->default(1);
            $table->decimal('total_harga', 15, 2); 
            $table->date('waktu_pembelian'); 
            $table->string('ruangan')->nullable(); 
            $table->string('no_kk')->nullable(); 
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recap_inventaris');
    }
};
