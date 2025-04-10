<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuratPerjalanan;
use App\Models\User;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use App\Models\karyawan;
use App\Notifications\ApprovalSPJNotification;
use App\Notifications\PengajuanSPJNotification;

class SuratPerjalananController extends Controller
{
    /**
     * Menampilkan daftar surat perjalanan.
     */
    public function index()
    {
        return view('suratperjalanan.index');
    }

    public function getSuratPerjalanan() 
    {
        $user = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrfail($user);
        // return $karyawan;
        $jabatan = $karyawan->jabatan;
        $divisi = $karyawan->divisi;
        if ($jabatan == 'Office Manager' || $jabatan == 'Education Manager' || $jabatan == 'SPV Sales' || $jabatan == 'Koordinator ITSM') {
            $SuratPerjalanan = SuratPerjalanan::with('karyawan')->whereHas('karyawan', function($query) use ($divisi) {
                $query->where('divisi', $divisi);
            })->latest()->get();
        }elseif($jabatan == 'HRD' || $jabatan == "Koordinator Office" || $jabatan == 'GM' || $jabatan == 'Direktur Utama' || $jabatan == 'Direktur'){
            $SuratPerjalanan = SuratPerjalanan::with('karyawan')->latest()->get();
        }else{
            $SuratPerjalanan = SuratPerjalanan::with('karyawan')->whereHas('karyawan', function($query) use ($user) {
                $query->where('id', $user);
            })->latest()->get();
        }
        return response()->json([
            'success' => true,
            'message' => 'List SuratPerjalanan',
            'data' => $SuratPerjalanan,
        ]);
    }

    /**
     * Menampilkan form untuk membuat surat perjalanan baru.
     */
    public function create()
    {
        $user = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrFail($user);
        return view('suratperjalanan.create', compact('karyawan'));
    }

