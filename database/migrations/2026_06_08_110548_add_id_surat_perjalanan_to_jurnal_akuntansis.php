<?php

use App\Support\MigrationHelper;
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
        if (Schema::hasTable('jurnal_akuntansis')) {
            if (!Schema::hasColumn('jurnal_akuntansis', 'id_surat_perjalanan')) {
                Schema::table('jurnal_akuntansis', function (Blueprint $table) {
                    $col = $table->unsignedBigInteger('id_surat_perjalanan')->nullable();
                    if (Schema::hasColumn('jurnal_akuntansis', 'id_perhitungan_net_sales')) {
                        $col->after('id_perhitungan_net_sales');
                    }
                });
            }

            if (
                Schema::hasColumn('jurnal_akuntansis', 'id_surat_perjalanan')
                && Schema::hasTable('surat_perjalanans')
                && !MigrationHelper::foreignKeyExistsOnColumn('jurnal_akuntansis', 'id_surat_perjalanan', 'surat_perjalanans')
            ) {
                Schema::table('jurnal_akuntansis', function (Blueprint $table) {
                    $table->foreign('id_surat_perjalanan')
                        ->references('id')
                        ->on('surat_perjalanans')
                        ->onDelete('cascade');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('jurnal_akuntansis')) {
            MigrationHelper::dropForeignIfExists('jurnal_akuntansis', ['id_surat_perjalanan']);

            if (Schema::hasColumn('jurnal_akuntansis', 'id_surat_perjalanan')) {
                Schema::table('jurnal_akuntansis', function (Blueprint $table) {
                    $table->dropColumn('id_surat_perjalanan');
                });
            }
        }
    }
};
