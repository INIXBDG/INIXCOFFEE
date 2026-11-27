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
        if (Schema::hasTable('pengajuan_lab_subs')) {
            Schema::table('pengajuan_lab_subs', function (Blueprint $table) {
                if (!Schema::hasColumn('pengajuan_lab_subs', 'lab_snapshot')) {
                    $col = $table->json('lab_snapshot')->nullable();
                    if (Schema::hasColumn('pengajuan_lab_subs', 'id_labs')) {
                        $col->after('id_labs');
                    }
                }
                if (
                    Schema::hasColumn('pengajuan_lab_subs', 'id_subs')
                    && !Schema::hasColumn('pengajuan_lab_subs', 'subs_snapshot')
                ) {
                    $table->json('subs_snapshot')->nullable()->after('id_subs');
                }
            });
        }
    }

};
