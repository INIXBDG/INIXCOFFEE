<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('target_penjualans', function (Blueprint $table) {
            $table->id();
            $table->string('id_sales'); // Implementasi id_sales bertipe string
            $table->integer('tahun');
            $table->decimal('nilai_target', 20, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('target_penjualans');
    }
};
