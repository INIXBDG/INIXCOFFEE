<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kwitansis', function (Blueprint $table) {
            $table->foreignId('id_rkm')
                  ->after('id') // letakkan setelah kolom id
                  ->constrained('r_k_m_s') // asumsi nama tabel RKM = 'rkms'
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('kwitansis', function (Blueprint $table) {
            $table->dropForeign(['id_rkm']);
            $table->dropColumn('id_rkm');
        });
    }
};
