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
        Schema::table('perbaikan_kendaraans', function (Blueprint $table) {
            $table->foreignId('pengajuanbarangs_id')->nullable()->constrained()->nullOnDelete()->after('bukti');
            $table->date('tanggal_perbaikan')->nullable()->after('pengajuanbarangs_id');
            $table->string('invoice')->nullable()->after('tanggal_perbaikan');
            $table->text('deskripsi_perbaikan')->nullable()->after('invoice');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perbaikan_kendaraans', function (Blueprint $table) {
            //
        });
    }
};
