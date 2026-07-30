<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('biaya_transportasi_drivers', function (Blueprint $table) {
            $table->bigInteger('id_pengajuan_spj')->after('id_pickup_driver')->nullable();
        });

        DB::statement('ALTER TABLE biaya_transportasi_drivers MODIFY id_pickup_driver INT NULL');
    }

    public function down(): void
    {
        Schema::table('biaya_transportasi_drivers', function (Blueprint $table) {
            $table->dropColumn('id_pengajuan_spj');
        });

        DB::statement('ALTER TABLE biaya_transportasi_drivers MODIFY id_pickup_driver INT NOT NULL');
    }
};
