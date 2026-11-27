<?php

use App\Support\MigrationHelper;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        MigrationHelper::addIndexIfNotExists('registrasis', 'id_instruktur');
        MigrationHelper::addIndexIfNotExists('registrasis', 'id_sales');
        MigrationHelper::addIndexIfNotExists('registrasis', 'created_at');

        MigrationHelper::addIndexIfNotExists('pesertas', 'nama');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        MigrationHelper::dropIndexIfExists('registrasis', 'registrasis_id_instruktur_index');
        MigrationHelper::dropIndexIfExists('registrasis', 'registrasis_id_sales_index');
        MigrationHelper::dropIndexIfExists('registrasis', 'registrasis_created_at_index');

        MigrationHelper::dropIndexIfExists('pesertas', 'pesertas_nama_index');
    }
};
