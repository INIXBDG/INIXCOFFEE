<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
        {
            // tambah kolom sementara
            Schema::table('jurnal_akuntansis', function (Blueprint $table) {
                $table->json('id_pengajuan_barang_temp')->nullable();
            });

            // convert data lama
            $data = DB::table('jurnal_akuntansis')->get();

            foreach ($data as $item) {

                $value = $item->id_pengajuan_barang
                    ? json_encode([$item->id_pengajuan_barang])
                    : json_encode([]);

                DB::table('jurnal_akuntansis')
                    ->where('id', $item->id)
                    ->update([
                        'id_pengajuan_barang_temp' => $value
                    ]);
            }

            try {
                Schema::table('jurnal_akuntansis', function (Blueprint $table) {
                    $table->dropForeign(['id_pengajuan_barang']);
                });
            } catch (\Exception $e) {
            }

            Schema::table('jurnal_akuntansis', function (Blueprint $table) {
                $table->dropColumn('id_pengajuan_barang');
            });

            // rename jadi nama asli
            Schema::table('jurnal_akuntansis', function (Blueprint $table) {
                $table->renameColumn(
                    'id_pengajuan_barang_temp',
                    'id_pengajuan_barang'
                );
            });
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jurnal_akuntansis', function (Blueprint $table) {
            //
        });
    }
};
