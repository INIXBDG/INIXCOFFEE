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

class DaftarTugasController extends Controller
{
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
            'tipe_turunan' => 'nullable|in:Shift 1,Shift 2',
        ];

        if ($user->jabatan === 'HRD') {
            $rules['Jabatan_Pembuat'] = 'required';
        }

        $validated = $request->validate($rules);

        $id_user = $user->jabatan === 'HRD' ? Karyawan::where('jabatan', $validated['Jabatan_Pembuat'])->first()?->id : $user->id;

        $tipe_turunan = null;

        if ($validated['Tipe'] === 'Harian' && !empty($validated['tipe_turunan'])) {
            $tipe_turunan = $validated['tipe_turunan'];
        }

        $kategori = KategoriDaftarTugas::create([
            'id_user' => $id_user,
            'Tipe' => $validated['Tipe'],
            'tipe_turunan' => $tipe_turunan,
            'judul_kategori' => $validated['tugas'],
            'Jabatan_Pembuat' => $user->jabatan,
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
            ->when($tipe_turunan !== 'all', fn($q) => $q->whereHas('kategoriDaftarTugas', fn($q2) => $q2->where('tipe_turunan', $tipe_turunan)));

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

    public function aktifkanTugas(Request $request)
    {
        $request->validate(['kategori_ids' => 'required|array']);

        $user = auth()->user();
        $kategoriIds = $request->kategori_ids;
        $created = 0;
        $today = now()->toDateString();

        foreach ($kategoriIds as $katId) {
            $kategori = KategoriDaftarTugas::find($katId);
            if (!$kategori) {
                continue;
            }

            $deadline = $this->hitungDeadline($kategori->Tipe);

            $query = KontrolTugas::where('id_karyawan', $user->id)->where('id_DaftarTugas', $kategori->id);

            if ($kategori->Tipe === 'Harian') {
                $query->whereDate('Deadline_Date', $today);
            } else {
                $query->where('status', 0)->whereDate('Deadline_Date', $deadline);
            }

            if (!$query->exists()) {
                KontrolTugas::create([
                    'id_karyawan' => $user->id,
                    'id_DaftarTugas' => $kategori->id,
                    'status' => 0,
                    'Deadline_Date' => $deadline,
                ]);
                $created++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$created} tugas berhasil diaktifkan",
        ]);
    }

    private function hitungDeadline($tipe)
    {
        return match ($tipe) {
            'Harian' => now()->toDateString(),
            'Mingguan' => now()->endOfWeek()->toDateString(),
            'Bulanan' => now()->endOfMonth()->toDateString(),
            'Quartal' => now()->addMonths(3)->endOfMonth()->toDateString(),
            'Semester' => now()->addMonths(6)->endOfMonth()->toDateString(),
            'Tahunan' => now()->endOfYear()->toDateString(),
            default => now()->toDateString(),
        };
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
            'bukti_file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        $tugas = KontrolTugas::findOrFail($request->tugas_id);

        if (auth()->user()->jabatan !== 'HRD' && $tugas->id_karyawan !== auth()->id()) {
            return response()->json(['message' => 'Tidak berhak mengupload bukti ini'], 403);
        }

        if ($request->hasFile('bukti_file')) {
            if ($tugas->bukti && Storage::disk('public')->exists($tugas->bukti)) {
                Storage::disk('public')->delete($tugas->bukti);
            }

            $path = $request->file('bukti_file')->store('bukti-tugas', 'public');
            $tugas->update(['bukti' => $path]);
        }

        return response()->json(['success' => true, 'message' => 'Bukti berhasil diupload']);
    }

    public function delete($id)
    {
        $tugas = KontrolTugas::findOrFail($id);

        if (auth()->user()->jabatan !== 'HRD' && $tugas->id_karyawan !== auth()->id()) {
            return response()->json(['message' => 'Tidak berhak menghapus tugas ini'], 403);
        }

        if ($tugas->bukti && Storage::disk('public')->exists($tugas->bukti)) {
            Storage::disk('public')->delete($tugas->bukti);
        }

        $tugas->delete();

        return response()->json(['success' => true, 'message' => 'Tugas berhasil dihapus']);
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

            // Filter tanggal berdasarkan Deadline_Date (bukan created_at)
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
            // HRD bisa melihat semua, non-HRD hanya data sendiri
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
}
