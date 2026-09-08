<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->index('id_perusahaan', 'idx_contacts_id_perusahaan');
        });

        Schema::table('aktivitas', function (Blueprint $table) {
            // Composite index untuk mengoptimalkan WHERE IN (...) dan ORDER BY sekaligus
            $table->index(['id_contact', 'created_at'], 'idx_aktivitas_contact_created');
        });

        Schema::table('perusahaans', function (Blueprint $table) {
            $table->index('deleted_at', 'idx_perusahaans_deleted_at');
        });
    }

    public function down()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex('idx_contacts_id_perusahaan');
        });

        Schema::table('aktivitas', function (Blueprint $table) {
            $table->dropIndex('idx_aktivitas_contact_created');
        });

        Schema::table('perusahaans', function (Blueprint $table) {
            $table->dropIndex('idx_perusahaans_deleted_at');
        });
    }
};
