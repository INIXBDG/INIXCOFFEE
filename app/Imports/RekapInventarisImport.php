<?php

namespace App\Imports;

use App\Models\Inventaris;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RekapInventarisImport implements ToCollection
{
    private $importedCount = 0;

    public function collection(Collection $rows)
    {
        Log::info('Start parsing custom Rekap Inventaris Excel layout...');

        // 1. Detect Category row and Header row dynamically
        $categoryRowIndex = 1;
        $headerRowIndex = 2;
        $targetYear = '2025'; // Default fallback

        foreach ($rows as $index => $row) {
            if ($index > 3) break;
            foreach ($row as $cell) {
                $cellStr = strtolower((string)$cell);
                if (str_contains($cellStr, 'inventaris')) {
                    $categoryRowIndex = $index;
                }
                if (str_contains($cellStr, 'nama barang')) {
                    $headerRowIndex = $index;
                }
                if (preg_match('/20\d{2}/', (string)$cell, $matches)) {
                    $targetYear = $matches[0];
                }
            }
        }

        $startDataRowIndex = $headerRowIndex + 1;
        Log::info("Detected Year: {$targetYear}, Category Row: {$categoryRowIndex}, Header Row: {$headerRowIndex}, Data starts at: {$startDataRowIndex}");

        // 2. Loop through the rows to process data
        $currentMonth = 'Januari';

        for ($i = $startDataRowIndex; $i < count($rows); $i++) {
            $row = $rows[$i];

            // If the row is empty or does not have cells, skip
            if (!isset($row[1]) && !isset($row[6]) && !isset($row[11]) && !isset($row[16])) {
                continue;
            }

            // Update current month from Column A (index 0) if not empty
            if (isset($row[0]) && !empty(trim((string)$row[0]))) {
                $currentMonth = trim((string)$row[0]);
            }

            // Loop through category columns of size 5 (index c, c+1, c+2, c+3, c+4)
            // Group 1: 1-5 (Office), Group 2: 6-10 (Kelas), Group 3: 11-15 (Education), Group 4: 16-20 (Sales & Digital), etc.
            for ($c = 1; $c < count($row); $c += 5) {
                if (!isset($row[$c]) || empty(trim((string)$row[$c])) || trim((string)$row[$c]) === '-') {
                    continue; // No item name, skip this group
                }

                $rawName = trim((string)$row[$c]);
                $hargaValue = isset($row[$c+1]) ? (string)$row[$c+1] : '0';
                $dateValue = isset($row[$c+2]) ? (string)$row[$c+2] : '';
                $deskripsi = isset($row[$c+3]) && !empty(trim((string)$row[$c+3])) ? trim((string)$row[$c+3]) : '-';
                $no_kk = isset($row[$c+4]) && !empty(trim((string)$row[$c+4])) ? trim((string)$row[$c+4]) : '-';

                // Get category name
                $rawCategory = isset($rows[$categoryRowIndex][$c]) ? trim((string)$rows[$categoryRowIndex][$c]) : '';
                if (empty($rawCategory) && isset($rows[$categoryRowIndex-1][$c])) {
                    $rawCategory = trim((string)$rows[$categoryRowIndex-1][$c]);
                }
                
                $category = $this->normalizeCategory($rawCategory);

                // Parse Qty and Name
                $qty = 1;
                $cleanName = $rawName;
                if (preg_match('/^(\d+)\s+(.+)$/', $rawName, $matches)) {
                    $qty = (int)$matches[1];
                    $cleanName = trim($matches[2]);
                }

                // Parse Price
                $rawHarga = str_replace(['Rp', '.', ',', ' '], '', $hargaValue);
                $total_harga = (float)$rawHarga;

                // Parse Date
                $waktu_pembelian = $this->parseCustomDate($dateValue, $targetYear, $currentMonth);

                $randomCode = strtoupper(substr($category, 0, 3));
                if (strlen($randomCode) < 3) {
                    $randomCode = str_pad($randomCode, 3, 'X');
                }
                $randomCode = substr($randomCode, 0, 3);

                // Insert into main Inventaris table
                Inventaris::create([
                    'name' => $cleanName,
                    'kategori' => $category,
                    'qty' => $qty,
                    'total_harga' => $total_harga,
                    'waktu_pembelian' => $waktu_pembelian,
                    'ruangan' => 'Lainnya',
                    'no_kk' => $no_kk,
                    'deskripsi' => $deskripsi,
                    'kodebarang' => $randomCode,
                    'type' => ($category === 'ITSM' || $category === 'Sales/Tim Digital') ? 'E' : 'NE',
                    'harga_beli' => $qty > 0 ? ($total_harga / $qty) : $total_harga,
                    'satuan' => 'unit',
                    'kondisi' => 'baik',
                ]);

                $this->importedCount++;
            }
        }

        Log::info("Finished parsing. Successfully imported {$this->importedCount} rows.");
    }

    private function normalizeCategory($rawCategory)
    {
        $rawCategoryLower = strtolower($rawCategory);

        if (str_contains($rawCategoryLower, 'office')) {
            return 'Office';
        }
        if (str_contains($rawCategoryLower, 'kelas')) {
            return 'Kelas';
        }
        if (str_contains($rawCategoryLower, 'education') || str_contains($rawCategoryLower, 'eduka')) {
            return 'Education';
        }
        if (str_contains($rawCategoryLower, 'sales') || str_contains($rawCategoryLower, 'digital')) {
            return 'Sales/Tim Digital';
        }
        if (str_contains($rawCategoryLower, 'itsm') || str_contains($rawCategoryLower, 'it')) {
            return 'ITSM';
        }
        if (str_contains($rawCategoryLower, 'cicilan') || str_contains($rawCategoryLower, 'kendaraan')) {
            return 'Cicilan Kendaraan';
        }

        // Default clean fallback
        $clean = str_replace('Inventaris ', '', $rawCategory);
        return $clean ?: 'Office';
    }

    private function parseCustomDate($dateValue, $targetYear, $currentMonth)
    {
        if (empty($dateValue) || $dateValue === '-') {
            return $this->getDefaultDate($currentMonth, $targetYear);
        }

        // If numeric (Excel serial date)
        if (is_numeric($dateValue)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue)->format('Y-m-d');
            } catch (\Exception $e) {
                return $this->getDefaultDate($currentMonth, $targetYear);
            }
        }

        $dateStr = trim($dateValue);
        $dateStr = $this->translateIndoMonth($dateStr);

        // If no year present in string, append the target year
        if (!preg_match('/20\d{2}/', $dateStr)) {
            $dateStr .= ' ' . $targetYear;
        }

        try {
            return Carbon::parse($dateStr)->format('Y-m-d');
        } catch (\Exception $e) {
            return $this->getDefaultDate($currentMonth, $targetYear);
        }
    }

    private function translateIndoMonth($dateStr)
    {
        $indo = ['januari', 'februari', 'maret', 'april', 'mei', 'juni', 'juli', 'agustus', 'september', 'oktober', 'november', 'desember'];
        $eng = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];
        return str_ireplace($indo, $eng, $dateStr);
    }

    private function getDefaultDate($monthName, $year)
    {
        $monthName = strtolower(trim($monthName));
        $monthNum = 1;

        $months = [
            'januari' => 1, 'jan' => 1, 'january' => 1,
            'februari' => 2, 'feb' => 2, 'february' => 2,
            'maret' => 3, 'mar' => 3, 'march' => 3,
            'april' => 4, 'apr' => 4,
            'mei' => 5, 'may' => 5,
            'juni' => 6, 'jun' => 6, 'june' => 6,
            'juli' => 7, 'jul' => 7, 'july' => 7,
            'agustus' => 8, 'agu' => 8, 'aug' => 8, 'august' => 8,
            'september' => 9, 'sep' => 9,
            'oktober' => 10, 'okt' => 10, 'october' => 10,
            'november' => 11, 'nov' => 11,
            'desember' => 12, 'des' => 12, 'december' => 12
        ];

        if (isset($months[$monthName])) {
            $monthNum = $months[$monthName];
        }

        return sprintf('%04d-%02d-01', $year, $monthNum);
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }
}
