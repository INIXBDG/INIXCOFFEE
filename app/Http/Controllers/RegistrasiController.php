<?php

namespace App\Http\Controllers;

use App\Exports\RegistrasiExport;
use App\Exports\RegistrasiPerSalesExport;
use App\Models\karyawan;
use App\Models\Nilaifeedback;
use App\Models\Peserta;
use App\Models\Registrasi;
use App\Models\RKM;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Gate;

class RegistrasiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View Registrasi', ['only' => ['index']]);
        $this->middleware('permission:Create Registrasi', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit Registrasi', ['only' => ['update', 'edit']]);
        $this->middleware('permission:Delete Registrasi', ['only' => ['destroy']]);
    }

    public function index()
    {
        $listSouvenir = souvenir::select('id', 'nama_souvenir')
                            ->where('stok', '>', 0)
                            ->get();

        return view('registrasi.index', compact('listSouvenir'));
    }

    public function getRegistrasiall(Request $request)
    {
        // Cek Hak Akses menggunakan Gate
        if (!Gate::allows('view-registrasi')) {
            return response()->json([
                'draw' => intval($request->input('draw', 1)), 
                'recordsTotal' => 0, 
                'recordsFiltered' => 0, 
                'data' => []
            ]);
        }

        // 1. Siapkan Kueri Dasar & Eager Loading spesifik kolom (Mengurangi beban RAM)
        $query = Registrasi::with([
            'peserta:id,nama,perusahaan_key', 
            'peserta.perusahaan:id,nama_perusahaan', 
            'materi:id,nama_materi', 
            'rkm:id,tanggal_awal,tanggal_akhir', 
            'souvenirpeserta.souvenir:id,nama_souvenir'
        ])->select('id', 'id_peserta', 'id_materi', 'id_rkm', 'id_instruktur', 'id_sales', 'created_at');

        // 2. Filter Role (Lebih aman dilakukan di backend daripada JavaScript)
        $idInstruktur = Auth::user()->id_instruktur;
        $idSales = Auth::user()->id_sales;

        if ($idInstruktur && $idInstruktur !== 'AD') {
            $query->where('id_instruktur', $idInstruktur);
        }
        if ($idSales && $idSales !== 'AM') {
            $query->where('id_sales', $idSales);
        }

        // 3. Hitung total data sebelum pencarian
        $recordsTotal = $query->count();

        // 4. Fitur Pencarian (Search DataTables)
        $searchValue = $request->input('search.value');
        if (!empty($searchValue)) {
            $query->where(function($q) use ($searchValue) {
                $q->whereHas('peserta', function($q2) use ($searchValue) {
                    $q2->where('nama', 'like', "%{$searchValue}%");
                })
                ->orWhereHas('peserta.perusahaan', function($q2) use ($searchValue) {
                    $q2->where('nama_perusahaan', 'like', "%{$searchValue}%");
                })
                ->orWhereHas('materi', function($q2) use ($searchValue) {
                    $q2->where('nama_materi', 'like', "%{$searchValue}%");
                })
                ->orWhereHas('souvenirpeserta.souvenir', function($q2) use ($searchValue) {
                    $q2->where('nama_souvenir', 'like', "%{$searchValue}%");
                });
            });
            $recordsFiltered = $query->count();
        } else {
            $recordsFiltered = $recordsTotal;
        }

        // 5. Paginasi & Pengurutan (Otomatis dari DataTables)
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        
        // Default urutkan berdasarkan created_at desc jika tidak ada request sortir
        $query->orderBy('created_at', 'desc')->skip($start)->take($length);

        // Ambil data terbatas
        $data = $query->get();

        // 6. Rapikan format data dengan Null-Safe operator (?->)
        $responseData = $data->map(function ($row) {
            $tglAwal = $row->rkm?->tanggal_awal ? \Carbon\Carbon::parse($row->rkm->tanggal_awal)->locale('id')->isoFormat('DD MMMM YYYY') : null;
            $tglAkhir = $row->rkm?->tanggal_akhir ? \Carbon\Carbon::parse($row->rkm->tanggal_akhir)->locale('id')->isoFormat('DD MMMM YYYY') : null;
            
            return [
                'id' => $row->id,
                'id_rkm' => $row->id_rkm,
                'nama_peserta' => $row->peserta?->nama ?? '-',
                'nama_perusahaan' => $row->peserta?->perusahaan?->nama_perusahaan ?? '-',
                'nama_materi' => $row->materi?->nama_materi ?? '-',
                'periode' => ($tglAwal && $tglAkhir) ? "{$tglAwal} s/d {$tglAkhir}" : '-',
                'id_instruktur' => $row->id_instruktur ?? '-',
                'id_sales' => $row->id_sales ?? '-',
                'nama_souvenir' => $row->souvenirpeserta?->souvenir?->nama_souvenir ?? null,
                'created_at' => $row->created_at ? $row->created_at->format('Y-m-d') : '-',
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw', 1)),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $responseData,
        ]);
    }

    /**
     * create.
     *
     * @return View
     */
    public function create()
    {
        // Get the maximum id from the Peserta table
        $maxId = Peserta::max('id');

        // Increment the maxId by 1 to get the next id
        $countPeserta = $maxId ? $maxId + 1 : 1;
        // return $nextId;

        return view('registrasi.create', compact('countPeserta'));
    }



    /**
     * storeSouvenir
     *
     * @param  mixed $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeSouvenir(Request $request)
    {
        // Validasi input (Tambahkan validasi exists untuk memastikan souvenir ada di database)
        $request->validate([
            'id_regist'   => 'required|exists:registrasis,id',
            'id_rkm'      => 'required',
            'id_souvenir' => 'required|exists:souvenirs,id',
        ]);

        try {
            // Memulai transaksi database
            DB::beginTransaction();

            $souvenir = souvenir::lockForUpdate()->find($request->id_souvenir);

            if (!$souvenir || $souvenir->stok <= 0) {
                DB::rollBack();
                return redirect()->back()->with(['error' => 'Stok souvenir habis atau tidak mencukupi!']);
            }

            souvenirpeserta::create([
                'id_regist'   => $request->id_regist,
                'id_rkm'      => $request->id_rkm,
                'id_souvenir' => $request->id_souvenir,
            ]);

            // 4. Kurangi stok souvenir sebanyak 1
            $souvenir->decrement('stok');

            DB::commit();

            return redirect()->back()->with(['success' => 'Souvenir berhasil ditambahkan dan stok telah dikurangi!']);
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Error saving souvenir: " . $e->getMessage());
            return redirect()->back()->with(['error' => 'Gagal menyimpan souvenir.']);
        }
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        // validate form
        // dd($request->all());
        $rkms = RKM::where('id', $request->id_rkm)->where('perusahaan_key', $request->perusahaan_key)->first();
        $peserta = Peserta::where('id', $request->id_peserta)->first();
        $registrasi = Registrasi::where('id_peserta', $request->id_peserta)->where('id_rkm', $request->id_rkm)->first();
        // dd($rkms);
        if ($registrasi === null) {
            if ($peserta === null) {
                $peserta = Peserta::create([
                    'nama' => $request->nama,
                    'jenis_kelamin' => $request->jenis_kelamin,
                    'email' => $request->email,
                    'no_hp' => $request->no_hp,
                    'alamat' => $request->alamat,
                    'perusahaan_key' => $request->perusahaan_key,
                    'tanggal_lahir' => $request->tanggal_lahir,
                ]);
            }
            if ($rkms === null) {
                return redirect()->route('registrasi.index')->with(['error' => 'Mohon maaf peserta ini daftar dikelas yang salah!']);
            }
            if ($rkms->isi_pax === '0') {
                return redirect()->route('registrasi.index')->with(['error' => 'Mohon maaf kapasitas kelas sudah penuh!']);
            } else {
                Registrasi::create([
                    'id_rkm' => $request->id_rkm,
                    'id_peserta' => $peserta->id,
                    'id_materi' => $rkms->materi_key,
                    'id_instruktur' => $rkms->instruktur_key,
                    'id_sales' => $rkms->sales_key,
                ]);

                $rkms->update([
                    'isi_pax' => $rkms->isi_pax - 1,
                ]);
            }
        } else {
            return redirect()->route('registrasi.index')->with(['error' => 'Mohon maaf anda sudah mendaftar kelas ini!']);
        }

        return redirect()->route('registrasi.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    /**
     * show.
     *
     * @param mixed $id
     */
    public function show(string $id): View
    {
        $post = Registrasi::findOrFail($id);

        return view('registrasi.show', compact('post'));
    }

    /**
     * edit.
     *
     * @param mixed $id
     *
     * @return View
     */
    public function edit(string $id)
    {
        $peserta = Registrasi::with('peserta', 'rkm')->findOrFail($id);
        $rkm = RKM::where('perusahaan_key', $peserta->rkm->perusahaan_key)->where('materi_key', $peserta->rkm->materi_key)->whereBetween('tanggal_awal', [$peserta->rkm->tanggal_awal, $peserta->rkm->tanggal_akhir])->get();

        // return $rkm;
        return view('registrasi.edit', compact('peserta', 'rkm'));
    }

    /**
     * update.
     *
     * @param mixed $request
     */
    public function update(Request $request, $id): RedirectResponse
    {
        // dd($request->all());
        $this->validate($request, [
            'id_rkm' => 'required',
            'id_peserta' => 'required',
        ]);

        $post = Registrasi::findOrFail($id);

        $post->update([
            'id_rkm' => $request->id_rkm,
            'id_peserta' => $request->id_peserta,
        ]);

        return redirect()->route('registrasi.index')->with(['success' => 'Data Berhasil Diubah!']);
    }

    /**
     * destroy.
     *
     * @return void
     */
    public function destroy($id)
    {
        $post = Registrasi::findOrFail($id);
        $feedback = Nilaifeedback::where('id_regist', $id)->first();
        $rkm = RKM::where('id', $post->id_rkm)->first();

        // Increment isi_pax by 1
        if ($rkm) {
            $rkm->increment('isi_pax');
        }

        if (!$feedback) {
            $post->delete();
        } else {
            $feedback->delete();
            $post->delete();
        }

        return redirect()->back()->with(['success' => 'Data Berhasil Dihapus!']);
    }

    public function exportExcel()
    {
        $registrasi = Registrasi::with(['rkm', 'peserta.perusahaan', 'materi', 'karyawan', 'sales', 'souvenirpeserta.souvenir'])->latest()->get();

        // Konfigurasi header Excel
        $data = $registrasi->map(function ($data, $index) {
            return [
                'No' => $index + 1,
                'Nama Peserta' => $data->peserta->nama,
                'Perusahaan' => $data->peserta->perusahaan->nama_perusahaan,
                'Materi Pelatihan' => $data->materi->nama_materi,
                'Periode Pelatihan' => $data->rkm->tanggal_awal.'s/d'.$data->rkm->tanggal_akhir,
                'Instruktur' => $data->karyawan?->kode_karyawan ?? '-',
                'Sales' => $data->sales?->kode_karyawan ?? '-',
                'Souvenir' => is_null($data->souvenirpeserta) || is_null($data->souvenirpeserta->first()) || is_null($data->souvenirpeserta->first()->souvenir)
             ? '-'
             : $data->souvenirpeserta->first()->souvenir->nama_souvenir

            ];
        });

        // Ekspor ke Excel
        return Excel::download(new RegistrasiExport($data), 'Data_Registrasi.xlsx');
    }

    public function exportPDF()
    {
        $registrasi = Registrasi::with(['rkm', 'peserta.perusahaan', 'materi', 'karyawan', 'sales', 'souvenirpeserta.souvenir'])->latest()->get();

        // $registrasi = $registrasi->map(function($item) {
        //     $item->souvenirpeserta->each(function($souvenirPeserta) {
        //         $souvenirPeserta->souvenir->makeHidden('blob_foto');
        //     });
        //     return $item;
        // });
        // Buat file PDF dari tampilan yang berisi data
        $pdf = Pdf::loadView('exports.registrasi-pdf', compact('registrasi'));

        return $pdf->download('Data_Registrasi.pdf');
    }

    public function exportExcelKhusus()
    {
        $user = auth()->user()->karyawan_id;
        $kode_karyawan = karyawan::where('id', $user)->value('kode_karyawan'); // Mengambil kode_karyawan

        $registrasi = Registrasi::with(['rkm', 'peserta.perusahaan', 'materi', 'karyawan', 'sales', 'souvenirpeserta.souvenir'])
                        ->where('id_instruktur', $kode_karyawan) // Filter berdasarkan id_instruktur
                        ->orWhere('id_sales', $kode_karyawan)    // Jika tidak ada di id_instruktur, filter berdasarkan id_sales
                        ->latest()
                        ->get();
        // Konfigurasi header Excel
        $data = $registrasi->map(function ($data, $index) {
            return [
                'No' => $index + 1,
                'Nama Peserta' => $data->peserta->nama,
                'Perusahaan' => $data->peserta->perusahaan->nama_perusahaan,
                'Materi Pelatihan' => $data->materi->nama_materi,
                'Periode Pelatihan' => $data->rkm->tanggal_awal.'s/d'.$data->rkm->tanggal_akhir,
                'Instruktur' => $data->karyawan?->kode_karyawan ?? '-',
                'Sales' => $data->sales?->kode_karyawan ?? '-',
                'Souvenir' => is_null($data->souvenirpeserta) || is_null($data->souvenirpeserta->first()) || is_null($data->souvenirpeserta->first()->souvenir)
             ? '-'
             : $data->souvenirpeserta->first()->souvenir->nama_souvenir

            ];
        });

        // Ekspor ke Excel
        return Excel::download(new RegistrasiPerSalesExport($data), 'Data_Registrasi.xlsx');
    }

    public function exportPDFKhusus()
    {
        $user = auth()->user()->karyawan_id;
        $kode_karyawan = karyawan::where('id', $user)->value('kode_karyawan'); // Mengambil kode_karyawan

        // Mengambil data registrasi dengan filter berdasarkan id_instruktur atau id_sales
        $registrasi = Registrasi::with(['rkm', 'peserta.perusahaan', 'materi', 'karyawan', 'sales', 'souvenirpeserta.souvenir'])
                        ->where('id_instruktur', $kode_karyawan)  // Filter berdasarkan id_instruktur
                        ->orWhere('id_sales', $kode_karyawan)     // Jika tidak ada di id_instruktur, filter berdasarkan id_sales
                        ->latest()
                        ->get();

        // Memproses souvenirpeserta untuk menghapus blob_foto
        $registrasi = $registrasi->map(function ($item) {
            $item->souvenirpeserta->each(function ($souvenirPeserta) {
                $souvenirPeserta->souvenir->makeHidden('blob_foto');
            });

            return $item;
        });

        // Buat file PDF dari tampilan yang berisi data registrasi
        $pdf = Pdf::loadView('exports.registrasi-pdf', compact('registrasi'));

        return $pdf->download('Data_Registrasi.pdf');
    }
}
