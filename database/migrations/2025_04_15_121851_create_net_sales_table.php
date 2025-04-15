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
        Schema::create('net_sales', function (Blueprint $table) {
            $table->id();
            $table->integer('id_rkm');
            $table->decimal('sebelumNetSales', 15)->nullable();
            $table->decimal('pajak', 15)->nullable();
            $table->decimal('cashback', 15)->nullable();
            $table->decimal('biaya_akomodasi', 15)->nullable();
            $table->decimal('entertaint', 15)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('net_sales');
    }
};
