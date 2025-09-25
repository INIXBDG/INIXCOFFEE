<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;
class rekapExamExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    use Exportable;

    protected $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function headings(): array
    {
        return [
            'Invoice',
            'Tanggal Pengajuan',
            'Nama Materi',
            'Nama Perusahaan',
            'Kode Exam',
            'Pax',
            'Nama Peserta',
            'Tanggal Exam',
            'Waktu Exam',
            'Grade Exam',
            'Hasil Exam',
            'Kartu Kredit',
            'Mata Uang',
            'Harga',
            'Kurs Harga',
            'Biaya Admin dalam dollar',
            'Kurs Biaya Admin',
            'Harga dalam Rupiah',
            'Total Harga dalam Rupiah'
        ];
    }

    public function collection()
    {
       $processedData = new Collection();
        $previousValues = [
            // Kolom-kolom yang ingin disatukan (di-merge secara visual dengan mengosongkan nilai yang sama)
            'invoice' => null,
            'tanggal_pengajuan' => null,
            'nama_materi' => null,
            'nama_perusahaan' => null,
            'kode_exam' => null,
        ];
         $sortedInvoices = $this->data->sortBy([
            'invoice',
            'tanggal_pengajuan',
            'nama_materi',
            'nama_perusahaan',
            'kode_exam',
            // Tambahkan kolom lain yang relevan untuk pengurutan
        ]);
         foreach ($sortedInvoices as $row) {
            // Konversi row ke array agar mudah dimanipulasi
            // Jika $row sudah berupa array, maka tidak perlu di-cast
            $rowData = (array) $row; // Langsung cast ke array, karena $row dari sortBy sudah array

            foreach ($previousValues as $key => $previousValue) {
                // Pastikan key ada di $rowData sebelum diakses
                if (array_key_exists($key, $rowData)) {
                    if ($rowData[$key] === $previousValue) {
                        $rowData[$key] = ''; // Kosongkan jika nilainya sama dengan baris sebelumnya
                    } else {
                        $previousValues[$key] = $rowData[$key]; // Update nilai sebelumnya
                    }
                } else {
                    // Handle jika kolom tidak ditemukan, mungkin beri nilai null atau biarkan saja
                    $previousValues[$key] = null;
                }
            }
            $processedData->push($rowData);
        }
        return $processedData;
    }
}
