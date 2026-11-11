<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelamar_penilaians', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pelamar_id')->constrained('pelamars')->cascadeOnDelete();
            $t->foreignId('folder_id')->nullable()->constrained('folders')->nullOnDelete();
            $t->unsignedBigInteger('dinilai_oleh')->nullable();
            $t->string('tahap_rekrutmen', 30)->nullable();
            $t->string('tahap_interview', 30)->nullable();
            $t->unsignedTinyInteger('rating');
            $t->text('catatan')->nullable();
            $t->string('file_penilaian')->nullable();
            $t->timestamp('tanggal_dinilai')->nullable();
            $t->timestamps();

            $t->unique(['pelamar_id', 'dinilai_oleh', 'tahap_rekrutmen'], 'uniq_penilai_tahap');
        });

        DB::table('pelamar_folders')->whereNotNull('rating')->orderBy('id')->each(function ($pf) {
            $pelamar = DB::table('pelamars')->where('id', $pf->pelamar_id)->first(['tahap_rekrutmen', 'tahap_interview']);

            DB::table('pelamar_penilaians')->insert([
                'pelamar_id' => $pf->pelamar_id,
                'folder_id' => $pf->folder_id,
                'dinilai_oleh' => $pf->dinilai_oleh,
                'tahap_rekrutmen' => $pelamar->tahap_rekrutmen ?? null,
                'tahap_interview' => $pelamar->tahap_interview ?? null,
                'rating' => $pf->rating,
                'catatan' => $pf->catatan,
                'file_penilaian' => $pf->file_penilaian,
                'tanggal_dinilai' => $pf->tanggal_dinilai,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelamar_penilaians');
    }
};