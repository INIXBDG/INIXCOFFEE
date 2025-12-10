<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perhitungan_net_sales', function (Blueprint $table) {

            $table->decimal('transportasi', 15, 2)->nullable()->change();
            $table->decimal('akomodasi_peserta', 15, 2)->nullable();
            $table->decimal('penginapan_meeting_room', 15, 2)->nullable();
            $table->decimal('akomodasi_sales_instruktur', 15, 2)->nullable();
            $table->decimal('reimburse_transport_sales_instruktur', 15, 2)->nullable();
            $table->decimal('sewa_laptop', 15, 2)->nullable();
            $table->decimal('fresh_money', 15, 2)->nullable()->change();
            $table->decimal('diskon', 15, 2)->nullable()->change();
            $table->decimal('entertaint', 15, 2)->nullable()->change();

            $table->text('jenis_transportasi')->nullable()->after('transportasi'); // Sewa Mobil / Tiket Pesawat
            $table->text('deskripsi_entertaint')->nullable()->after('entertaint');

        });
    }


    public function down(): void
    {
        Schema::table('perhitungan_net_sales', function (Blueprint $table) {

            $table->dropColumn([
                'jenis_transportasi',
                'akomodasi_peserta',
                'penginapan_meeting_room',
                'akomodasi_sales_instruktur',
                'reimburse_transport_sales_instruktur',
                'sewa_laptop',
                'deskripsi_entertaint',
            ]);

            $table->decimal('transportasi', 15)->nullable()->change();
            $table->decimal('fresh_money', 15)->nullable()->change();
            $table->decimal('diskon', 15)->nullable()->change();
            $table->decimal('entertaint', 15)->nullable()->change();
        });
    }
};
