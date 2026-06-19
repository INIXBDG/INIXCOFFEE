<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('stock_opnames', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_opnames', 'stock_masuk')) {
                $table->integer('stock_masuk')->default(0)->after('stock_awal');
            }
            if (!Schema::hasColumn('stock_opnames', 'stock_keluar')) {
                $table->integer('stock_keluar')->default(0)->after('stock_masuk');
            }
        });

        Schema::table('stock_opname_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_opname_logs', 'jenis_transaksi')) {
                $table->string('jenis_transaksi')->default('masuk')->after('tanggal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->dropColumn(['stock_masuk', 'stock_keluar']);
        });

        Schema::table('stock_opname_logs', function (Blueprint $table) {
            $table->dropColumn('jenis_transaksi');
        });
    }
};