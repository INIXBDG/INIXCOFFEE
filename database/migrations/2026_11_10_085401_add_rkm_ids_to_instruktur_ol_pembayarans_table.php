<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instruktur_ol_pembayarans', function (Blueprint $table) {
            $table->json('rkm_ids')->nullable()->after('rkm_id');
        });

        DB::table('instruktur_ol_pembayarans')
            ->whereNotNull('rkm_id')
            ->orderBy('id')
            ->each(function ($row) {
                DB::table('instruktur_ol_pembayarans')
                    ->where('id', $row->id)
                    ->update(['rkm_ids' => json_encode([(int) $row->rkm_id])]);
            });
    }

    public function down(): void
    {
        Schema::table('instruktur_ol_pembayarans', function (Blueprint $table) {
            $table->dropColumn('rkm_ids');
        });
    }
};