<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\KategoriDaftarTugas;
use App\Models\KontrolTugas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
            'id_user' => $validated['jabatan_pembuat'] ?? $user->jabatan,
            'Tipe' => $validated['Tipe'],
            'judul_kategori' => $validated['tugas'],
            'id_user' => $id_user,
            'jabatan_pembuat' => $user->jabatan,
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
        $user = Auth::user();
        $tipe = $request->get('tipe', 'Harian');
        $tanggal = $request->get('tanggal', today()->format('Y-m-d'));

        $query = KontrolTugas::with(['kategoriDaftarTugas', 'karyawan'])
            ->when($user->jabatan !== 'HRD', fn($q) => $q->where('id_karyawan', $user->id))
            ->whereHas('kategoriDaftarTugas', fn($q) => $q->where('Tipe', $tipe));

        // Filter berdasarkan deadline, bukan created_at
        $query->whereDate('Deadline_Date', $tanggal);

        $data = $query->latest()->get();

        return response()->json([
            'data' => $data,
            'filter' => ['tipe' => $tipe, 'tanggal' => $tanggal],
        ]);
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
            // Hapus file lama jika ada
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

        // Hapus file bukti jika ada
        if ($tugas->bukti && Storage::disk('public')->exists($tugas->bukti)) {
            Storage::disk('public')->delete($tugas->bukti);
        }

        $tugas->delete();

        return response()->json(['success' => true, 'message' => 'Tugas berhasil dihapus']);
    }

    private function createOrSkipTugas($kategori, $userId, $startDate, $endDate, $deadline)
    {
        $exists = KontrolTugas::where('id_karyawan', $userId)
            ->where('id_DaftarTugas', $kategori->id)
            ->whereBetween('Deadline_Date', [$startDate, $endDate])
            ->exists();

        if (!$exists) {
            KontrolTugas::create([
                'id_karyawan' => $userId,
                'id_DaftarTugas' => $kategori->id,
                'status' => 0,
                'Deadline_Date' => $deadline,
            ]);
        }
    }

    public function UpdateTugasHarian()
    {
        $auth = auth()->user();

        $dataKategori = KategoriDaftarTugas::where('Tipe', 'Harian')->where('id_user', $auth->id)->get();

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

        return back()->with('success', 'Berhasil mengupdate tugas harian');
    }
    public function UpdateTugasMingguan()
    {
        $auth = auth()->user();

        $dataKategori = KategoriDaftarTugas::where('Tipe', 'Mingguan')->where('id_user', $auth->id)->get();

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

        return back()->with('success', 'Berhasil mengupdate tugas mingguan');
    }
    public function UpdateTugasBulanan()
    {
        $auth = auth()->user();

        $dataKategori = KategoriDaftarTugas::where('Tipe', 'Bulanan')->where('id_user', $auth->id)->get();

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

        return back()->with('success', 'Berhasil mengupdate tugas bulanan');
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
}
