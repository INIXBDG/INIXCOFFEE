<?php

namespace App\Traits;

use App\Models\AbsensiKaryawan;
use App\Models\KategoriDaftarTugas;
use App\Models\KontrolTugas;

trait ShiftGenerator
{
    public function generateTasksForShift($karyawanId, $shiftName, $date)
    {
        $kategoris = KategoriDaftarTugas::where('Tipe', 'Harian')
            ->where(function ($q) use ($shiftName) {
                $q->where('tipe_turunan', $shiftName)
                    ->orWhereNull('tipe_turunan')
                    ->orWhere('tipe_turunan', '');
            })
            ->get();

        foreach ($kategoris as $kat) {
            $exists = KontrolTugas::where('id_karyawan', $karyawanId)
                ->where('id_DaftarTugas', $kat->id)
                ->whereDate('Deadline_Date', $date)
                ->exists();

            if (!$exists) {
                KontrolTugas::create([
                    'id_karyawan'    => $karyawanId,
                    'id_DaftarTugas' => $kat->id,
                    'status'         => 0,
                    'Deadline_Date'  => $date,
                    'urutan'         => (KontrolTugas::max('urutan') ?? 0) + 1,
                ]);
            }
        }

        AbsensiKaryawan::updateOrCreate(
            ['id_karyawan' => $karyawanId, 'tanggal' => $date],
            ['shift' => $shiftName === 'Shift 1' ? 1 : 2]
        );
    }
}