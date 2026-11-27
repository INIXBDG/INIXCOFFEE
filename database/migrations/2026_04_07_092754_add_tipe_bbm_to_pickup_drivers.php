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
        if (Schema::hasTable('pickup_drivers')) {
            Schema::table('pickup_drivers', function (Blueprint $table) {
                if (!Schema::hasColumn('pickup_drivers', 'KM_awal')) {
                    $table->string('KM_awal')->nullable();
                }
                if (!Schema::hasColumn('pickup_drivers', 'KM_akhir')) {
                    $table->string('KM_akhir')->nullable();
                }
                if (!Schema::hasColumn('pickup_drivers', 'tipe_perjalanan')) {
                    $table->string('tipe_perjalanan')->nullable();
                }
            });
        }

        if (Schema::hasTable('perbaikan_kendaraans')) {
            Schema::table('perbaikan_kendaraans', function (Blueprint $table) {
                if (!Schema::hasColumn('perbaikan_kendaraans', 'tanggal_perbaikan')) {
                    $table->date('tanggal_perbaikan')->nullable();
                }
                if (!Schema::hasColumn('perbaikan_kendaraans', 'selesai_perbaikan')) {
                    $table->string('selesai_perbaikan')->nullable();
                }
                if (!Schema::hasColumn('perbaikan_kendaraans', 'detail_perbaikan')) {
                    $table->text('detail_perbaikan')->nullable();
                }
                if (!Schema::hasColumn('perbaikan_kendaraans', 'document')) {
                    $table->string('document')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pickup_drivers', function (Blueprint $table) {
            //
        });
    }
};
