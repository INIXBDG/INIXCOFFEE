<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TunjanganExport implements FromArray, WithHeadings, ShouldAutoSize
{
    protected $post;
    protected $jenis_tunjangan;

    public function __construct($post, $jenis_tunjangan)
    {
        $this->post = $post;
        $this->jenis_tunjangan = $jenis_tunjangan;
    }

    public function headings(): array
    {
        $columns = ['Nama Karyawan'];
        foreach ($this->jenis_tunjangan as $tunjangan) {
            $columns[] = $tunjangan->nama_tunjangan;
        }
        $columns[] = 'Total Tunjangan yang diterima';

        return $columns;
    }

    public function array(): array
    {
        $data = [];

        // Untuk menyimpan total per kolom
        $totalPerJenis = [];
        foreach ($this->jenis_tunjangan as $tunjangan) {
            $totalPerJenis[$tunjangan->nama_tunjangan] = 0;
        }
        $grandTotal = 0;
        $grandTotalTunjangan = 0;
        $grandTotalPotongan = 0;
        $grandTotalPremiHadir = 0;
        foreach ($this->post as $namaKaryawan => $tunjanganList) {
            $tunjanganValues = [];
            foreach ($this->jenis_tunjangan as $tunjangan) {
                $tunjanganValues[$tunjangan->nama_tunjangan] = 0;
            }

            foreach ($tunjanganList as $item) {
                $nama = $item->jenistunjangan->nama_tunjangan;
                if (array_key_exists($nama, $tunjanganValues)) {
                    $tunjanganValues[$nama] += floatval($item->total);
                    $totalPerJenis[$nama] += floatval($item->total);
                }
                if ($item->jenistunjangan->tipe == 'Potongan') {
                    $grandTotalPotongan += floatval($item->total);
                } elseif ($item->jenistunjangan->nama_tunjangan == 'Absensi') {
                    $grandTotalPremiHadir += floatval($item->total);
                } else {
                    $grandTotalTunjangan += floatval($item->total);
                }
            }

            $totalTunjangan = array_sum($tunjanganValues);
            $grandTotal += $totalTunjangan;

            $row = [$namaKaryawan];
            foreach ($this->jenis_tunjangan as $tunjangan) {
                $row[] = $tunjanganValues[$tunjangan->nama_tunjangan];
            }
            $row[] = $totalTunjangan;

            $data[] = $row;
        }

        // Tambahkan baris total di akhir sebagai 'tfoot'
        $footer = ['Total'];
        foreach ($this->jenis_tunjangan as $tunjangan) {
            $footer[] = $totalPerJenis[$tunjangan->nama_tunjangan];
        }
        $footer[] = $grandTotal;

        $data[] = $footer;

        $spasi = [''];
        $data[] = $spasi;

        $tfoot1 = ['Total Tunjangan'];
        $tfoot1[] = $grandTotalTunjangan;
        $data[] = $tfoot1;
        $tfoot2 = ['Total Potongan'];
        $tfoot2[] = $grandTotalPotongan;
        $data[] = $tfoot2;
        $tfoot3 = ['Total Premi Hadir'];
        $tfoot3[] = $grandTotalPremiHadir;
        $data[] = $tfoot3;
        return $data;
    }
}
