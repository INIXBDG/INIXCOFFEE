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
        Schema::create('detail_expense_hubs', function (Blueprint $table) {
            $table->id();
            $table->integer('id_expenseHub');
            $table->string('nama_pengajuan');
            $table->integer('jumlah');
            $table->decimal('harga_pengajuan');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_expense_hubs');
    }
};
