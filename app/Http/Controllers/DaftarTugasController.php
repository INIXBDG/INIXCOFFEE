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
use App\Models\AbsensiKaryawan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Notifications\ShiftConfirmationNotification;
use App\Traits\ShiftGenerator;

class DaftarTugasController extends Controller
{
    use ShiftGenerator;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View DaftarTugas OB', ['only' => ['index', 'getKategori', 'get', 'getAvailableCategories', 'chartData', 'exportExcel', 'exportPdf']]);
        $this->middleware('permission:Store DaftarTugas OB', ['only' => ['store', 'importExcel']]);
        $this->middleware('permission:Aktifkan DaftarTugas OB', ['only' => ['aktifkanTugas']]);
        $this->middleware('permission:Update DaftarTugas OB Kategori', ['only' => ['updateKategori', 'bulkUpdateTipeTurunan']]);
        $this->middleware('permission:Delete DaftarTugas OB Kategori', ['only' => ['deleteKategori']]);
        $this->middleware('permission:Update DaftarTugas OB Kategori', ['only' => ['updateStatus', 'uploadBukti']]);
        $this->middleware('permission:Delete DaftarTugas OB Kategori', ['only' => ['delete', 'bulkDelete']]);
        $this->middleware('permission:Store DaftarTugas OB', ['only' => ['reorderKategori', 'reorderTugas']]);
        $this->middleware('permission:Perbaiki DaftarTugas Data OB', ['only' => ['perbaikanData', 'getForPerbaikan', 'updatePerbaikan', 'bulkUpdatePerbaikan', 'bulkSavePerbaikan']]);
    }

    public function index()
    {
        $dataKategori = KategoriDaftarTugas::with('karyawan')->orderBy('urutan')->get();
        $karyawan = Karyawan::select('id', 'nama_lengkap')->get();
        $officeBoy = Karyawan::where('jabatan', 'Office Boy')->get();
        $auth = Auth::id();

        return view('office.daftarTugas.index', compact('dataKategori', 'karyawan', 'officeBoy', 'auth'));
    }

    public function getKategori()
    {
        $dataKategori = KategoriDaftarTugas::with('karyawan')->orderBy('urutan')->get();
        return response()->json($dataKategori);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'tugas' => 'required|string|max:255',
            'Tipe' => 'required|in:Harian,Mingguan,Bulanan,Quartal,Semester,Tahunan',
            'tipe_turunan' => 'nullable',
        ]);

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

        $maxUrutan = KategoriDaftarTugas::max('urutan') ?? 0;

        $kategori = KategoriDaftarTugas::create([
            'id_user' => null,
            'Tipe' => $validated['Tipe'],
            'tipe_turunan' => $tipe_turunan,
            'judul_kategori' => $validated['tugas'],
            'Jabatan_Pembuat' => 'Office Boy',
            'urutan' => $maxUrutan + 1,
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

    public function reorderKategori(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:kategori_daftar_tugas,id',
        ]);

        $user = auth()->user();
        $updated = 0;

        foreach ($request->ids as $index => $id) {
            $kategori = KategoriDaftarTugas::find($id);
            if (!$kategori) {
                continue;
            }
            $kategori->update(['urutan' => $index + 1]);
            $updated++;
        }

        return response()->json(['success' => true, 'message' => "Urutan {$updated} kategori berhasil diperbarui"]);
    }

    public function reorderTugas(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:kontrol_tugas,id',
        ]);

        $user = auth()->user();
        $updated = 0;

        foreach ($request->ids as $index => $id) {
            $tugas = KontrolTugas::find($id);
            if (!$tugas) {
                continue;
            }
            $tugas->update(['urutan' => $index + 1]);
            $updated++;
        }

        return response()->json(['success' => true, 'message' => "Urutan {$updated} tugas berhasil disimpan"]);
    }

    public function get(Request $request)
    {
        $user = auth()->user();

        $tipe = $request->get('tipe', 'all');
        $tipe_turunan = $request->get('tipe_turunan', 'all');
        $tanggal = $request->get('tanggal', Carbon::today()->format('Y-m-d'));

        $tanggalCarbon = Carbon::parse($tanggal)->startOfDay();

        $query = KontrolTugas::with(['kategoriDaftarTugas', 'karyawan'])
            ->when($user->jabatan !== 'HRD', fn($q) => $q->where('id_karyawan', $user->id))
            ->when($tipe !== 'all', fn($q) => $q->whereHas('kategoriDaftarTugas', fn($q2) => $q2->where('Tipe', $tipe)))
            ->when($tipe_turunan !== 'all', fn($q) => $q->whereHas('kategoriDaftarTugas', fn($q2) => $q2->where('tipe_turunan', $tipe_turunan)));

        $query->where(function ($q) use ($tanggalCarbon) {
            $q->whereDate('Deadline_Date', '=', $tanggalCarbon->format('Y-m-d'));
        });

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

    public function getAvailableCategories(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();
        $todayDate = $today->day;
        $todayStr = $today->format('Y-m-d');

        $userShiftHariIni = $this->tentukanShiftHariIni($user->id, $todayStr);

        $query = KategoriDaftarTugas::with('karyawan')->when($user->jabatan !== 'HRD', function ($q) use ($user) {
            $q->where('Jabatan_Pembuat', $user->jabatan);
        });

        $kategori = $query->orderBy('urutan')->get();
        $available = [];

        foreach ($kategori as $kat) {
            $canActivate = false;
            $deadline = null;
            $reason = null;

            if ($kat->Tipe === 'Harian') {
                $canActivate = true;
                $deadline = $todayStr;

                if (!empty($kat->tipe_turunan)) {
                    if ($kat->tipe_turunan !== $userShiftHariIni) {
                        $canActivate = false;
                        $reason = "Anda terdaftar sebagai {$userShiftHariIni} hari ini, task ini untuk {$kat->tipe_turunan}";
                    }
                }
            } elseif ($kat->Tipe === 'Bulanan') {
                $targetDate = $kat->tipe_turunan ? (int) $kat->tipe_turunan : 1;
                if ($todayDate == $targetDate) {
                    $canActivate = true;
                    $deadline = $today->copy()->setDay($targetDate)->format('Y-m-d');
                } else {
                    $daysUntil = $targetDate - $todayDate;
                    $reason = "Harian tanggal {$targetDate}, hari ini tanggal {$todayDate} ({$daysUntil} hari lagi)";
                }
            } elseif ($kat->Tipe === 'Mingguan') {
                $hariMap = ['Saturday' => 'Sabtu', 'Sunday' => 'Minggu'];
                $hariIni = $today->dayName;
                $shiftHariIni = $hariMap[$hariIni] ?? null;

                $canActivate = true;
                $deadline = $this->hitungDeadlineMingguan($kat->tipe_turunan);

                if (!empty($kat->tipe_turunan) && $kat->tipe_turunan !== $shiftHariIni) {
                    $canActivate = false;
                    $reason = "Shift {$kat->tipe_turunan}, hari ini {$hariIni} ({$shiftHariIni})";
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
            'today' => $todayStr,
            'shift_hari_ini' => $userShiftHariIni,
        ]);
    }

    private function tentukanShiftHariIni($karyawanId, $today)
    {
        $absensiHariIni = AbsensiKaryawan::where('id_karyawan', $karyawanId)->where('tanggal', $today)->first();

        if ($absensiHariIni && !empty($absensiHariIni->shift)) {
            return $absensiHariIni->shift == 1 ? 'Shift 1' : 'Shift 2';
        }

        $tugasAktif = KontrolTugas::where('id_karyawan', $karyawanId)
            ->whereDate('Deadline_Date', $today)
            ->whereHas('kategoriDaftarTugas', function ($q) {
                $q->where('Tipe', 'Harian')->whereIn('tipe_turunan', ['Shift 1', 'Shift 2']);
            })
            ->with('kategoriDaftarTugas')
            ->first();

        if ($tugasAktif) {
            return $tugasAktif->kategoriDaftarTugas->tipe_turunan;
        }

        $absensiKemarin = AbsensiKaryawan::where('id_karyawan', $karyawanId)
            ->where('tanggal', Carbon::yesterday('Asia/Jakarta')->toDateString())
            ->first();

        if ($absensiKemarin && !empty($absensiKemarin->shift)) {
            return $absensiKemarin->shift == 1 ? 'Shift 1' : 'Shift 2';
        }

        return 'Shift 1';
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

        if ($user->jabatan !== 'Office Boy') {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Hanya Office Boy yang bisa mengaktifkan tugas untuk dirinya sendiri.',
                ],
                403,
            );
        }

        $kategoriIds = $request->kategori_ids;
        $created = 0;
        $skipped = 0;
        $errors = [];
        $today = Carbon::today();
        $todayStr = $today->format('Y-m-d');

        $userShift = $this->tentukanShiftHariIni($user->id, $todayStr);

        foreach ($kategoriIds as $katId) {
            $kategori = KategoriDaftarTugas::find($katId);
            if (!$kategori) {
                $errors[] = "Kategori ID {$katId} tidak ditemukan";
                continue;
            }

            if ($kategori->Tipe === 'Harian' && !empty($kategori->tipe_turunan)) {
                if ($kategori->tipe_turunan !== $userShift) {
                    $errors[] = "Task '{$kategori->judul_kategori}' untuk {$kategori->tipe_turunan}, Anda shift {$userShift}";
                    continue;
                }
            }

            if ($kategori->Tipe === 'Mingguan' && !empty($kategori->tipe_turunan)) {
                $hariMap = ['Saturday' => 'Sabtu', 'Sunday' => 'Minggu'];
                $hariIni = $today->dayName;
                $shiftHariIni = $hariMap[$hariIni] ?? null;

                if ($kategori->tipe_turunan !== $shiftHariIni) {
                    $errors[] = "Task '{$kategori->judul_kategori}' untuk {$kategori->tipe_turunan}, hari ini {$hariIni}";
                    continue;
                }
            }

            $deadline = $this->hitungDeadline($kategori->Tipe, $kategori->tipe_turunan);
            if ($kategori->Tipe === 'Harian') {
                $deadline = $todayStr;
            }

            $exists = KontrolTugas::where('id_karyawan', $user->id)->where('id_DaftarTugas', $kategori->id)->whereDate('Deadline_Date', $deadline)->exists();

            if ($exists) {
                $skipped++;
                $errors[] = "Task '{$kategori->judul_kategori}' sudah aktif untuk deadline {$deadline}";
                continue;
            }

            $nextUrutan = (KontrolTugas::max('urutan') ?? 0) + 1;

            KontrolTugas::create([
                'id_karyawan' => $user->id,
                'id_DaftarTugas' => $kategori->id,
                'status' => 0,
                'Deadline_Date' => $deadline,
                'urutan' => $nextUrutan,
            ]);
            $created++;
        }

        $message = "{$created} tugas berhasil diaktifkan";
        if ($skipped > 0) {
            $message .= ", {$skipped} sudah ada sebelumnya";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
        ]);
    }

    private function hitungDeadline($tipe, $tipe_turunan = null)
    {
        $now = Carbon::now();

        return match ($tipe) {
            'Harian' => $now->format('Y-m-d'),
            'Mingguan' => $this->hitungDeadlineMingguan($tipe_turunan),
            'Bulanan' => $this->hitungDeadlineBulanan($tipe_turunan),
            'Quartal' => $now->copy()->addMonths(3)->endOfMonth()->format('Y-m-d'),
            'Semester' => $now->copy()->addMonths(6)->endOfMonth()->format('Y-m-d'),
            'Tahunan' => $now->copy()->endOfYear()->format('Y-m-d'),
            default => $now->format('Y-m-d'),
        };
    }

    private function hitungDeadlineMingguan($shift = null)
    {
        $now = Carbon::now();

        if ($shift === 'Sabtu') {
            return $now->copy()->next(Carbon::SATURDAY)->format('Y-m-d');
        }

        if ($shift === 'Minggu') {
            return $now->copy()->next(Carbon::SUNDAY)->format('Y-m-d');
        }

        return $now->copy()->endOfWeek()->format('Y-m-d');
    }

    private function hitungDeadlineBulanan($tanggal = null)
    {
        $now = Carbon::now();
        $targetDate = $tanggal ? (int) $tanggal : 1;

        if ($now->day > $targetDate) {
            return $now->copy()->addMonth()->setDay($targetDate)->format('Y-m-d');
        }

        return $now->copy()->setDay($targetDate)->format('Y-m-d');
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:kontrol_tugas,id',
            'status' => 'required|in:0,1',
        ]);

        $tugas = KontrolTugas::findOrFail($request->id);

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

        $buktiData = $this->parseBukti($tugas->bukti);

        if ($request->hasFile('bukti_before')) {
            if ($buktiData['before'] && Storage::disk('public')->exists($buktiData['before'])) {
                Storage::disk('public')->delete($buktiData['before']);
            }
            $buktiData['before'] = $request->file('bukti_before')->store('bukti-tugas', 'public');
        }

        if ($request->hasFile('bukti_after')) {
            if (empty($buktiData['before'])) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Foto Before wajib diupload terlebih dahulu sebelum mengupload Foto After',
                    ],
                    422,
                );
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
            'bukti' => $buktiData,
        ]);
    }

    public function delete($id)
    {
        $tugas = KontrolTugas::findOrFail($id);

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

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids ?? [];
        $tasks = KontrolTugas::whereIn('id', $ids)->get();

        foreach ($tasks as $tugas) {
            $buktiData = $this->parseBukti($tugas->bukti);

            if ($buktiData['before'] && Storage::disk('public')->exists($buktiData['before'])) {
                Storage::disk('public')->delete($buktiData['before']);
            }

            if ($buktiData['after'] && Storage::disk('public')->exists($buktiData['after'])) {
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
            $tipe = $request->tipe;
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
        ]);

        return response()->json(['success' => true, 'message' => 'Kategori berhasil diperbarui']);
    }

    public function getPendingShift()
    {
        $karyawan = auth()->user()->karyawan;

        if (!$karyawan) return response()->json(['pending' => false]);

        $notification = $karyawan->unreadNotifications()
            ->where('type', ShiftConfirmationNotification::class)
            ->latest('created_at')
            ->first();

        if (!$notification) return response()->json(['pending' => false]);

        return response()->json([
            'pending'         => true,
            'notification_id' => $notification->id,
            'shift'           => $notification->data['shift'],
            'date'            => $notification->data['date'],
            'message'         => $notification->data['message'] ?? 'Apakah Anda setuju mengambil shift ini?',
        ]);
    }

    public function approveShift(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|string',
            'shift' => 'required|in:1,2',
            'date'  => 'required|date',
        ]);

        $karyawan  = auth()->user()->karyawan;
        $shiftName = 'Shift ' . $request->shift;

        $taken = KontrolTugas::whereDate('Deadline_Date', $request->date)
            ->whereHas('KategoriDaftarTugas', fn($q) => $q->where('tipe_turunan', $shiftName))
            ->where('id_karyawan', '!=', $karyawan->id)
            ->exists();

        if ($taken) {
            $karyawan->notifications()->where('id', $request->notification_id)->delete();
            return response()->json(['success' => false, 'message' => "Maaf, {$shiftName} sudah diambil rekan lain."], 422);
        }

        $this->generateTasksForShift($karyawan->id, $shiftName, $request->date);

        $karyawan->notifications()->where('id', $request->notification_id)->delete();

        return response()->json(['success' => true, 'message' => "{$shiftName} berhasil diambil!"]);
    }

    public function rejectShift(Request $request)
    {
        auth()->user()->karyawan
            ->notifications()
            ->where('id', $request->notification_id)
            ->delete();

        return response()->json(['success' => true]);
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
            $kategori->update(['tipe_turunan' => $request->tipe_turunan]);
            $updated++;
        }

        return response()->json(['success' => true, 'message' => "{$updated} kategori berhasil diperbarui"]);
    }

    public function deleteKategori(Request $request)
    {
        $request->validate(['id' => 'required|exists:kategori_daftar_tugas,id']);
        $kategori = KategoriDaftarTugas::findOrFail($request->id);

        KontrolTugas::where('id_DaftarTugas', $kategori->id)
            ->get()
            ->each(function ($tugas) {
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

            $data = $query->orderBy('Deadline_Date')->orderBy('urutan')->orderBy('id', 'asc')->get();
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

        $targetUserId = $request->filled('karyawan_id') ? $request->karyawan_id : $user->id;
        $jabatanPembuat = Karyawan::find($targetUserId)?->jabatan;

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
                $decoded = json_decode($bukti, true);
                return is_array($decoded) ? $decoded + ['before' => null, 'after' => null] : ['before' => $bukti, 'after' => null];
            }
            return ['before' => $bukti, 'after' => null];
        } catch (\Exception $e) {
            return ['before' => $bukti, 'after' => null];
        }
    }

    public function perbaikanData()
    {
        $officeBoy = Karyawan::where('jabatan', 'Office Boy')->select('id', 'nama_lengkap')->get();
        $kategori = KategoriDaftarTugas::select('id', 'judul_kategori', 'Tipe', 'tipe_turunan')->orderBy('Tipe')->orderBy('judul_kategori')->get();

        return view('office.daftarTugas.perbaikanData', compact('officeBoy', 'kategori'));
    }

    public function getForPerbaikan(Request $request)
    {
        $hasFilter = $request->filled('start_date') 
                || $request->filled('end_date')
                || ($request->filled('tipe') && $request->tipe !== 'all')
                || ($request->filled('kategori') && $request->kategori !== 'all')
                || ($request->filled('karyawan') && $request->karyawan !== 'all');

        if (!$hasFilter) {
            return response()->json([
                'data' => [],
                'requires_filter' => true,
                'message' => 'Silakan isi minimal satu filter (tanggal/tipe/kategori/OB) untuk memuat data.',
            ]);
        }

        $perPage = min((int) $request->get('per_page', 50), 200);
        $page    = max((int) $request->get('page', 1), 1);

        $query = KontrolTugas::with(['kategoriDaftarTugas', 'karyawan']);

        if ($request->filled('start_date')) {
            $query->whereDate('Deadline_Date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('Deadline_Date', '<=', $request->end_date);
        }
        if ($request->filled('tipe') && $request->tipe !== 'all') {
            $query->whereHas('kategoriDaftarTugas', fn($q) => $q->where('Tipe', $request->tipe));
        }
        if ($request->filled('kategori') && $request->kategori !== 'all') {
            $query->where('id_DaftarTugas', $request->kategori);
        }
        if ($request->filled('karyawan') && $request->karyawan !== 'all') {
            $request->karyawan === 'null' 
                ? $query->whereNull('id_karyawan') 
                : $query->where('id_karyawan', $request->karyawan);
        }

        $data = $query->orderBy('Deadline_Date')
                    ->orderBy('urutan')
                    ->orderBy('id', 'desc')
                    ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $data->items(),
            'pagination' => [
                'total'        => $data->total(),
                'per_page'     => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'has_more'     => $data->hasMorePages(),
            ],
            'requires_filter' => false,
        ]);
    }

    public function confirmShift(Request $request)
    {
        $request->validate([
            'shift' => 'required|in:1,2',
            'date' => 'required|date',
        ]);

        $user = auth()->user();
        $karyawan = $user->karyawan;
        $shiftName = 'Shift ' . $request->shift;
        $date = $request->date;

        $shiftTaken = KontrolTugas::whereDate('Deadline_Date', $date)
            ->whereHas('KategoriDaftarTugas', fn($q) => $q->where('tipe_turunan', $shiftName))
            ->exists();

        if ($shiftTaken) {
            return response()->json(['success' => false, 'message' => "Maaf, {$shiftName} sudah diambil oleh karyawan lain."], 422);
        }

        $userHasShift = KontrolTugas::where('id_karyawan', $karyawan->id)
            ->whereDate('Deadline_Date', $date)
            ->whereHas('KategoriDaftarTugas', fn($q) => $q->where('Tipe', 'Harian'))
            ->exists();

        if ($userHasShift) {
            return response()->json(['success' => false, 'message' => "Anda sudah memiliki tugas shift hari ini."], 422);
        }

        $this->generateTasksForShift($karyawan->id, $shiftName, $date);

        $user->unreadNotifications->where('type', 'App\Notifications\ShiftConfirmationNotification')->markAsRead();

        return response()->json(['success' => true, 'message' => "{$shiftName} berhasil diambil dan tugas telah digenerate."]);
    }

    public function updatePerbaikan(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:kontrol_tugas,id',
            'deadline_date' => 'nullable|date',
            'id_karyawan' => 'nullable|exists:karyawans,id',
            'tipe' => 'nullable|in:Harian,Mingguan,Bulanan,Quartal,Semester,Tahunan',
            'tipe_turunan' => 'nullable|string|max:255',
        ]);

        $tugas = KontrolTugas::findOrFail($validated['id']);
        $kategori = $tugas->kategoriDaftarTugas;

        $updateTugas = [];
        if ($request->filled('deadline_date')) {
            $updateTugas['Deadline_Date'] = $validated['deadline_date'];
        }
        if ($request->filled('id_karyawan')) {
            $updateTugas['id_karyawan'] = $validated['id_karyawan'];
        }

        $updateKategori = [];
        if ($request->filled('tipe')) {
            $updateKategori['Tipe'] = $validated['tipe'];
        }
        if ($request->has('tipe_turunan')) {
            $updateKategori['tipe_turunan'] = $validated['tipe_turunan'];
        }

        if (empty($updateTugas) && empty($updateKategori)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada perubahan yang dikirim'], 422);
        }

        if (!empty($updateTugas)) {
            $tugas->update($updateTugas);
        }
        if (!empty($updateKategori)) {
            $kategori->update($updateKategori);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbaiki',
            'data' => $tugas->fresh(['kategoriDaftarTugas', 'karyawan']),
        ]);
    }

    public function bulkUpdatePerbaikan(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:kontrol_tugas,id',
            'deadline_date' => 'nullable|date',
            'id_karyawan' => 'nullable|exists:karyawans,id',
            'tipe' => 'nullable|in:Harian,Mingguan,Bulanan,Quartal,Semester,Tahunan',
            'tipe_turunan' => 'nullable|string|max:255',
        ]);

        $updateTugas = [];
        if ($request->filled('deadline_date')) {
            $updateTugas['Deadline_Date'] = $validated['deadline_date'];
        }
        if ($request->filled('id_karyawan')) {
            $updateTugas['id_karyawan'] = $validated['id_karyawan'];
        }

        $updateKategori = [];
        if ($request->filled('tipe')) {
            $updateKategori['Tipe'] = $validated['tipe'];
        }
        if ($request->has('tipe_turunan')) {
            $updateKategori['tipe_turunan'] = $validated['tipe_turunan'];
        }

        if (empty($updateTugas) && empty($updateKategori)) {
            return response()->json(['success' => false, 'message' => 'Isi minimal satu field untuk diubah'], 422);
        }

        $countTugas = 0;
        if (!empty($updateTugas)) {
            $countTugas = KontrolTugas::whereIn('id', $validated['ids'])->update($updateTugas);
        }

        $countKategori = 0;
        if (!empty($updateKategori)) {
            $kategoriIds = KontrolTugas::whereIn('id', $validated['ids'])->pluck('id_DaftarTugas')->unique();
            $countKategori = KategoriDaftarTugas::whereIn('id', $kategoriIds)->update($updateKategori);
        }

        return response()->json([
            'success' => true,
            'message' => "{$countTugas} tugas dan {$countKategori} kategori berhasil diperbarui",
        ]);
    }

    public function bulkSavePerbaikan(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:kontrol_tugas,id',
            'items.*.deadline_date' => 'nullable|date',
            'items.*.id_karyawan' => 'nullable|exists:karyawans,id',
            'items.*.tipe' => 'nullable|in:Harian,Mingguan,Bulanan,Quartal,Semester,Tahunan',
            'items.*.tipe_turunan' => 'nullable|string|max:255',
        ]);

        $updated = 0;
        $skipped = 0;
        $updatedCategories = [];

        foreach ($validated['items'] as $item) {
            $tugas = KontrolTugas::find($item['id']);
            if (!$tugas) {
                continue;
            }

            $updateTugas = [];
            if (!empty($item['deadline_date'])) {
                $updateTugas['Deadline_Date'] = $item['deadline_date'];
            }
            if (isset($item['id_karyawan']) && $item['id_karyawan'] !== '') {
                $updateTugas['id_karyawan'] = $item['id_karyawan'];
            }

            $updateKategori = [];
            if (!empty($item['tipe'])) {
                $updateKategori['Tipe'] = $item['tipe'];
            }
            if (isset($item['tipe_turunan'])) {
                $updateKategori['tipe_turunan'] = $item['tipe_turunan'];
            }

            if (empty($updateTugas) && empty($updateKategori)) {
                $skipped++;
                continue;
            }

            if (!empty($updateTugas)) {
                $tugas->update($updateTugas);
            }

            if (!empty($updateKategori)) {
                $kategori = $tugas->kategoriDaftarTugas;
                // Mencegah update kategori yang sama berulang kali dalam 1 batch
                if (!in_array($kategori->id, $updatedCategories)) {
                    $kategori->update($updateKategori);
                    $updatedCategories[] = $kategori->id;
                }
            }
            $updated++;
        }

        $message = "{$updated} tugas berhasil diperbarui";
        if ($skipped > 0) {
            $message .= ", {$skipped} dilewati (tidak ada perubahan)";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'updated' => $updated,
            'skipped' => $skipped,
        ]);
    }

    public function checkPendingShift()
    {
        $karyawanId = auth()->user()->karyawan->id ?? null;
        if (!$karyawanId) return response()->json(['pending' => false]);

        $pending = Cache::get("pending_shift_{$karyawanId}");

        if ($pending) {
            return response()->json([
                'pending' => true,
                'shift' => $pending['shift'],
                'date' => $pending['date'],
                'message' => $pending['message']
            ]);
        }

        return response()->json(['pending' => false]);
    }
}
