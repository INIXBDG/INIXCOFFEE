<?php

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
        Schema::table('maintenances', function (Blueprint $table) {
            $table->unsignedTinyInteger('bulan')->nullable()->after('id');
            $table->string('jenis_maintenance', 150)->nullable()->after('bulan');
            $table->date('tanggal')->nullable()->after('jenis_maintenance');
            $table->string('no_kk', 50)->nullable()->after('keterangan');
            $table->string('section', 50)->nullable()->after('no_kk');
        });

        DB::table('maintenances')->orderBy('id')->chunkById(500, function ($rows) {
            foreach ($rows as $r) {
                $tgl = $r->tanggal_mulai
                    ?: ($r->created_at ? Carbon::parse($r->created_at)->toDateString() : now()->toDateString());

                $ket  = trim((string) $r->keterangan);
                $info = array_filter([
                    $r->divisi  && $r->divisi  !== '-' ? 'Divisi: ' . $r->divisi   : null,
                    $r->teknisi && $r->teknisi !== '-' ? 'Teknisi: ' . $r->teknisi : null,
                ]);
                if ($info) {
                    $ket = trim($ket . ' [' . implode('; ', $info) . ']');
                }

                DB::table('maintenances')->where('id', $r->id)->update([
                    'tanggal'           => $tgl,
                    'bulan'             => Carbon::parse($tgl)->month,
                    'jenis_maintenance' => $r->nama_barang ?: ($r->kategori ?: 'Tidak diketahui'),
                    'no_kk'             => $r->no_voucher,
                    'section'           => stripos((string) $r->kategori, 'kendara') !== false ? self::KENDARAAN : self::KANTOR,
                    'biaya'             => $r->biaya ?? 0,
                    'keterangan'        => $ket !== '' ? $ket : null,
                ]);
            }
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropColumn([
                'kategori', 'divisi', 'teknisi', 'nama_barang',
                'tanggal_mulai', 'tanggal_selesai', 'no_voucher', 'status',
            ]);
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->index('tanggal');
            $table->index(['section', 'tanggal']);
            $table->index('no_kk');
        });
    }

    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropIndex(['tanggal']);
            $table->dropIndex(['section', 'tanggal']);
            $table->dropIndex(['no_kk']);
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->string('kategori')->nullable();
            $table->string('divisi')->nullable();
            $table->string('teknisi')->nullable();
            $table->string('nama_barang')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->string('no_voucher')->nullable();
            $table->string('status')->default('On Progress');
        });

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

        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropColumn(['bulan', 'jenis_maintenance', 'tanggal', 'no_kk', 'section']);
        });
    }
};