<?php

namespace App\Http\Controllers;
use Google\Client as GoogleClient;
use Google\Service\Sheets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardItsmController extends Controller
{
    private $spreadsheetId = '1w_xAH-peVcIYhgnVDw6jiycw4bjXdPZlcMOzhYe3n7w'; // Ganti dengan spreadsheet ID Anda
    private $range = "'Pivot Table 1'!A2:G";

    private function getGoogleClient()
    {
        $client = new GoogleClient();
        $client->setAuthConfig(storage_path('app/google/credentials.json'));
        $client->addScope(Sheets::SPREADSHEETS_READONLY);
        return $client;
    }

    public function getListBulan()
    {
        $data = $this->fetchDataFromSpreadsheet();

        $bulanList = [];

        foreach ($data as $row) {
            $bulan = $row[0] ?? null;  // Asumsi kolom 0 adalah "BULAN TAHUN"
            if ($bulan && !in_array($bulan, $bulanList)) {
                $bulanList[] = $bulan;
            }
        }

        // Urutkan bulan jika perlu, misal berdasarkan urutan munculnya
        sort($bulanList);

        return response()->json($bulanList);
    }


    private function fetchDataFromSpreadsheet()
    {
        $client = $this->getGoogleClient();
        $service = new Sheets($client);
        $response = $service->spreadsheets_values->get($this->spreadsheetId, $this->range);
        return $response->getValues() ?? [];
    }

    // 1. Jumlah Permintaan (total permintaan per bulan, divisi, kategori, dll)
    public function getJumlahPermintaan(Request $request)
    {
        $data = $this->fetchDataFromSpreadsheet();

        $filterMonth = $request->query('filterMonth', 'all'); // Filter bulan, default 'all'

        $result = [];

        foreach ($data as $row) {
            $bulan = $row[0] ?? '';         // Asumsi kolom 0 adalah "BULAN TAHUN"
            $divisi = $row[2] ?? '';        // Asumsi kolom 2 adalah "Divisi"
            $countStatus = isset($row[6]) ? (int) $row[6] : 0;

            // Filter bulan jika filter aktif
            if ($filterMonth !== 'all' && $filterMonth !== $bulan) {
                continue; // skip jika bukan bulan yg dipilih
            }

            // Group berdasarkan divisi
            $key = $divisi;

            if (!isset($result[$key])) {
                $result[$key] = 0;
            }
            $result[$key] += $countStatus;
        }

        $labels = array_keys($result);
        $values = array_values($result);

        return response()->json(['labels' => $labels, 'values' => $values]);
    }

    public function getJumlahPIC(Request $request)
    {
        // Asumsikan ini data keseluruhan dari spreadsheet, perlu ganti sesuai method fetch aktual
        $data = $this->fetchDataFromSpreadsheet();

        $filterMonth = $request->query('filterMonth', 'all');

        // Inisialisasi hitungan PIC untuk ketiga kategori
        $picCountByCategory = [
            'Programming' => 0,
            'Digital' => 0,
            'Technical Support' => 0,
        ];

        foreach ($data as $index => $row) {
            $bulan = $row[0] ?? '';           // Kolom Bulan Tahun (format: YYYY-MM)
            $keperluan = strtolower($row[1] ?? ''); // Kolom Keperluan, diubah ke lowercase untuk pengecekan
            $countStatus = intval($row[6] ?? 0);    // Kolom COUNTA of Status (jumlah pekerjaan)

            // Filter bulan jika ada filter selain all
            if ($filterMonth !== 'all' && $filterMonth !== $bulan) {
                continue;
            }

            // Tentukan kategori PIC berdasarkan isi kolom keperluan
            if (strpos($keperluan, 'programming') !== false) {
                $picCountByCategory['Programming'] += $countStatus;
            } elseif (strpos($keperluan, 'digital') !== false) {
                $picCountByCategory['Digital'] += $countStatus;
            } elseif (strpos($keperluan, 'technical support') !== false) {
                $picCountByCategory['Technical Support'] += $countStatus;
            }
            // Jika keperluan tidak mengandung ketiganya, maka abaikan
        }

        $labels = array_keys($picCountByCategory);
        $values = array_values($picCountByCategory);

        return response()->json(['labels' => $labels, 'values' => $values]);
    }

    public function getRerataDurasi(Request $request)
    {
        $data = $this->fetchDataFromSpreadsheet(); // Data array baris

        $toSeconds = function($timeString) {
            if (!$timeString) return 0;
            $parts = explode(':', $timeString);
            if (count($parts) == 2) {
                return ((int)$parts[0] * 60) + (int)$parts[1];
            } elseif (count($parts) == 3) {
                return ((int)$parts[0] * 3600) + ((int)$parts[1] * 60) + (int)$parts[2];
            }
            return 0;
        };

        $filterMonth = $request->query('filterMonth', 'all');

        $durasiPerKeperluan = [];

        foreach ($data as $row) {
            $bulan = $row[0] ?? '';
            $keperluan = $row[1] ?? '';
            $durasiStr = $row[5] ?? '';

            if ($filterMonth !== 'all' && $filterMonth !== $bulan) {
                continue;
            }

            if ($bulan && $keperluan && $durasiStr) {
                $durasiDetik = $toSeconds($durasiStr);
                if (!isset($durasiPerKeperluan[$keperluan])) {
                    $durasiPerKeperluan[$keperluan] = [];
                }
                $durasiPerKeperluan[$keperluan][] = $durasiDetik;
            }
        }

        $labels = [];
        $values = [];
        foreach ($durasiPerKeperluan as $keperluan => $durasiArray) {
            $labels[] = $keperluan;
            $values[] = round(array_sum($durasiArray) / count($durasiArray));
        }

        return response()->json(['labels' => $labels, 'values' => $values]);
    }

    // 4. Jumlah Permintaan Per Bulan (singkat dari no 1)
    public function getJumlahPermintaanPerBulan()
    {
        $data = $this->fetchDataFromSpreadsheet();

        $result = [];

        foreach ($data as $row) {
            $bulan = $row[0] ?? '';
            $countStatus = isset($row[6]) ? (int) $row[6] : 0;

            if (!isset($result[$bulan])) {
                $result[$bulan] = 0;
            }
            $result[$bulan] += $countStatus;
        }

        ksort($result);

        $labels = array_keys($result);
        $values = array_values($result);

        return response()->json([
            'labels' => $labels,
            'values' => $values,
        ]);
    }

    // 5. Rata-rata Ketepatan Respon Ticketing
    public function getRerataKetepatanResponse(Request $request)
    {
        $data = $this->fetchDataFromSpreadsheet(); // Data array baris

        $toSeconds = function($timeString) {
            if (!$timeString) return 0;
            $parts = explode(':', $timeString);
            if (count($parts) == 2) {
                return ((int)$parts[0] * 60) + (int)$parts[1];
            } elseif (count($parts) == 3) {
                return ((int)$parts[0] * 3600) + ((int)$parts[1] * 60) + (int)$parts[2];
            }
            return 0;
        };

        $filterMonth = $request->query('filterMonth', 'all');

        $ketepatanPerKeperluan = [];

        foreach ($data as $row) {
            $bulan = $row[0] ?? '';
            $keperluan = $row[1] ?? '';
            $ketepatanStr = $row[4] ?? ''; // kolom 4: ketepatan response

            if ($filterMonth !== 'all' && $filterMonth !== $bulan) {
                continue;
            }

            if ($bulan && $keperluan && $ketepatanStr) {
                $ketepatanDetik = $toSeconds($ketepatanStr);
                if (!isset($ketepatanPerKeperluan[$keperluan])) {
                    $ketepatanPerKeperluan[$keperluan] = [];
                }
                $ketepatanPerKeperluan[$keperluan][] = $ketepatanDetik;
            }
        }

        $labels = [];
        $values = [];
        foreach ($ketepatanPerKeperluan as $keperluan => $ketepatanArray) {
            $labels[] = $keperluan;
            $values[] = round(array_sum($ketepatanArray) / count($ketepatanArray));
        }

        return response()->json(['labels' => $labels, 'values' => $values]);
    }


    public function getPermintaanSeringDiajukan(Request $request)
    {
        $data = $this->fetchDataFromSpreadsheet(); // Ambil data dari sumber sesuai implementasi Anda

        $result = [];

        foreach ($data as $row) {
            $kategori = $row[3] ?? ''; // Asumsikan kategori ada di index 3
            $status = $row[6] ?? '';   // Asumsikan status ada di index 6

            if (!empty($kategori) && $status !== '') {
                if (!isset($result[$kategori])) {
                    $result[$kategori] = 0;
                }
                $result[$kategori] += 1; // hitung 1 per baris dengan kategori dan status valid
            }
        }

        arsort($result); // Urutkan descending berdasarkan jumlah permintaan

        $labels = array_keys($result);
        $values = array_values($result);

        return response()->json([
            'labels' => $labels,
            'values' => $values
        ]);
    }

}

