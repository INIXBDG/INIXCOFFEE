<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\KategoriDaftarTugas;
use App\Models\KontrolTugas;
use Carbon\Carbon;
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
        $user = Auth::user();
        $dataKategori = KategoriDaftarTugas::with('karyawan')
            ->when($user->jabatan !== 'HRD', fn($q) => $q->where('id_user', $user->id))
            ->get();
        $karyawan = Karyawan::select('id', 'nama_lengkap')->get();
        $officeBoy = Karyawan::where('jabatan', 'Office Boy')->get();
        $auth = Auth()->user()->id;
        return view('office.daftarTugas.index', compact('dataKategori', 'karyawan', 'officeBoy', 'auth'));
    }

    public function getKategori()
    {
        $user = Auth::user();
        $dataKategori = KategoriDaftarTugas::with('karyawan')
            ->when($user->jabatan !== 'HRD', function ($q) use ($user) {
                $q->where(function ($query) use ($user) {
                    $query->where('id_user', $user->id)->orWhereNull('id_user');
                });
            })
            ->get();
        return response()->json($dataKategori);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $rules = [
            'tugas' => 'required|string|max:255',
            'Tipe' => 'required|in:Harian,Mingguan,Bulanan,Quartal,Semester,Tahunan',
        ];
        if ($user->jabatan === 'HRD') {
            $rules['jabatan_pembuat'] = 'required';
        }
        $validated = $request->validate($rules);
        $id_user = $user->jabatan === 'HRD' ? Karyawan::where('jabatan', $validated['jabatan_pembuat'])->first()?->id : $user->id;
        $kategori = KategoriDaftarTugas::create([
            'id_user' => $id_user,
            'Tipe' => $validated['Tipe'],
            'judul_kategori' => $validated['tugas'],
            'jabatan_pembuat' => $user->jabatan,
        ]);
        return response()->json(['success' => true, 'message' => 'Kategori berhasil ditambahkan', 'data' => $kategori], 201);
    }

    public function get(Request $request)
    {
        $user = Auth::user();
        $tipe = $request->get('tipe', 'all');
        $tanggal = $request->get('tanggal', today()->format('Y-m-d'));
        $query = KontrolTugas::with(['kategoriDaftarTugas', 'karyawan'])
            ->when($user->jabatan !== 'HRD', fn($q) => $q->where('id_karyawan', $user->id))
            ->when($tipe !== 'all', fn($q) => $q->whereHas('kategoriDaftarTugas', fn($q2) => $q2->where('Tipe', $tipe)));
        $query->whereDate('Deadline_Date', $tanggal);
        $data = $query->latest()->get();
        return response()->json(['data' => $data, 'filter' => ['tipe' => $tipe, 'tanggal' => $tanggal]]);
    }

    public function aktifkanTugas(Request $request)
    {
        $request->validate(['kategori_ids' => 'required|array']);
        $user = Auth::user();
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

            $exists = $query->exists();

            if (!$exists) {
                KontrolTugas::create([
                    'id_karyawan' => $user->id,
                    'id_DaftarTugas' => $kategori->id,
                    'status' => 0,
                    'Deadline_Date' => $deadline,
                ]);
                $created++;
            }
        }

        return response()->json(['success' => true, 'message' => "{$created} tugas berhasil diaktifkan"]);
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
        $request->validate(['id' => 'required|exists:kontrol_tugas,id', 'status' => 'required|in:0,1']);
        $tugas = KontrolTugas::findOrFail($request->id);
        if (Auth::user()->jabatan !== 'HRD' && $tugas->id_karyawan !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Tidak berhak mengubah status ini'], 403);
        }
        $tugas->update(['status' => (int) $request->status]);
        return response()->json(['success' => true, 'message' => 'Status berhasil diperbarui', 'status' => $tugas->status]);
    }

    public function uploadBukti(Request $request)
    {
        $request->validate([
            'tugas_id' => 'required|exists:kontrol_tugas,id',
            'bukti_file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);
        $tugas = KontrolTugas::findOrFail($request->tugas_id);
        if (Auth::user()->jabatan !== 'HRD' && $tugas->id_karyawan !== Auth::id()) {
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
        if (Auth::user()->jabatan !== 'HRD' && $tugas->id_karyawan !== Auth::id()) {
            return response()->json(['message' => 'Tidak berhak menghapus tugas ini'], 403);
        }
        if ($tugas->bukti && Storage::disk('public')->exists($tugas->bukti)) {
            Storage::disk('public')->delete($tugas->bukti);
        }
        $tugas->delete();
        return response()->json(['success' => true, 'message' => 'Tugas berhasil dihapus']);
    }

    public function updateKategori(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:kategori_daftar_tugas,id',
            'judul_kategori' => 'required|string|max:255',
            'tipe' => 'required|in:Harian,Mingguan,Bulanan,Quartal,Semester,Tahunan',
        ]);
        $kategori = KategoriDaftarTugas::findOrFail($request->id);
        if (Auth::id() !== $kategori->id_user && Auth::user()->jabatan !== 'HRD') {
            return response()->json(['message' => 'Tidak berhak mengedit kategori ini'], 403);
        }
        $kategori->update([
            'judul_kategori' => $request->judul_kategori,
            'Tipe' => $request->tipe,
            'id_user' => Auth()->user()->id,
        ]);
        return response()->json(['success' => true, 'message' => 'Kategori berhasil diperbarui']);
    }

    public function deleteKategori(Request $request)
    {
        $request->validate(['id' => 'required|exists:kategori_daftar_tugas,id']);
        $kategori = KategoriDaftarTugas::findOrFail($request->id);
        if (Auth::id() !== $kategori->id_user && Auth::user()->jabatan !== 'HRD') {
            return response()->json(['message' => 'Tidak berhak menghapus kategori ini'], 403);
        }
        KontrolTugas::where('id_DaftarTugas', $kategori->id)->each(function ($tugas) {
            if ($tugas->bukti && Storage::disk('public')->exists($tugas->bukti)) {
                Storage::disk('public')->delete($tugas->bukti);
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
            'status' => 'nullable|integer|in:0,1',
            'karyawan' => 'nullable|exists:karyawans,id',
        ]);

        $reportType = $request->get('report_type', 'tugas');
        $filename = 'Laporan_Tugas_' . $reportType . '_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new DaftarTugasReportExport($reportType, $request->start_date, $request->end_date, $request->tipe, $request->status, $request->karyawan), $filename);
    }

    public function exportPdf(Request $request)
    {
        $request->validate([
            'report_type' => 'nullable|in:kategori,tugas',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'tipe' => 'nullable|string',
            'status' => 'nullable|integer|in:0,1',
            'karyawan' => 'nullable|exists:karyawans,id',
        ]);

        $reportType = $request->get('report_type', 'tugas');

        if ($reportType === 'kategori') {
            $query = \App\Models\KategoriDaftarTugas::with('karyawan');

            if ($request->karyawan) {
                $query->where('id_user', $request->karyawan);
            }
            if ($request->tipe && $request->tipe !== 'all') {
                $query->where('Tipe', $request->tipe);
            }
            if (Auth::user()->jabatan !== 'HRD') {
                $query->where('id_user', Auth::id());
            }

            $data = $query->orderBy('Tipe')->orderBy('judul_kategori')->get();
        } else {
            $query = KontrolTugas::with(['kategoriDaftarTugas', 'karyawan']);

            if ($request->start_date) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->end_date) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }
            if ($request->tipe && $request->tipe !== 'all') {
                $query->whereHas('kategoriDaftarTugas', fn($q) => $q->where('Tipe', $request->tipe));
            }
            if ($request->status !== null) {
                $query->where('status', $request->status);
            }
            if ($request->karyawan) {
                $query->where('id_karyawan', $request->karyawan);
            }
            if (Auth::user()->jabatan !== 'HRD') {
                $query->where('id_karyawan', Auth::id());
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
            'filterStatus' => $request->status,
            'filterKaryawan' => $request->karyawan,
            'totalTugas' => $totalTugas,
            'totalSelesai' => $totalSelesai,
            'totalPending' => $totalPending,
            'approver' => auth()->user()->karyawan->jabatan ?? 'Manager',
        ]);

        return $pdf->stream('Laporan_Tugas_' . $reportType . '_' . date('Y-m-d') . '.pdf');
    }
}
