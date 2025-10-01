<?php

namespace App\Http\Controllers;
 
use App\Exports\pengajuanKlaimExport;
use App\Models\absensi_noRecord;
use App\Models\AbsensiKaryawan;
use App\Models\karyawan;
use App\Models\pembatalanCuti;
use App\Models\pengajuancuti;
use App\Models\User;
use App\Notifications\cancelLeaveExchangeNotification;
use App\Notifications\noRecordExchangeNotification;
use App\Notifications\schemeWorkExchangeNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class pengajuanKlaimController extends Controller
{
    public function index()
    {
        $id_karyawan = auth()->user()->karyawan_id;

        $noRecord = absensi_noRecord::where('jenis_PK', 'No Record')
            ->with(['absensiKaryawan', 'karyawan'])
            ->get();

        $schemeWork = absensi_noRecord::where('jenis_PK', 'Scheme Work')
            ->whereHas('absensiKaryawan')
            ->whereHas('karyawan')
            ->with(['absensiKaryawan', 'karyawan'])
            ->get();

        $cancelLeave = pembatalanCuti::whereHas('karyawan')
            ->with(['pengajuancuti', 'karyawan'])
            ->get();

        return view('pengajuanklaim.index', compact('noRecord', 'schemeWork', 'cancelLeave'));
    }

    public function noRecord()
    {
        $user = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrFail($user);

        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $data_absen = AbsensiKaryawan::where('id_karyawan', $user)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->get();

        $karyawanall = karyawan::where('divisi', '!=', 'Direksi')
            ->where('divisi', $karyawan->divisi)
            ->get();

        return view('pengajuanklaim.createNoRecord', compact('karyawan', 'karyawanall', 'data_absen'));
    }

    public function createNoRecord(Request $request)
    {
        $this->validate($request, [
            'id_karyawan'   => 'required|integer',
            'kendala'       => 'required|string',
            'tanggal_absen' => 'required|date',
            'bukti_gambar'  => 'required|image',
            'kronologi'     => 'required|string',
        ]);

        if($request->kendala == "Absen Pulang"){
            // Ambil data absensi berdasarkan tanggal & karyawan
            $absen = AbsensiKaryawan::whereDate('tanggal', $request->tanggal_absen)
                ->where('id_karyawan', $request->id_karyawan)
                ->whereNull('jam_keluar')
                ->whereNotNull('jam_masuk')
                ->first();
                // dd($absen);

            if (!$absen) {
                return back()->withErrors(['tanggal_absen' => 'Tanggal tidak ditemukan di data absensi. Anda hanya dapat mengajukan Absen Pulang pada tanggal kerja yang valid.']);
            }
        }


        // Cegah pengajuan ganda untuk tanggal yang sama
        $existingKlaim = absensi_noRecord::where('id_karyawan', $request->id_karyawan)
            ->whereDate('tanggal', $request->tanggal_absen)
            ->whereIn('approval', [0, 1]) // 0 = pending, 1 = disetujui
            ->first();

        if ($existingKlaim) {
            return back()->withErrors(['tanggal_absen' => 'Pengajuan untuk tanggal ini sudah ada atau sedang diproses.'])->withInput();
        }

        // Batasi Human Error maksimal 3x
        if ($request->kendala === 'Human Error') {
            $jumlahHE = absensi_noRecord::where('id_karyawan', $request->id_karyawan)
                ->where('kendala', 'Human Error')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            if ($jumlahHE >= 3) {
                return back()->withErrors([
                    'kendala' => 'Pengajuan dengan kendala "Human Error" hanya diperbolehkan maksimal 3 kali dalam sebulan.'
                ])->withInput();
            }
        }

       // Ambil file dari request
        $file = $request->file('bukti_gambar');

        // Validasi file (disarankan untuk produksi)
        $request->validate([
            'bukti_gambar' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Maks 2MB
        ]);

        // Buat nama file unik
        $filename = 'bukti_' . now()->format('Y_m_d_H_i_s') . '.' . $file->getClientOriginalExtension();

        // Simpan file ke direktori public/storage/pengajuan_klaim
        $path = Storage::disk('public')->put('pengajuan_klaim', $file);

        // Dapatkan path relatif untuk disimpan di database
        $fotoPath = $path; // Contoh: pengajuan_klaim/bukti_2025_09_01_13_16_20.jpg

        // Dapatkan URL publik (untuk ditampilkan di view)
        $fotoUrl = Storage::url($path); // Contoh: https://yoursite.com/storage/pengajuan_klaim/bukti_2025_09_01_13_16_20.jp

        // Validasi upload gambar
        if (!$fotoPath) {
            return back()->withErrors(['bukti_gambar' => 'Tidak dapat melampirkan bukti'])->withInput();
        }

        if (!$request->filled('tanggal_absen')) {
        return back()->withErrors(['tanggal_absen' => 'Tanggal absen tidak boleh kosong.'])->withInput();
        }

            // Simpan data ke tabel absensi_no_records
            absensi_noRecord::create([
                'id_karyawan'   => $request->id_karyawan,
                'tanggal'       => $request->tanggal_absen, // ✅ HARUS ADA & VALID
                'jenis_PK'      => 'No Record',
                'kendala'       => $request->kendala,
                'bukti_gambar'  => $fotoPath,
                'kronologi'     => $request->kronologi,
                'approval'      => 0,
            ]);

            // Kirim notifikasi
            $karyawan = karyawan::find($request->id_karyawan);
            $hrd = karyawan::where('jabatan', 'HRD')->first();

            $kodePenerima = [];
            if ($hrd) $kodePenerima[] = $hrd->kode_karyawan;
            if ($karyawan) $kodePenerima[] = $karyawan->kode_karyawan;

            $users = User::whereHas('karyawan', function ($query) use ($kodePenerima) {
                $query->whereIn('kode_karyawan', $kodePenerima);
            })->get();

            $statusMessage = "Menunggu Persetujuan HRD";
            $notificationData = [
                'tipe'            => 'no_record',
                'nama_lengkap'    => $karyawan->nama_lengkap,
                'kendala'         => $request->kendala,
                'tanggal'         => $request->tanggal_absen,
                'kronologi'       => $request->kronologi,
                'status'          => $statusMessage,
                'approval'        => 0,
                'alasan_approval' => null,
            ];

            $path = '/pengajuan-klaim?tabel=no_record';
            foreach ($users as $user) {
                NotificationFacade::send($user, new noRecordExchangeNotification($notificationData, $path));
            }

            // Redirect sesuai jabatan
            $jabatan = auth()->user()->karyawan->jabatan ?? null;
            if (!in_array($jabatan, ['HRD', 'Koordinator ITSM', 'Education Manager', 'SPV Sales'])) {
                return redirect('/pengajuan-klaim?tabel=no_record')->with('success', 'Berhasil mengajukan, menunggu persetujuan HRD.');
            }

            return redirect('/absensi/karyawan')->with('success', 'Berhasil mengajukan.');
    }

    public function deleteNoRecord(Request $request)
    {
        $this->validate($request, [
            'id_noRecord'       => 'required|integer',
        ]);
        $noRecord = absensi_noRecord::find($request->id_noRecord);
        $noRecord->delete();

        return redirect('/pengajuan-klaim?tabel=no_record')->with('success', 'Data Berhasil Dihapus');
    }

    public function cancelLeave()
    {
        $user = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrFail($user);

        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $data_cuti = pengajuancuti::where('id_karyawan', $user)
            ->where('approval_manager', '1')
            ->whereBetween('tanggal_awal', [$startOfMonth, $endOfMonth])
            ->get();

        $karyawanall = karyawan::where('divisi', '!=', 'Direksi')
            ->where('divisi', $karyawan->divisi)
            ->get();

        return view('absensi.pembatalancuti', compact('karyawan', 'karyawanall', 'data_cuti'));
    }

    public function createCancelLeave(Request $request)
    {
        $this->validate($request, [
            'id_karyawan'   => 'required|integer',
            'tanggal_cuti' => 'required|integer',
            'bukti_gambar'  => 'required|image',
            'kronologi'     => 'required|string',
        ]);

       // Ambil file dari request
        $file = $request->file('bukti_gambar');

        // Validasi file (disarankan untuk produksi)
        $request->validate([
            'bukti_gambar' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Maks 2MB
        ]);

        // Buat nama file unik
        $filename = 'bukti_' . now()->format('Y_m_d_H_i_s') . '.' . $file->getClientOriginalExtension();

        // Simpan file ke direktori public/storage/pengajuan_klaim
        $path = Storage::disk('public')->put('pengajuan_klaim', $file);

        // Dapatkan path relatif untuk disimpan di database
        $fotoPath = $path; // Contoh: pengajuan_klaim/bukti_2025_09_01_13_16_20.jpg

        // Dapatkan URL publik (untuk ditampilkan di view)
        $fotoUrl = Storage::url($path); // Contoh: https://yoursite.com/storage/pengajuan_klaim/bukti_2025_09_01_13_16_20.jp


        if (!$fotoPath) {
            return back()->withErrors(['bukti_gambar' => 'Tidak dapat melampirkan bukti'])->withInput();
        }

        $data_cuti = pengajuancuti::where('id', $request->tanggal_cuti)->first();

        pembatalanCuti::create([
            'id_karyawan'   => $request->id_karyawan,
            'id_cuti'       => $request->tanggal_cuti,
            'bukti_gambar'  => $fotoPath,
            'kronologi'     => $request->kronologi,
            'approval'      => '0',
            'tipe'          => $data_cuti->tipe,
            'tanggal_awal'  => $data_cuti->tanggal_awal,
            'tanggal_akhir' => $data_cuti->tanggal_akhir,
            'durasi'        => $data_cuti->durasi,
            'kontak'        => $data_cuti->kontak,
            'alasan'        => $data_cuti->alasan,
            'surat_sakit'   => $data_cuti->surat_sakit,
        ]);

        $karyawan = karyawan::find($request->id_karyawan);
        $hrd = karyawan::where('jabatan', 'HRD')->first();

        $kodePenerima = [];

        if ($hrd) {
            $kodePenerima[] = $hrd->kode_karyawan;
        }

        if ($karyawan) {
            $kodePenerima[] = $karyawan->kode_karyawan;
        }

        $users = User::whereHas('karyawan', function ($query) use ($kodePenerima) {
            $query->whereIn('kode_karyawan', $kodePenerima);
        })->get();

        $approval = 0;
        $statusMessage = "Menunggu Persetujuan HRD";

        if ($approval === 0) {
            $statusMessage = "Menunggu Persetujuan HRD";
        }

        $notificationData = [
            'tipe'            => 'cancel_leave',
            'nama_lengkap'    => $karyawan->nama_lengkap,
            'kronologi'       => $request->kronologi,
            'jenis'           => $data_cuti->tipe,
            'tanggal_awal'    => $data_cuti->tanggal_awal,
            'tanggal_akhir'   => $data_cuti->tanggal_akhir,
            'status'          => $statusMessage,
            'durasi'          => $data_cuti->durasi,
            'alasan'          => $data_cuti->alasan,
            'approval'        => 0,
            'alasan_approval' => null,
        ];

        $path = '/pengajuan-klaim?tabel=cancel_leave';

        foreach ($users as $user) {
            NotificationFacade::send($user, new cancelLeaveExchangeNotification($notificationData, $path));
        }

        return redirect('/pengajuan-klaim?tabel=cancel_leave')->with('success', 'Berhasil mengajukan');
    }

    public function deleteCancelLeave(Request $request)
    {
        $this->validate($request, [
            'id_cancel_leave'       => 'required|integer',
        ]);
        $cancelLeave = pembatalanCuti::find($request->id_cancel_leave);
        $cancelLeave->delete();

        return redirect('/pengajuan-klaim?tabel=cancel_leave')->with('success', 'Data Berhasil Dihapus');
    }

    public function schemeWork()
    {
        $user = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrFail($user);

        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $data_absen = AbsensiKaryawan::where('id_karyawan', $user)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->get();

        $karyawanall = karyawan::where('divisi', '!=', 'Direksi')
            ->where('divisi', $karyawan->divisi)
            ->get();

        return view('pengajuanklaim.createSchemaWork', compact('karyawan', 'karyawanall', 'data_absen'));
    }
    public function createSchemeWork(Request $request)
    {
        $this->validate($request, [
            'id_karyawan'   => 'required|integer',
            'tanggal_absen' => 'required|integer',
            'bukti_gambar'  => 'required|image',
            'kronologi'     => 'required|string',
        ]);

           // Ambil file dari request
        $file = $request->file('bukti_gambar');

        // Validasi file (disarankan untuk produksi)
        $request->validate([
            'bukti_gambar' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Maks 2MB
        ]);

        // Buat nama file unik
        $filename = 'bukti_' . now()->format('Y_m_d_H_i_s') . '.' . $file->getClientOriginalExtension();

        // Simpan file ke direktori public/storage/pengajuan_klaim
        $path = Storage::disk('public')->put('pengajuan_klaim', $file);

        // Dapatkan path relatif untuk disimpan di database
        $fotoPath = $path; // Contoh: pengajuan_klaim/bukti_2025_09_01_13_16_20.jpg

        // Dapatkan URL publik (untuk ditampilkan di view)
        $fotoUrl = Storage::url($path); // Contoh: https://yoursite.com/storage/pengajuan_klaim/bukti_2025_09_01_13_16_20.jp

        if (!$fotoPath) {
            return back()->withErrors(['bukti_gambar' => 'Tidak dapat melampirkan bukti'])->withInput();
        }

        absensi_noRecord::create([
            'id_karyawan'   => $request->id_karyawan,
            'jenis_PK'      => 'Scheme Work',
            'id_absen'      => $request->tanggal_absen,
            'bukti_gambar'  => $fotoPath,
            'kronologi'     => $request->kronologi,
            'approval'      => '0',
        ]);
        $absen = AbsensiKaryawan::where('id', $request->tanggal_absen)->first();

        $karyawan = karyawan::find($request->id_karyawan);
        $hrd = karyawan::where('jabatan', 'HRD')->first();

        $kodePenerima = [];

        if ($hrd) {
            $kodePenerima[] = $hrd->kode_karyawan;
        }

        if ($karyawan) {
            $kodePenerima[] = $karyawan->kode_karyawan;
        }

        $users = User::whereHas('karyawan', function ($query) use ($kodePenerima) {
            $query->whereIn('kode_karyawan', $kodePenerima);
        })->get();

        $approval = 0;
        $statusMessage = "Menunggu Persetujuan HRD";

        if ($approval === 0) {
            $statusMessage = "Menunggu Persetujuan HRD";
        }

        $notificationData = [
            'tipe'            => 'scheme_work',
            'nama_lengkap'    => $karyawan->nama_lengkap,
            'tanggal'         => $absen->tanggal,
            'status'          => $statusMessage,
            'kronologi'       => $request->kronologi,
            'approval'        => 0,
            'alasan_approval' => null,
        ];

        $path = '/pengajuan-klaim?tabel=schema_work';

        foreach ($users as $user) {
            NotificationFacade::send($user, new schemeWorkExchangeNotification($notificationData, $path));
        }

        return redirect('/pengajuan-klaim?tabel=schema_work')->with('success', 'Berhasil mengajukan');
    }
    public function deleteSchemeWork(Request $request)
    {
        $this->validate($request, [
            'id_scheme_work'       => 'required|integer',
        ]);
        $schemeWork = absensi_noRecord::find($request->id_scheme_work);
        $schemeWork->delete();

        return redirect('/pengajuan-klaim?tabel=schema_work')->with('success', 'Data Berhasil Dihapus');
    }

    public function pengajuanKlaimExcel(Request $request)
    {
        // dd($request->all());
        $jenisPK = $request->input('jenis_PK');
        $filename = 'pengajuan-klaim-' . now()->format('Y_m_d_H_i') . '-' . $jenisPK . '.xlsx';

        return Excel::download(new pengajuanKlaimExport($jenisPK), $filename);
    }

    public function pengajuanKlaimPDF(Request $request)
    {
        // dd($request->all());

        $jenisPK = $request->input('jenis_PK');

        if ($jenisPK === 'Cancel Leave') {
            $rows = pembatalanCuti::with(['karyawan', 'pengajuancuti'])->get();

            $pdf = Pdf::loadView('exports.cancelLeavePDF', [
                'rows'    => $rows,
                'jenisPK' => $jenisPK,
            ])->setPaper('A4', 'portrait');
        } else {
            $rows = absensi_noRecord::with(['karyawan', 'absensiKaryawan'])
                ->where('jenis_PK', $jenisPK)
                ->get();

            $pdf = Pdf::loadView('exports.pengajuanKlaimPDF', [
                'rows'    => $rows,
                'jenisPK' => $jenisPK,
            ])->setPaper('A4', 'portrait');
        }

        $filename = 'rekap-' . $jenisPK . '-' . now()->format('Y_m_d_H_i') . '.pdf';
        return $pdf->download($filename);
    }

    public function approval(Request $request)
    {
        // dd($request->all());
        $this->validate($request, [
            'type'       => 'required|string',
            'action'       => 'required|string',
        ]);

        $jabatan = auth()->user()->karyawan->jabatan ?? null;
        if (!in_array($jabatan, ['HRD', 'Koordinator ITSM', 'Education Manager', 'SPV Sales'])) {
            return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menyetujui pengajuan ini.',
                    'data' => $request->type,
                ]);
        }

        DB::beginTransaction();

        try {
            switch ($request->type) {
            case 'noRecord':
                $jenis_PK = absensi_noRecord::findOrfail($request->id);
                // $jenis_PK->approval = $request->approval;
                $jenis_PK->approval_date = now();
                if ($request->filled('alasan_approval')) {
                    $jenis_PK->alasan_approval = $request->alasan_approval;
                }
                $jenis_PK->save();
                if($jenis_PK->kendala == 'Absen Pulang'){
                    $cekAbsen = AbsensiKaryawan::where('id_karyawan', $jenis_PK->id_karyawan)
                                ->whereDate('tanggal', $jenis_PK->tanggal)
                                ->first();
                    // dd($cekAbsen);
                    if (!$cekAbsen) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Data Absen tidak ditemukan.',
                            'data' => $request->type,
                        ]);
                    }
                    $cekAbsen->jam_keluar =  '17:00:00';
                    $cekAbsen->save();
                    $jenis_PK->id_absen = $cekAbsen->id;
                    $jenis_PK->approval = 1;
                    $jenis_PK->waktu_masuk = $cekAbsen->jam_masuk;
                    $jenis_PK->waktu_pulang = $cekAbsen->jam_keluar;
                    $jenis_PK->save();
                }else{
                $newAbsen = AbsensiKaryawan::create([
                    'id_karyawan'         => $jenis_PK->id_karyawan,
                    'tanggal'             => $jenis_PK->tanggal,
                    'jam_masuk'           => $jenis_PK->waktu_masuk ?? '08:00:00',
                    'jam_keluar'          => $jenis_PK->waktu_pulang ?? '17:00:00',
                    'keterangan'              => 'Masuk (' . $request->type . ')',
                    'waktu_keterlambatan' => '00:00:00',
                    'keterangan_pulang'              => 'Pulang (' . $request->type . ')',
                    'foto' => $jenis_PK->bukti_gambar
                ]);
                $jenis_PK->id_absen = $newAbsen->id;
                $jenis_PK->approval = 1;
                $jenis_PK->waktu_masuk = $newAbsen->jam_masuk;
                $jenis_PK->waktu_pulang = $newAbsen->jam_keluar;
                $jenis_PK->save();
                }
               
                 // ✅ Kirim notifikasi
                $karyawan = karyawan::find($jenis_PK->id_karyawan);
                $approvers = karyawan::whereIn('jabatan', ['HRD', 'Koordinator ITSM'])->get();
                $kodePenerima = $approvers->pluck('kode_karyawan')->toArray();

                if ($karyawan) {
                    $kodePenerima[] = $karyawan->kode_karyawan;
                }

                $users = User::whereHas('karyawan', function ($query) use ($kodePenerima) {
                    $query->whereIn('kode_karyawan', $kodePenerima);
                })->get();

                $statusMessage = $jenis_PK->approval == 1 ? "Telah Disetujui Oleh HRD" : "Telah Ditolak Oleh HRD";


                $notificationData = [
                    'tipe'            => 'no_record',
                    'nama_lengkap'    => $karyawan->nama_lengkap,
                    'kendala'         => $jenis_PK->kendala,
                    'tanggal'         => $jenis_PK->tanggal,
                    'kronologi'       => $jenis_PK->kronologi,
                    'status'          => $statusMessage,
                    'approval'        => "Approve",
                    'alasan_approval' => $request->alasan_approval ?? null,
                ];

                $path = 'pengajuan-klaim?tabel=no_record';
                foreach ($users as $user) {
                    NotificationFacade::send($user, new noRecordExchangeNotification($notificationData, $path));
                }
            break;

            case 'schemeWork':
                $jenis_PK = absensi_noRecord::findOrfail($request->id);

                if (!$jenis_PK) {
                    return response()->json([
                            'success' => false,
                            'message' => 'Data Absen tidak ditemukan.',
                            'data' => $request->type,
                    ]);
                }
                // dd($jenis_PK);
        
                $jenis_PK->approval = '1';
                if ($request->filled('alasan_approval')) {
                    $jenis_PK->alasan_approval = $request->alasan_approval;
                }
                $jenis_PK->approval_date = now();

                $absen = AbsensiKaryawan::where('id', $jenis_PK->id_absen)->first();
                // hanya jika disetujui
                
                    if ($absen) {
                        // Format dari input form (request) = H:i (contoh: 11:00)
                        $waktuDiizinkan = \Carbon\Carbon::createFromFormat('H:i', $request->jam_masuk);

                        // Format dari database = H:i:s (contoh: 11:00:00)
                        $waktuAbsen = \Carbon\Carbon::createFromFormat('H:i:s', $absen->jam_masuk);

                        // Cek apakah masih dalam toleransi keterlambatan
                        if ($waktuAbsen->lte($waktuDiizinkan->copy()->addMinutes(59))) {
                            $absen->waktu_keterlambatan = "00:00:00";
                        }

                        $absen->save();
                    }

                    // Simpan waktu masuk & pulang yang diizinkan ke absensi_noRecord
                    $jenis_PK->waktu_masuk = $request->jam_masuk;
                    $jenis_PK->waktu_pulang = $request->jam_pulang;
               
                $jenis_PK->save();

                $karyawan = karyawan::find($jenis_PK->id_karyawan);
                $hrd = karyawan::where('jabatan', 'HRD')->first();

                $kodePenerima = [];

                if ($hrd) {
                    $kodePenerima[] = $hrd->kode_karyawan;
                }

                if ($karyawan) {
                    $kodePenerima[] = $karyawan->kode_karyawan;
                }

                $users = User::whereHas('karyawan', function ($query) use ($kodePenerima) {
                    $query->whereIn('kode_karyawan', $kodePenerima);
                })->get();


                $statusMessage = $jenis_PK->approval == 1 ? "Telah Disetujui Oleh HRD" : "Ditolak Oleh HRD";

                $notificationData = [
                    'tipe'            => 'scheme_work',
                    'nama_lengkap'    => $karyawan->nama_lengkap,
                    'kendala'         => $jenis_PK->kendala,
                    'tanggal'         => $absen->tanggal,
                    'kronologi'       => $jenis_PK->kronologi,
                    'status'          => $statusMessage,
                    'approval'        => $request->approval,
                    'alasan_approval' => $request->alasan_approval ?? null,
                ];

                $path = '/pengajuan-klaim?tabel=schema_work';

                foreach ($users as $user) {
                    NotificationFacade::send($user, new schemeWorkExchangeNotification($notificationData, $path));
                }
            break;

            case 'cancelLeave':
                $jenis_PK = pembatalanCuti::findOrFail($request->id);
                // dd($jenis_PK);
                $jenis_PK->approval = 1;
                if ($request->filled('alasan_approval')) {
                    $jenis_PK->alasan_approval = $request->alasan_approval;
                }
                $jenis_PK->approval_date = now();
                $jenis_PK->save();
                    // dd($jenis_PK->id_cuti, $request->approval);

                $karyawan = karyawan::find($jenis_PK->id_karyawan);
                $hrd = karyawan::where('jabatan', 'HRD')->first();

                $kodePenerima = [];

                if ($hrd) {
                    $kodePenerima[] = $hrd->kode_karyawan;
                }

                if ($karyawan) {
                    $kodePenerima[] = $karyawan->kode_karyawan;
                }
                $users = User::whereHas('karyawan', function ($query) use ($kodePenerima) {
                    $query->whereIn('kode_karyawan', $kodePenerima);
                })->get();
                $statusMessage = $jenis_PK->approval == 1 ? "Telah Disetujui Oleh HRD" : "Telah Ditolak Oleh HRD";
                $data_cuti = pengajuancuti::where('id', $jenis_PK->id_cuti)->first();
                if (!$data_cuti) {
                    return response()->json([
                            'success' => false,
                            'message' => 'Data Cuti tidak ditemukan.',
                            'data' => $request->type,
                    ]);
                }
                // dd($data_cuti);
                $notificationData = [
                    'tipe'            => 'cancel_leave',
                    'nama_lengkap'    => $karyawan->nama_lengkap,
                    'kronologi'       => $request->kronologi,
                    'jenis'           => $data_cuti->tipe,
                    'tanggal_awal'    => $data_cuti->tanggal_awal,
                    'tanggal_akhir'   => $data_cuti->tanggal_akhir,
                    'status'          => $statusMessage,
                    'durasi'          => $data_cuti->durasi,
                    'alasan'          => $data_cuti->alasan,
                    'approval'        => $jenis_PK->approval,
                    'alasan_approval' => null,
                ];
                if ($request->action == "approve") {
                    $deletingData = pengajuancuti::findOrFail($jenis_PK->id_cuti);
                    $deletingData->delete();
                }
                $path = '/pengajuan-klaim?tabel=cancel_leave';

                foreach ($users as $user) {
                    NotificationFacade::send($user, new cancelLeaveExchangeNotification($notificationData, $path));
                }
            break;

            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal Mengambil Data.',
                    'data' => $request->type,
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Berhasil memproses data.',
                'data' => $request->type,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal.' . $e->getMessage() ,
                'data' => $request->type,
            ]);
        }
    }
    public function reject(Request $request)
    {
        // dd($request->all());
        $this->validate($request, [
            'type'       => 'required|string',
            'action'       => 'required|string',
        ]);

        $jabatan = auth()->user()->karyawan->jabatan ?? null;
        if (!in_array($jabatan, ['HRD', 'Koordinator ITSM', 'Education Manager', 'SPV Sales'])) {
            return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menyetujui pengajuan ini.',
                    'data' => $request->type,
                ]);
        }

        DB::beginTransaction();

        try {
            switch ($request->type) {
            case 'noRecord':
                $jenis_PK = absensi_noRecord::findOrfail($request->id);
                $jenis_PK->approval = 2;
                $jenis_PK->alasan_approval = $request->reject_reason;
                $jenis_PK->save();
            break;
            case 'schemeWork':
                $jenis_PK = absensi_noRecord::findOrfail($request->id);
                // dd($jenis_PK);
                $jenis_PK->approval = 2;
                $jenis_PK->alasan_approval = $request->reject_reason;
                $jenis_PK->save();
            break;
            case 'cancelLeave':
                $jenis_PK = pembatalanCuti::findOrfail($request->id);
                $jenis_PK->approval = 2;
                $jenis_PK->alasan_approval = $request->reject_reason;
                $jenis_PK->save();
            break;
            default:
            return response()->json([
                    'success' => false,
                    'message' => 'Gagal Mengambil Data.',
                    'data' => $request->type,
                ]);
         }
         DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Berhasil memproses data.',
                'data' => $request->type,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal.' . $e->getMessage() ,
                'data' => $request->type,
            ]);
        }

    }
}
