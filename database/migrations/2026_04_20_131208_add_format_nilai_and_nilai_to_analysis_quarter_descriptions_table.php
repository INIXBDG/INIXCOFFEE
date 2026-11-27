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
        if (Schema::hasTable('analysis_quarter_descriptions')) {
            Schema::table('analysis_quarter_descriptions', function (Blueprint $table) {
                // Menambahkan kolom format_nilai (contoh: 'Persentase', 'Rupiah', 'Angka')
                if (!Schema::hasColumn('analysis_quarter_descriptions', 'format_nilai')) {
                    $table->string('format_nilai')->nullable()->after('description');
                }

                // Menambahkan kolom nilai (tipe decimal dengan 15 digit total dan 2 digit di belakang koma)
                if (!Schema::hasColumn('analysis_quarter_descriptions', 'nilai')) {
                    $table->decimal('nilai', 15, 2)->nullable()->after('format_nilai');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analysis_quarter_descriptions', function (Blueprint $table) {
            $table->dropColumn(['format_nilai', 'nilai']);
        });
    }
};
