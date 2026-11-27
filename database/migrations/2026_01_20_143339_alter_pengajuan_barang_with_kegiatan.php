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
        if (Schema::hasTable('pengajuanbarangs')) {
            Schema::table('pengajuanbarangs', function (Blueprint $table) {
                if (!Schema::hasColumn('pengajuanbarangs', 'id_kegiatan')) {
                    $table->text('id_kegiatan')->nullable();
                }
            });
        }    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
