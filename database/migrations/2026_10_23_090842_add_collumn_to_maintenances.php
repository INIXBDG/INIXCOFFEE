<?php

use App\Support\MigrationHelper;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const KENDARAAN = 'Maintenance Kendaraan';
    private const KANTOR    = 'Maintenance Kantor';

    public function up(): void
    {
        if (!Schema::hasTable('maintenances')) {
            return;
        }

        if (Schema::hasTable('maintenances')) {
            Schema::table('maintenances', function (Blueprint $table) {
                if (!Schema::hasColumn('maintenances', 'bulan')) {
                    $table->unsignedTinyInteger('bulan')->nullable()->after('id');
                }
                if (!Schema::hasColumn('maintenances', 'jenis_maintenance')) {
                    $table->string('jenis_maintenance', 150)->nullable()->after('bulan');
                }
                if (!Schema::hasColumn('maintenances', 'tanggal')) {
                    $table->date('tanggal')->nullable()->after('jenis_maintenance');
                }
                if (!Schema::hasColumn('maintenances', 'no_kk')) {
                    $table->string('no_kk', 50)->nullable()->after('keterangan');
                }
                if (!Schema::hasColumn('maintenances', 'section')) {
                    $table->string('section', 50)->nullable()->after('no_kk');
                }
            });
        }

        if (Schema::hasColumn('maintenances', 'tanggal_mulai')) {
            DB::table('maintenances')->orderBy('id')->chunkById(500, function ($rows) {
                foreach ($rows as $r) {
                    $tgl = $r->tanggal_mulai
                        ?: ($r->created_at ? Carbon::parse($r->created_at)->toDateString() : now()->toDateString());

                    $ket  = trim((string) $r->keterangan);
                    $info = array_filter([
                        ($r->divisi ?? null)  && $r->divisi  !== '-' ? 'Divisi: ' . $r->divisi   : null,
                        ($r->teknisi ?? null) && $r->teknisi !== '-' ? 'Teknisi: ' . $r->teknisi : null,
                    ]);
                    if ($info) {
                        $ket = trim($ket . ' [' . implode('; ', $info) . ']');
                    }

                    DB::table('maintenances')->where('id', $r->id)->update([
                        'tanggal'           => $tgl,
                        'bulan'             => Carbon::parse($tgl)->month,
                        'jenis_maintenance' => ($r->nama_barang ?? null) ?: (($r->kategori ?? null) ?: 'Tidak diketahui'),
                        'no_kk'             => $r->no_voucher ?? null,
                        'section'           => stripos((string) ($r->kategori ?? ''), 'kendara') !== false ? self::KENDARAAN : self::KANTOR,
                        'biaya'             => $r->biaya ?? 0,
                        'keterangan'        => $ket !== '' ? $ket : null,
                    ]);
                }
            });
        }

        $dropCols = array_values(array_filter([
            'kategori', 'divisi', 'teknisi', 'nama_barang',
            'tanggal_mulai', 'tanggal_selesai', 'no_voucher', 'status',
        ], fn (string $col) => Schema::hasColumn('maintenances', $col)));

        if (!empty($dropCols)) {
            Schema::table('maintenances', function (Blueprint $table) use ($dropCols) {
                $table->dropColumn($dropCols);
            });
        }

        MigrationHelper::addIndexIfNotExists('maintenances', 'tanggal');
        MigrationHelper::addIndexIfNotExists('maintenances', ['section', 'tanggal']);
        MigrationHelper::addIndexIfNotExists('maintenances', 'no_kk');
    }

    public function down(): void
    {
        if (!Schema::hasTable('maintenances')) {
            return;
        }

        MigrationHelper::dropIndexIfExists('maintenances', ['tanggal']);
        MigrationHelper::dropIndexIfExists('maintenances', ['section', 'tanggal']);
        MigrationHelper::dropIndexIfExists('maintenances', ['no_kk']);

        Schema::table('maintenances', function (Blueprint $table) {
            if (!Schema::hasColumn('maintenances', 'kategori')) {
                $table->string('kategori')->nullable();
            }
            if (!Schema::hasColumn('maintenances', 'divisi')) {
                $table->string('divisi')->nullable();
            }
            if (!Schema::hasColumn('maintenances', 'teknisi')) {
                $table->string('teknisi')->nullable();
            }
            if (!Schema::hasColumn('maintenances', 'nama_barang')) {
                $table->string('nama_barang')->nullable();
            }
            if (!Schema::hasColumn('maintenances', 'tanggal_mulai')) {
                $table->date('tanggal_mulai')->nullable();
            }
            if (!Schema::hasColumn('maintenances', 'tanggal_selesai')) {
                $table->date('tanggal_selesai')->nullable();
            }
            if (!Schema::hasColumn('maintenances', 'no_voucher')) {
                $table->string('no_voucher')->nullable();
            }
            if (!Schema::hasColumn('maintenances', 'status')) {
                $table->string('status')->default('On Progress');
            }
        });

        if (Schema::hasColumn('maintenances', 'section')) {
            DB::table('maintenances')->orderBy('id')->chunkById(500, function ($rows) {
                foreach ($rows as $r) {
                    DB::table('maintenances')->where('id', $r->id)->update([
                        'kategori'      => $r->section === self::KENDARAAN ? 'Kendaraan' : 'Fasilitas Kantor',
                        'nama_barang'   => $r->jenis_maintenance,
                        'tanggal_mulai' => $r->tanggal,
                        'no_voucher'    => $r->no_kk,
                        'status'        => 'Selesai',
                    ]);
                }
            });
        }

        $dropCols = array_values(array_filter(
            ['bulan', 'jenis_maintenance', 'tanggal', 'no_kk', 'section'],
            fn (string $col) => Schema::hasColumn('maintenances', $col)
        ));
        if (!empty($dropCols)) {
            Schema::table('maintenances', function (Blueprint $table) use ($dropCols) {
                $table->dropColumn($dropCols);
            });
        }
    }
};