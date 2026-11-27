<?php

use App\Support\MigrationHelper;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        MigrationHelper::addIndexIfNotExists('contacts', 'id_perusahaan', 'idx_contacts_id_perusahaan');

        // Composite index untuk mengoptimalkan WHERE IN (...) dan ORDER BY sekaligus
        MigrationHelper::addIndexIfNotExists('aktivitas', ['id_contact', 'created_at'], 'idx_aktivitas_contact_created');

        MigrationHelper::addIndexIfNotExists('perusahaans', 'deleted_at', 'idx_perusahaans_deleted_at');
    }

    public function down()
    {
        MigrationHelper::dropIndexIfExists('contacts', 'idx_contacts_id_perusahaan');
        MigrationHelper::dropIndexIfExists('aktivitas', 'idx_aktivitas_contact_created');
        MigrationHelper::dropIndexIfExists('perusahaans', 'idx_perusahaans_deleted_at');
    }
};