    /**
     * Menyimpan surat perjalanan baru ke dalam database.
     */
    public function store(Request $request)
    {
        // return $request->all();
        $request->validate([
            'id_karyawan' => 'required|string|max:255',
            'tipe' => ['required', 'string', 'max:255', 'not_in:-,null'], // Disallow '-' and 'null'
            'tujuan' => 'required|string|max:255',
            'tanggal_berangkat' => 'required|date',
            'tanggal_pulang' => 'required|date|after_or_equal:tanggal_berangkat',
            'alasan' => 'required|string',
        ], [
            'tipe.not_in' => 'Anda harus memilih jenis travel yang valid.',
            'tanggal_pulang.after_or_equal' => 'Tanggal Pulang tidak boleh kurang dari Tanggal Berangkat.',
        ]);
              

        $suratPerjalanan = SuratPerjalanan::create($request->all());

        $karyawan = karyawan::findOrFail($request->id_karyawan);
        $divisi = $karyawan->divisi;
        $jabatan = $karyawan->jabatan;

        $Offman = karyawan::where('jabatan' , 'Office Manager')->first();
        $kooroff = karyawan::where('jabatan', 'Koordinator Office')->first();
        $koorso = karyawan::where('jabatan', 'Koordinator ITSM')->first();
        $Eduman = karyawan::where('jabatan' , 'Education Manager')->first();
        $SPVSales = karyawan::where('jabatan' , 'SPV Sales')->first();
        $GM = karyawan::where('jabatan' , 'GM')->first();
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
                    case 'IT Service Management':
                        // $users[] = $Offman->kode_karyawan; // Offman
                        $users[] = $koorso->kode_karyawan; // Offman
                        break;
                }
                break;
        }

        // Retrieve users based on the filtered list of kode_karyawan
        $users = User::whereHas('karyawan', function ($query) use ($users) {
            $query->whereIn('kode_karyawan', $users);
        })->get();

        // return $users;
        $data = $suratPerjalanan;
        $type = 'Mengajukan Surat Perjalanan';

        $path = '/suratperjalanan';
        
        foreach ($users as $user) {
           NotificationFacade::send($user, new PengajuanSPJNotification($data, $path, $type));
        }

        return redirect()->route('suratperjalanan.index')->with('success', 'Surat perjalanan berhasil dibuat.');
    }

    /**
     * Menampilkan detail surat perjalanan tertentu.
     */
    public function show($id)
    {
        $suratperjalanan = SuratPerjalanan::with('karyawan')->findOrFail($id);
        // return $suratperjalanan;
        $divisi = $suratperjalanan->karyawan->divisi;
        $jabatan = $suratperjalanan->karyawan->jabatan;
        if($jabatan === 'SPV Sales' || $jabatan === 'Office Manager' || $jabatan === 'Education Manager' || $jabatan = 'Koordinator Office'){
            $manager = karyawan::where('jabatan', 'GM')->first();
        } elseif($divisi == 'Office'){
            // $manager = karyawan::where('jabatan', 'Office Manager')->first();
            $manager = karyawan::where('jabatan', 'Koordinator Office')->first();

        } elseif($divisi == 'Sales & Marketing'){
            $manager = karyawan::where('jabatan', 'SPV Sales')->first();
        } elseif($divisi == 'Education' ){
            $manager = karyawan::where('jabatan', 'Education Manager')->first();
        }elseif($divisi == 'Direksi' ){
            $manager = karyawan::where('id', $suratperjalanan->id_karyawan)->first();
        }else{
            $manager = karyawan::where('jabatan', 'GM')->first();
        }
        
        // $hrd = karyawan::where('jabatan', 'HRD')->first();
        // $office_manager = karyawan::where('jabatan', 'Office Manager')->first();
        $office_manager = karyawan::where('jabatan', 'Finance & Accounting')->first();
        $hrd = karyawan::where('jabatan', 'Koordinator Office')->first();


        return view('suratperjalanan.form', compact('suratperjalanan', 'manager', 'hrd', 'office_manager'));
    }

    /**
     * Menampilkan form untuk mengedit surat perjalanan.
     */
    public function edit($id)
    {
        $suratperjalanan = SuratPerjalanan::findOrFail($id);
        $user = $suratperjalanan->id_karyawan;
        $karyawan = karyawan::findOrFail($user);
        return view('suratperjalanan.edit', compact('suratperjalanan', 'karyawan'));
    }

    public function editspj($id)
    {
        $suratperjalanan = SuratPerjalanan::findOrFail($id);
        $user = $suratperjalanan->id_karyawan;
        $karyawan = karyawan::findOrFail($user);
        return view('suratperjalanan.editspj', compact('suratperjalanan', 'karyawan'));
    }

    /**
     * Memperbarui surat perjalanan di dalam database.
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());
        $request->validate([
            'id_karyawan' => 'required|string|max:255',
            'approval_hrd' => 'required|string|max:255',
            'durasi' => 'required|string|max:255',
            'ratemakan' => 'nullable',
            'ratespj' => 'nullable',
            'ratetaksi' => 'nullable',
            'total' => 'required',
        ]);
        $suratPerjalanan = SuratPerjalanan::findOrFail($id);
            $suratPerjalanan->update($request->all());
            $karyawan = karyawan::findOrFail($suratPerjalanan->id_karyawan);
            $Offman = karyawan::where('jabatan' , 'Office Manager')->first();
            $kooroff = karyawan::where('jabatan', 'Koordinator Office')->first();
            
            $users = [
                $karyawan->kode_karyawan,
                $Offman->kode_karyawan,
                $kooroff->kode_karyawan,
            ];
    
            // Retrieve the first matching user based on the 'kode_karyawan'
            $users = User::whereHas('karyawan', function ($query) use ($users) {
                $query->whereIn('kode_karyawan', array_filter($users));
            })->get();
    
            $data = $suratPerjalanan;
    
            $to = $karyawan->nama_lengkap;
    
            $path = '/suratperjalanan';
            
            foreach ($users as $user) {
               NotificationFacade::send($user, new ApprovalSPJNotification($data, $path, $to));
            }
        return redirect()->route('suratperjalanan.index')->with('success', 'Surat perjalanan berhasil diperbarui.');
    }

    /**
     * Menghapus surat perjalanan dari database.
     */
    public function destroy($id)
    {
        $suratPerjalanan = SuratPerjalanan::findOrFail($id);
        $suratPerjalanan->delete();

        return redirect()->route('suratperjalanan.index')->with('success', 'Surat perjalanan berhasil dihapus.');
    }

    public function approval(Request $request ,$id)
    {
        $suratPerjalanan = SuratPerjalanan::findOrFail($id);
        $suratPerjalanan->update($request->all());
        $karyawan = karyawan::findOrFail($suratPerjalanan->id_karyawan);
        $HRD = karyawan::where('jabatan' , 'HRD')->first();
        // $Offman = karyawan::where('jabatan' , 'Office Manager')->first();
        $kooroff = karyawan::where('jabatan', 'Koordinator Office')->first();


        $users = [
            $karyawan->kode_karyawan,
            $HRD->kode_karyawan,
            $kooroff->kode_karyawan,
        ];

        // Retrieve the first matching user based on the 'kode_karyawan'
        $users = User::whereHas('karyawan', function ($query) use ($users) {
            $query->whereIn('kode_karyawan', array_filter($users));
        })->get();

        $data = $suratPerjalanan;

        $to = $karyawan->nama_lengkap;

        $path = '/suratperjalanan';
        
        foreach ($users as $user) {
           NotificationFacade::send($user, new ApprovalSPJNotification($data, $path, $to));
        }

        return redirect()->route('suratperjalanan.index')->with('success', 'Surat perjalanan berhasil disetujui.');
    }
}
