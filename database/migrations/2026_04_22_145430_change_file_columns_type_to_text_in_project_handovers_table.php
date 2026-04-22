<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('project_handovers', function (Blueprint $table) {
            $table->text('bast_file')->nullable()->change();
            $table->text('final_report_file')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('project_handovers', function (Blueprint $table) {
            $table->string('bast_file')->nullable()->change();
            $table->string('final_report_file')->nullable()->change();
        });
    }
};