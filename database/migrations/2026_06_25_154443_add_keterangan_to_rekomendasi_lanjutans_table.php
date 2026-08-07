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
        if (!Schema::hasTable('rekomendasi_lanjutans')) {
            return;
        }

        if (Schema::hasColumn('rekomendasi_lanjutans', 'keterangan')) {
            return;
        }

        $hasIdMateri = Schema::hasColumn('rekomendasi_lanjutans', 'id_materi');

        Schema::table('rekomendasi_lanjutans', function (Blueprint $table) use ($hasIdMateri) {
            if ($hasIdMateri) {
                $table->text('keterangan')->nullable()->after('id_materi');
            } else {
                $table->text('keterangan')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('rekomendasi_lanjutans')) {
            return;
        }

        if (!Schema::hasColumn('rekomendasi_lanjutans', 'keterangan')) {
            return;
        }

        Schema::table('rekomendasi_lanjutans', function (Blueprint $table) {
            $table->dropColumn('keterangan');
        });
    }
};