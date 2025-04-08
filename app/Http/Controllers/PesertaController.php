<?php

namespace App\Http\Controllers;

use App\Models\karyawan;
use App\Models\Perusahaan;
use App\Models\Peserta;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class PesertaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View Peserta', ['only' => ['index']]);
        $this->middleware('permission:Create Peserta', ['only' => ['create','store']]);
        $this->middleware('permission:Edit Peserta', ['only' => ['update','edit']]);
    }
    public function index()
    {
        // $post = Peserta::with('perusahaan')->all();
        $post = Peserta::with('perusahaan')->get();

        // return $post;
        return view('peserta.index', compact('post'));
    }

    public function getPesertaall()
    {
        // $registrasi = Registrasi::with('rkm', 'peserta.perusahaan', 'materi')->get();
        $peserta = Peserta::with('perusahaan')->get();

        $jabatan = Auth::user()->jabatan;
        if ($jabatan == 'Sales'|| $jabatan == 'Adm Sales' || $jabatan == 'GM'|| $jabatan == 'SPV Sales'
        || $jabatan == 'Instruktur'|| $jabatan == 'Education Manager' || $jabatan == 'Office Manager'
        || $jabatan == 'Customer Care' || $jabatan == 'Customer Service' || $jabatan == 'Admin Holding' 
        || $jabatan == 'Finance & Accounting' || $jabatan == 'Koordinator Office'
        || $jabatan == 'HRD' || $jabatan == 'Programmer' || $jabatan == 'Direktur Utama' || $jabatan == 'Direktur') {
            return response()->json([
                'success' => true,
                'message' => 'List Registrasi',
                'data' => $peserta,
            ]);
        }else{
            return response()->json([
                'success' => true,
                'message' => 'List Registrasi',
                'data' => '',
            ]);
        }
    }

    public function getPesertaById($id)
    {
        $peserta = Peserta::with('perusahaan')->findOrFail($id);
            return response()->json([
                'success' => true,
                'message' => 'List Registrasi',
                'data' => $peserta,
            ]);
    }

    /**
     * create
     *
     * @return View
     */
    public function create(): View
    {
        $perusahaans = Perusahaan::all();
        return view('peserta.create', compact('perusahaans'));
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        //validate form
        // dd($request->all());
        $this->validate($request, [
            'nama'     => 'required',
            'jenis_kelamin'   => 'required',
            'email'   => 'required',
            'no_hp'   => 'required',
            'alamat'   => 'nullable',
            'perusahaan_key'   => 'required',
            'tanggal_lahir'   => 'nullable',

        ]);

        Peserta::create([
            'nama'     => $request->nama,
            'jenis_kelamin'     => $request->jenis_kelamin,
            'email'     => $request->email,
            'no_hp'     => $request->no_hp,
            'alamat'     => $request->alamat,
            'perusahaan_key'     => $request->perusahaan_key,
            'tanggal_lahir'   => $request->tanggal_lahir
        ]);

        return redirect()->route('peserta.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    /**
     * show
     *
     * @param  mixed $id
     * @return View
     */
    public function show(string $id): View
    {
        $post = Peserta::findOrFail($id);

        return view('peserta.show', compact('post'));
    }

    /**
     * edit
     *
     * @param  mixed $id
     * @return View
     */
    public function edit(string $id)
    {
        $peserta = Peserta::with('perusahaan')->findOrFail($id);
        // return $peserta;

        return view('peserta.edit', compact('peserta'));
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
            'nama'     => 'required',
            'jenis_kelamin'   => 'required',
            'email'   => 'required',
            'no_hp'   => 'required',
            'alamat'   => 'required',
            'tanggal_lahir'   => 'required',
            'perusahaan_key'   => 'required',
        ]);

        $post = Peserta::findOrFail($id);

            $post->update([
                'nama'     => $request->nama,
                'jenis_kelamin'     => $request->jenis_kelamin,
                'email'     => $request->email,
                'no_hp'     => $request->no_hp,
                'alamat'     => $request->alamat,
                'tanggal_lahir'   => $request->tanggal_lahir,
                'perusahaan_key'   => $request->perusahaan_key
            ]);

        return redirect()->route('peserta.index')->with(['success' => 'Data Berhasil Diubah!']);
    }

    /**
     * destroy
     *
     * @param  mixed $post
     * @return void
     */
    public function destroy($id): RedirectResponse
    {
        $post = Peserta::findOrFail($id);

        $post->delete();

        return redirect()->route('peserta.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }

    public function exportExcel()
    {
        $dataPeserta = Peserta::with('perusahaan')->get(); // Ambil data dari model

        // Konfigurasi header Excel
        $data = $dataPeserta->map(function ($peserta, $index) {
            return [
                'No' => $index + 1,
                'Nama' => $peserta->nama,
                'Email' => $peserta->email,
                'Jenis Kelamin' => $peserta->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan',
                'Nomor Handphone' => $peserta->no_hp,
                'Alamat' => $peserta->alamat,
                'Perusahaan' => $peserta->perusahaan->nama_perusahaan,
                'Tanggal Lahir' => \Carbon\Carbon::parse($peserta->tanggal_lahir)->format('d F Y')
            ];
        });

        // Ekspor ke Excel
        return Excel::download(new \App\Exports\PesertaExport($data), 'Data_Peserta.xlsx');
    }

    public function exportPDF()
    {
        $dataPeserta = Peserta::with('perusahaan')->get(); // Ambil data dari model

        // Buat file PDF dari tampilan yang berisi data
        $pdf = PDF::loadView('exports.peserta-pdf', compact('dataPeserta'));

        return $pdf->download('Data_Peserta.pdf');
    }

    public function exportExcelKhusus()
    {
        $user = auth()->user()->karyawan_id;
        $kode_karyawan = karyawan::where('id', $user)->value('kode_karyawan'); // Mengambil kode_karyawan
         // Mengambil data peserta dengan filter pada sales_key di relasi perusahaan
        $dataPeserta = Peserta::with('perusahaan')
                ->whereHas('perusahaan', function($query) use ($kode_karyawan) {
                    $query->where('sales_key', $kode_karyawan); // Filter berdasarkan sales_key di perusahaan
                })
                ->latest()
                ->get();

        // Konfigurasi header Excel
        $data = $dataPeserta->map(function ($peserta, $index) {
            return [
                'No' => $index + 1,
                'Nama' => $peserta->nama,
                'Email' => $peserta->email,
                'Jenis Kelamin' => $peserta->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan',
                'Nomor Handphone' => $peserta->no_hp,
                'Alamat' => $peserta->alamat,
                'Perusahaan' => $peserta->perusahaan->nama_perusahaan,
                'Sales' => $peserta->perusahaan->sales_key,  // Mengambil sales_key dari relasi perusahaan
                'Tanggal Lahir' => \Carbon\Carbon::parse($peserta->tanggal_lahir)->format('d F Y')
            ];
        });

        // Ekspor ke Excel
        return Excel::download(new \App\Exports\PesertaPerSalesExport($data), 'Data_Peserta.xlsx');
    }

    public function exportPDFKhusus()
    {
        $user = auth()->user()->karyawan_id;
        $kode_karyawan = karyawan::where('id', $user)->value('kode_karyawan'); // Mengambil kode_karyawan
    
        $dataPeserta = Peserta::with('perusahaan')
                ->whereHas('perusahaan', function($query) use ($kode_karyawan) {
                    $query->where('sales_key', $kode_karyawan); // Filter berdasarkan sales_key di perusahaan
                })
                ->latest()
                ->get();
    
        // Buat file PDF dari tampilan yang berisi data
        $pdf = PDF::loadView('exports.peserta-pdf', compact('dataPeserta'))
                  ->setPaper('a4', 'landscape'); // Set paper to A4 landscape
    
        return $pdf->download('Data_Peserta.pdf');
    }
    
}
