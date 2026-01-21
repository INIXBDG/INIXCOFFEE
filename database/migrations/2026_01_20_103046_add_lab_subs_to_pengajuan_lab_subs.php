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
        Schema::table('pengajuan_lab_subs', function (Blueprint $table) {
            $table->json('lab_snapshot')->nullable()->after('id_labs');
            $table->json('subs_snapshot')->nullable()->after('id_subs');
        });
    }

};
