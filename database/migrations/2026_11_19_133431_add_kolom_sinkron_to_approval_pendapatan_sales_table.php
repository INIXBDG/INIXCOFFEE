<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approval_pendapatan_sales', function (Blueprint $table) {
            $table->decimal('total_penjualan_kotor', 20, 2)->nullable()->after('pax');
            $table->json('catatan_sinkron')->nullable()->after('total_penjualan_kotor');
        });

        DB::statement('UPDATE approval_pendapatan_sales SET total_penjualan_kotor = COALESCE(harga_net, 0) * COALESCE(pax, 0) WHERE total_penjualan_kotor IS NULL');
    }

    public function down(): void
    {
        Schema::table('approval_pendapatan_sales', function (Blueprint $table) {
            $table->dropColumn(['total_penjualan_kotor', 'catatan_sinkron']);
        });
    }
};