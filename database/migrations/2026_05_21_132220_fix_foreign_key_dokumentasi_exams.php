<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dokumentasi_exams', function (Blueprint $table) {
            $table->dropForeign(['id_registrasi']);
        });

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

    public function down(): void
    {
        Schema::table('dokumentasi_exams', function (Blueprint $table) {

            $table->dropForeign(['id_registrasi']);
        });

        Schema::table('dokumentasi_exams', function (Blueprint $table) {

            $table->foreign('id_registrasi')
                ->references('id')
                ->on('registrasis')
                ->onDelete('cascade');
        });
    }
};