<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('instruktur_ol_silabus', function (Blueprint $table) {
            $table->unsignedBigInteger('rkm_id')->nullable()->index();
        });

        Schema::table('instruktur_ol_silabus', function (Blueprint $table) {
            $table->string('file_path')->nullable()->change();
            $table->string('file_nama_asli')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('instruktur_ol_silabus', function (Blueprint $table) {
            $table->dropIndex(['rkm_id']);
            $table->dropColumn('rkm_id');
        });
    }
};