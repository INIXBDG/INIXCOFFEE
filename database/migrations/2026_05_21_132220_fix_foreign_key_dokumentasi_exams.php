<?php

use App\Support\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('dokumentasi_exams') || !Schema::hasColumn('dokumentasi_exams', 'id_registrasi')) {
            return;
        }

        if (MigrationHelper::foreignKeyExistsOnColumn('dokumentasi_exams', 'id_registrasi', 'registexams')) {
            return;
        }

        MigrationHelper::dropForeignIfExists('dokumentasi_exams', ['id_registrasi']);

        if (Schema::hasTable('registexams')) {
            DB::statement("
                DELETE d
                FROM dokumentasi_exams d
                LEFT JOIN registexams r
                ON d.id_registrasi = r.id
                WHERE r.id IS NULL
            ");

            Schema::table('dokumentasi_exams', function (Blueprint $table) {
                $table->foreign('id_registrasi')
                    ->references('id')
                    ->on('registexams')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('dokumentasi_exams') || !Schema::hasColumn('dokumentasi_exams', 'id_registrasi')) {
            return;
        }

        MigrationHelper::dropForeignIfExists('dokumentasi_exams', ['id_registrasi']);

        if (Schema::hasTable('registrasis') && !MigrationHelper::foreignKeyExistsOnColumn('dokumentasi_exams', 'id_registrasi', 'registrasis')) {
            Schema::table('dokumentasi_exams', function (Blueprint $table) {
                $table->foreign('id_registrasi')
                    ->references('id')
                    ->on('registrasis')
                    ->onDelete('cascade');
            });
        }
    }
};