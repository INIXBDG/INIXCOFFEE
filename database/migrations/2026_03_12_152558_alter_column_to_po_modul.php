<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('nomor_moduls')) {
            if (!Schema::hasColumn('nomor_moduls', 'uploaded')) {
                Schema::table('nomor_moduls', function (Blueprint $table) {
                    $table->dateTime('uploaded')->nullable()->after('status');
                });
            }

            if (Schema::hasColumn('nomor_moduls', 'status')) {
                DB::statement("
                    ALTER TABLE nomor_moduls 
                    MODIFY status ENUM('Menunggu','Disetujui','Uploaded') 
                    DEFAULT 'Menunggu'
                ");
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('nomor_moduls')) {
            if (Schema::hasColumn('nomor_moduls', 'uploaded')) {
                Schema::table('nomor_moduls', function (Blueprint $table) {
                    $table->dropColumn('uploaded');
                });
            }

            if (Schema::hasColumn('nomor_moduls', 'status')) {
                DB::statement("
                    ALTER TABLE nomor_moduls 
                    MODIFY status ENUM('Menunggu','Disetujui') 
                    DEFAULT 'Menunggu'
                ");
            }
        }
    }
};
