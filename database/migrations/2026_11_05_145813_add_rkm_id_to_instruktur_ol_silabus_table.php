<?php

use App\Support\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('instruktur_ol_silabus')) {
            if (!Schema::hasColumn('instruktur_ol_silabus', 'rkm_id')) {
                Schema::table('instruktur_ol_silabus', function (Blueprint $table) {
                    $table->unsignedBigInteger('rkm_id')->nullable();
                });
            }

            MigrationHelper::addIndexIfNotExists('instruktur_ol_silabus', 'rkm_id');

            Schema::table('instruktur_ol_silabus', function (Blueprint $table) {
                if (Schema::hasColumn('instruktur_ol_silabus', 'file_path')) {
                    $table->string('file_path')->nullable()->change();
                }
                if (Schema::hasColumn('instruktur_ol_silabus', 'file_nama_asli')) {
                    $table->string('file_nama_asli')->nullable()->change();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('instruktur_ol_silabus')) {
            MigrationHelper::dropIndexIfExists('instruktur_ol_silabus', ['rkm_id']);
            if (Schema::hasColumn('instruktur_ol_silabus', 'rkm_id')) {
                Schema::table('instruktur_ol_silabus', function (Blueprint $table) {
                    $table->dropColumn('rkm_id');
                });
            }
        }
    }
};