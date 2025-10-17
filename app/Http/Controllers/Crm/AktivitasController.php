<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Aktivitas;
use App\Models\Perusahaan;
use App\Models\Contact;
use App\Models\Peserta;
use App\Models\User;
use App\Models\TargetActivity;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AktivitasController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        $allowedJabatan = ['Adm Sales', 'SPV Sales', 'HRD', 'Finance & Accounting', 'GM', 'Sales', 'Direktur Utama', 'Direktur'];

        if ($user->jabatan === 'Sales') {
            $idSales = $user->id_sales;
            $data = Aktivitas::where('id_sales', $idSales)->get();
            $perusahaan = Perusahaan::where('sales_key', $idSales)->get();
        } elseif (in_array($user->jabatan, $allowedJabatan)) {
            $data = Aktivitas::all();
            $perusahaan = Perusahaan::all();
        } else {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return view('crm.aktivitas.index', compact('data', 'perusahaan'));
    }

    public function getContactsAndPeserta($id)
    {
        $contacts = Contact::where('id_perusahaan', $id)
            ->select('id', 'nama', 'email', 'divisi')
            ->get()
            ->map(function ($contact) {
                return [
                    'id' => $contact->id,
                    'nama' => $contact->nama,
                    'email' => $contact->email,
                    'divisi' => $contact->divisi,
                    'type' => 'contact',
                ];
            });

        $peserta = Peserta::where('perusahaan_key', $id)
            ->select('id', 'nama', 'email')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'nama' => $p->nama,
                    'email' => $p->email,
                    'divisi' => 'C-Peserta',
                    'type' => 'peserta',
                ];
            });

        $allData = $contacts->concat($peserta);

        return response()->json($allData);
    }

    public function indexJson()
    {
        try {
            $user = Auth::user();
            $allowedJabatan = ['Adm Sales', 'HRD', 'Finance & Accounting', 'GM', 'SPV Sales'];

            $query = Aktivitas::with(['contact', 'peserta'])
                ->select('id', 'id_sales', 'id_contact', 'id_peserta', 'aktivitas', 'pax', 'total', 'harga', 'deskripsi', 'waktu_aktivitas', 'created_at');

            if ($user->jabatan === 'Sales') {
                $query->where('id_sales', $user->id_sales);
            } elseif (!in_array($user->jabatan, $allowedJabatan)) {
                return response()->json([
                    'error' => 'Unauthorized access.'
                ], 403);
            }

            $draw = request()->get('draw', 1);
            $start = request()->get('start', 0);
            $length = request()->get('length', 10);
            $searchValue = request()->get('search')['value'] ?? '';
            $orderColumnIndex = request()->get('order')[0]['column'] ?? 0;
            $orderDirection = request()->get('order')[0]['dir'] ?? 'asc';

            $orderColumns = ['id', 'id_sales', 'id_contact', 'aktivitas', 'pax', 'total', 'harga', 'deskripsi', 'waktu_aktivitas'];
            $orderColumn = $orderColumns[$orderColumnIndex] ?? 'id';

            // Hitung total semua data sebelum filter
            $totalRecords = $query->count();


            // 🔍 Filter pencarian umum
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('aktivitas', 'like', "%{$searchValue}%")
                        ->orWhere('deskripsi', 'like', "%{$searchValue}%")
                        ->orWhereHas('contact', function ($q2) use ($searchValue) {
                            $q2->where('nama', 'like', "%{$searchValue}%")
                                ->orWhereHas('perusahaan', function ($q3) use ($searchValue) {
                                    $q3->where('nama_perusahaan', 'like', "%{$searchValue}%");
                                });
                        })
                        ->orWhereHas('peserta', function ($q2) use ($searchValue) {
                            $q2->where('nama', 'like', "%{$searchValue}%")
                                ->orWhereHas('perusahaan', function ($q3) use ($searchValue) {
                                    $q3->where('nama_perusahaan', 'like', "%{$searchValue}%");
                                });
                        });
                });
            }


            // 🔹 Filter tambahan
            $filterAktivitas = request()->get('filter_aktivitas');
            $filterWaktuStart = request()->get('filter_waktu_start');
            $filterWaktuEnd = request()->get('filter_waktu_end');
            $filterCreatedStart = request()->get('filter_created_start');
            $filterCreatedEnd = request()->get('filter_created_end');

            if ($filterAktivitas) {
                $query->where('aktivitas', $filterAktivitas);
            }

            if ($filterWaktuStart && $filterWaktuEnd) {
                $query->whereBetween('waktu_aktivitas', [$filterWaktuStart, $filterWaktuEnd]);
            } elseif ($filterWaktuStart) {
                $query->whereDate('waktu_aktivitas', '>=', $filterWaktuStart);
            } elseif ($filterWaktuEnd) {
                $query->whereDate('waktu_aktivitas', '<=', $filterWaktuEnd);
            }

            if ($filterCreatedStart && $filterCreatedEnd) {
                $query->whereBetween('created_at', [$filterCreatedStart, $filterCreatedEnd]);
            } elseif ($filterCreatedStart) {
                $query->whereDate('created_at', '>=', $filterCreatedStart);
            } elseif ($filterCreatedEnd) {
                $query->whereDate('created_at', '<=', $filterCreatedEnd);
            }

            // Hitung total setelah filter
            $totalFiltered = $query->count();

            // Ambil data dengan paginasi
            $data = $query->orderBy('created_at', 'desc')
                ->orderBy($orderColumn, $orderDirection)
                ->offset($start)
                ->limit($length)
                ->get()
                ->map(function ($item) {
                    $namaKontak = null;
                    $namaPerusahaan = null;

                    // Cek apakah aktivitas terkait peserta
                    if (!empty($item->id_peserta)) {
                        $namaKontak = $item->peserta?->nama;
                        $namaPerusahaan = $item->peserta?->perusahaan?->nama_perusahaan;
                    }

                    // Jika tidak ada peserta, coba ambil dari contact
                    else {
                        $namaKontak = $item->contact?->nama;
                        $namaPerusahaan = $item->contact?->perusahaan?->nama_perusahaan;
                    }

                    // Fallback khusus untuk aktivitas "DB" (Database baru)
                    if ($item->aktivitas === 'DB' && empty($namaPerusahaan)) {
                        $namaPerusahaan = $item->deskripsi
                            ? str_replace(['Database baru "', '" berhasil ditambahkan'], '', $item->deskripsi)
                            : '-';
                    }

                    // Tentukan kolom "kontak" untuk tampilan
                    if (empty($namaKontak) && empty($namaPerusahaan)) {
                        $kontak = '-';
                    } elseif (!empty($namaKontak) && !empty($namaPerusahaan)) {
                        $kontak = $namaKontak . ' (' . $namaPerusahaan . ')';
                    } else {
                        $kontak = $namaKontak ?: $namaPerusahaan;
                    }

                    //  Ubah nama aktivitas jadi lebih ramah tampil
                    $aktivitas = match ($item->aktivitas) {
                        'Incharge'    => 'Incharge Inhouse',
                        'Form_Masuk'  => 'Form Masuk',
                        'Form_Keluar' => 'Form Keluar',
                        default       => ucfirst($item->aktivitas),
                    };

                    // Bersihkan deskripsi untuk aktivitas DB
                    $deskripsi = $item->aktivitas === 'DB'
                        ? 'Database baru berhasil ditambahkan'
                        : $item->deskripsi;

                    // Format hasil akhir data
                    return [
                        'id' => $item->id,
                        'kontak' => $kontak,
                        'id_sales' => $item->id_sales,
                        'aktivitas' => $aktivitas,
                        'pax' => $item->pax,
                        'harga' => $item->harga,
                        'total' => $item->total,
                        'deskripsi' => $deskripsi,
                        'waktu_aktivitas' => \Carbon\Carbon::parse($item->waktu_aktivitas)->format('d/m/Y'),
                    ];
                });


            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('IndexJson Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], 500);
        }
    }

    public function targetAktivitas($id_sales)
    {
        try {
            $target = TargetActivity::where('id_sales', $id_sales)->first();

            if (!$target) {
                return response()->json(['message' => 'Target belum ditetapkan untuk sales ini.'], 404);
            }

            $deadline = \Carbon\Carbon::parse($target->deadline)->format('d/m/Y');

            // Daftar jenis aktivitas yang akan dibandingkan
            $jenisAktivitas = [
                'Contact', 'Call', 'Visit', 'Email', 'Meet',
                'DB', 'PA', 'PI', 'Incharge', 'Telemarketing',
                'FormM', 'FormK'
            ];

            $hasil = [];

            foreach ($jenisAktivitas as $jenis) {
                $targetJumlah = $target->$jenis ?? 0;

                // Hitung realisasi aktual dari tabel Aktivitas
                $realisasi = Aktivitas::where('id_sales', $id_sales)
                    ->where(function($q) use ($jenis) {
                        // Sesuaikan nama field dengan nilai di DB
                        if ($jenis === 'FormM') {
                            $q->where('aktivitas', 'Form_Masuk');
                        } elseif ($jenis === 'FormK') {
                            $q->where('aktivitas', 'Form_Keluar');
                        } else {
                            $q->where('aktivitas', $jenis);
                        }
                    })
                    ->count();

                // Tentukan status capaian
                $status = match(true) {
                    $realisasi >= $targetJumlah && $targetJumlah > 0 => '✅ Tercapai',
                    $realisasi < $targetJumlah && $targetJumlah > 0 => '❌ Belum tercapai',
                    default => '-',
                };

                $hasil[] = [
                    'jenis' => $jenis,
                    'target' => $targetJumlah,
                    'realisasi' => $realisasi,
                    'status' => $status,
                    'deadline' => $deadline
                ];
            }

            return response()->json([
                'sales' => $id_sales,
                'deadline' => $deadline,
                'data' => $hasil
            ]);

        } catch (\Exception $e) {
            \Log::error('Target Aktivitas Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memuat target aktivitas.'], 500);
        }
    }

    public function semuaTargetAktivitas()
    {
        try {
            $allowedUser = ['Adm Sales', 'SPV Sales', 'HRD', 'Finance & Accounting', 'GM', 'Direktur Utama', 'Direktur'];
            $user = auth()->user();

            // 🔹 Jika user bukan role manajemen → tampilkan target miliknya sendiri
            if (!in_array($user->jabatan, $allowedUser)) {
                $target = TargetActivity::where('id_sales', $user->id_sales)->first();
                if (!$target) {
                    return response()->json(['message' => 'Belum ada target aktivitas.']);
                }

                $deadline = \Carbon\Carbon::parse($target->deadline)->format('d/m/Y');
                $jenisAktivitas = [
                    'Contact', 'Call', 'Visit', 'Email', 'Meet',
                    'DB', 'PA', 'PI', 'Incharge', 'Telemarketing',
                    'FormM', 'FormK'
                ];

                $hasil = [];
                foreach ($jenisAktivitas as $jenis) {
                    $targetJumlah = $target->$jenis ?? 0;
                    $realisasi = Aktivitas::where('id_sales', $user->id_sales)
                        ->where(function ($q) use ($jenis) {
                            if ($jenis === 'FormM') $q->where('aktivitas', 'Form_Masuk');
                            elseif ($jenis === 'FormK') $q->where('aktivitas', 'Form_Keluar');
                            else $q->where('aktivitas', $jenis);
                        })
                        ->count();

                    $hasil[] = [
                        'jenis' => $jenis,
                        'target' => $targetJumlah,
                        'realisasi' => $realisasi,
                        'percent' => $targetJumlah > 0 ? round(($realisasi / $targetJumlah) * 100) : 0,
                        'deadline' => $deadline
                    ];
                }

                return response()->json([
                    'id_sales' => $user->id_sales,
                    'deadline' => $deadline,
                    'data' => $hasil
                ]);
            }

            // 🔹 Jika role manajemen → tampilkan semua sales
            $salesList = User::whereNotNull('id_sales')
                ->whereNotIn('jabatan', $allowedUser)
                ->get();

            $data = [];
            foreach ($salesList as $sales) {
                $target = TargetActivity::where('id_sales', $sales->id_sales)->first();
                if (!$target) continue;

                $deadline = \Carbon\Carbon::parse($target->deadline)->format('d/m/Y');
                $jenisAktivitas = [
                    'Contact', 'Call', 'Visit', 'Email', 'Meet',
                    'DB', 'PA', 'PI', 'Incharge', 'Telemarketing',
                    'FormM', 'FormK'
                ];

                $hasil = [];
                foreach ($jenisAktivitas as $jenis) {
                    $targetJumlah = $target->$jenis ?? 0;
                    $realisasi = Aktivitas::where('id_sales', $sales->id_sales)
                        ->where(function ($q) use ($jenis) {
                            if ($jenis === 'FormM') $q->where('aktivitas', 'Form_Masuk');
                            elseif ($jenis === 'FormK') $q->where('aktivitas', 'Form_Keluar');
                            else $q->where('aktivitas', $jenis);
                        })
                        ->count();

                    $hasil[] = [
                        'jenis' => $jenis,
                        'target' => $targetJumlah,
                        'realisasi' => $realisasi,
                        'percent' => $targetJumlah > 0 ? round(($realisasi / $targetJumlah) * 100) : 0,
                        'deadline' => $deadline
                    ];
                }

                $data[] = [
                    'id_sales' => $sales->id_sales,
                    'deadline' => $deadline,
                    'data' => $hasil
                ];
            }

            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            \Log::error('Error Semua Target Aktivitas: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memuat semua target aktivitas.'], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_contact' => 'required|integer',
            'id_peluang' => 'required|integer',
            'aktivitas' => 'required|in:Call,Email,Visit,Meet,Incharge,PA,PI,DB,Telemarketing,Form_Masuk,Form_Keluar',
            'deskripsi' => 'required|string',
            'waktu_aktivitas' => 'required|date',
        ]);

        $validated['id_sales'] = $request->input('id_sales', auth()->user()->id_sales ?? null);
        $aktivitas = Aktivitas::create($validated);

        return back()->with([
            'message' => 'Aktivitas berhasil direcord.',
            'data' => $aktivitas,
        ]);
    }

    public function storeNew(Request $request)
    {
        $validated = $request->validate([
            'id_perusahaan'   => 'required|integer',
            'id_contact'      => 'required|string',
            'id_peluang'      => 'nullable',
            'contact_type'    => 'nullable|string|in:contact,peserta',
            'aktivitas'       => 'required|in:Call,Email,Visit,Meet,Incharge,PA,PI,DB,Telemarketing,Form_Masuk,Form_Keluar',
            'deskripsi'       => 'nullable|string',
            'waktu_aktivitas' => 'required|date',
        ]);

        $validated['id_sales'] = $request->input('id_sales', auth()->user()->id_sales);

        // 🔹 Jika user menambahkan kontak baru
        if ($request->id_contact === 'new') {
            $contact = Contact::create([
                'id_perusahaan' => (int) $request->id_perusahaan,
                'sales_key'     => $validated['id_sales'],
                'nama'          => trim($request->nama_perusahaan),
                'email'         => trim($request->email_perusahaan),
                'cp'            => trim($request->cp_perusahaan),
                'divisi'        => trim($request->divisi_perusahaan),
                'status'        => '1'
            ]);

            // ✅ Simpan ID kontak baru ke aktivitas utama
            $validated['id_contact'] = $contact->id;
            $validated['id_peserta'] = null;

            // 🔹 Tambahkan log aktivitas "Contact baru"
            Aktivitas::create([
                'id_sales'        => $validated['id_sales'],
                'id_contact'      => $contact->id,
                'aktivitas'       => 'Contact',
                'deskripsi'       => 'Contact baru berhasil ditambahkan',
                'waktu_aktivitas' => now(),
            ]);
        } else {
            // 🔹 Jika user memilih kontak atau peserta yang sudah ada
            $contactId = (int) $request->id_contact;

            if ($request->contact_type === 'peserta') {
                if (!Peserta::where('id', $contactId)->exists()) {
                    return back()->withErrors([
                        'id_contact' => 'Peserta yang dipilih tidak ditemukan.'
                    ]);
                }
                $validated['id_peserta'] = $contactId;
                $validated['id_contact'] = null;
            } else {
                if (!Contact::where('id', $contactId)->exists()) {
                    return back()->withErrors([
                        'id_contact' => 'Kontak yang dipilih tidak valid.'
                    ]);
                }
                $validated['id_contact'] = $contactId;
                $validated['id_peserta'] = null;
            }
        }

        // 🔹 Hitung total otomatis jika ada pax dan harga
        if ($request->filled('pax')) {
            $validated['pax'] = $request->pax;
        }

        if ($request->filled('harga')) {
            $validated['harga'] = $request->harga;
        }

        if ($request->filled('pax') && $request->filled('harga')) {
            $validated['total'] = $request->pax * $request->harga;
        }

        // 🔹 Simpan aktivitas utama
        $aktivitas = Aktivitas::create($validated);

        return back()->with([
            'message' => 'Aktivitas berhasil direkam.',
            'data'    => $aktivitas,
        ]);
    }

    public function delete($id)
    {
        try {
            $aktivitas = Aktivitas::find($id);

            if (!$aktivitas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aktivitas tidak ditemukan.',
                ], 404);
            }

            $aktivitas->delete();

            return response()->json([
                'success' => true,
                'message' => 'Aktivitas berhasil dihapus.',
                'id' => $id
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal menghapus aktivitas.',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $aktivitas = Aktivitas::findOrFail($id);

        // Data dasar yang selalu diupdate
        $updateData = [
            'aktivitas' => $request->aktivitas,
            'deskripsi' => $request->deskripsi,
            'waktu_aktivitas' => $request->waktu_aktivitas,
        ];

        // Jika termasuk aktivitas yang membutuhkan pax dan harga
        if (in_array($request->aktivitas, ['PA', 'Form_Masuk', 'Form_Keluar'])) {
            $updateData['pax'] = $request->filled('pax') ? $request->pax : null;
            $updateData['harga'] = $request->filled('harga') ? $request->harga : null;

            if ($request->filled('pax') && $request->filled('harga')) {
                $updateData['total'] = $request->pax * $request->harga;
            } else {
                $updateData['total'] = null;
            }
        }
        // Selain itu, kosongkan nilai-nilai ini
        else {
            $updateData['pax'] = null;
            $updateData['harga'] = null;
            $updateData['total'] = null;
        }

        // Lakukan 1x update
        $aktivitas->update($updateData);

        return response()->json([
            'message' => 'Aktivitas berhasil diperbarui.',
        ]);
    }
}
