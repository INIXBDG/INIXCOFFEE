<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        if (Schema::hasTable('biaya_transportasi_drivers')) {
            if (!Schema::hasColumn('biaya_transportasi_drivers', 'id_pengajuan_spj')) {
                Schema::table('biaya_transportasi_drivers', function (Blueprint $table) {
                    $col = $table->bigInteger('id_pengajuan_spj')->nullable();
                    if (Schema::hasColumn('biaya_transportasi_drivers', 'id_pickup_driver')) {
                        $col->after('id_pickup_driver');
                    }
                });
            }

            if (Schema::hasColumn('biaya_transportasi_drivers', 'id_pickup_driver')) {
                DB::statement('ALTER TABLE biaya_transportasi_drivers MODIFY id_pickup_driver INT NULL');
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('biaya_transportasi_drivers')) {
            if (Schema::hasColumn('biaya_transportasi_drivers', 'id_pengajuan_spj')) {
                Schema::table('biaya_transportasi_drivers', function (Blueprint $table) {
                    $table->dropColumn('id_pengajuan_spj');
                });
            }

            if (Schema::hasColumn('biaya_transportasi_drivers', 'id_pickup_driver')) {
                DB::statement('ALTER TABLE biaya_transportasi_drivers MODIFY id_pickup_driver INT NOT NULL');
            }
        }
    }
};
