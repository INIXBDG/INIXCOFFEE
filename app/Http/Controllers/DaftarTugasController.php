<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\KategoriDaftarTugas;
use App\Models\KontrolTugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Exports\DaftarTugasReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DaftarTugasImport;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DaftarTugasController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View DaftarTugas OB', ['only' => ['index', 'getKategori', 'get', 'getAvailableCategories']]);
        $this->middleware('permission:Store DaftarTugas OB', ['only' => ['store']]);
        $this->middleware('permission:Aktifkan DaftarTugas OB', ['only' => ['aktifkanTugas']]);
        $this->middleware('permission:Update DaftarTugas OB Kategori', ['only' => ['updateKategori']]);
        $this->middleware('permission:Delete DaftarTugas OB Kategori', ['only' => ['deleteKategori']]);
    }

    public function index()
    {
        $dataKategori = KategoriDaftarTugas::with('karyawan')->get();
        $karyawan = Karyawan::select('id', 'nama_lengkap')->get();
        $officeBoy = Karyawan::where('jabatan', 'Office Boy')->get();
        $auth = Auth::id();

        return view('office.daftarTugas.index', compact('dataKategori', 'karyawan', 'officeBoy', 'auth'));
    }

    public function getKategori()
    {
        $dataKategori = KategoriDaftarTugas::with('karyawan')->get();
        return response()->json($dataKategori);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'tugas' => 'required|string|max:255',
            'Tipe' => 'required|in:Harian,Mingguan,Bulanan,Quartal,Semester,Tahunan',
            'tipe_turunan' => 'nullable',
        ];

        if ($user->jabatan === 'HRD') {
            $rules['jabatan_pembuat'] = 'required';
        }

        $validated = $request->validate($rules);

        $id_user = $user->jabatan === 'HRD' ? Karyawan::where('jabatan', $validated['jabatan_pembuat'])->first()?->id : $user->id;

        $tipe_turunan = $validated['tipe_turunan'];

        if ($request->filled('tipe_turunan')) {
            $tipe = $request->Tipe;
            $turunan = $request->tipe_turunan;

            if ($tipe === 'Harian' && !in_array($turunan, ['Shift 1', 'Shift 2'])) {
                return response()->json(['message' => 'Shift untuk Harian harus Shift 1 atau Shift 2'], 422);
            }

            if ($tipe === 'Mingguan' && !in_array($turunan, ['Sabtu', 'Minggu'])) {
                return response()->json(['message' => 'Shift untuk Mingguan harus Sabtu atau Minggu'], 422);
            }

            if ($tipe === 'Bulanan') {
                if (!is_numeric($turunan) || $turunan < 1 || $turunan > 31) {
                    return response()->json(['message' => 'Tanggal untuk Bulanan harus angka 1-31'], 422);
                }
            }
        }

        $kategori = KategoriDaftarTugas::create([
            'id_user' => $id_user,
            'Tipe' => $validated['Tipe'],
            'tipe_turunan' => $tipe_turunan,
            'judul_kategori' => $validated['tugas'],
            'Jabatan_Pembuat' => 'Office Boy',
        ]);

        return response()->json(
            [
                'success' => true,
                'message' => 'Kategori berhasil ditambahkan',
                'data' => $kategori,
            ],
            201,
        );
    }

    public function get(Request $request)
    {
        $user = auth()->user();

        $tipe = $request->get('tipe', 'all');
        $tipe_turunan = $request->get('tipe_turunan', 'all');
        $tanggal = $request->get('tanggal', today()->format('Y-m-d'));

        $query = KontrolTugas::with(['kategoriDaftarTugas', 'karyawan'])
            ->when($user->jabatan !== 'HRD', fn($q) => $q->where('id_karyawan', $user->id))
            ->when($tipe !== 'all', fn($q) => $q->whereHas('kategoriDaftarTugas', fn($q2) => $q2->where('Tipe', $tipe)))
            ->when($tipe_turunan !== 'all', function ($q) use ($tipe_turunan) {
                $q->whereHas(
                    'kategoriDaftarTugas',
                    fn($q2) => $q2->where('tipe_turunan', $tipe_turunan)->orWhere(function ($q3) use ($tipe_turunan) {
                        if ($tipe_turunan === 'all') {
                            $q3->whereNull('tipe_turunan');
                        }
                    }),
                );
            });

        $query->whereDate('Deadline_Date', $tanggal);

        $data = $query->latest()->get();

        return response()->json([
            'data' => $data,
            'filter' => [
                'tipe' => $tipe,
                'tanggal' => $tanggal,
                'tipe_turunan' => $tipe_turunan,
            ],
        ]);
    }

    public function getAvailableCategories(Request $request)
    {
        $user = auth()->user();
        $today = now()->toDateString();
        $todayDate = now()->day;
        $now = now();

        $query = KategoriDaftarTugas::with('karyawan')
            ->when($user->jabatan !== 'HRD', function ($q) use ($user) {
                $q->where('Jabatan_Pembuat', $user->jabatan);
            });

        $kategori = $query->get();
        $available = [];

        foreach ($kategori as $kat) {
            $canActivate = false;
            $deadline = null;
            $reason = null;

            if ($kat->Tipe === 'Harian') {
                $canActivate = true;
                $deadline = $today;

                if (!empty($kat->tipe_turunan)) {
                    $shift1Taken = KontrolTugas::whereDate('Deadline_Date', $today)->where('id_karyawan', '!=', $user->id)->whereHas('kategoriDaftarTugas', fn($q) => $q->where('Tipe', 'Harian')->where('tipe_turunan', 'Shift 1'))->exists();

                    if ($kat->tipe_turunan === 'Shift 1' && $shift1Taken) {
                        $canActivate = false;
                        $reason = 'Shift 1 sudah diambil karyawan lain';
                    }
                }
            } elseif ($kat->Tipe === 'Bulanan') {
                $targetDate = $kat->tipe_turunan ? (int) $kat->tipe_turunan : 1;
                if ($todayDate == $targetDate) {
                    $canActivate = true;
                    $deadline = $now->copy()->setDay($targetDate)->toDateString();
                } else {
                    $reason = "Harian tanggal {$targetDate}, hari ini tanggal {$todayDate}";
                }
            } elseif ($kat->Tipe === 'Mingguan') {
                $hariMap = ['Saturday' => 'Sabtu', 'Sunday' => 'Minggu'];
                $hariIni = $now->dayName;
                $shiftHariIni = $hariMap[$hariIni] ?? null;

                $canActivate = true;
                $deadline = $this->hitungDeadlineMingguan($kat->tipe_turunan);

                if (!empty($kat->tipe_turunan) && $kat->tipe_turunan !== $shiftHariIni) {
                    $reason = "Shift {$kat->tipe_turunan}, hari ini {$hariIni}";
                }
            } elseif (in_array($kat->Tipe, ['Quartal', 'Semester', 'Tahunan'])) {
                $canActivate = true;
                $deadline = $this->hitungDeadline($kat->Tipe);
            }

            if ($canActivate && $deadline) {
                $exists = KontrolTugas::where('id_karyawan', $user->id)->where('id_DaftarTugas', $kat->id)->whereDate('Deadline_Date', $deadline)->exists();

                if ($exists) {
                    $canActivate = false;
                    $reason = 'Sudah aktif untuk periode ini';
                }
            }

            if ($canActivate) {
                $available[] = [
                    'id' => $kat->id,
                    'judul_kategori' => $kat->judul_kategori,
                    'Tipe' => $kat->Tipe,
                    'tipe_turunan' => $kat->tipe_turunan,
                    'karyawan' => $kat->karyawan?->nama_lengkap,
                    'deadline_preview' => $deadline,
                    'badge_color' => $this->getBadgeColor($kat->Tipe),
                ];
            }
        }

        return response()->json([
            'available' => $available,
            'count' => count($available),
            'today' => $today,
        ]);
    }

    private function getBadgeColor($tipe)
    {
        return match ($tipe) {
            'Harian' => 'bg-primary',
            'Mingguan' => 'bg-info text-dark',
            'Bulanan' => 'bg-warning text-dark',
            'Quartal' => 'bg-success',
            'Semester' => 'bg-secondary',
            'Tahunan' => 'bg-dark',
            default => 'bg-light text-dark',
        };
    }

    public function chartData(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $karyawan = $request->get('karyawan', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $user = auth()->user();

        $query = KontrolTugas::with('kategoriDaftarTugas')
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

    public function aktifkanTugas(Request $request)
    {
        $request->validate(['kategori_ids' => 'required|array']);

        $user = auth()->user();
        $kategoriIds = $request->kategori_ids;
        $created = 0;
        $skipped = 0;
        $errors = [];
        $today = now()->toDateString();

        foreach ($kategoriIds as $katId) {
            $kategori = KategoriDaftarTugas::find($katId);
            if (!$kategori) {
                $errors[] = "Kategori ID {$katId} tidak ditemukan";
                continue;
            }

            Log::info('Cek Permission', [
                'user_jabatan' => $user->jabatan,
                'user_id' => $user->id,
                'kategori_id_user' => $kategori->id_user,
                'kategori_judul' => $kategori->judul_kategori
            ]);

            $deadline = $this->hitungDeadline($kategori->Tipe, $kategori->tipe_turunan);

            if ($kategori->Tipe === 'Harian') {
                $deadline = $today;
            }

            Log::info('Deadline Calculation', [
                'kategori_id' => $kategori->id,
                'tipe' => $kategori->Tipe,
                'deadline' => $deadline
            ]);

            $exists = KontrolTugas::where('id_karyawan', $user->id)
                ->where('id_DaftarTugas', $kategori->id)
                ->whereDate('Deadline_Date', $deadline)
                ->exists();

            if ($exists) {
                $skipped++;
                $errors[] = "Task '{$kategori->judul_kategori}' sudah aktif untuk deadline {$deadline}";
                continue;
            }

            KontrolTugas::create([
                'id_karyawan' => $user->id,
                'id_DaftarTugas' => $kategori->id,
                'status' => 0,
                'Deadline_Date' => $deadline,
            ]);
            $created++;
        }

        $message = "{$created} tugas berhasil diaktifkan";
        if ($skipped > 0) {
            $message .= ", {$skipped} sudah ada sebelumnya";
        }
        if (!empty($errors)) {
            $message .= '. ' . count($errors) . ' error (cek detail)';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
        ], !empty($errors) ? 200 : 200);
    }

    private function hitungDeadline($tipe, $tipe_turunan = null)
    {
        $now = now();

        return match ($tipe) {
            'Harian' => $now->toDateString(),

            'Mingguan' => $this->hitungDeadlineMingguan($tipe_turunan),

            'Bulanan' => $this->hitungDeadlineBulanan($tipe_turunan),

            'Quartal' => $now->addMonths(3)->endOfMonth()->toDateString(),
            'Semester' => $now->addMonths(6)->endOfMonth()->toDateString(),
            'Tahunan' => $now->endOfYear()->toDateString(),
            default => $now->toDateString(),
        };
    }

    private function hitungDeadlineMingguan($shift = null)
    {
        $now = now();

        if ($shift === 'Sabtu') {
            return $now->copy()->next(Carbon::SATURDAY)->toDateString();
        }

        if ($shift === 'Minggu') {
            return $now->copy()->next(Carbon::SUNDAY)->toDateString();
        }

        return $now->copy()->endOfWeek()->toDateString();
    }

    private function hitungDeadlineBulanan($tanggal = null)
    {
        $now = now();
        $targetDate = $tanggal ? (int) $tanggal : 1;

        if ($now->day > $targetDate) {
            return $now->copy()->addMonth()->setDay($targetDate)->toDateString();
        }

        return $now->copy()->setDay($targetDate)->toDateString();
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:kontrol_tugas,id',
            'status' => 'required|in:0,1',
        ]);

        $tugas = KontrolTugas::findOrFail($request->id);

        if (auth()->user()->jabatan !== 'HRD' && $tugas->id_karyawan !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Tidak berhak mengubah status ini'], 403);
        }

        $tugas->update(['status' => (int) $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diperbarui',
            'status' => $tugas->status,
        ]);
    }

    public function uploadBukti(Request $request)
    {
        $request->validate([
            'tugas_id' => 'required|exists:kontrol_tugas,id',
            'bukti_before' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'bukti_after' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $tugas = KontrolTugas::findOrFail($request->tugas_id);

        if (auth()->user()->jabatan !== 'HRD' && $tugas->id_karyawan !== auth()->id()) {
            return response()->json(['message' => 'Tidak berhak mengupload bukti ini'], 403);
        }

        $buktiData = $this->parseBukti($tugas->bukti);

        if ($request->hasFile('bukti_before')) {
            if ($buktiData['before'] && Storage::disk('public')->exists($buktiData['before'])) {
                Storage::disk('public')->delete($buktiData['before']);
            }
            $buktiData['before'] = $request->file('bukti_before')->store('bukti-tugas', 'public');
        }

        if ($request->hasFile('bukti_after')) {
            if (empty($buktiData['before'])) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Foto Before wajib diupload terlebih dahulu sebelum mengupload Foto After'
                ], 422);
            }
            if ($buktiData['after'] && Storage::disk('public')->exists($buktiData['after'])) {
                Storage::disk('public')->delete($buktiData['after']);
            }
            $buktiData['after'] = $request->file('bukti_after')->store('bukti-tugas', 'public');
        }

        $tugas->update(['bukti' => json_encode($buktiData)]);

        if (!empty($buktiData['after'])) {
            $tugas->update(['status' => 1]);
        }

        return response()->json([
            'success' => true, 
            'message' => 'Bukti berhasil diupdate',
            'status' => $tugas->status,
            'bukti' => $buktiData
        ]);
    }

    public function delete($id)
    {
        $tugas = KontrolTugas::findOrFail($id);

        if (auth()->user()->jabatan !== 'HRD' && $tugas->id_karyawan !== auth()->id()) {
            return response()->json(['message' => 'Tidak berhak menghapus tugas ini'], 403);
        }

        $buktiData = $this->parseBukti($tugas->bukti);
        if ($buktiData['before'] && Storage::disk('public')->exists($buktiData['before'])) {
            Storage::disk('public')->delete($buktiData['before']);
        }
        if ($buktiData['after'] && Storage::disk('public')->exists($buktiData['after'])) {
            Storage::disk('public')->delete($buktiData['after']);
        }

        $tugas->delete();

        return response()->json(['success' => true, 'message' => 'Tugas berhasil dihapus']);
    }

    public function bulkDelete(Request $request) {
        $ids = $request->ids ?? [];

        $tasks = KontrolTugas::whereIn('id', $ids)->get();

        foreach ($tasks as $tugas) {

            if (
                auth()->user()->jabatan !== 'HRD' &&
                $tugas->id_karyawan !== auth()->id()
            ) {
                continue;
            }

            $buktiData = $this->parseBukti($tugas->bukti);

            if (
                $buktiData['before'] &&
                Storage::disk('public')->exists($buktiData['before'])
            ) {
                Storage::disk('public')->delete($buktiData['before']);
            }

            if (
                $buktiData['after'] &&
                Storage::disk('public')->exists($buktiData['after'])
            ) {
                Storage::disk('public')->delete($buktiData['after']);
            }

            $tugas->delete();
        }

        return response()->json(['success' => true, 'message' => 'Tugas berhasil dihapus']);
    }

    public function updateKategori(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:kategori_daftar_tugas,id',
            'judul_kategori' => 'required|string|max:255',
            'tipe' => 'required|in:Harian,Mingguan,Bulanan,Quartal,Semester,Tahunan',
            'tipe_turunan' => 'nullable',
        ]);
        $kategori = KategoriDaftarTugas::findOrFail($request->id);

        $tipe_turunan = $request->tipe_turunan;

        if ($request->filled('tipe_turunan')) {
            $tipe = $request->Tipe;
            $turunan = $request->tipe_turunan;

            if ($tipe === 'Harian' && !in_array($turunan, ['Shift 1', 'Shift 2'])) {
                return response()->json(['message' => 'Shift untuk Harian harus Shift 1 atau Shift 2'], 422);
            }

            if ($tipe === 'Mingguan' && !in_array($turunan, ['Sabtu', 'Minggu'])) {
                return response()->json(['message' => 'Shift untuk Mingguan harus Sabtu atau Minggu'], 422);
            }

            if ($tipe === 'Bulanan') {
                if (!is_numeric($turunan) || $turunan < 1 || $turunan > 31) {
                    return response()->json(['message' => 'Tanggal untuk Bulanan harus angka 1-31'], 422);
                }
            }
        }

        $kategori->update([
            'judul_kategori' => $request->judul_kategori,
            'Tipe' => $request->tipe,
            'tipe_turunan' => $tipe_turunan,
            'Jabatan_Pembuat' => 'Office Boy',
        ]);

        return response()->json(['success' => true, 'message' => 'Kategori berhasil diperbarui']);
    }

    public function bulkUpdateTipeTurunan(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:kategori_daftar_tugas,id',
            'tipe_turunan' => 'nullable|in:Shift 1,Shift 2,Sabtu,Minggu',
        ]);

        $updated = 0;

        foreach ($request->ids as $id) {
            $kategori = KategoriDaftarTugas::findOrFail($id);

            $kategori->update([
                'tipe_turunan' => $request->tipe_turunan,
            ]);
            $updated++;
        }

        return response()->json(['success' => true, 'message' => "{$updated} kategori berhasil diperbarui"]);
    }

    public function deleteKategori(Request $request)
    {
        $request->validate(['id' => 'required|exists:kategori_daftar_tugas,id']);
        $kategori = KategoriDaftarTugas::findOrFail($request->id);
        if (Auth::id() !== $kategori->id_user && Auth::user()->jabatan !== 'HRD') {
            return response()->json(['message' => 'Tidak berhak menghapus kategori ini'], 403);
        }
        KontrolTugas::where('id_DaftarTugas', $kategori->id)->each(function ($tugas) {
            $buktiData = $this->parseBukti($tugas->bukti);
            if ($buktiData['before'] && Storage::disk('public')->exists($buktiData['before'])) {
                Storage::disk('public')->delete($buktiData['before']);
            }
            if ($buktiData['after'] && Storage::disk('public')->exists($buktiData['after'])) {
                Storage::disk('public')->delete($buktiData['after']);
            }
            $tugas->delete();
        });
        $kategori->delete();
        return response()->json(['success' => true, 'message' => 'Kategori dan tugas terkait berhasil dihapus']);
    }

    public function exportExcel(Request $request)
    {
        $request->validate([
            'report_type' => 'nullable|in:kategori,tugas',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'tipe' => 'nullable|string',
            'tipe_turunan' => 'nullable|in:Shift 1,Shift 2',
            'status' => 'nullable|integer|in:0,1',
            'karyawan' => 'nullable|exists:karyawans,id',
        ]);

        $reportType = $request->get('report_type', 'tugas');
        $filename = 'Laporan_Tugas_' . $reportType . '_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new DaftarTugasReportExport($reportType, $request->start_date, $request->end_date, $request->tipe, $request->tipe_turunan, $request->status, $request->karyawan), $filename);
    }

    public function exportPdf(Request $request)
    {
        $request->validate([
            'report_type' => 'nullable|in:kategori,tugas',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'tipe' => 'nullable|string',
            'tipe_turunan' => 'nullable|in:Shift 1,Shift 2',
            'status' => 'nullable|integer|in:0,1',
            'karyawan' => 'nullable|exists:karyawans,id',
        ]);

        $reportType = $request->get('report_type', 'tugas');
        $user = auth()->user();

        if ($reportType === 'kategori') {
            $query = \App\Models\KategoriDaftarTugas::with('karyawan');

            if ($request->karyawan) {
                $query->where('id_user', $request->karyawan);
            }
            if ($request->tipe && $request->tipe !== 'all') {
                $query->where('Tipe', $request->tipe);
            }
            if ($request->tipe_turunan && $request->tipe_turunan !== 'all') {
                $query->where('tipe_turunan', $request->tipe_turunan);
            }
            if ($user->jabatan !== 'HRD') {
                $query->where('id_user', $user->id);
            }

            $data = $query->orderBy('Tipe')->orderBy('judul_kategori')->get();
        } else {
            $query = \App\Models\KontrolTugas::with(['kategoriDaftarTugas', 'karyawan']);

            if ($request->start_date) {
                $query->whereDate('Deadline_Date', '>=', $request->start_date);
            }
            if ($request->end_date) {
                $query->whereDate('Deadline_Date', '<=', $request->end_date);
            }
            if ($request->tipe && $request->tipe !== 'all') {
                $query->whereHas('kategoriDaftarTugas', fn($q) => $q->where('Tipe', $request->tipe));
            }
            if ($request->tipe_turunan && $request->tipe_turunan !== 'all') {
                $query->whereHas('kategoriDaftarTugas', fn($q) => $q->where('tipe_turunan', $request->tipe_turunan));
            }
            if ($request->status !== null && $request->status !== '') {
                $query->where('status', $request->status);
            }
            if ($request->karyawan) {
                $query->where('id_karyawan', $request->karyawan);
            }
            if ($user->jabatan !== 'HRD') {
                $query->where('id_karyawan', $user->id);
            }

            $data = $query->orderBy('Deadline_Date')->orderBy('created_at', 'desc')->get();
        }

        $totalTugas = $reportType === 'tugas' ? $data->count() : 0;
        $totalSelesai = $reportType === 'tugas' ? $data->where('status', 1)->count() : 0;
        $totalPending = $totalTugas - $totalSelesai;

        $pdf = Pdf::loadView('office.reports.daftar_tugas_pdf', [
            'data' => $data,
            'reportType' => $reportType,
            'startDate' => $request->start_date,
            'endDate' => $request->end_date,
            'filterTipe' => $request->tipe,
            'filterTipeTurunan' => $request->tipe_turunan,
            'filterStatus' => $request->status,
            'filterKaryawan' => $request->karyawan,
            'totalTugas' => $totalTugas,
            'totalSelesai' => $totalSelesai,
            'totalPending' => $totalPending,
            'approver' => $user->karyawan->jabatan ?? 'Manager',
        ]);

        return $pdf->stream('Laporan_Tugas_' . $reportType . '_' . date('Y-m-d') . '.pdf');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'karyawan_id' => 'nullable|exists:karyawans,id',
        ]);

        $user = auth()->user();

        if ($user->jabatan !== 'HRD' && $request->filled('karyawan_id')) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Hanya HRD yang dapat mengimport untuk karyawan lain',
                ],
                403,
            );
        }

        $targetUserId = $request->filled('karyawan_id') ? $request->karyawan_id : $user->id;
        $jabatanPembuat = $user->jabatan === 'HRD' ? Karyawan::find($targetUserId)?->jabatan : $user->jabatan;

        try {
            $import = new DaftarTugasImport($targetUserId, $jabatanPembuat);
            Excel::import($import, $request->file('file'));

            $stats = $import->getStats();

            $message = 'Import selesai. ';
            if ($stats['created'] > 0) {
                $message .= "✅ {$stats['created']} tugas baru dibuat. ";
            }
            if ($stats['skipped'] > 0) {
                $message .= "⏭️ {$stats['skipped']} baris dilewati. ";
            }

            $response = [
                'success' => true,
                'message' => trim($message),
                'stats' => $stats,
            ];

            if (!empty($stats['errors'])) {
                $response['warnings'] = array_slice($stats['errors'], 0, 10);
            }

            return response()->json($response);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => array_slice($errors, 0, 10),
                ],
                422,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Import gagal: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    private function parseBukti($bukti)
    {
        if (!$bukti) {
            return ['before' => null, 'after' => null];
        }
        try {
            if (is_string($bukti) && str_starts_with($bukti, '{')) {
                return json_decode($bukti, true);
            }
            return ['before' => $bukti, 'after' => null];
        } catch (\Exception $e) {
            return ['before' => $bukti, 'after' => null];
        }
    }
}
