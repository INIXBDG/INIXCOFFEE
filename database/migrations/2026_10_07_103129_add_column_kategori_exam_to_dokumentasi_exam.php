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
        if (Schema::hasTable('dokumentasi_exams')) {
            Schema::table('dokumentasi_exams', function (Blueprint $table) {
                if (!Schema::hasColumn('dokumentasi_exams', 'kategori_exam')) {
                    $table->enum('kategori_exam', ['BNSP', 'Internasional', 'Inixcert', 'Authorize'])->nullable()->after('nama_exam');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumentasi_exams', function (Blueprint $table) {
            $table->dropColumn('kategori_exam');
        });
    }
};
