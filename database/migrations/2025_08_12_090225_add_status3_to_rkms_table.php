<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::statement("
            ALTER TABLE `r_k_m_s`
            MODIFY `status` ENUM('0','1','2','3') NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::statement("UPDATE `r_k_m_s` SET `status` = '2' WHERE `status` = '3'");

        DB::statement("
            ALTER TABLE `r_k_m_s`
            MODIFY `status` ENUM('0','1','2') NOT NULL
        ");
    }
};
