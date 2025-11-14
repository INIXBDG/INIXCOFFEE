<?php

namespace App\Http\Controllers;

use App\Models\Tickets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardItsmController extends Controller
{
    /**
     * Helper untuk menambahkan filter bulan ke query Eloquent.
     */
    private function applyMonthFilter($query, $month)
    {
        if ($month && $month !== 'all') {
            // Asumsi 'created_at' adalah kolom tanggal tiket
            $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month]);
        }
        return $query;
    }

    /**
     * Mengambil daftar bulan unik dari data tiket.
     */
    public function getListBulan()
    {
        $bulanList = Tickets::selectRaw("DISTINCT DATE_FORMAT(created_at, '%Y-%m') as bulan_tahun")
            ->whereNotNull('created_at')
            ->orderBy('bulan_tahun', 'desc')
            ->pluck('bulan_tahun');

        return response()->json($bulanList);
    }

    /**
     * 1. Jumlah Permintaan (Ticketing) per Divisi.
     */
    public function getJumlahPermintaan(Request $request)
    {
        $query = Tickets::select('divisi', DB::raw('COUNT(*) as total'))
            ->groupBy('divisi');

        $this->applyMonthFilter($query, $request->query('filterMonth'));

        $result = $query->pluck('total', 'divisi');

        return response()->json([
            'labels' => $result->keys(),
            'values' => $result->values()
        ]);
    }

    /**
     * Menghitung jumlah tiket berdasarkan kategori PIC dari kolom 'keperluan'.
     */
    public function getJumlahPIC(Request $request)
    {
        $query = Tickets::selectRaw("
                CASE
                    WHEN LOWER(keperluan) LIKE '%programming%' THEN 'Programming'
                    WHEN LOWER(keperluan) LIKE '%digital%' THEN 'Digital'
                    WHEN LOWER(keperluan) LIKE '%technical support%' THEN 'Technical Support'
                    ELSE 'Lainnya'
                END as pic_category,
                COUNT(*) as total
            ")
            ->groupBy('pic_category');

        $this->applyMonthFilter($query, $request->query('filterMonth'));

        $result = $query->get()
                       ->where('pic_category', '!=', 'Lainnya')
                       ->pluck('total', 'pic_category');

        $picCountByCategory = [
            'Programming' => $result->get('Programming', 0),
            'Digital' => $result->get('Digital', 0),
            'Technical Support' => $result->get('Technical Support', 0),
        ];

        return response()->json([
            'labels' => array_keys($picCountByCategory),
            'values' => array_values($picCountByCategory)
        ]);
    }

    /**
     * Menghitung Rata-rata Durasi Pengerjaan per Keperluan (dalam detik).
     */
    public function getRerataDurasi(Request $request)
    {
        // PERUBAHAN 2: Menggunakan CONCAT untuk menggabungkan tanggal dan jam selesai
        $query = Tickets::select(
                'keperluan',
                DB::raw('AVG(TIMESTAMPDIFF(SECOND, created_at, CONCAT(tanggal_selesai, " ", jam_selesai))) as avg_duration')
            )
            // Pastikan 'tanggal_selesai' tidak null
            ->whereNotNull('tanggal_selesai')
            ->whereNotNull('jam_selesai')
            ->groupBy('keperluan');

        $this->applyMonthFilter($query, $request->query('filterMonth'));

        $result = $query->pluck('avg_duration', 'keperluan');

        return response()->json([
            'labels' => $result->keys(),
            'values' => $result->values()->map(fn($val) => round($val))
        ]);
    }

    /**
     * 4. Jumlah Permintaan Per Bulan (total semua tiket per bulan).
     */
    public function getJumlahPermintaanPerBulan()
    {
        $result = Tickets::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bulan_tahun, COUNT(*) as total")
            ->groupBy('bulan_tahun')
            ->orderBy('bulan_tahun', 'asc')
            ->pluck('total', 'bulan_tahun');

        return response()->json([
            'labels' => $result->keys(),
            'values' => $result->values(),
        ]);
    }

    /**
     * 5. Rata-rata Ketepatan (Kecepatan) Respon per Keperluan (dalam detik).
     */
    public function getRerataKetepatanResponse(Request $request)
    {
        // PERUBAHAN 3: Menggunakan CONCAT untuk menggabungkan tanggal dan jam response
        $query = Tickets::select(
                'keperluan',
                DB::raw('AVG(TIMESTAMPDIFF(SECOND, created_at, CONCAT(tanggal_response, " ", jam_response))) as avg_response')
            )
            // Pastikan 'tanggal_response' tidak null
            ->whereNotNull('tanggal_response')
            ->whereNotNull('jam_response')
            ->groupBy('keperluan');

        $this->applyMonthFilter($query, $request->query('filterMonthKetepatan'));

        $result = $query->pluck('avg_response', 'keperluan');

        return response()->json([
            'labels' => $result->keys(),
            'values' => $result->values()->map(fn($val) => round($val))
        ]);
    }

    /**
     * Menghitung kategori permintaan yang paling sering diajukan.
     */
    public function getPermintaanSeringDiajukan(Request $request)
    {
        $result = Tickets::select('kategori', DB::raw('COUNT(*) as total'))
            ->whereNotNull('kategori')
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->pluck('total', 'kategori');

        return response()->json([
            'labels' => $result->keys(),
            'values' => $result->values()
        ]);
    }

    
}
