<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('perusahaans')) {
            Schema::table('perusahaans', function (Blueprint $table) {
                if (!Schema::hasColumn('perusahaans', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    public function down()
    {
        Schema::table('perusahaans', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
