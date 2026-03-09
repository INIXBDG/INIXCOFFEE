<?php

namespace App\Http\Controllers;

use App\Models\KategoriDaftarTugas;
use App\Models\KontrolTugas;
use Carbon\Carbon;
use Google\Service\ServiceControl\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DaftarTugasController extends Controller
{
    public function index()
    {
        $dataKategori = KategoriDaftarTugas::all();
        return view('office.daftarTugas.index', compact('dataKategori'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tugas' => 'required|string',
            'Tipe' => 'required|string',
        ]);

        $data = new KategoriDaftarTugas();
        $data->Jabatan_Pembuat = 'Office Boy';
        $data->Tipe = $request->Tipe;
        $data->judul_kategori = $request->tugas;
        $data->save();

        return back()->with(['success', 'berhasil menambahkan kategori tugas']);
    }

    public function get(Request $request)
    {
        $auth = auth()->user();

        $query = KontrolTugas::with(['KategoriDaftarTugas', 'karyawan'])->where('id_karyawan', $auth->id);

        if ($request->filled('tipe')) {
            $query->whereHas('KategoriDaftarTugas', function ($q) use ($request) {
                $q->where('Tipe', $request->tipe);
            });
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        } else {
            $query->whereDate('created_at', now());
        }

        $data = $query->get();

        return response()->json([
            'data' => $data,
            'filter' => [
                'tipe' => $request->tipe ?? 'Harian',
                'tanggal' => $request->tanggal ?? now()->format('Y-m-d'),
            ],
        ]);
    }

    public function updateStatus(Request $request)
    {
        $data = KontrolTugas::where('id', $request->id)->first();

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ]);
        }

        $data->status = $data->status == 0 ? 1 : 0;
        $data->save();

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diupdate',
            'status' => $data->status,
        ]);
    }

    public function uploadBukti(Request $request)
    {
        $id = $request->tugas_id;
        $request->validate(
            [
                'bukti_file' => 'required',
            ],
            [
                'bukti_file.required' => 'File bukti wajib diupload.',
            ],
        );

        $tugas = KontrolTugas::findOrFail($id);

        if ($request->hasFile('bukti_file')) {
            $file = $request->file('bukti_file');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            $path = $file->storeAs('bukti-tugas', $fileName, 'public');

            $tugas->update([
                'bukti' => $path,
            ]);
        }

        return response()->json([
            'message' => 'Bukti berhasil diupload',
        ]);
    }

    public function delete($id)
    {
        $dataKontrol = KontrolTugas::where('id', $id)->first();
        $dataKontrol->delete();

        return response()->json(['success', 'berhasil menghapus data']);
    }

    public function UpdateTugasHarian()
    {
        $auth = Auth()->user();

        $dataKategori = KategoriDaftarTugas::where('Jabatan_Pembuat', $auth->jabatan)->where('Tipe', 'Harian')->get();

        foreach ($dataKategori as $data) {
            $exists = KontrolTugas::where('id_karyawan', $auth->id)->where('id_DaftarTugas', $data->id)->whereDate('created_at', now())->exists();

            if (!$exists) {
                $KontrolTugas = new KontrolTugas();
                $KontrolTugas->id_karyawan = $auth->id;
                $KontrolTugas->id_DaftarTugas = $data->id;
                $KontrolTugas->status = 0;
                $KontrolTugas->Deadline_Date = now()->toDateString();
                $KontrolTugas->save();
            }
        }

        return response()->json(['success' => true, 'message' => 'berhasil mengupdate data']);
    }

    public function UpdateTugasMingguan()
    {
        $auth = Auth()->user();

        $dataKategori = KategoriDaftarTugas::where('Jabatan_Pembuat', $auth->jabatan)->where('Tipe', 'Mingguan')->get();

        foreach ($dataKategori as $data) {
            $exists = KontrolTugas::where('id_karyawan', $auth->id)
                ->where('id_DaftarTugas', $data->id)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->exists();

            if (!$exists) {
                KontrolTugas::create([
                    'id_karyawan' => $auth->id,
                    'id_DaftarTugas' => $data->id,
                    'status' => 0,
                    'Deadline_Date' => now()->endOfWeek()->toDateString(),
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'berhasil mengupdate data']);
    }

    public function UpdateTugasBulanan()
    {
        $auth = Auth()->user();

        $dataKategori = KategoriDaftarTugas::where('Jabatan_Pembuat', $auth->jabatan)->where('Tipe', 'Bulanan')->get();

        foreach ($dataKategori as $data) {
            $exists = KontrolTugas::where('id_karyawan', $auth->id)->where('id_DaftarTugas', $data->id)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->exists();

            if (!$exists) {
                KontrolTugas::create([
                    'id_karyawan' => $auth->id,
                    'id_DaftarTugas' => $data->id,
                    'status' => 0,
                    'Deadline_Date' => now()->endOfMonth()->toDateString(),
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'berhasil mengupdate data']);
    }

    public function UpdateTugasQuartal()
    {
        $auth = Auth()->user();

        $dataKategori = KategoriDaftarTugas::where('Jabatan_Pembuat', $auth->jabatan)->where('Tipe', 'Quartal')->get();

        foreach ($dataKategori as $data) {
            $exists = KontrolTugas::where('id_karyawan', $auth->id)
                ->where('id_DaftarTugas', $data->id)
                ->whereBetween('created_at', [now()->startOfQuarter(), now()->endOfQuarter()])
                ->exists();

            if (!$exists) {
                KontrolTugas::create([
                    'id_karyawan' => $auth->id,
                    'id_DaftarTugas' => $data->id,
                    'status' => 0,
                    'Deadline_Date' => now()->endOfQuarter()->toDateString(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'berhasil mengupdate data',
        ]);
    }

    public function UpdateTugasSemester()
    {
        $auth = Auth()->user();

        $dataKategori = KategoriDaftarTugas::where('Jabatan_Pembuat', $auth->jabatan)->where('Tipe', 'Semester')->get();

        $now = now();

        $semesterStart = $now->month <= 6 ? $now->copy()->startOfYear() : $now->copy()->month(7)->startOfMonth();

        $semesterEnd = $now->month <= 6 ? $now->copy()->month(6)->endOfMonth() : $now->copy()->endOfYear();

        foreach ($dataKategori as $data) {
            $exists = KontrolTugas::where('id_karyawan', $auth->id)
                ->where('id_DaftarTugas', $data->id)
                ->whereBetween('created_at', [$semesterStart, $semesterEnd])
                ->exists();

            if (!$exists) {
                KontrolTugas::create([
                    'id_karyawan' => $auth->id,
                    'id_DaftarTugas' => $data->id,
                    'status' => 0,
                    'Deadline_Date' => $semesterEnd->toDateString(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'berhasil mengupdate data',
        ]);
    }

    public function UpdateTugasTahunan()
    {
        $auth = Auth()->user();

        $dataKategori = KategoriDaftarTugas::where('Jabatan_Pembuat', $auth->jabatan)->where('Tipe', 'Tahunan')->get();

        foreach ($dataKategori as $data) {
            $exists = KontrolTugas::where('id_karyawan', $auth->id)->where('id_DaftarTugas', $data->id)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->exists();

            if (!$exists) {
                KontrolTugas::create([
                    'id_karyawan' => $auth->id,
                    'id_DaftarTugas' => $data->id,
                    'status' => 0,
                    'Deadline_Date' => now()->endOfYear()->toDateString(),
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'berhasil mengupdate data']);
    }
}
