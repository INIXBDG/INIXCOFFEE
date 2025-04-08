<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use App\Models\Perusahaan;
use App\Models\karyawan;
use App\Models\Peserta;
use App\Models\comment;
use App\Models\eksam;
use App\Models\exam;
use App\Models\Registrasi;
use App\Models\nilaifeedback;

use Carbon\CarbonImmutable;
use App\Models\RKM;
use Illuminate\Support\Facades\DB;
use generateWeeks;

class PerusahaanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View Perusahaan', ['only' => ['index']]);
        $this->middleware('permission:Create Perusahaan', ['only' => ['create','store']]);
        $this->middleware('permission:Edit Perusahaan', ['only' => ['update','edit']]);
        $this->middleware('permission:Delete Perusahaan', ['only' => ['destroy']]);
    }
    public function index(): View
    {
        // $perusahaans = Perusahaan::latest()->paginate(25);
        $perusahaans = Perusahaan::with('karyawan')->paginate(25);


        return view('perusahaan.index', compact('perusahaans'));
    }
    public function getPerusahaanById(){
        $idSales = auth()->user()->id_sales;
        if ($idSales == 'AM'){
            $perusahaans = Perusahaan::where('nama_perusahaan', 'LIKE', '%'.request('q').'%')
                    ->paginate(20);
        }
        else{
        $perusahaans = Perusahaan::where('sales_key', $idSales) // Sesuaikan dengan nama kolom yang sesuai di tabel Perusahaan
                    ->where('nama_perusahaan', 'LIKE', '%'.request('q').'%')
                    ->paginate(20);
        }

        return response()->json($perusahaans);
    }


    /**
     * create
     *
     * @return View
     */
    public function create(): View
    {
        $sales = karyawan::where('jabatan', 'sales')->get();
        return view('perusahaan.create', compact('sales'));
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate the request
        $this->validate($request, [
            'nama_perusahaan' => 'required',
            'kategori_perusahaan' => 'nullable',
            'lokasi' => 'nullable',
            'sales_key' => 'nullable',
            'status' => 'nullable',
            'npwp' => 'nullable',
            'alamat' => 'nullable',
            'cp' => 'nullable',
            'no_telp' => 'nullable',
            'foto_npwp' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ]);

        // Check for existing record with the same data
        $existingRecord = Perusahaan::where('nama_perusahaan', $request->nama_perusahaan)
            ->where('kategori_perusahaan', $request->kategori_perusahaan)
            // ->where('lokasi', $request->lokasi)
            // ->where('sales_key', $request->sales_key)
            // ->where('status', $request->status)
            // ->where('npwp', $request->npwp)
            // ->where('alamat', $request->alamat)
            // ->where('no_telp', $request->no_telp)
            // ->where('cp', $request->cp)
            ->first();
        // return $existingRecord;
        // If a record exists, return an error response
        if ($existingRecord) {
            return redirect()->back()->withErrors(['error' => 'Data ini duplikat! Mohon cari dan edit jika ingin diubah.']);
        }

        // Handle file upload for 'foto_npwp'
        $filename = null;
        if ($request->hasFile('foto_npwp')) {
            $file = $request->file('foto_npwp');
            $extension = $file->getClientOriginalExtension();
            $filename = $request->nama_perusahaan . '_npwp.' . $extension;
            $file->storeAs('public/npwp', $filename);
        }

        // Create a new record
        Perusahaan::create([
            'nama_perusahaan' => $request->nama_perusahaan,
            'kategori_perusahaan' => $request->kategori_perusahaan,
            'lokasi' => $request->lokasi,
            'sales_key' => $request->sales_key,
            'status' => $request->status,
            'npwp' => $request->npwp,
            'alamat' => $request->alamat,
            'no_telp' => $request->no_telp,
            'cp' => $request->cp,
            'foto_npwp' => $filename,
        ]);

        // Redirect with success message
        return redirect()->route('perusahaan.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }


    //

    /**
     * show
     *
     * @param  mixed $id
     * @return View
     */
    public function show(string $id)
    {
        //get post by ID
        $post = Perusahaan::with('karyawan')->findOrFail($id);
        $peserta = Peserta::where('perusahaan_key', $id)->get();
        // return $post;
        return view('perusahaan.show', compact('post', 'peserta'));
    }

    /**
     * edit
     *
     * @param  mixed $id
     * @return View
     */
    public function edit(string $id): View
    {
        //get post by ID
        $perusahaans = Perusahaan::findOrFail($id);
        $sales = karyawan::where('jabatan', 'sales')->get();

        //render view with post
        return view('perusahaan.edit', compact('perusahaans', 'sales'));
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
        //validate form
        $this->validate($request, [
            'nama_perusahaan' => 'required',
            'kategori_perusahaan' => 'nullable',
            'lokasi' => 'nullable',
            'sales_key' => 'nullable',
            'status' => 'nullable',
            'npwp' => 'nullable',
            'alamat' => 'nullable',
            'cp' => 'nullable',
            'no_telp' => 'nullable',
            'foto_npwp' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ]);

        $post = Perusahaan::findOrFail($id);

        if ($request->hasFile('foto_npwp')) {

            Storage::delete('public/npwp/'.$post->foto_npwp);

            $file = $request->file('foto_npwp');
            $filename = $request->nama_perusahaan . '_npwp.' . $file->getClientOriginalExtension();
            $file->storeAs('public/npwp', $filename);

            //update post with new image
            $post->update([
                'nama_perusahaan' => $request->nama_perusahaan,
                'kategori_perusahaan' => $request->kategori_perusahaan,
                'lokasi' => $request->lokasi,
                'sales_key' => $request->sales_key,
                'status' => $request->status,
                'npwp' => $request->npwp,
                'alamat' => $request->alamat,
                'no_telp' => $request->no_telp,
                'cp' => $request->cp,
                'foto_npwp' => $filename,
            ]);

        } else {
            $post->update([
                'nama_perusahaan' => $request->nama_perusahaan,
                'kategori_perusahaan' => $request->kategori_perusahaan,
                'lokasi' => $request->lokasi,
                'sales_key' => $request->sales_key,
                'status' => $request->status,
                'npwp' => $request->npwp,
                'alamat' => $request->alamat,
                'no_telp' => $request->no_telp,
                'cp' => $request->cp,
            ]);
        }

        return redirect()->route('perusahaan.index')->with(['success' => 'Data Berhasil Diubah!']);
    }

    /**
     * destroy
     *
     * @param  mixed $post
     * @return void
     */
    public function destroy($id): RedirectResponse
    {
        // Temukan perusahaan dengan relasi rkms
        $post = Perusahaan::with('rkms')->findOrFail($id);

        // Temukan peserta yang terkait dengan perusahaan
        $peserta = Peserta::where('perusahaan_key', $id);

        // Hapus semua rkms yang terkait dengan perusahaan ini
        foreach ($post->rkms as $rkm) {
            // Temukan entitas yang terkait dengan RKM
            $registrasi = Registrasi::where('id_rkm', $rkm->id);
            $feedback = Nilaifeedback::where('id_rkm', $rkm->id);
            $exam = eksam::where('id_rkm', $rkm->id);
            $comment = Comment::where('rkm_key', $rkm->id);

            // Hapus semua entitas yang terkait dengan RKM
            $registrasi->delete();
            $feedback->delete();
            $exam->delete();
            $comment->delete();

            // Hapus RKM itu sendiri
            $rkm->delete();
        }

        // Hapus peserta yang terkait dengan perusahaan ini
        $peserta->delete();

        // Hapus perusahaan itu sendiri
        Storage::delete('public/npwp/' . $post->foto_npwp);
        $post->delete();

        return redirect()->route('perusahaan.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }



    // public function joinPerusahaanKaryawan()
    // {
    //     $startDate = CarbonImmutable::create(2020, 1, 1);
    //     $endDate = CarbonImmutable::create(2030, 12, 31);
    //     $now = CarbonImmutable::now()->locale('id_ID');

    //     $monthRanges = [];
    //     $date = $startDate;

    //     while ($date->month <= $endDate->month && $date->year <= $endDate->year) {
    //         $startOfMonth = $date->startOfMonth();
    //         $endOfMonth = $date->endOfMonth();

    //         $weekRanges = [];
    //         $startOfWeek = $startOfMonth->startOfWeek();
    //         while ($startOfWeek->lte($endOfMonth)) {
    //             $endOfWeek = $startOfWeek->copy()->endOfWeek();
    //             $weekRanges[] = ['start' => $startOfWeek->format('Y-m-d'), 'end' => $endOfWeek->format('Y-m-d')];
    //             $startOfWeek = $startOfWeek->addWeek();
    //         }

    //         $monthRanges[] = ['month' => $startOfMonth->translatedFormat('F-Y'), 'weeks' => $weekRanges];

    //         $date = $date->addMonth();
    //     }

    //     $years = [];
    //     $date = CarbonImmutable::create(2010, 1, 1); // Start from January 1, 2010

    //     while ($date->year <= 2030) { // Until the year 2030
    //         $years[] = $date->year;
    //         $date = $date->addYear(); // Add one year
    //     }

    //     $months = [];
    //     for ($i = 1; $i <= 12; $i++) {
    //         $months[] = $now->month($i)->translatedFormat('F');
    //     }

    //     $weekRanges = [];
    //     $date = $now->startOfMonth();

    //     while ($date->lte($endOfMonth) && $date->month == $now->month) {
    //         $startOfWeek = $date->startOfWeek()->format('Y-m-d');
    //         $endOfWeek = $date->endOfWeek()->format('Y-m-d');
    //         $weekRanges[] = ['start' => $startOfWeek, 'end' => $endOfWeek];

    //         $date = $date->addWeek();
    //     }

    //     $rkmsByWeek = [];
    //     foreach ($weekRanges as $weekRange) {
    //         $rows = RKM::with(['materi'])
    //             ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
    //             ->whereBetween('tanggal_awal', [$weekRange['start'],  $weekRange['end']])
    //             ->whereBetween('tanggal_akhir', [$weekRange['start'],  $weekRange['end']])
    //             ->select('r_k_m_s.materi_key', 'r_k_m_s.ruang','r_k_m_s.metode_kelas','r_k_m_s.event', 'r_k_m_s.tanggal_awal',
    //                 DB::raw('GROUP_CONCAT(r_k_m_s.instruktur_key SEPARATOR ", ") AS instruktur_all'),
    //                 DB::raw('GROUP_CONCAT(r_k_m_s.perusahaan_key SEPARATOR ", ") AS perusahaan_all'),
    //                 DB::raw('GROUP_CONCAT(r_k_m_s.sales_key SEPARATOR ", ") AS sales_all'),
    //                 DB::raw('SUM(r_k_m_s.pax) AS total_pax'))
    //             ->groupBy('r_k_m_s.materi_key', 'r_k_m_s.ruang','r_k_m_s.metode_kelas','r_k_m_s.event', 'r_k_m_s.tanggal_awal',)
    //             ->get();

    //         foreach ($rows as $row) {
    //             if ($row->instruktur_all == null){
    //                 $sales_ids = explode(', ', $row->sales_all);
    //                 $perusahaan_ids = explode(', ', $row->perusahaan_all);
    //                 $row->sales = Karyawan::whereIn('kode_karyawan', $sales_ids)->get();
    //                 $row->perusahaan = Perusahaan::whereIn('id', $perusahaan_ids)->get();

    //             }else{
    //                 $sales_ids = explode(', ', $row->sales_all);
    //                 $perusahaan_ids = explode(', ', $row->perusahaan_all);
    //                 $instruktur_ids = explode(', ', $row->instruktur_all);
    //                 $row->instruktur = Karyawan::whereIn('kode_karyawan', $instruktur_ids)->get();
    //                 $row->sales = Karyawan::whereIn('kode_karyawan', $sales_ids)->get();
    //                 $row->perusahaan = Perusahaan::whereIn('id', $perusahaan_ids)->get();
    //             }

    //         }
    //         $rkmsByWeek[] = ['weekRange' => $weekRange, 'rkms' => $rows];
    //     }

    //     $json = response()->json($rkmsByWeek);
    //     return $json;
    // }

    public function datas($tahun, $bulan,){
        // Perhitungan startDate dan endDate yang benar
        $startDate = "{$tahun}-{$bulan}-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $rkms = RKM::with(['materi'])
            ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
            ->whereYear('tanggal_awal', $tahun)
            ->whereMonth('tanggal_awal', $bulan)
            ->whereBetween('tanggal_awal', [$startDate, $endDate])
            ->whereBetween('tanggal_akhir', [$startDate, $endDate])
            ->select('r_k_m_s.materi_key', 'r_k_m_s.ruang','r_k_m_s.metode_kelas','r_k_m_s.event',
                DB::raw('GROUP_CONCAT(r_k_m_s.instruktur_key SEPARATOR ", ") AS instruktur_all'),
                DB::raw('GROUP_CONCAT(r_k_m_s.perusahaan_key SEPARATOR ", ") AS perusahaan_all'),
                DB::raw('GROUP_CONCAT(r_k_m_s.sales_key SEPARATOR ", ") AS sales_all'),
                DB::raw('SUM(r_k_m_s.pax) AS total_pax'))
            ->groupBy('r_k_m_s.materi_key', 'r_k_m_s.ruang','r_k_m_s.metode_kelas','r_k_m_s.event')
            ->get();

        foreach ($rkms as $row) {
            $instruktur_ids = explode(', ', $row->instruktur_all);
            $sales_ids = explode(', ', $row->sales_all);
            $perusahaan_ids = explode(', ', $row->perusahaan_all);

            $row->instruktur = Karyawan::whereIn('kode_karyawan', $instruktur_ids)->get();
            $row->sales = Karyawan::whereIn('kode_karyawan', $sales_ids)->get();
            $row->perusahaan = Perusahaan::whereIn('id', $perusahaan_ids)->get();
        }

        return response()->json(['data' => $rkms]);

    }

}
