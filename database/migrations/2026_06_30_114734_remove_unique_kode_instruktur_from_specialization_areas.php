<?php

use App\Support\MigrationHelper;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        MigrationHelper::dropUniqueIfExists('specialization_areas', ['kode_instruktur']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        MigrationHelper::addUniqueIfNotExists('specialization_areas', 'kode_instruktur');
    }
};
