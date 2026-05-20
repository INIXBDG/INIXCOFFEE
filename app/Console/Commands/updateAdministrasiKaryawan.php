<?php

namespace App\Console\Commands;

use App\Models\AdministrasiKaryawan;
use App\Models\formPenilaian;
use App\Models\karyawan;
use App\Models\TunjanganKaryawan;
use Carbon\Carbon;
use Illuminate\Console\Command;

class updateAdministrasiKaryawan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-administrasi-karyawan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $administrasi = AdministrasiKaryawan::all();

        foreach ($administrasi as $a) {
            $dateline = Carbon::parse($a->dateline)->endOfDay();
            $tanggalSelesai = Carbon::parse($a->tanggal_selesai)->endOfDay(); 

            if ($dateline < $now && $tanggalSelesai !== null && !in_array($a->status, ['selesai', 'pending', 'terlambat'])) {
                $a->status = 'terlambat';
                $a->save();

                $this->info("Administrasi karyawan berhasil diupdate!");
            }
        }

        // update administrasi karyawan kontrak dan kartap
        $probations = karyawan::where('status_aktif', '1')
                        ->where('divisi', '!=', 'Direksi')
                        ->where('jabatan', '!=', 'GM')
                        ->whereNull('awal_kontrak')
                        ->whereNotNull('akhir_probation')
                        ->get();
        $kontraks = karyawan::where('status_aktif', '1')
                        ->where('divisi', '!=', 'Direksi')
                        ->where('jabatan', '!=', 'GM')
                        ->whereNull('awal_tetap')
                        ->whereNotNull('akhir_kontrak')
                        ->get();
                        

        //  Probation to Kontrak
        foreach ($probations as $p) {
            $bulanSelesai = Carbon::parse($p->akhir_probation)->month;
            $dateline = Carbon::parse($p->akhir_probation)->subDays(7);

            if ($now->month === $bulanSelesai && $now->day === 1) {
                AdministrasiKaryawan::create([
                    'nama_administrasi' => 'Pembuatan Kontrak Kerja '.$p->nama_lengkap,
                    'id_karyawan' => $p->id,
                    'dateline' => $dateline,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->info('Pembuatan administrasi kontrak kerja '.$p->nama_lengkap);
            }
        }
        
        $administrasiKontrak = AdministrasiKaryawan::with('karyawan')
                                ->where('status', 'proses')
                                ->where('nama_administrasi', 'like', '%Pembuatan Kontrak Kerja%')
                                ->get();

        foreach ($administrasiKontrak as $administrasi) {
            if ($administrasi->karyawan->awal_kontrak && $administrasi->status === 'proses') {
                $administrasi->update([
                    'status' => 'selesai',
                    'tanggal_selesai' => $administrasi->karyawan->updated_at,
                    'updated_at' => now()
                ]);

                $this->info('Administrasi '.$administrasi->nama_administrasi.' berhasil diupdate');
            }
        }
        
        // Kontrak to Tetap
        foreach ($kontraks as $k) {
            $bulanSelesai = Carbon::parse($k->akhir_kontrak)->month;
            $dateline = Carbon::parse($k->akhir_kontrak)->subDays(7);

            if ($now->month === $bulanSelesai && $now->day === 1) {
                AdministrasiKaryawan::create([
                    'nama_administrasi' => 'Pembuatan Administrasi Karyawan Tetap '.$k->nama_lengkap,
                    'id_karyawan' => $k->id,
                    'dateline' => $dateline,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->info('Pembuatan administrasi karyawan tetap '.$k->nama_lengkap);
            }
        }
        
        $administrasiKartap = AdministrasiKaryawan::with('karyawan')
                                ->where('status', 'proses')
                                ->where('nama_administrasi', 'like', '%Pembuatan Administrasi Karyawan Tetap%')
                                ->get();

        foreach ($administrasiKartap as $administrasi) {
            if ($administrasi->karyawan->awal_tetap && $administrasi->status === 'proses') {
                $administrasi->update([
                    'status' => 'selesai',
                    'tanggal_selesai' => $administrasi->karyawan->updated_at,
                    'updated_at' => now()
                ]);

                $this->info('Administrasi '.$administrasi->nama_administrasi.' berhasil diupdate');
            }
        }


        // Form penilaian 
        $tahun = $now->copy()->year;
        if ($now->month === 5 && $now->day === 1) {
            $penilaian = formPenilaian::where('tahun', $tahun)->whereIn('quartal', ['Q1', 'Q2'])->orderBy('created_at', 'desc')->first();

            if (!$penilaian) {
                AdministrasiKaryawan::create([
                    'nama_administrasi' => 'Penilaian 360 Rutin Semester 1',
                    'dateline' => $now->copy()->month(6),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                AdministrasiKaryawan::create([
                    'nama_administrasi' => 'Penilaian 360 Rutin Semester 1',
                    'dateline' => $now->copy()->month(6),
                    'status' => 'selesai',
                    'tanggal_selesai' => Carbon::parse($penilaian->created_at),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->info("Administrasi Penilaian 360 rutin berhasil dibuat");
        }

        if ($now->month === 10 && $now->day === 30) {
            $penilaian = formPenilaian::where('tahun', $tahun)->whereIn('quartal', ['Q3', 'Q4'])->orderBy('created_at', 'desc')->first();

            if (!$penilaian) {
                AdministrasiKaryawan::create([
                    'nama_administrasi' => 'Penilaian 360 Rutin Semester 2',
                    'dateline' => $now->copy()->month(11),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                AdministrasiKaryawan::create([
                    'nama_administrasi' => 'Penilaian 360 Rutin Semester 2',
                    'dateline' => $now->copy()->month(11),
                    'status' => 'selesai',
                    'tanggal_selesai' => Carbon::parse($penilaian->created_at),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->info("Administrasi Penilaian 360 rutin berhasil dibuat");
        }


        // Tunjangan dan Payroll
        if ($now->day === 1) {
            $tunjangan = AdministrasiKaryawan::create([
                'nama_administrasi' => 'Tunjangan bulan '.$now->translatedFormat('F'),
                'dateline' => $now->copy()->addMonth(1)->day(8),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $payroll = AdministrasiKaryawan::create([
                'nama_administrasi' => 'Payroll bulan '.$now->translatedFormat('F'),
                'dateline' => $now->copy()->day(28),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->info("Administrasi Tunjangan dan Payroll bulan ".$now->translatedFormat('F'). ' berhasil dibuat');


            $dbTunjangan = TunjanganKaryawan::where('tahun', $now->format('Y'))->where('bulan', $now->format('n'))->first();

            if ($dbTunjangan) {
                $tunjangan->update([
                    'status' => 'selesai',
                    'tanggal_selesai' => Carbon::parse($dbTunjangan->created_at),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
