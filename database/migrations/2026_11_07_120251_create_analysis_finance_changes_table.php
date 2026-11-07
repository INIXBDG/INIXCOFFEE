<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analysis_finance_indicators', function (Blueprint $t) {
            $t->id();
            $t->unsignedSmallInteger('tahun');
            $t->unsignedTinyInteger('no');
            $t->string('analysis')->nullable();
            $t->text('deskripsi')->nullable();
            $t->text('formula')->nullable();
            $t->timestamps();
            $t->unique(['tahun', 'no']);
        });

        Schema::create('analysis_finance_changes', function (Blueprint $t) {
            $t->id();
            $t->unsignedSmallInteger('tahun');
            $t->unsignedTinyInteger('quarter');
            $t->unsignedSmallInteger('no')->default(1);
            $t->string('keterangan');
            $t->decimal('nilai_lalu', 18, 2)->nullable(); 
            $t->decimal('nilai_ini', 18, 2)->nullable();
            $t->timestamps();
            $t->index(['tahun', 'quarter']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_finance_changes');
        Schema::dropIfExists('analysis_finance_indicators');
    }
};