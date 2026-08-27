<?php

namespace App\Http\Controllers;

use App\Models\karyawan;
use App\Models\Perusahaan;
use App\Models\Peserta;
use App\Models\Registrasi;
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

    /**
     * Helper untuk mendapatkan sales_key milik user yang sedang login
     */
    private function getSalesKey()
    {
        $user = Auth::user();
        if (!$user) {   
            return null;
        }
        if (!empty($user->id_sales)) {
            return $user->id_sales;
        }
        if (!empty($user->karyawan_id)) {
            return karyawan::where('id', $user->karyawan_id)->value('kode_karyawan');
        }
        return null;
    }

    public function index()
    {
        $user = Auth::user();
        $query = Peserta::with('perusahaan');

        if ($user && $user->jabatan === 'Sales') {
            $salesKey = $this->getSalesKey();
            $query->whereHas('perusahaan', function ($q) use ($salesKey) {
                $q->where('sales_key', $salesKey);
            });
        }

        $post = $query->get();

        return view('peserta.index', compact('post'));
    }

    public function getPesertaall()
    {
        $user = Auth::user();
        $jabatan = $user->jabatan;

        $query = Peserta::with('perusahaan', 'latestRegistrasi')->latest();

        if ($jabatan === 'Sales') {
            $salesKey = $this->getSalesKey();
            $query->whereHas('perusahaan', function ($q) use ($salesKey) {
                $q->where('sales_key', $salesKey);
            });
        }

        $peserta = $query->get();

        if (in_array($jabatan, [
            'Sales',
            'Adm Sales',
            'GM',
            'SPV Sales',
            'Instruktur',
            'Education Manager',
            'Office Manager',
            'Customer Care',
            'Customer Service',
            'Admin Holding',
            'Finance & Accounting',
            'Koordinator Office',
            'HRD',
            'Programmer',
            'Direktur Utama',
            'Direktur',
            'Technical Support',
        ])) {
            return response()->json([
                'success' => true,
                'message' => 'List Peserta',
                'data' => $peserta,
            ]);
        } else {
            return response()->json([
                'success' => true,
                'message' => 'List Peserta',
                'data' => '',
            ]);
        }
    }

    public function getPesertaById($id)
    {
        $user = Auth::user();
        $query = Peserta::with('perusahaan');

        if ($user && $user->jabatan === 'Sales') {
            $salesKey = $this->getSalesKey();
            $query->whereHas('perusahaan', function ($q) use ($salesKey) {
                $q->where('sales_key', $salesKey);
            });
        }

        $peserta = $query->findOrFail($id);

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
        $user = Auth::user();
        if ($user && $user->jabatan === 'Sales') {
            $salesKey = $this->getSalesKey();
            $perusahaans = Perusahaan::where('sales_key', $salesKey)->get();
        } else {
            $perusahaans = Perusahaan::all();
        }

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
        $this->validate($request, [
            'nama'            => 'required',
            'jenis_kelamin'   => 'required',
            'email'           => 'required',
            'no_hp'           => 'required',
            'alamat'          => 'nullable',
            'perusahaan_key'  => 'required',
            'tanggal_lahir'   => 'nullable',
        ]);

        Peserta::create([
            'nama'           => $request->nama,
            'jenis_kelamin'  => $request->jenis_kelamin,
            'email'          => $request->email,
            'no_hp'          => $request->no_hp,
            'alamat'         => $request->alamat,
            'perusahaan_key' => $request->perusahaan_key,
            'tanggal_lahir'  => $request->tanggal_lahir
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
        $user = Auth::user();
        $query = Peserta::query();

        if ($user && $user->jabatan === 'Sales') {
            $salesKey = $this->getSalesKey();
            $query->whereHas('perusahaan', function ($q) use ($salesKey) {
                $q->where('sales_key', $salesKey);
            });
        }

        $post = $query->findOrFail($id);

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
        $user = Auth::user();
        $query = Peserta::with('perusahaan');

        if ($user && $user->jabatan === 'Sales') {
            $salesKey = $this->getSalesKey();
            $query->whereHas('perusahaan', function ($q) use ($salesKey) {
                $q->where('sales_key', $salesKey);
            });
        }

        $peserta = $query->findOrFail($id);

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
        $user = Auth::user();
        $query = Peserta::query();

        if ($user && $user->jabatan === 'Sales') {
            $salesKey = $this->getSalesKey();
            $query->whereHas('perusahaan', function ($q) use ($salesKey) {
                $q->where('sales_key', $salesKey);
            });
        }

        $post = $query->findOrFail($id);

        $this->validate($request, [
            'nama'           => 'required',
            'jenis_kelamin'  => 'required',
            'email'          => 'required',
            'no_hp'          => 'required',
            'alamat'         => 'required',
            'tanggal_lahir'  => 'required',
            'perusahaan_key' => 'required',
        ]);

        $post->update([
            'nama'           => $request->nama,
            'jenis_kelamin'  => $request->jenis_kelamin,
            'email'          => $request->email,
            'no_hp'          => $request->no_hp,
            'alamat'         => $request->alamat,
            'tanggal_lahir'  => $request->tanggal_lahir,
            'perusahaan_key' => $request->perusahaan_key
        ]);

        return redirect()->route('peserta.index')->with(['success' => 'Data Berhasil Diubah!']);
    }

    /**
     * destroy
     *
     * @param  mixed $id
     * @return RedirectResponse
     */
    public function destroy($id): RedirectResponse
    {
        $user = Auth::user();
        $query = Peserta::query();

        if ($user && $user->jabatan === 'Sales') {
            $salesKey = $this->getSalesKey();
            $query->whereHas('perusahaan', function ($q) use ($salesKey) {
                $q->where('sales_key', $salesKey);
            });
        }

        $post = $query->findOrFail($id);

        $post->delete();

        return redirect()->route('peserta.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }

    public function exportExcel()
    {
        $user = Auth::user();
        if ($user && $user->jabatan === 'Sales') {
            return $this->exportExcelKhusus();
        }

        $dataPeserta = Peserta::with('perusahaan')->get();

        $data = $dataPeserta->map(function ($peserta, $index) {
            return [
                'No'             => $index + 1,
                'Nama'           => $peserta->nama,
                'Email'          => $peserta->email,
                'Jenis Kelamin'  => $peserta->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan',
                'Nomor Handphone'=> $peserta->no_hp,
                'Alamat'         => $peserta->alamat,
                'Perusahaan'     => $peserta->perusahaan ? $peserta->perusahaan->nama_perusahaan : '-',
                'Tanggal Lahir'  => \Carbon\Carbon::parse($peserta->tanggal_lahir)->format('d F Y')
            ];
        });

        return Excel::download(new \App\Exports\PesertaExport($data), 'Data_Peserta.xlsx');
    }

    public function exportPDF()
    {
        $user = Auth::user();
        if ($user && $user->jabatan === 'Sales') {
            return $this->exportPDFKhusus();
        }

        $dataPeserta = Peserta::with('perusahaan')->get();
        $pdf = PDF::loadView('exports.peserta-pdf', compact('dataPeserta'));

        return $pdf->download('Data_Peserta.pdf');
    }

    public function exportExcelKhusus()
    {
        $salesKey = $this->getSalesKey();

        $dataPeserta = Peserta::with('perusahaan')
                ->whereHas('perusahaan', function($query) use ($salesKey) {
                    $query->where('sales_key', $salesKey);
                })
                ->latest()
                ->get();

        $data = $dataPeserta->map(function ($peserta, $index) {
            return [
                'No'             => $index + 1,
                'Nama'           => $peserta->nama,
                'Email'          => $peserta->email,
                'Jenis Kelamin'  => $peserta->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan',
                'Nomor Handphone'=> $peserta->no_hp,
                'Alamat'         => $peserta->alamat,
                'Perusahaan'     => $peserta->perusahaan ? $peserta->perusahaan->nama_perusahaan : '-',
                'Sales'          => $peserta->perusahaan ? $peserta->perusahaan->sales_key : '-',
                'Tanggal Lahir'  => \Carbon\Carbon::parse($peserta->tanggal_lahir)->format('d F Y')
            ];
        });

        return Excel::download(new \App\Exports\PesertaPerSalesExport($data), 'Data_Peserta.xlsx');
    }

    public function exportPDFKhusus()
    {
        $salesKey = $this->getSalesKey();

        $dataPeserta = Peserta::with('perusahaan')
                ->whereHas('perusahaan', function($query) use ($salesKey) {
                    $query->where('sales_key', $salesKey);
                })
                ->latest()
                ->get();

        $pdf = PDF::loadView('exports.peserta-pdf', compact('dataPeserta'))
                  ->setPaper('a4', 'landscape');

        return $pdf->download('Data_Peserta.pdf');
    }
}

