<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Aktivitas;
use App\Models\Contact;
use App\Models\lokasi;
use App\Models\Materi;
use App\Models\Peluang;
use App\Models\Perusahaan;
use App\Models\RKM;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;    
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ContactController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View CRM History Status', ['only' => ['allHistoryStatus', 'allHistoryStatusData']]);
        $this->middleware('permission:View Contact CRM', ['only' => ['index', 'getPerusahaan', 'detail']]);
        $this->middleware('permission:Store Contact CRM', ['only' => ['store']]);
        $this->middleware('permission:Update Contact CRM', ['only' => ['update']]);
        $this->middleware('permission:Delete Contact CRM', ['only' => ['delete']]);
    }

    public function index()
    {
        $lokasi = lokasi::all();
        return view('crm.contact.index', compact('lokasi'));
    }

    public function getPerusahaan(Request $request)
    {
        $user = Auth::user();
        $allowedJabatan = [
            'Adm Sales', 'SPV Sales', 'HRD', 'Finance & Accounting',
            'GM', 'Sales', 'Direktur Utama', 'Direktur'
        ];

        if ($user->jabatan === 'Sales') {
            $idSales = $user->id_sales;
            $baseQuery = Perusahaan::where('sales_key', $idSales);
        } elseif (in_array($user->jabatan, $allowedJabatan)) {
            $baseQuery = Perusahaan::query();
        } else {
            return response()->json(['error' => 'Anda tidak memiliki akses ke data ini.'], 403);
        }

        $recordsTotal = $baseQuery->count();

        $query = clone $baseQuery;

        if ($request->filled('sales_key')) {
            $query->where('sales_key', $request->sales_key);
        }

        if ($request->has('search') && $request->search['value'] != '') {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('nama_perusahaan', 'like', "%$search%")
                    ->orWhere('lokasi', 'like', "%$search%")
                    ->orWhere('sales_key', 'like', "%$search%")
                    ->orWhere('status', 'like', "%$search%");
            });
        }

        $recordsFiltered = $query->count();

        if ($request->has('order')) {
            $columns = [
                'id',
                'nama_perusahaan',
                'lokasi',
                'status',
                'sales_key',
                'kelas_terakhir',
                'aktivitas_terakhir_date',
            ];
            $order = $request->order[0];
            $colIndex = $order['column'] ?? 0;
            $dir = $order['dir'] ?? 'asc';
            if (isset($columns[$colIndex])) {
                $query->orderBy($columns[$colIndex], $dir);
            }
        } else {
            $query->orderBy('id', 'desc');
        }

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $query->skip($start)->take($length);

        $data = $query->get();

        $kelasTerakhir = [];
        $aktivitasTerakhir = [];

        foreach ($data as $item) {
            $kelasTerakhir[$item->id] = RKM::where('perusahaan_key', $item->id)
                ->latest()
                ->with('materi')
                ->first();

            $contactIds = $item->contacts->pluck('id');

            $aktivitasTerakhir[$item->id] = Aktivitas::whereIn('id_contact', $contactIds)
                ->latest()
                ->first();
        }

        $responseData = $data->map(function ($contact) use ($kelasTerakhir, $aktivitasTerakhir) {
            return [
                'id' => $contact->id,
                'nama_perusahaan' => $contact->nama_perusahaan,
                'npwp' => $contact->npwp,
                'alamat' => $contact->alamat,
                'kategori_perusahaan' => $contact->kategori_perusahaan,
                'lokasi' => $contact->lokasi,
                'email' => $contact->email,
                'status' => $contact->status,
                'sales_key' => $contact->sales_key,
                'kelas_terakhir' => isset($kelasTerakhir[$contact->id])
                    ? ($kelasTerakhir[$contact->id]->materi->nama_materi)
                    : 'Belum ada kelas',
                'kelas_terakhir_date' => isset($kelasTerakhir[$contact->id])
                    ? $kelasTerakhir[$contact->id]->created_at->translatedFormat('d F Y')
                    : null,
                'aktivitas_terakhir_date' => isset($aktivitasTerakhir[$contact->id])
                    ? $aktivitasTerakhir[$contact->id]->created_at->format('d-m-Y')
                    : 'Belum ada aktivitas',
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $responseData,
        ]);
    }

    public function detail($id)
    {
        $data = Perusahaan::with(['contacts', 'peserta.latestRegistrasi.materi'])->where('id', $id)->firstOrFail();

        $items = [];

        // Tambahkan semua contacts
        foreach ($data->contacts as $contact) {
            $items[] = [
                'id' => $contact->id,
                'nama' => $contact->nama,
                'type' => 'contact',
                'label' => "[Contact] " . $contact->nama . " (" . ($contact->email ?? 'Tidak ada email') . ")"
            ];
        }

        // Tambahkan semua peserta
        foreach ($data->peserta as $peserta) {
            $items[] = [
                'id' => $peserta->id,
                'nama' => $peserta->nama,
                'type' => 'peserta',
                'label' => "[Peserta] " . $peserta->nama. " (" . ($peserta->email ?? 'Tidak ada email') . ")"
            ];
        }

        // Urutkan berdasarkan nama
        usort($items, function ($a, $b) {
            return strcasecmp($a['label'], $b['label']);
        });

        // Ambil data lainnya (jika diperlukan)
        $aktivitass = Aktivitas::with(['contact', 'peserta'])
            ->where(function ($query) use ($data) {
                $query->whereIn('id_contact', $data->contacts->pluck('id'))
                    ->orWhereIn('id_peserta', $data->peserta->pluck('id'));
            })
            ->orderByDesc('created_at')
            ->get();

        $aktivitas = Aktivitas::with(['contact', 'peserta'])
            ->where(function ($query) use ($data) {
                $query->whereIn('id_contact', $data->contacts->pluck('id'))
                    ->orWhereIn('id_peserta', $data->peserta->pluck('id'));
            })
            ->whereNull('id_peluang')
            ->orderByDesc('created_at')
            ->get();


        $peluang = Peluang::where('id_contact', $data->id)
            ->with('materiRelation')
            ->get();

            // dd($items);

        $materi = Materi::all();


        return view('crm.contact.detail', compact('data', 'items', 'aktivitas','aktivitass', 'peluang', 'materi'));
    }

    public function store(Request $request)
    {
        // Validasi input sesuai field perusahaan
        $validated = $request->validate([
            'nama_perusahaan'      => 'required|string|max:255',
            'kategori_perusahaan'  => 'nullable|string|max:255',
            'lokasi'               => 'nullable|string|max:255',
            // 'sales_key'            => 'nullable|string|max:255',
            'status'               => 'nullable|string|max:255',
            'npwp'                 => 'nullable|string|max:255',
            'alamat'               => 'nullable|string|max:1000',
            'cp'                   => 'nullable|string|max:20',
            'no_telp'              => 'nullable|string|max:20',
            'email'                => 'nullable|email|max:255',
            'foto_npwp'            => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ]);

        // Handle upload foto_npwp, jika ada
        if ($request->hasFile('foto_npwp')) {
            $file = $request->file('foto_npwp');
            $extension = $file->getClientOriginalExtension();
            $filename = $validated['nama_perusahaan'] . '_npwp.' . $extension;
            $file->storeAs('public/npwp', $filename);
            $validated['foto_npwp'] = $filename;
        }

        // Menambahkan id_sales dari input manual atau default dari user login
        $id_sales = $request->input('id_sales', auth()->user()->id_sales ?? null);

        $validated['sales_key'] = $id_sales;

        // Simpan data perusahaan
        $perusahaan = Perusahaan::create($validated + ['sales_key' => $id_sales]);

        $aktivitas = new Aktivitas();
        $aktivitas->id_sales = $id_sales;
        $aktivitas->aktivitas = 'DB';
        $aktivitas->deskripsi = 'Database baru "' . $perusahaan->nama_perusahaan . '" berhasil ditambahkan';
        $aktivitas->waktu_aktivitas = Carbon::now();
        $aktivitas->save();

        return back()->with([
            'message' => 'Data perusahaan berhasil disimpan.',
            'data' => $perusahaan,
            'aktivitas' => $aktivitas,
        ]);
    }

    public function delete($id)
    {
        $contact = Contact::where('id', $id)->first();
        $contact->delete();

        return back()->with([
            'message' => 'Kontak berhasil dihapus.',
        ]);
    }

    public function update($id, Request $request)
    {
        $validated = $request->validate([
            'nama_perusahaan' => 'required|string|max:255',
            'kategori_perusahaan' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'lokasi' => 'nullable|string|max:255',
            'status' => 'required|string|max:50',
            'npwp' => 'nullable|string|max:50',
            'alamat' => 'nullable|string|max:500',
            'no_telp' => 'nullable|string|max:20',
            'cp' => 'nullable|string|max:100',
            'foto_npwp' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $contact = Perusahaan::findOrFail($id);

        if (!empty($contact->status) && $contact->status !== $validated['status']) {
            $historyStatus = $contact->history_status_array;

            $historyStatus[] = [
                'status_lama' => $contact->status,
                'status_baru' => $validated['status'],
                'waktu_perubahan' => now()->toDateTimeString(),
                'diubah_oleh' => auth()->check() ? auth()->user()->id_sales : 'sistem'
            ];

            $contact->history_status = json_encode($historyStatus);
        }

        $contact->nama_perusahaan = $validated['nama_perusahaan'];
        $contact->kategori_perusahaan = $validated['kategori_perusahaan'];
        $contact->email = $validated['email'];
        $contact->lokasi = $validated['lokasi'] ?? $contact->lokasi;
        $contact->status = $validated['status'];
        $contact->npwp = $validated['npwp'] ?? $contact->npwp;
        $contact->alamat = $validated['alamat'] ?? $contact->alamat;
        $contact->no_telp = $validated['no_telp'] ?? $contact->no_telp;
        $contact->cp = $validated['cp'] ?? $contact->cp;

        if ($request->hasFile('foto_npwp')) {
            $file = $request->file('foto_npwp');
            $extension = $file->getClientOriginalExtension();
            $filename = $validated['nama_perusahaan'] . '_npwp.' . $extension;
            $file->storeAs('public/npwp', $filename);
            $contact->foto_npwp = 'npwp/' . $filename;
        }

        $contact->save();

        return back()->with([
            'message' => 'Kontak berhasil diperbarui.',
        ]);
    }

    public function allHistoryStatus()
    {
        // Mengambil semua data perusahaan yang memiliki riwayat status
        $perusahaans = Perusahaan::whereNotNull('history_status')->get();

        $totalConversionDays = 0;
        $conversionCount = 0;
        $transitionRate = [];
        $userPerformance = [];
        $timeBasedTrends = [];

        // Melakukan iterasi pada setiap perusahaan untuk mengkalkulasi analitik
        foreach ($perusahaans as $perusahaan) {
            $history = $perusahaan->history_status_array;

            // 1. Mengkalkulasi Durasi Konversi Status (Lead Time)
            if (count($history) > 1) {
                $firstDate = strtotime($history[0]['waktu_perubahan']);
                $lastDate = strtotime(end($history)['waktu_perubahan']);
                $diffDays = ($lastDate - $firstDate) / (60 * 60 * 24);
                $totalConversionDays += $diffDays;
                $conversionCount++;
            }

            foreach ($history as $item) {
                $lama = $item['status_lama'] ?? '-';
                $baru = $item['status_baru'] ?? '-';
                $user = $item['diubah_oleh'] ?? '-';
                $waktu = date('Y-m-d', strtotime($item['waktu_perubahan']));

                // 2. Mengkalkulasi Rasio Transisi Status
                $transitionKey = $lama . ' -> ' . $baru;
                if (!isset($transitionRate[$transitionKey])) {
                    $transitionRate[$transitionKey] = 0;
                }
                $transitionRate[$transitionKey]++;

                // 4. Mengkalkulasi Volume Aktivitas Berdasarkan Tanggal
                if (!isset($timeBasedTrends[$waktu])) {
                    $timeBasedTrends[$waktu] = 0;
                }
                $timeBasedTrends[$waktu]++;
            }
        }

        // Memformat hasil kalkulasi
        $averageConversionDays = $conversionCount > 0 ? round($totalConversionDays / $conversionCount, 2) : 0;

        arsort($transitionRate);
        arsort($userPerformance);
        ksort($timeBasedTrends);

        return view('crm.contact.all_history_status', compact(
            'averageConversionDays',
            'transitionRate',
            'timeBasedTrends'
        ));
    }

    public function allHistoryStatusData(Request $request)
    {
        // Mengambil semua data perusahaan yang memiliki riwayat status
        $perusahaans = Perusahaan::whereNotNull('history_status')->get();

        $allHistory = [];

        // Menggabungkan seluruh data riwayat status ke dalam satu array
        foreach ($perusahaans as $perusahaan) {
            $historyArray = $perusahaan->history_status_array;

            foreach ($historyArray as $history) {
                $allHistory[] = [
                    'waktu_perubahan' => $history['waktu_perubahan'] ?? null,
                    'nama_perusahaan' => $perusahaan->nama_perusahaan,
                    'status_lama' => $history['status_lama'] ?? '-',
                    'status_baru' => $history['status_baru'] ?? '-'
                ];
            }
        }

        // Mengurutkan data secara default berdasarkan waktu perubahan terbaru
        usort($allHistory, function ($a, $b) {
            return strtotime($b['waktu_perubahan']) - strtotime($a['waktu_perubahan']);
        });

        // Memproses fitur pencarian global DataTables
        $searchValue = $request->input('search.value');
        if (!empty($searchValue)) {
            $allHistory = array_filter($allHistory, function ($item) use ($searchValue) {
                return false !== strpos(strtolower($item['nama_perusahaan']), strtolower($searchValue)) ||
                    false !== strpos(strtolower($item['status_lama']), strtolower($searchValue)) ||
                    false !== strpos(strtolower($item['status_baru']), strtolower($searchValue));
            });
            $allHistory = array_values($allHistory);
        }

        $totalRecords = count($allHistory);

        // Memproses batasan paginasi (Server-side Slicing)
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $slicedData = array_slice($allHistory, $start, $length);

        // Mengembalikan respons berformat JSON sesuai dengan spesifikasi DataTables
        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $slicedData
        ]);
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $allowedJabatan = [
            'Adm Sales', 'SPV Sales', 'HRD', 'Finance & Accounting',
            'GM', 'Sales', 'Direktur Utama', 'Direktur'
        ];

        $salesName = null;

        if ($user->jabatan === 'Sales') {
            $idSales = $user->id_sales;
            $baseQuery = Perusahaan::with('contacts')->where('sales_key', $idSales);

            // Ekstraksi nama lengkap untuk user Sales yang sedang login
            if ($user->karyawan) {
                $salesName = $user->karyawan->nama_lengkap;
            }
        } elseif (in_array($user->jabatan, $allowedJabatan)) {
            $baseQuery = Perusahaan::with('contacts');
        } else {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        // Ekstraksi nama lengkap berdasarkan filter jika user bukan Sales
        if ($request->filled('sales_key') && $user->jabatan !== 'Sales') {
            $baseQuery->where('sales_key', $request->sales_key);

            $salesUser = User::with('karyawan')->where('id_sales', $request->sales_key)
                ->orWhereHas('karyawan', function ($q) use ($request) {
                    $q->where('kode_karyawan', $request->sales_key);
                })->first();

            if ($salesUser && $salesUser->karyawan) {
                $salesName = $salesUser->karyawan->nama_lengkap;
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $baseQuery->where(function ($q) use ($search) {
                $q->where('nama_perusahaan', 'like', "%$search%")
                    ->orWhere('lokasi', 'like', "%$search%")
                    ->orWhere('sales_key', 'like', "%$search%")
                    ->orWhere('status', 'like', "%$search%");
            });
        }

        $data = $baseQuery->orderBy('id', 'desc')->get();

        $kelasTerakhir = [];
        $aktivitasTerakhir = [];

        foreach ($data as $item) {
            $kelasTerakhir[$item->id] = RKM::where('perusahaan_key', $item->id)
                ->latest()
                ->with('materi')
                ->first();

            $contactIds = $item->contacts->pluck('id');

            $aktivitasTerakhir[$item->id] = Aktivitas::whereIn('id_contact', $contactIds)
                ->latest()
                ->first();
        }

        $pdf = Pdf::loadView('crm.contact.pdf', compact('data', 'kelasTerakhir', 'aktivitasTerakhir', 'salesName'));

        // Pembentukan nama file dinamis
        $fileName = 'Data_Database_Client';
        if ($salesName) {
            // Mengganti spasi dengan underscore untuk standar penamaan file
            $fileName .= '_' . str_replace(' ', '_', $salesName);
        }
        $fileName .= '.pdf';

        return $pdf->download($fileName);
    }
}
