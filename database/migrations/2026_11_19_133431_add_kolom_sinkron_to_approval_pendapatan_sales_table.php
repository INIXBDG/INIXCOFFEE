<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('approval_pendapatan_sales')) {
            Schema::table('approval_pendapatan_sales', function (Blueprint $table) {
                if (!Schema::hasColumn('approval_pendapatan_sales', 'total_penjualan_kotor')) {
                    $table->decimal('total_penjualan_kotor', 20, 2)->nullable()->after('pax');
                }
                if (!Schema::hasColumn('approval_pendapatan_sales', 'catatan_sinkron')) {
                    $table->json('catatan_sinkron')->nullable()->after('total_penjualan_kotor');
                }
            });

            if (
                Schema::hasColumn('approval_pendapatan_sales', 'total_penjualan_kotor')
                && Schema::hasColumn('approval_pendapatan_sales', 'harga_net')
                && Schema::hasColumn('approval_pendapatan_sales', 'pax')
            ) {
                DB::statement('UPDATE approval_pendapatan_sales SET total_penjualan_kotor = COALESCE(harga_net, 0) * COALESCE(pax, 0) WHERE total_penjualan_kotor IS NULL');
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('approval_pendapatan_sales')) {
            $cols = array_values(array_filter(
                ['total_penjualan_kotor', 'catatan_sinkron'],
                fn (string $col) => Schema::hasColumn('approval_pendapatan_sales', $col)
            ));
            if (!empty($cols)) {
                Schema::table('approval_pendapatan_sales', function (Blueprint $table) use ($cols) {
                    $table->dropColumn($cols);
                });
            }
        }
    }
};