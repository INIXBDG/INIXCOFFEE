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
        Schema::create('outstandings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_rkm');
            $table->decimal('net_sales', 15, 2)->nullable();
            $table->enum('status_pembayaran', ['0', '1']);//0 belum 1 sudah
            $table->date('due_date');
            $table->date('tanggal_bayar')->nullable();
            $table->string('pic')->nullable();
            $table->string('sales_key')->nullable();
            $table->string('no_regist')->nullable();
            $table->string('no_invoice')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outstandings');
    }
};
