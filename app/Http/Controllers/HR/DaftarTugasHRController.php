<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\karyawan;
use App\Models\KontrolTugas;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DaftarTugasHRController extends Controller
{
public function __construct()
    {
        $this->middleware('auth');

        // Proteksi ketat: Hanya jabatan HRD yang boleh mengakses fitur ini
        $this->middleware(function ($request, $next) {
            if (Auth::user()->jabatan !== 'HRD') {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => 'Akses ditolak. Fitur ini khusus untuk HRD (View Only).'
                    ], 403);
                }
                abort(403, 'Akses ditolak. Halaman ini hanya dapat dilihat oleh HRD.');
            }
            return $next($request);
        });
    }

    /**
     * Menampilkan halaman View-Only Tugas Aktif & Grafik Kinerja Tugas untuk HRD
     */
    public function index()
    {
        $officeBoy = karyawan::where('jabatan', 'Office Boy')
            ->select('id', 'nama_lengkap')
            ->get();

        return view('HR.daftar_tugas.index', compact('officeBoy'));
    }

    /**
     * Endpoint AJAX: Mengambil daftar Tugas Aktif berdasarkan filter
     */
    public function get(Request $request)
    {
        $user = Auth::user();

        $tipe = $request->get('tipe', 'all');
        $tipe_turunan = $request->get('tipe_turunan', 'all');
        $tanggal = $request->get('tanggal', Carbon::today()->format('Y-m-d'));

        $tanggalCarbon = Carbon::parse($tanggal)->startOfDay();

        $query = KontrolTugas::with(['kategoriDaftarTugas', 'karyawan'])
            // Catatan: Hapus baris when() di bawah ini jika semua user di inix-hr boleh melihat semua data OB
            ->when($user->jabatan !== 'HRD', fn($q) => $q->where('id_karyawan', $user->id))
            ->when($tipe !== 'all', fn($q) => $q->whereHas('kategoriDaftarTugas', fn($q2) => $q2->where('Tipe', $tipe)))
            ->when($tipe_turunan !== 'all', fn($q) => $q->whereHas('kategoriDaftarTugas', fn($q2) => $q2->where('tipe_turunan', $tipe_turunan)))
            ->whereDate('Deadline_Date', '=', $tanggalCarbon->format('Y-m-d'));

        $data = $query->orderBy('urutan')->orderBy('id', 'asc')->get();

        return response()->json([
            'data' => $data,
            'filter' => [
                'tipe' => $tipe,
                'tanggal' => $tanggal,
                'tipe_turunan' => $tipe_turunan,
            ],
        ]);
    }

    /**
     * Endpoint AJAX: Mengambil data untuk Grafik Kinerja Tugas
     */
    public function chartData(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $karyawan = $request->get('karyawan', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $user = Auth::user();

        $query = KontrolTugas::with('kategoriDaftarTugas')
            // Catatan: Hapus baris when() di bawah ini jika semua user di inix-hr boleh melihat semua data OB
            ->when($user->jabatan !== 'HRD', fn($q) => $q->where('id_karyawan', $user->id))
            ->when($karyawan !== 'all', fn($q) => $q->where('id_karyawan', $karyawan));

        if ($startDate) {
            $query->whereDate('Deadline_Date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('Deadline_Date', '<=', $endDate);
        }

        $data = $query->get();
        $groups = [];

        foreach ($data as $item) {
            $date = Carbon::parse($item->Deadline_Date);
            $key = '';

            switch ($period) {
                case 'weekly':
                    $key = $date->startOfWeek()->format('d M Y');
                    break;
                case 'monthly':
                    $key = $date->format('F Y');
                    break;
                case 'quarterly':
                    $quarter = ceil($date->month / 3);
                    $key = "Q{$quarter} {$date->year}";
                    break;
                case 'yearly':
                    $key = $date->format('Y');
                    break;
            }

            if (!isset($groups[$key])) {
                $groups[$key] = ['selesai' => 0, 'pending' => 0];
            }

            if ($item->status == 1) {
                $groups[$key]['selesai']++;
            } else {
                $groups[$key]['pending']++;
            }
        }

        ksort($groups);

        $labels = array_keys($groups);
        $dataSelesai = array_map(fn($v) => $v['selesai'], array_values($groups));
        $dataPending = array_map(fn($v) => $v['pending'], array_values($groups));

        return response()->json([
            'labels' => $labels,
            'dataSelesai' => $dataSelesai,
            'dataPending' => $dataPending,
        ]);
    }
}
