<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\DokumentasiExam;
use App\Models\eksam;
use App\Models\Registrasi;
use App\Models\registexam;
use App\Models\Peserta;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dokumentasi_exams', function (Blueprint $table) {
            $table->dropForeign(['id_registrasi']);
        });

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

            $table->foreign('id_registrasi')
                ->references('id')
                ->on('registrasis')
                ->onDelete('cascade');
        });
    }
};
