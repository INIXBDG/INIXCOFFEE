<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenilaianExamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penilaian_exams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_rkm');
            $table->tinyInteger('nilai_emote')->comment('Nilai representasi emote dari 1 hingga 4');
            $table->timestamps();

            // Mendefinisikan Foreign Key
            $table->foreign('id_rkm')->references('id')->on('r_k_m_s')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penilaian_exams');
    }
}
