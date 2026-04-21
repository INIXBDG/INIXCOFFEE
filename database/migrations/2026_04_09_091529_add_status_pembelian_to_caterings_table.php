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
        Schema::table('caterings', function (Blueprint $table) {
            $table->string('status_pembelian')->nullable();
            $table->string('tanggal_pembelian')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('caterings', function (Blueprint $table) {
            //
        });
    }
};
