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
        $extractId = static function ($json) {
            $ids = json_decode($json ?? '[]', true, 512, JSON_THROW_ON_ERROR);

            if (!is_array($ids)) {
                throw new \RuntimeException('Cannot roll back id_pengajuan_barang: expected a JSON array.');
            }

            if (count($ids) > 1) {
                throw new \RuntimeException('Cannot roll back id_pengajuan_barang: a row contains multiple IDs.');
            }

            $id = $ids[0] ?? null;

            if ($id !== null && !is_int($id) && !(is_string($id) && ctype_digit($id))) {
                throw new \RuntimeException('Cannot roll back id_pengajuan_barang: an ID is not an integer.');
            }

            return $id;
        };

        DB::table('jurnal_akuntansis')
            ->select('id', 'id_pengajuan_barang')
            ->orderBy('id')
            ->chunkById(500, function ($items) use ($extractId) {
                foreach ($items as $item) {
                    $extractId($item->id_pengajuan_barang);
                }
            });

        Schema::table('jurnal_akuntansis', function (Blueprint $table) {
            $table->unsignedBigInteger('id_pengajuan_barang_temp')->nullable();
        });

        DB::table('jurnal_akuntansis')
            ->select('id', 'id_pengajuan_barang')
            ->orderBy('id')
            ->chunkById(500, function ($items) use ($extractId) {
                foreach ($items as $item) {
                    DB::table('jurnal_akuntansis')
                        ->where('id', $item->id)
                        ->update(['id_pengajuan_barang_temp' => $extractId($item->id_pengajuan_barang)]);
                }
            });

        Schema::table('jurnal_akuntansis', function (Blueprint $table) {
            $table->dropColumn('id_pengajuan_barang');
        });

        Schema::table('jurnal_akuntansis', function (Blueprint $table) {
            $table->renameColumn('id_pengajuan_barang_temp', 'id_pengajuan_barang');
        });

        Schema::table('jurnal_akuntansis', function (Blueprint $table) {
            $table->foreign('id_pengajuan_barang')
                ->references('id')
                ->on('pengajuanbarangs')
                ->onDelete('set null');
        });
    }
};
