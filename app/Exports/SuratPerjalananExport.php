<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SuratPerjalananExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data->map(function ($item) {
            return [
                $item->karyawan->nama_lengkap ?? '-',
                $item->karyawan->divisi ?? '-',
                $item->karyawan->jabatan ?? '-',
                $item->jenis_dinas ?? '-',
                $item->tipe ?? '-',
                $item->tujuan ?? '-',
                $item->alasan ?? '-',
                optional($item->tanggal_berangkat)->format('Y-m-d') ?? '-',
                optional($item->tanggal_pulang)->format('Y-m-d') ?? '-',
                $item->durasi ? $item->durasi . ' Hari' : '0 Hari',
                'Rp. ' . number_format($item->ratemakan ?? 0, 0, ',', '.'),
                'Rp. ' . number_format($item->ratespj ?? 0, 0, ',', '.'),
                'Rp. ' . number_format($item->ratetaksi ?? 0, 0, ',', '.'),
                'Rp. ' . number_format($item->aksi ?? 0, 0, ',', '.'),
                $item->approval_manager === null ? '-' : (
                    $item->approval_manager == '0' ? 'Belum disetujui' : (
                        $item->approval_manager == '1' ? 'Disetujui' : 'Ditolak'
                    )
                ),
                $item->approval_hrd === null ? '-' : (
                    $item->approval_hrd == '0' ? 'Belum disetujui' : (
                        $item->approval_hrd == '1' ? 'Disetujui' : 'Ditolak'
                    )
                ),
                $item->approval_direksi === null ? '-' : (
                    $item->approval_direksi == '0' ? 'Belum disetujui' : (
                        $item->approval_direksi == '1' ? 'Disetujui' : 'Ditolak'
                    )
                ),

            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Divisi',
            'Jabatan',
            'Jenis Dinas',
            'Tipe',
            'Tujuan',
            'Alasan',
            'Tanggal Berangkat',
            'Tanggal Pulang',
            'Durasi',
            'Rate Makan',
            'Rate SPJ',
            'Rate Taksi',
            'Total',
            'Keterangan Manager',
            'Keterangan HRD',
            'Keterangan Direksi',
        ];
    }
}
