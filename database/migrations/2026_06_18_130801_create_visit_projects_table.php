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
        Schema::create('visit_projects', function (Blueprint $table) {
            $table->id();
            $table->string('kegiatan');
            $table->string('lokasi');
            $table->string('pic_name');
            $table->date('tanggal');
            $table->string('photo_path');
            $table->text('desc');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit_projects');
    }
};
