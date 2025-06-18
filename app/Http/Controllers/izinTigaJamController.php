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
        $this->validate($request, [
            'id_karyawan'   => 'required',
            'jam_mulai'     => 'required|date_format:H:i',
            'jam_selesai'   => 'required|date_format:H:i',
            'durasi'        => 'required|string',
            'alasan'        => 'required|string',
        ]);

        $karyawan = karyawan::findOrFail($request->id_karyawan);

        $absensiKaryawan = AbsensiKaryawan::where('id_karyawan', $karyawan->id)
            ->whereDate('tanggal', now()->toDateString())
            ->first();

        if (!$absensiKaryawan) {
            return redirect()->route('pengajuanizin.index')->with(['error' => 'Anda diharapkan absen terlebih dahulu!']);
        } else {
            izinTigaJam::create([
                'id_karyawan'   => $request->id_karyawan,
                'jam_mulai'     => $request->jam_mulai,
                'jam_selesai'   => $request->jam_selesai,
                'durasi'        => $request->durasi,
                'alasan'        => $request->alasan,
                'approval'      => '0',
            ]);
        }


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
                        if ($koorSO)  $kodePenerima[] = $koorSO->kode_karyawan;
                        break;
                }
                break;
        }

        // Ambil user dari karyawan yang sesuai
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

        // Kirim notifikasi ke semua user atasan
        foreach ($users as $user) {
            NotificationFacade::send($user, new IzinExchangeNotification($notificationData, $path, $to, $type));
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

        // Logika penolakan: jika isi alasan_approval dan approval == 2, ubah jadi 4
        if ($request->approval == 2 && $request->filled('alasan_approval')) {
            $request->merge([
                'approval' => 4,
                'alasan_approval' => $request->alasan_approval,
            ]);
        }

        // Cek apakah user saat ini diizinkan approve berdasarkan urutan alur
        $allowedApproval = false;

        if (
            in_array($jabatan, ['Koordinator Office', 'Office Manager', 'Education Manager', 'SPV Sales', 'Koordinator ITSM']) &&
            $currentApproval == 0 && $request->approval == 1
        ) {
            $allowedApproval = true;
        } elseif (
            $jabatan === 'HRD' && $currentApproval == 1 && $request->approval == 2
        ) {
            $allowedApproval = true;
        } elseif (
            in_array($jabatan, ['Koordinator Office', 'Office Manager', 'Education Manager', 'SPV Sales', 'Koordinator ITSM', 'HRD', 'GM']) &&
            $request->approval == 4
        ) {
            // Penolakan diperbolehkan siapa saja dari list di atas
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

        // Ambil karyawan yang mengajukan dan HRD
        $karyawan = karyawan::findOrFail($post->id_karyawan);
        $HRD = karyawan::where('jabatan', 'HRD')->first();

        // Daftar kode karyawan yang akan menerima notifikasi
        $users_kode = [
            $karyawan->kode_karyawan,
            $HRD->kode_karyawan ?? null,
        ];

        $users = User::whereHas('karyawan', function ($query) use ($users_kode) {
            $query->whereIn('kode_karyawan', array_filter($users_kode));
        })->get();

        $data = $post;

        // Ambil jabatan koordinator dari divisi terkait
        $data_koordinator = ['Office Manager', 'Education Manager', 'SPV Sales', 'Koordinator Office', 'Koordinator ITSM'];

        $koordinator = karyawan::where('divisi', auth()->user()->divisi)
            ->whereIn('jabatan', $data_koordinator)
            ->first();

        $jabatanKoordinator = $koordinator ? $koordinator->jabatan : 'Koordinator';
        $approval = $request->approval;

        // Buat pesan notifikasi
        $type = "Izin 3 Jam";

        $to = $karyawan->nama_lengkap;
        $path = '/pengajuanizin';

        foreach ($users as $user) {
            NotificationFacade::send($user, new IzinExchangeNotification($data, $path, $to, $type));
        }

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
