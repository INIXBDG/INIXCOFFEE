<?php

namespace App\Http\Controllers;

use App\Models\detailPengajuanBarang;
use App\Models\tracking_pengajuan_barang;
use App\Models\Karyawan;
use App\Models\Materi;
use App\Models\Module;
use App\Models\PengajuanBarang;
use App\Models\PengajuanKlaimModul;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KlaimModulController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        if (!$user || !$user->karyawan) {
            return view('auth.login');
        }
        $jabatan = $user->karyawan->jabatan;
        return view('pengajuanklaimmodul.index', compact('jabatan'));
    }

    public function getKlaimModul($month, $year)
    {
        $user = auth()->user();
        $karyawan = $user->karyawan;
        $jabatan = $karyawan->jabatan;
        $divisi = $karyawan->divisi;

        $commonWith = [
            'module.karyawan:id,kode_karyawan,nama_lengkap,divisi,jabatan',
            'module.instructors' => function ($query) {
                $query->select(['users.id', 'users.username', 'users.karyawan_id'])
                    ->with('karyawan:id,kode_karyawan,nama_lengkap');
            },
            'module:id,title,category,kode_karyawan,link'
        ];

        $baseQuery = PengajuanKlaimModul::with($commonWith)
            ->select('pengajuan_klaim_modul.id', 'module_id', 'price', 'status', 'approved_at', 'created_at')
            ->whereMonth('created_at', $month == 'All' ? now()->month : $month)
            ->whereYear('created_at', $year)
            ->latest();

        if ($jabatan == 'Finance & Accounting' || $jabatan == 'GM') {
            $klaimModul = $baseQuery->get();

        } elseif ($jabatan == 'Education Manager') {
            $klaimModul = $baseQuery->whereHas('module', function ($query) use ($karyawan) {
                $query->where(function ($q) use ($karyawan) {
                    // Modul dari divisi Education
                    $q->whereIn('kode_karyawan', function ($sub) {
                        $sub->select('kode_karyawan')
                            ->from('karyawans')
                            ->where('divisi', 'Education');
                    })
                        // ATAU Education Manager adalah instruktur di modul ini
                        ->orWhereHas('instructors', function ($q2) use ($karyawan) {
                            $q2->where('users.karyawan_id', $karyawan->id);
                        });
                });
            })->get();

        } else {
            // Instruktur biasa
            $klaimModul = $baseQuery->whereHas('module', function ($query) use ($karyawan, $divisi) {
                $query->where(function ($q) use ($karyawan, $divisi) {
                    if ($divisi == 'Education') {
                        // Instruktur Education: lihat SEMUA modul dari divisi Education
                        $q->whereIn('kode_karyawan', function ($sub) {
                            $sub->select('kode_karyawan')
                                ->from('karyawans')
                                ->where('divisi', 'Education');
                        })
                            // ATAU modul dimana dia adalah instructor
                            ->orWhereHas('instructors', function ($q2) use ($karyawan) {
                                $q2->where('users.karyawan_id', $karyawan->id);
                            });
                    } else {
                        // Instruktur non-Education: hanya lihat modul sendiri atau dimana dia instructor
                        $q->where('kode_karyawan', $karyawan->kode_karyawan)
                            ->orWhereHas('instructors', function ($q2) use ($karyawan) {
                                $q2->where('users.karyawan_id', $karyawan->id);
                            });
                    }
                });
            })->get();
        }

        return response()->json([
            'success' => true,
            'message' => 'List Klaim Modul',
            'data' => $klaimModul,
        ]);
    }
    public function create()
    {
        $user = auth()->user();
        $karyawan = $user->karyawan;
        $instructors = User::whereHas('karyawan', function ($query) {
            $query->where('divisi', 'Education');
        })->get();
        $categories = Materi::distinct()->pluck('kategori_materi')->filter();
        return view('pengajuanklaimmodul.create', compact('karyawan', 'instructors', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'instructors' => 'required|array|min:1',
            'instructors.*' => 'exists:users,id',
            'link' => 'required|url|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $module = Module::create([
                'title' => $request->title,
                'category' => $request->category,
                'link' => $request->link,
                'description' => $request->description,
                'kode_karyawan' => auth()->user()->karyawan->kode_karyawan,
            ]);

            $module->instructors()->attach($request->instructors);

            PengajuanKlaimModul::create([
                'module_id' => $module->id,
                'status' => 'Diajukan dan Sedang Ditinjau oleh Education Manager',
            ]);

            DB::commit();
            return redirect()->route('pengajuanklaimmodul.index')->with('success', 'Klaim Modul berhasil diajukan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $data = PengajuanKlaimModul::with([
            'module.karyawan',
            'module.instructors.karyawan'
        ])->findOrFail($id);
        return view('pengajuanklaimmodul.show', compact('data'));
    }

    public function edit($id)
    {
        $klaimModul = PengajuanKlaimModul::with(['module.instructors'])->findOrFail($id);
        $karyawan = auth()->user()->karyawan;
        $instructors = User::whereHas('karyawan', function ($query) {
            $query->where('divisi', 'Education');
        })->get();
        $categories = Materi::distinct()->pluck('kategori_materi')->filter();
        return view('pengajuanklaimmodul.edit', compact('klaimModul', 'karyawan', 'instructors', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $klaimModul = PengajuanKlaimModul::with('module')->findOrFail($id);
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'instructors' => 'required|array|min:1',
            'instructors.*' => 'exists:users,id',
            'link' => 'required|url|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $klaimModul->module->update([
                'title' => $request->title,
                'category' => $request->category,
                'link' => $request->link,
                'description' => $request->description,
            ]);

            $klaimModul->module->instructors()->sync($request->instructors);
            DB::commit();

            return redirect()->route('pengajuanklaimmodul.show', $id)
                ->with('success', 'Klaim Modul berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $klaimModul = PengajuanKlaimModul::with('module')->findOrFail($id);
            $klaimModul->module->delete();
            DB::commit();
            return redirect()->route('pengajuanklaimmodul.index')
                ->with('success', 'Klaim Modul berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function approve(Request $request, $id)
    {
        $klaimModul = PengajuanKlaimModul::with(['module.karyawan'])->findOrFail($id);
        $request->validate([
            'approval' => 'required|in:1,2',
            'price' => 'required_if:approval,1|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            if ($request->approval == '1') {
                $klaimModul->update([
                    'price' => $request->price,
                    'status' => 'Disetujui oleh Education Manager',
                    'approved_at' => now(),
                ]);

                $module = $klaimModul->module;
                $karyawanPembuat = Karyawan::where('kode_karyawan', $module->kode_karyawan)->first();

                if ($karyawanPembuat) {
                    $pengajuanBarang = PengajuanBarang::create([
                        'id_karyawan' => $karyawanPembuat->id,
                        'tipe' => 'Training & Sertifikasi',
                        'id_kegiatan' => null,
                    ]);

                    detailPengajuanBarang::create([
                        'id_pengajuan_barang' => $pengajuanBarang->id,
                        'nama_barang' => $module->title,
                        'qty' => 1,
                        'harga' => $request->price,
                        'keterangan' => 'Modul Kategori: ' . $module->category . ' | Link: ' . $module->link,
                    ]);

                    $tracking = tracking_pengajuan_barang::create([
                        'id_pengajuan_barang' => $pengajuanBarang->id,
                        'tracking' => 'Telah disetujui oleh Education Manager dan sedang diproses oleh Finance',
                        'tanggal' => now(),
                    ]);

                    $pengajuanBarang->update(['id_tracking' => $tracking->id]);

                    $financeUser = User::whereHas('karyawan', function($q) {
                        $q->where('jabatan', 'Finance & Accounting');
                    })->first();
                }

            } else {
                $klaimModul->update([
                    'status' => 'Ditolak: ' . ($request->alasan ?? 'Tanpa alasan'),
                    'approved_at' => null,
                ]);
            }

            DB::commit();
            return redirect()->route('pengajuanklaimmodul.index')
                ->with('success', 'Klaim Modul berhasil diproses.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}