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
        Schema::create('lead_projects', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lead');
            $table->string('perusahaan_id');
            $table->decimal('estimasi_nilai', 15, 2);
            $table->enum('status', ['new', 'negosiasi', 'won', 'lost'])->default('new');
            $table->string('sales_id'); // Merujuk ke kode_karyawan
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_projects');
    }
};
