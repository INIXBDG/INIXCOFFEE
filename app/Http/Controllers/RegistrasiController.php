<?php

namespace App\Http\Controllers;

use App\Exports\RegistrasiExport;
use App\Exports\RegistrasiPerSalesExport;
use App\Models\karyawan;
use App\Models\Nilaifeedback;
use App\Models\Registrasi;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\Peserta;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\RKM;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class RegistrasiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View Registrasi', ['only' => ['index']]);
        $this->middleware('permission:Create Registrasi', ['only' => ['create','store']]);
        $this->middleware('permission:Edit Registrasi', ['only' => ['update','edit']]);
        $this->middleware('permission:Delete Registrasi', ['only' => ['destroy']]);
    }
    public function index()
    {

        return view('registrasi.index');
    }

    public function getRegistrasiall()
    {
        try {
            // Ambil data registrasi beserta relasinya
            $registrasi = Registrasi::with(['rkm', 'peserta.perusahaan', 'materi', 'karyawan', 'sales', 'souvenirpeserta.souvenir'])
                                    ->latest()
                                    ->get();

            // Cek jabatan user untuk menentukan respons yang sesuai
            $jabatan = Auth::user()->jabatan;
            if (in_array($jabatan, ['Sales', 'Adm Sales', 'GM', 'SPV Sales', 'Instruktur', 'Education Manager', 'Office Manager', 'Customer Care', 'Customer Service', 'Admin Holding', 'Finance & Accounting', 'HRD', 'Koordinator Office', 'Programmer', 'Direktur Utama', 'Direktur', 'Technical Support'])) {
                return response()->json([
                    'success' => true,
                    'message' => 'List Registrasi',
                    'data' => $registrasi,
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'List Registrasi',
                    'data' => '',
                ]);
            }

        } catch (\Exception $e) {
            // Catat error ke log
            Log::error("Error fetching registrasi data: " . $e->getMessage());

            // Kirim respons error
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * create
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
     * store
     *
     * @param  mixed $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        //validate form
        // dd($request->all());
        $rkms = RKM::where('id', $request->id_rkm)->where('perusahaan_key', $request->perusahaan_key)->first();
        $peserta = Peserta::where('id', $request->id_peserta)->first();
        $registrasi = Registrasi::where('id_peserta', $request->id_peserta)->where('id_rkm', $request->id_rkm)->first();
        // dd($rkms);
        if ($registrasi === null) {
            if ($peserta === null) {
                $peserta = Peserta::create([
                    'nama'            => $request->nama,
                    'jenis_kelamin'   => $request->jenis_kelamin,
                    'email'           => $request->email,
                    'no_hp'           => $request->no_hp,
                    'alamat'          => $request->alamat,
                    'perusahaan_key'  => $request->perusahaan_key,
                    'tanggal_lahir'   => $request->tanggal_lahir
                ]);
            }
            if ($rkms === null) {
                return redirect()->route('registrasi.index')->with(['error' => 'Mohon maaf peserta ini daftar dikelas yang salah!']);
            }
            if ($rkms->isi_pax === '0') {
                return redirect()->route('registrasi.index')->with(['error' => 'Mohon maaf kapasitas kelas sudah penuh!']);
            }else{
                Registrasi::create([
                    'id_rkm'        => $request->id_rkm,
                    'id_peserta'    => $peserta->id,
                    'id_materi'     => $rkms->materi_key,
                    'id_instruktur' => $rkms->instruktur_key,
                    'id_sales'      => $rkms->sales_key,
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
     * show
     *
     * @param  mixed $id
     * @return View
     */
    public function show(string $id): View
    {
        $post = Registrasi::findOrFail($id);

        return view('registrasi.show', compact('post'));
    }

    /**
     * edit
     *
     * @param  mixed $id
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
     * update
     *
     * @param  mixed $request
     * @param  mixed $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        // dd($request->all());
        $this->validate($request, [
            'id_rkm'     => 'required',
            'id_peserta'   => 'required',
        ]);

        $post = Registrasi::findOrFail($id);

            $post->update([
                'id_rkm'     => $request->id_rkm,
                'id_peserta'     => $request->id_peserta,
            ]);

        return redirect()->route('registrasi.index')->with(['success' => 'Data Berhasil Diubah!']);
    }

    /**
     * destroy
     *
     * @param  mixed $post
     * @return void
     */
    public function destroy($id)
    {
        $post = Registrasi::findOrFail($id);
        $feedback = Nilaifeedback::where('id_regist', $id)->first();
        $rkm = RKM::where('id', $post->id_rkm)->first();

        // Increment isi_pax by 1
        $rkm->increment('isi_pax');

        if(!$feedback){
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
                'Periode Pelatihan' => $data->rkm->tanggal_awal . 's/d' . $data->rkm->tanggal_akhir,
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
        $pdf = PDF::loadView('exports.registrasi-pdf', compact('registrasi'));

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
                'Periode Pelatihan' => $data->rkm->tanggal_awal . 's/d' . $data->rkm->tanggal_akhir,
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
        $registrasi = $registrasi->map(function($item) {
            $item->souvenirpeserta->each(function($souvenirPeserta) {
                $souvenirPeserta->souvenir->makeHidden('blob_foto');
            });
            return $item;
        });

        // Buat file PDF dari tampilan yang berisi data registrasi
        $pdf = PDF::loadView('exports.registrasi-pdf', compact('registrasi'));

        return $pdf->download('Data_Registrasi.pdf');
    }


}
