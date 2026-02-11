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
        Schema::create('detail_person_k_p_i_s', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_target');
            $table->unsignedBigInteger('detailTargetKey');
            $table->string('id_karyawan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_person_k_p_i_s');
    }
};
