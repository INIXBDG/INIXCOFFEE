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
        if (Schema::hasTable('eksams')) {
            Schema::table('eksams', function (Blueprint $table) {
                if (!Schema::hasColumn('eksams', 'id_pengajuan_barang')) {
                    $table->unsignedBigInteger('id_pengajuan_barang')->nullable()->after('mata_uang');
                }
            });
        }
    }

    public function down()
    {
        // kososng
    }
};
