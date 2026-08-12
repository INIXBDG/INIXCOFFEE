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
        Schema::create('kelas_settings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_rkm');

            $table->date('week_start')->nullable()->comment('Tanggal mulai minggu (untuk pengelompokan)');
            $table->date('week_end')->nullable()->comment('Tanggal akhir minggu');

            $table->string('kelas', 255)->nullable();
            $table->date('dari')->nullable();
            $table->date('sampai')->nullable();

            $table->string('ruangan', 100)->nullable();
            $table->string('device', 50)->nullable();             
            $table->string('device_instruktur', 50)->nullable(); 

            $table->unsignedSmallInteger('pax')->default(0)->comment('Jumlah peserta');

            $table->string('instruktur', 100)->nullable();
            $table->string('pc_its', 50)->nullable()->comment('PIC TS');

            $table->string('asset', 255)->nullable();
            $table->longText('software')->nullable();
            $table->longText('keterangan')->nullable();

            $table->string('status', 20)->default('Kuning');

            $table->longText('comments')->nullable()->comment('Daftar komentar dalam format JSON');

            $table->timestamps();
            $table->softDeletes();

            $table->index('week_start');
            $table->index('ruangan');
            $table->index('instruktur');
            $table->index('status');
            $table->index('dari');
            $table->index('sampai');
            $table->index(['week_start', 'week_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas_settings');
    }
};
