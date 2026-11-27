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
        if (Schema::hasTable('perbaikan_kendaraans')) {
            Schema::table('perbaikan_kendaraans', function (Blueprint $table) {
                if (!Schema::hasColumn('perbaikan_kendaraans', 'pengajuanbarangs_id')) {
                    $table->foreignId('pengajuanbarangs_id')->nullable()->constrained()->nullOnDelete()->after('bukti');
                }
                // $table->date('tanggal_perbaikan')->nullable()->after('pengajuanbarangs_id');
                if (!Schema::hasColumn('perbaikan_kendaraans', 'invoice')) {
                    $table->string('invoice')->nullable()->after('tanggal_perbaikan');
                }
                if (!Schema::hasColumn('perbaikan_kendaraans', 'deskripsi_perbaikan')) {
                    $table->text('deskripsi_perbaikan')->nullable()->after('invoice');
                }
            });
        }
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
