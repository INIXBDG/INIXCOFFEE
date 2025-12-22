<?php

namespace App\Http\Controllers\office;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;


// Import semua model yang dibutuhkan
use App\Models\souvenir;
use App\Models\souvenirpeserta;
use App\Models\PengajuanSouvenir;
use App\Models\DetailPengajuanSouvenir; // Asumsi model ini ada berdasarkan relasi detail()
use App\Models\PenambahanSouvenir;
use App\Models\PenukaranSouvenir;

class DashboardSouvenirController extends Controller
{
    public function index()
    {
        // 1. Data Paling Banyak Dipilih Peserta (Top 5)
        // Mengambil dari tabel souvenirpeserta, dikelompokkan berdasarkan id_souvenir
        $topPeserta = souvenirpeserta::select('id_souvenir', DB::raw('count(*) as total_pilih'))
            ->with('souvenir') // Eager load nama souvenir
            ->groupBy('id_souvenir')
            ->orderByDesc('total_pilih')
            ->limit(5)
            ->get();

        // 2. Data Paling Banyak Dibeli OFFICE (Top 5)
        // Karena data item ada di tabel detail, kita sum qty dari DetailPengajuanSouvenir
        // Kita juga bisa filter berdasarkan status pengajuan di tabel parent jika perlu
        $topOffice = DetailPengajuanSouvenir::select('id_souvenir', DB::raw('sum(pax) as total_beli'))
            ->with('souvenir')
            ->groupBy('id_souvenir')
            ->orderByDesc('total_beli')
            ->limit(5)
            ->get();

        // 3. Data Stock Souvenir (Semua Data atau Limit)
        // Diurutkan dari stok paling sedikit (untuk warning) atau terbanyak
        $stockSouvenir = souvenir::select('id', 'nama_souvenir', 'stok')
            ->orderBy('stok', 'asc') // Menampilkan stok terendah dulu agar aware
            ->get();

        // 4. Data Souvenir Tambahan (Distribusi Manual/Penambahan)
        // Mengambil jumlah qty yang dikeluarkan melalui menu Penambahan
        $topPenambahan = PenambahanSouvenir::select('id_souvenir', DB::raw('sum(qty) as total_keluar'))
            ->with('souvenir')
            ->groupBy('id_souvenir')
            ->orderByDesc('total_keluar')
            ->limit(5)
            ->get();

        // 5. Jumlah Total Penukaran Souvenir
        // Menghitung total baris di tabel penukaran
        $totalPenukaran = PenukaranSouvenir::count();

        // Statistik Penukaran per Bulan (Opsional - untuk grafik)
        $chartPenukaran = PenukaranSouvenir::select(
                DB::raw('MONTH(tanggal_tukar) as bulan'),
                DB::raw('count(*) as total')
            )
            ->whereYear('tanggal_tukar', date('Y'))
            ->groupBy('bulan')
            ->get();

        $tahunSekarang = date('Y');

        // === ANALISA SELISIH (OFFICE vs PESERTA) - TAHUN INI ===
        $analisaSelisih = souvenir::select('id', 'nama_souvenir')
            ->get()
            ->map(function($item) use ($tahunSekarang) {

                // A. Hitung Total Beli (Office)
                // SOLUSI: Cek tahun dari tabel PARENT (PengajuanSouvenir), bukan tabel detail
                $totalBeli = DetailPengajuanSouvenir::where('id_souvenir', $item->id)
                    ->whereHas('pengajuan', function($q) use ($tahunSekarang) {
                        $q->whereYear('created_at', $tahunSekarang);
                    })
                    ->sum('pax'); // Pastikan nama kolom jumlah adalah 'qty' atau 'pax'

                // B. Hitung Total Pilih (Peserta)
                // SOLUSI: Cek tahun dari tabel RKM (Tanggal Awal Kegiatan)
                $totalPakai = souvenirpeserta::where('id_souvenir', $item->id)
                    ->whereHas('rkm', function($q) use ($tahunSekarang) {
                        $q->whereYear('tanggal_awal', $tahunSekarang);
                    })
                    ->count();

                // C. Hitung Selisih
                $selisih = $totalBeli - $totalPakai;

                $item->total_masuk = $totalBeli;
                $item->total_keluar = $totalPakai;
                $item->selisih_flow = $selisih;

                return $item;
            })
            ->filter(function($item) {
                return $item->total_masuk > 0 || $item->total_keluar > 0;
            })
            ->sortBy('selisih_flow');


        return view('office.dashboardsouveir.dashboard', compact(
            'topPeserta',
            'topOffice',
            'stockSouvenir',
            'topPenambahan',
            'totalPenukaran',
            'chartPenukaran',
            'analisaSelisih'
        ));
    }
}
