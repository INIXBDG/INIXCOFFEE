<?php

namespace App\Http\Controllers;

use App\Exports\pengajuanIzinExport;
use App\Models\AbsensiKaryawan;
use App\Models\izinTigaJam;
use App\Models\karyawan;
use App\Models\User;
use App\Notifications\IzinExchangeNotification;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;

class izinTigaJamController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        return view('pengajuanizin.index');
    }

    public function getPengajuanIzin()
    {
        $user = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrFail($user);
        $jabatan = $karyawan->jabatan;
        $divisi = $karyawan->divisi;

        if (in_array($jabatan, ['Office Manager', 'Education Manager', 'SPV Sales', 'Koordinator ITSM'])) {
            $pengajuanizin = izinTigaJam::with('karyawan')->whereHas('karyawan', function ($query) use ($divisi) {
                $query->where('divisi', $divisi);
            })->latest()->get();
        } elseif (in_array($jabatan, ['HRD', 'GM', 'Koordinator Office'])) {
            $pengajuanizin = izinTigaJam::with('karyawan')->latest()->get();
        } else {
            $pengajuanizin = izinTigaJam::with('karyawan')->whereHas('karyawan', function ($query) use ($user) {
                $query->where('id', $user);
            })->latest()->get();
        }

        // Tambahkan tanggal_pengajuan_terformat ke setiap item
        $pengajuanizin->transform(function ($item) {
            $item->tanggal_pengajuan_terformat = \Carbon\Carbon::parse($item->tanggal_pengajuan)->format('d M Y');
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'List pengajuanizin',
            'data' => [
                'pengajuanizin' => $pengajuanizin,
                'divisi' => $divisi,
            ],
        ]);
    }

    public function create()
    {
        $user = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrFail($user);
        $karyawanall = karyawan::where('divisi', '!=', 'Direksi')->where('divisi', $karyawan->divisi)->get();
        return view('pengajuanizin.create', compact('karyawan', 'karyawanall'));
    }

    public function store(Request $request)
    {
        // Validasi input
        $this->validate($request, [
            'id_karyawan'   => 'required',
            'tanggal'        => 'required|date',
            'jam_mulai'     => 'required|date_format:H:i',
            'jam_selesai'   => 'required|date_format:H:i',
            'durasi'        => 'required|string',
            'alasan'        => 'required|string',
            'tanggal_pengajuan' => 'required|date',
        ]);
        // dd($request->all());

        $karyawan = karyawan::findOrFail($request->id_karyawan);

        $tanggal = $request->input('tanggal');
        $tanggalPengajuan = $request->input('tanggal_pengajuan');
        $hariIni = now()->toDateString();

        // // Cek apakah tanggal izin adalah hari ini
        $isToday = $tanggal === $hariIni;

        // // Validasi rentang tanggal izin: hanya kemarin, hari ini, atau besok
        // $tanggalIzin = \Carbon\Carbon::parse($tanggal)->startOfDay();
        // $today = \Carbon\Carbon::parse($hariIni)->startOfDay();
        // $kemarin = $today->copy()->subDay();
        // $besok = $today->copy()->addDay();

        // // Cek apakah tanggal izin di luar rentang (kemarin s/d besok)
        // if ($tanggalIzin->lt($kemarin) || $tanggalIzin->gt($besok)) {
        //     return redirect()->route('pengajuanizin.index')
        //         ->with(['error' => 'Izin hanya dapat diajukan untuk kemarin, hari ini, atau besok.']);
        // }

        // Hanya lakukan validasi ketat jika mengajukan izin untuk HARI INI
        if ($isToday) {
            $jamMulai = \Carbon\Carbon::createFromFormat('H:i', $request->jam_mulai);
            $sekarang = \Carbon\Carbon::now();

            // Pengecualian untuk jam 08:00
            $isJamDelapan = $jamMulai->format('H:i') === '08:00';

            if (!$isJamDelapan) {
                // Cek apakah sudah absen
                $absensiKaryawan = AbsensiKaryawan::where('id_karyawan', $karyawan->id)
                    ->whereDate('tanggal', $hariIni)
                    ->first();

                if (!$absensiKaryawan) {
                    return redirect()->route('pengajuanizin.index')
                        ->with(['error' => 'Anda harus absen terlebih dahulu jika mengajukan izin untuk hari ini (kecuali izin jam 08:00).']);
                }
            }

            // Validasi jam mulai tidak boleh kurang dari waktu sekarang
            if ($jamMulai->lt($sekarang)) {
                return redirect()->route('pengajuanizin.index')
                    ->with(['error' => 'Jam mulai tidak boleh kurang dari waktu saat ini.']);
            }
        }

        // Untuk izin kemarin atau besok: TIDAK perlu validasi absensi dan jam
        // Langsung simpan saja

        // Simpan izin
        izinTigaJam::create([
            'id_karyawan'        => $request->id_karyawan,
            'tanggal'            => $request->tanggal,
            'jam_mulai'          => $request->jam_mulai,
            'jam_selesai'        => $request->jam_selesai,
            'durasi'             => $request->durasi,
            'alasan'             => $request->alasan,
            'tanggal_pengajuan'  => $tanggalPengajuan,
            'approval'           => '0',
        ]);

        // Ambil divisi dan jabatan karyawan
        $divisi = $karyawan->divisi;
        $jabatan = $karyawan->jabatan;

        // Ambil daftar atasan berdasarkan jabatan
        $Offman    = karyawan::where('jabatan', 'Office Manager')->first();
        $kooroff   = karyawan::where('jabatan', 'Koordinator Office')->first();
        $koorSO    = karyawan::where('jabatan', 'Koordinator ITSM')->first();
        $Eduman    = karyawan::where('jabatan', 'Education Manager')->first();
        $SPVSales  = karyawan::where('jabatan', 'SPV Sales')->first();
        $GM        = karyawan::where('jabatan', 'GM')->first();

        $kodePenerima = [];

        switch ($jabatan) {
            case 'SPV Sales':
            case 'Office Manager':
            case 'Education Manager':
            case 'Koordinator Office':
            case 'Koordinator ITSM':
                if ($GM) $kodePenerima[] = $GM->kode_karyawan;
                break;

            default:
                switch ($divisi) {
                    case 'Education':
                        if ($Eduman) $kodePenerima[] = $Eduman->kode_karyawan;
                        break;
                    case 'Sales & Marketing':
                        if ($SPVSales) $kodePenerima[] = $SPVSales->kode_karyawan;
                        break;
                    case 'Office':
                        if ($kooroff) $kodePenerima[] = $kooroff->kode_karyawan;
                        break;
                    case 'IT Service Management':
                        if ($koorSO)  $kodePenerima[] = $koorSO->kode_karyawan;
                        break;
                }
                break;
        }

        $users = User::whereHas('karyawan', function ($query) use ($kodePenerima) {
            $query->whereIn('kode_karyawan', $kodePenerima);
        })->get();

        // Siapkan data notifikasi
        $notificationData = [
            'tipe'         => 'Izin 3 Jam',
            'jam_mulai'    => $request->jam_mulai,
            'jam_selesai'  => $request->jam_selesai,
            'durasi'       => $request->durasi,
            'approval'     => 0,
            'alasan_approval' => null,
        ];

        $type = 'Izin 3 Jam';
        $path = '/pengajuanizin';
        $to = $karyawan->nama_lengkap;

        foreach ($users as $user) {
            $receiverId = $user->id;

            NotificationFacade::send(
                $user,
                new IzinExchangeNotification(
                    $notificationData,
                    $path,
                    $to,
                    $type,
                    $receiverId  
                )
            );
        }


        return redirect()->route('pengajuanizin.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }
    public function show($id)
    {
        $suratperjalanan = izinTigaJam::with('karyawan')->findOrFail($id);
        $divisi = $suratperjalanan->karyawan->divisi;
        $jabatan = $suratperjalanan->karyawan->jabatan;
        if ($jabatan === 'SPV Sales' || $jabatan === 'Office Manager' || $jabatan === 'Education Manager' || $jabatan === 'Koordinator Office') {
            $manager = karyawan::where('jabatan', 'GM')->first();
        } elseif ($divisi == 'Office') {
            $manager = karyawan::where('jabatan', 'Koordinator Office')->first();
        } elseif ($divisi == 'IT Service Management') {
            $manager = karyawan::where('jabatan', 'Koordinator ITSM')->first();
        } elseif ($divisi == 'Sales & Marketing') {
            $manager = karyawan::where('jabatan', 'SPV Sales')->first();
        } elseif ($divisi == 'Education') {
            $manager = karyawan::where('jabatan', 'Education Manager')->first();
        }
        $hrd = karyawan::where('jabatan', 'HRD')->first();

        return view('pengajuanizin.form', compact('suratperjalanan', 'manager', 'hrd'));
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
        $this->validate($request, [
            'approval' => 'nullable',
            'alasan_approval' => 'nullable',
        ]);

        $post = izinTigaJam::findOrFail($id);
        $jabatan = auth()->user()->jabatan;
        $currentApproval = $post->approval;

        // Ambil data karyawan yang mengajukan
        $karyawan = karyawan::findOrFail($post->id_karyawan);
        $jabatanPemohon = $karyawan->jabatan;

        // Logika penolakan: jika isi alasan_approval dan approval == 2, ubah jadi 4
        if ($request->approval == 2 && $request->filled('alasan_approval')) {
            $request->merge([
                'approval' => 4,
                'alasan_approval' => $request->alasan_approval,
            ]);
        }

        $allowedApproval = false;

        // ===== APPROVAL UNTUK YANG BISA DIAPPROVE GM =====
        $jabatanTinggi = ['SPV Sales', 'Koordinator ITSM'];
        $jabatansepesial = ['HRD', 'Finance & Accounting', 'Office Boy', 'Driver'];
        $divisiPemohon = $karyawan->divisi;

        if (
            in_array($jabatanPemohon, $jabatanTinggi) ||
            ($divisiPemohon === 'Office' && in_array($jabatanPemohon, $jabatansepesial))
        ) {
            // Untuk kategori ini: GM bisa langsung approve dari status 0 ke 1
            if ($jabatan === 'GM' && $currentApproval == 0 && $request->approval == 1) {
                $allowedApproval = true;
            }
            // HRD tetap bisa approve final dari 1 ke 2
            elseif ($jabatan === 'HRD' && $currentApproval == 1 && $request->approval == 2) {
                $allowedApproval = true;
            }
        }
        // ===== APPROVAL UNTUK KARYAWAN BIASA (FLOW NORMAL) =====
        else {
            if (
                in_array($jabatan, ['Koordinator Office', 'Office Manager', 'Education Manager', 'SPV Sales', 'Koordinator ITSM']) &&
                $currentApproval == 0 && $request->approval == 1
            ) {
                $allowedApproval = true;
            }
            // Approval final oleh HRD
            elseif (
                $jabatan === 'HRD' && $currentApproval == 1 && $request->approval == 2
            ) {
                $allowedApproval = true;
            }
        }

        // ===== PENOLAKAN (BISA DILAKUKAN SIAPA SAJA) =====
        if (
            in_array($jabatan, ['Koordinator Office', 'Office Manager', 'Education Manager', 'SPV Sales', 'Koordinator ITSM', 'HRD', 'GM']) &&
            $request->approval == 4
        ) {
            $allowedApproval = true;
        }

        if ($allowedApproval) {
            $post->update([
                'approval' => $request->approval,
                'alasan_approval' => $request->alasan_approval,
                'date_approval' => now(),
            ]);
        } else {
            return redirect()->route('pengajuanizin.index')->with(['error' => 'Anda tidak berhak melakukan approval pada tahap ini.']);
        }

        // Rest of the notification logic remains the same...
        // (notification code here)

        return redirect()->route('pengajuanizin.index')->with(['success' => 'Data Berhasil Diubah!']);
    }

    /**
     * destroy
     *
     * @param  mixed $post
     * @return void
     */
    public function destroy($id)
    {
        $post = izinTigaJam::findOrFail($id);
        $post->delete();

        return redirect()->route('pengajuanizin.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }

    public function pengajuanJamExcel(Request $request)
    {
        $filename = 'pengajuan-izin3jam-' . now()->format('Y_m_d_H_i') . '.xlsx';

        return Excel::download(new pengajuanIzinExport(), $filename);
    }

    public function pengajuanJamPDF(Request $request)
    {
        $jenisPK = $request->input('jenis_PK');

        $rows = izinTigaJam::with('karyawan')->get();

        $pdf = Pdf::loadView('exports.pengajuanizinjamPDF', [
            'rows'    => $rows,
        ])->setPaper('A4', 'portrait');

        $filename = 'rekap-pengajuanIzin3jam' . '-' . now()->format('Y_m_d_H_i') . '.pdf';
        return $pdf->download($filename);
    }
}
