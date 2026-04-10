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
        Schema::create('detail_target_k_p_i_s', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_targetKPI');
            $table->string('jabatan')->nullable();
            $table->string('divisi')->nullable();
            $table->text('jangka_target')->nullable();
            $table->text('detail_jangka')->nullable();
            $table->string('tipe_target')->nullable();
            $table->bigInteger('nilai_target')->nullable();
            $table->bigInteger('manual_value')->nullable();
            $table->string('manual_document')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_target_k_p_i_s');
    }
};
