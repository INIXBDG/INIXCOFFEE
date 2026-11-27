<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('project_administrations')) {
            Schema::table('project_administrations', function (Blueprint $table) {
                if (!Schema::hasColumn('project_administrations', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        if (Schema::hasTable('project_handovers')) {
            Schema::table('project_handovers', function (Blueprint $table) {
                if (!Schema::hasColumn('project_handovers', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    public function down()
    {
        Schema::table('project_administrations', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('project_handovers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};