<?php

namespace App\Http\Controllers;

use App\Models\pengajuancuti;
use App\Models\karyawan;
use App\Models\User;
use App\Notifications\ApprovalCutiNotification;
use App\Notifications\CutiExchangeNotification;
use App\Notifications\PengajuanCutiNotification;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification as NotificationFacade;


class PengajuancutiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(): View
    {
        return view('pengajuancuti.index');
    }

    public function getPengajuanCuti() 
    {
        $user = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrfail($user);
        // return $karyawan;
        $jabatan = $karyawan->jabatan;
        $divisi = $karyawan->divisi;
        if ($jabatan == 'Office Manager' || $jabatan == 'Education Manager' || $jabatan == 'SPV Sales' || $jabatan == 'Koordinator ITSM') {
            $pengajuancuti = PengajuanCuti::with('karyawan')->whereHas('karyawan', function($query) use ($divisi) {
                $query->where('divisi', $divisi);
            })->latest()->get();
        }elseif($jabatan == 'HRD' || $jabatan == 'GM' || $jabatan == 'Koordinator Office'){
            $pengajuancuti = pengajuancuti::with('karyawan')->latest()->get();
        }else{
            $pengajuancuti = PengajuanCuti::with('karyawan')->whereHas('karyawan', function($query) use ($user) {
                $query->where('id', $user);
            })->latest()->get();
        }
        return response()->json([
            'success' => true,
            'message' => 'List pengajuancuti',
            'data' => $pengajuancuti,
        ]);
    }

    /**
     * create
     *
     * @return View
     */
    public function create()
    {
        $user = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrFail($user);
        $karyawanall = karyawan::where('divisi', '!=', 'Direksi')->where('divisi', $karyawan->divisi)->get();
        // return $karyawan;
        return view('pengajuancuti.create', compact('karyawan', 'karyawanall'));
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        // dd($request->all());
        // Validate form
        $this->validate($request, [
            'id_karyawan'   => 'required',
            'backup_karyawan1'   => 'required',
            'backup_karyawan2'   => 'nullable',
            'tipe'          => ['required', 'string', 'not_in:-,null'], // Disallow '-' and 'null'
            'tanggal_awal'  => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
            'durasi'        => 'required',
            'kontak'        => 'required|numeric',
            'alasan'        => 'required',
            'surat_sakit'   => 'required_if:tipe,sakit|nullable|mimes:jpg,jpeg,png,pdf|max:2048', // Validate file type and size
        ], [
            'tipe.not_in' => 'Anda harus memilih tipe cuti yang valid.',
            'tanggal_akhir.after_or_equal' => 'Tanggal Selesai Cuti tidak boleh kurang dari Tanggal Mulai Cuti.',
            'surat_sakit.required_if' => 'Harus sertakan surat sakit.',
            'surat_sakit.mimes' => 'Surat sakit harus berupa file dengan format: jpg, jpeg, png, pdf.',
            'surat_sakit.max' => 'Surat sakit tidak boleh lebih dari 2MB.',
        ]);
        
        $karyawan = karyawan::where('id', $request->id_karyawan)->first();
        // Assuming $karyawan contains the data of the karyawan retrieved from the database
        $awal_kontrak = $karyawan->awal_kontrak;

        // Parse the date from the string 'awal_kontrak'
        $awal_kontrak_date = \Carbon\Carbon::parse($awal_kontrak);

        // Calculate the difference in months from 'awal_kontrak' to now
        $hitungbulan = $awal_kontrak_date->diffInMonths(now());
        // dd($hitungbulan);
        if($hitungbulan <= 5 && $request->tipe == 'Cuti'){
            return redirect()->back()->withInput()->withErrors(['error' => 'Anda belum bisa mengajukan cuti!']);
        }

        // Handle file upload
        $suratSakitPath = null;
        if ($request->hasFile('surat_sakit')) {
            $file = $request->file('surat_sakit');
            $suratSakitPath = $file->store('surat_sakit', 'public');
        }
        // Check if leave quota is exhausted or duration exceeds available quota
        if ($request->tipe == 'Cuti' && $request->cuti == '0') {
            return redirect()->back()->withInput()->withErrors(['error' => 'Kuota Cuti Anda habis!']);
        }
        if ($request->tipe == 'Cuti' && $request->durasi > $request->cuti) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Durasi Cuti melampaui kuota yang tersedia!']);
        }
        if ($request->backup_karyawan1 == null || $request->backup_karyawan1 == '-') {
            return redirect()->back()->withInput()->withErrors(['error' => 'Harus mengisi pengganti anda pada saat cuti!']);
        }
        // Create the leave request
        pengajuancuti::create([
            'tipe'             => $request->tipe,
            'id_karyawan'      => $request->id_karyawan,
            'backup_karyawan1'      => $request->backup_karyawan1,
            'backup_karyawan2'      => $request->backup_karyawan2,
            'tanggal_awal'     => $request->tanggal_awal,
            'tanggal_akhir'    => $request->tanggal_akhir,
            'durasi'           => $request->durasi,
            'kontak'           => $request->kontak,
            'alasan'           => $request->alasan,
            'surat_sakit'      => $suratSakitPath, // Store the file path in the database
            'approval_manager' => '0',
        ]);
    
        // Retrieve users based on the filtered list of kode_karyawan
        $karyawan = karyawan::findOrFail($request->id_karyawan);
        $divisi = $karyawan->divisi;
        $jabatan = $karyawan->jabatan;
    
        $Offman = karyawan::where('jabatan', 'Office Manager')->first();
        $kooroff = karyawan::where('jabatan', 'Koordinator Office')->first();
        $koorSO = karyawan::where('jabatan', 'Koordinator ITSM')->first();
        $Eduman = karyawan::where('jabatan', 'Education Manager')->first();
        $SPVSales = karyawan::where('jabatan', 'SPV Sales')->first();
        $GM = karyawan::where('jabatan', 'GM')->first();
        $users = []; // Start with the current karyawan's kode_karyawan
    
        switch ($jabatan) {
            case 'SPV Sales':
            case 'Office Manager':
            case 'Education Manager':
            case 'Koordinator Office':
            case 'Koordinator ITSM':
                $users[] = $GM->kode_karyawan; // GM
        break;
        
            default:
                switch ($divisi) {
                    case 'Education':
                        $users[] = $Eduman->kode_karyawan; // Eduman
                        break;
        
                    case 'Sales & Marketing':
                        $users[] = $SPVSales->kode_karyawan; // SPVSales
                        break;
        
                    case 'Office':
                        // $users[] = $Offman->kode_karyawan; // Offman
                        $users[] = $kooroff->kode_karyawan; // Offman
                        break;
                    case 'Office':
                        // $users[] = $Offman->kode_karyawan; // Offman
                        $users[] = $koorSO->kode_karyawan; // Offman
                        break;
                }
                break;
        }
        
        // Retrieve users based on the filtered list of kode_karyawan
        $users = User::whereHas('karyawan', function ($query) use ($users) {
            $query->whereIn('kode_karyawan', $users);
        })->get();
    
        // Send notifications
        $data = $request->except('surat_sakit');
        $type = 'Mengajukan Cuti';
        $path = '/pengajuancuti';
    
        foreach ($users as $user) {
            NotificationFacade::send($user, new PengajuanCutiNotification($data, $path, $type));
        }
        
        $people = [];
        // Add the backup karyawan values from the request, excluding '-' values
        if ($request->backup_karyawan1 !== '-') {
            $people[] = $request->backup_karyawan1;
        }

        if ($request->backup_karyawan2 !== '-') {
            $people[] = $request->backup_karyawan2;
        }

        // Retrieve users based on the filtered list of kode_karyawan
        $users = User::whereHas('karyawan', function ($query) use ($people) {
            $query->whereIn('kode_karyawan', $people);
        })->get();

        // Send notifications
        $data = $request->except('surat_sakit');
        $type = 'Meminta anda untuk menggantikan posisi nya dikarenakan';
        $path = '/pengajuancuti';

        foreach ($users as $user) {
            NotificationFacade::send($user, new CutiExchangeNotification($data, $path, $type));
        }

    
        return redirect()->route('pengajuancuti.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    public function show($id)
    {
        $suratperjalanan = pengajuancuti::with('karyawan')->findOrFail($id);
        // return $suratperjalanan;
        $divisi = $suratperjalanan->karyawan->divisi;
        $jabatan = $suratperjalanan->karyawan->jabatan;
        if($jabatan === 'SPV Sales' || $jabatan === 'Office Manager' || $jabatan === 'Education Manager' || $jabatan === 'Koordinator Office'){
            $manager = karyawan::where('jabatan', 'GM')->first();
        } elseif($divisi == 'Office'){
            $manager = karyawan::where('jabatan', 'Koordinator Office')->first();
        } elseif($divisi == 'IT Service Management'){
            $manager = karyawan::where('jabatan', 'Koordinator ITSM')->first();
        } elseif($divisi == 'Sales & Marketing'){
            $manager = karyawan::where('jabatan', 'SPV Sales')->first();
        } elseif($divisi == 'Education' ){
            $manager = karyawan::where('jabatan', 'Education Manager')->first();
        }
        $hrd = karyawan::where('jabatan', 'HRD')->first();
        // $office_manager = karyawan::where('jabatan', 'Office Manager')->first();
        $office_manager = karyawan::where('jabatan', 'Koordinator Office')->first();
        $orang1 = karyawan::where('kode_karyawan', $suratperjalanan->backup_karyawan1)->first();
        $orang1 = $orang1->nama_lengkap ?? '-';
        $orang2 = karyawan::where('kode_karyawan', $suratperjalanan->backup_karyawan2)->first();
        $orang2 = $orang2->nama_lengkap ?? '-';

        return view('pengajuancuti.form', compact('suratperjalanan', 'manager', 'hrd', 'office_manager', 'orang1', 'orang2'));
    }
    

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Validate the incoming request
        $this->validate($request, [
            'approval' => 'nullable',
            'alasan' => 'nullable',
        ]);

        // Retrieve the specific 'pengajuancuti' record
        $post = pengajuancuti::findOrFail($id);

        // Get the user's role
        $jabatan = auth()->user()->jabatan;

        // Update the record based on the user's role
        if (in_array($jabatan, ['Office Manager', 'Koordinator Office', 'Education Manager', 'SPV Sales', 'GM', 'Koordinator ITSM'])) {
            $post->update([
                'approval_manager' => $request->approval,
                'alasan_manager' => $request->alasan,
            ]);
            
        } else {
            return redirect()->route('pengajuancuti.index')->with(['error' => 'Tidak Bisa mengubah Approval!']);
        }
        // dd($request->all());
        $karyawan = karyawan::findOrFail($post->id_karyawan);
        $HRD = karyawan::where('jabatan' , 'HRD')->first();
        if($post->tipe === 'Cuti' && $post->approval_manager === '1'){
            $durasi = $post->durasi;  
            $karyawan->decrement('cuti', $durasi);    
        }
        $users = [
            $karyawan->kode_karyawan,
            $HRD->kode_karyawan
        ];
        // return $users;
        // Retrieve the first matching user based on the 'kode_karyawan'
        $users = User::whereHas('karyawan', function ($query) use ($users) {
            $query->whereIn('kode_karyawan', array_filter($users));
        })->get();

        $data = $post;

        $to = $karyawan->nama_lengkap;

        $path = '/pengajuancuti';
        
        foreach ($users as $user) {
           NotificationFacade::send($user, new ApprovalCutiNotification($data, $path, $to));
        }

        return redirect()->route('pengajuancuti.index')->with(['success' => 'Data Berhasil Diubah!']);
    }

    /**
     * destroy
     *
     * @param  mixed $post
     * @return void
     */
    public function destroy($id)
    {
        $post = pengajuancuti::with('karyawan')->findOrFail($id);

        $cuti = $post->karyawan->cuti;
        $tambah_cuti = $post->durasi;
        if ($post->tipe === 'Cuti' && $post->approval_manager === '1') {
            $post->karyawan->update(['cuti' => $cuti + $tambah_cuti]);
        }
        // return $post;

        $post->delete();

        return redirect()->route('pengajuancuti.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }
}
