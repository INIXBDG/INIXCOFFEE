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
        Schema::create('sub_checklist_keperluans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_keperluan_id')->constrained('checklist_keperluans')->cascadeOnDelete();
            
            $table->boolean('materi_module')->default(false);
            $table->boolean('materi_elearning')->default(false);

            $table->boolean('cb_instruktur')->default(false);
            $table->boolean('cb_peserta')->default(false);

            $table->boolean('maksi_instruktur')->default(false);
            $table->boolean('maksi_peserta')->default(false);

            $table->boolean('kelas_ac')->default(false);
            $table->boolean('kelas_jam')->default(false);
            $table->boolean('kelas_buku')->default(false);
            $table->boolean('kelas_pulpen')->default(false);
            $table->boolean('kelas_permen')->default(false);
            $table->boolean('kelas_camilan')->default(false);
            $table->boolean('kelas_minuman')->default(false);
            $table->boolean('kelas_lampu')->default(false);
            $table->boolean('kelas_kondisi_kebersihan')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_checklist_keperluans');
    }
};
