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
use App\Models\Contact;
use App\Models\eksam;
use App\Models\exam;
use App\Models\lokasi;
use App\Models\Registrasi;
use App\Models\nilaifeedback;
use App\Models\Peluang;
use Carbon\CarbonImmutable;
use App\Models\RKM;
use App\Models\SopPerusahaan;
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
        $jabatan = auth()->user()->jabatan;
        $idSales = auth()->user()->id_sales;
        if ($idSales == 'VN'){
            $perusahaans = Perusahaan::where('nama_perusahaan', 'LIKE', '%'.request('q').'%')
                    ->paginate(20);
        }else if($jabatan == 'Customer Care' || $jabatan == 'Admin Holding'){
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
        $lokasi = lokasi::all();
        $sales = karyawan::where('jabatan', 'sales')->get();
        return view('perusahaan.create', compact('sales', 'lokasi'));
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

    public function merge(Request $request)
    {
        $request->validate([
            'primary_id' => 'required|exists:perusahaans,id',
            'duplicate_id' => 'required|exists:perusahaans,id|different:primary_id',
        ]);

        DB::beginTransaction();
        try {
            $primaryId = $request->primary_id;
            $duplicateId = $request->duplicate_id;

            // 1. Pembaruan Relasi RKM
            RKM::where('perusahaan_key', $duplicateId)
                ->update(['perusahaan_key' => $primaryId]);

            // 2. Pembaruan Relasi Peserta
            Peserta::where('perusahaan_key', $duplicateId)
                ->update(['perusahaan_key' => $primaryId]);

            // 3. Pembaruan Relasi Contact
            Contact::where('id_perusahaan', $duplicateId)
                ->update(['id_perusahaan' => $primaryId]);

            // 4. Pembaruan Relasi Peluang
            // Berdasarkan relasi di model: hasMany(Peluang::class, 'id_contact', 'id')
            Peluang::where('id_contact', $duplicateId)
                ->update(['id_contact' => $primaryId]);

            // 5. Pembaruan Relasi Registrasi
            // Sesuaikan kolom foreign key dengan skema database aktual (contoh: perusahaan_key)
            // Registrasi::where('perusahaan_key', $duplicateId)->update(['perusahaan_key' => $primaryId]);

            // 6. Penghapusan Data Perusahaan Duplikat
            Perusahaan::where('id', $duplicateId)->delete();

            DB::commit();

            return response()->json([
                'status' => 'success', 
                'message' => 'Data perusahaan beserta relasi berhasil digabungkan.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error', 
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    public function indexSop(){
        $perusahaan = Perusahaan::with('sop', 'karyawan')->get();
        // dd($perusahaan->toArray());

        return view('office.sop.index', compact('perusahaan'));
    }

    public function detailSop($id){
        $perusahaan = Perusahaan::with('sop', 'picPenagihan')->findOrFail($id);
        // dd($perusahaan);

        return view('office.sop.detail', compact('perusahaan'));
    }

    public function storeSop(Request $request){
        $request->validate([
            'judul' => 'required|string',
            'sop' => 'required|array',
            'sop.*' => 'required|string',
        ]);

        SopPerusahaan::create([
            'id_perusahaan' => $request->id_perusahaan,
            'judul' => $request->judul,
            'sop' => json_encode($request->sop),
        ]);

        return redirect()->route('sop.perusahaan.detail', $request->id_perusahaan)->with('success', 'SOP berhasil ditambahkan!');
    }

    public function updateSop(Request $request, $id){
        $request->validate([
            'judul' => 'required|string',
            'sop' => 'required|array',
            'sop.*' => 'required|string',
        ]);

        $sop = SopPerusahaan::findOrFail($id);
        $sop->update([
            'judul' => $request->judul,
            'sop' => json_encode($request->sop),
        ]);

        return redirect()->route('sop.perusahaan.detail', $sop->id_perusahaan)->with('success', 'SOP berhasil diperbarui!');
    }

    public function deleteSop($id){
        $sop = SopPerusahaan::findOrFail($id);
        $perusahaanId = $sop->id_perusahaan;
        $sop->delete();

        return redirect()->route('sop.perusahaan.detail', $perusahaanId)->with('success', 'SOP berhasil dihapus!');
    }

}
