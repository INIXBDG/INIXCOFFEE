<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('eksams')) {
            Schema::table('eksams', function (Blueprint $table) {
                // Mengubah tipe data menjadi text/json untuk menyimpan array
                if (!Schema::hasColumn('eksams', 'file_invoice')) {
                    $table->text('file_invoice')->nullable()->after('invoice');
                }
            });
        }
    }

    public function down()
    {
        Schema::table('eksams', function (Blueprint $table) {
            $table->dropColumn('file_invoice');
        });
    }
};
