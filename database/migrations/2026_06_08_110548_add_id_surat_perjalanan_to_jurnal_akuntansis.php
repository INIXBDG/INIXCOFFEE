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
        Schema::table('jurnal_akuntansis', function (Blueprint $table) {
            $table->unsignedBigInteger('id_surat_perjalanan')
                ->nullable()
                ->after('id_perhitungan_net_sales');

            $table->foreign('id_surat_perjalanan')
                ->references('id')
                ->on('surat_perjalanans')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jurnal_akuntansis', function (Blueprint $table) {
            $table->dropForeign(['id_surat_perjalanan']);
            $table->dropColumn('id_surat_perjalanan');
        });
    }
};
