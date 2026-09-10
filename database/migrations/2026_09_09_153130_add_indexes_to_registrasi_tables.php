<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('registrasis', function (Blueprint $table) {
            $table->index('id_instruktur');
            $table->index('id_sales');
            $table->index('created_at'); // Mempercepat proses order by terbaru
        });

        Schema::table('pesertas', function (Blueprint $table) {
            $table->index('nama'); // Mempercepat proses pencarian nama
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrasis', function (Blueprint $table) {
            $table->dropIndex('registrasis_id_instruktur_index');
            $table->dropIndex('registrasis_id_sales_index');
            $table->dropIndex('registrasis_created_at_index');
        });

        Schema::table('pesertas', function (Blueprint $table) {
            $table->dropIndex('pesertas_nama_index');
        });
    }
};
