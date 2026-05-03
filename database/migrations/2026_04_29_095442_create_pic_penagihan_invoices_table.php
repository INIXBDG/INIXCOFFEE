<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pic_penagihan_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('id_rkm');
            $table->unsignedBigInteger('perusahaan_id');
            $table->text('alamat')->nullable();
            $table->string('category');
            $table->string('pic');
            $table->string('telepon');
            $table->enum('status', ['0', '1', '2', '3'])->default('0');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pic_penagihan_invoices');
    }
};
