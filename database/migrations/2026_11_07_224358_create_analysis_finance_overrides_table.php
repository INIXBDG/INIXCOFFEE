<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analysis_finance_overrides', function (Blueprint $t) {
            $t->id();
            $t->unsignedSmallInteger('tahun');
            $t->string('periode', 4);
            $t->unsignedTinyInteger('no');
            $t->decimal('realisasi', 10, 2)->nullable();
            $t->decimal('pencapaian', 10, 2)->nullable();
            $t->timestamps();
            $t->unique(['tahun', 'periode', 'no']);
        });

        if (!Schema::hasColumn('analysis_finance_initiatives', 'keterangan')) {
            Schema::table('analysis_finance_initiatives', function (Blueprint $t) {
                $t->text('keterangan')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_finance_overrides');

        if (Schema::hasColumn('analysis_finance_initiatives', 'keterangan')) {
            Schema::table('analysis_finance_initiatives', function (Blueprint $t) {
                $t->dropColumn('keterangan');
            });
        }
    }
};