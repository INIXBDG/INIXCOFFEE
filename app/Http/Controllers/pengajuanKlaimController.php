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

class pengajuanKlaimController extends Controller
{
    public function index()
    {
        $id_karyawan = auth()->user()->karyawan_id;

        $noRecord = absensi_noRecord::where('jenis_PK', 'No Record')
            ->whereHas('absensiKaryawan')
            ->whereHas('karyawan')
            ->with(['absensiKaryawan', 'karyawan'])
            ->get();


        $schemeWork = absensi_noRecord::where('jenis_PK', 'Scheme Work')
            ->whereHas('absensiKaryawan')
            ->whereHas('karyawan')
            ->with(['absensiKaryawan', 'karyawan'])
            ->get();

        $cancelLeave = pembatalanCuti::whereHas('pengajuancuti')
            ->whereHas('karyawan')
            ->whereHas('pengajuancuti')
            ->with(['pengajuancuti', 'karyawan'])
            ->get();

        return view('pengajuanklaim.index', compact('noRecord', 'schemeWork', 'cancelLeave'));
    }
    //untuk Absen Tidak terkirim bro
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
            'kendala'       => 'required|string|in:Human Error,System Error',
            'tanggal_absen' => 'required|integer',
            'bukti_gambar'  => 'required|image',
            'kronologi'     => 'required|string',
        ]);

        $karyawan = karyawan::where('id', $request->id_karyawan)->first();
        $absen = AbsensiKaryawan::where('id', $request->tanggal_absen)->first();

        if ($request->kendala === 'Human Error') {
            $jumlahHE = absensi_noRecord::where('id_karyawan', $request->id_karyawan)
                ->where('kendala', 'Human Error')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            if ($jumlahHE >= 3) {
                return back()->withErrors(['kendala' => 'Pengajuan dengan kendala "Human Error" hanya diperbolehkan maksimal 3 kali dalam sebulan.'])->withInput();
            }
        }

        $file = $request->file('bukti_gambar');
        $ext = $file->getClientOriginalExtension();
        $filename = 'bukti_' . now()->format('Y_m_d_H_i_s') . '.' . $ext;
        $destinationPath = public_path('pengajuan_klaim');

        $file->move($destinationPath, $filename);

        $fotoPath = 'pengajuan_klaim/' . $filename;

        if (!$fotoPath) {
            return back()->withErrors(['bukti_gambar' => 'Tidak dapat melampirkan bukti'])->withInput();
        }

        absensi_noRecord::create([
            'id_karyawan'   => $request->id_karyawan,
            'jenis_PK'      => 'No Record',
            'kendala'       => $request->kendala,
            'id_absen'      => $request->tanggal_absen,
            'bukti_gambar'  => $fotoPath,
            'kronologi'     => $request->kronologi,
            'approval'      => '0',
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
            'tipe'            => 'no_record',
            'nama_lengkap'    => $karyawan->nama_lengkap,
            'kendala'         => $request->kendala,
            'tanggal'         => $absen->tanggal,
            'kronologi'       => $request->kronologi,
            'status'          => $statusMessage,
            'approval'        => 0,
            'alasan_approval' => null,
        ];

        $path = '/pengajuan-klaim?tabel=no_record';

        foreach ($users as $user) {
            NotificationFacade::send($user, new noRecordExchangeNotification($notificationData, $path));
        }

        return redirect('/pengajuan-klaim?tabel=no_record')->with('success', 'Berhasil mengajukan');
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
    public function approveNoRecord(Request $request)
    {
        $this->validate($request, [
            'approval'       => 'required|integer|in:1,2',
            'id_absen'       => 'required|integer',
            'id_karyawan'    => 'required|integer',
        ]);

        $jenis_PK = absensi_noRecord::where('id_karyawan', $request->id_karyawan)
            ->where('id_absen', $request->id_absen)
            ->first();

        if (!$jenis_PK) {
            return redirect()->back()->withErrors('Data tidak ditemukan.');
        }

        $jenis_PK->approval = $request->approval;
        if ($request->filled('alasan_approval')) {
            $jenis_PK->alasan_approval = $request->alasan_approval;
        }
        $jenis_PK->approval_date = now();
        $jenis_PK->save();

        $absen = AbsensiKaryawan::where('id', $jenis_PK->id_absen)->first();
        $absen->waktu_keterlambatan = "00:00:00";
        $absen->save();

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


        $statusMessage = $request->approval == 1 ? "Telah Disetujui Oleh HRD" : "Ditolak Oleh HRD";

        $notificationData = [
            'tipe'            => 'no_record',
            'nama_lengkap'    => $karyawan->nama_lengkap,
            'kendala'         => $jenis_PK->kendala,
            'tanggal'         => $absen->tanggal,
            'kronologi'       => $jenis_PK->kronologi,
            'status'          => $statusMessage,
            'approval'        => $request->approval,
            'alasan_approval' => $request->alasan_approval ?? null,
        ];

        $path = 'pengajuan-klaim?tabel=no_record';

        foreach ($users as $user) {
            NotificationFacade::send($user, new noRecordExchangeNotification($notificationData, $path));
        }

        return redirect('pengajuan-klaim?tabel=no_record')->with('success', 'Berhasil memproses data absensi.');
    }
    //untuk si pembatalan cuti
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

        return view('pengajuanklaim.crateCancelLeave', compact('karyawan', 'karyawanall', 'data_cuti'));
    }
    public function createCancelLeave(Request $request)
    {
        $this->validate($request, [
            'id_karyawan'   => 'required|integer',
            'tanggal_cuti' => 'required|integer',
            'bukti_gambar'  => 'required|image',
            'kronologi'     => 'required|string',
        ]);

        if ($request->kendala === 'Human Error') {
            $jumlahHE = absensi_noRecord::where('id_karyawan', $request->id_karyawan)
                ->where('kendala', 'Human Error')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            if ($jumlahHE >= 3) {
                return back()->withErrors(['kendala' => 'Pengajuan dengan kendala "Human Error" hanya diperbolehkan maksimal 3 kali dalam sebulan.'])->withInput();
            }
        }

        $file = $request->file('bukti_gambar');
        $ext = $file->getClientOriginalExtension();
        $filename = 'bukti_' . now()->format('Y_m_d_H_i_s') . '.' . $ext;
        $destinationPath = public_path('pengajuan_klaim');

        $file->move($destinationPath, $filename);

        $fotoPath = 'pengajuan_klaim/' . $filename;

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
    public function approveCancelLeave(Request $request)
    {
        $this->validate($request, [
            'approval'       => 'required|integer|in:1,2',
            'id_CL'       => 'required|integer',
            'id_karyawan'    => 'required|integer',
        ]);

        $jenis_PK = pembatalanCuti::where('id_karyawan', $request->id_karyawan)
            ->where('id', $request->id_CL)
            ->first();

        if (!$jenis_PK) {
            return redirect()->back()->withErrors('Data tidak ditemukan.');
        }

        $jenis_PK->approval = $request->approval;
        if ($request->filled('alasan_approval')) {
            $jenis_PK->alasan_approval = $request->alasan_approval;
        }
        $jenis_PK->approval_date = now();
        $jenis_PK->save();

        if ($request->approval === 1) {
            $deletingData = pengajuancuti::where('id', $jenis_PK->id_cuti)->first();
            $deletingData->delete();
        }

        $absen = AbsensiKaryawan::where('id', $jenis_PK->id_karyawan)->first();

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

        $data_cuti = pengajuancuti::where('id', $request->id_CL)->first();

        $statusMessage = $request->approval == 1 ? "Telah Disetujui Oleh HRD" : "Telah Ditolak Oleh HRD";

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

        return redirect('/pengajuan-klaim?tabel=cancel_leave')->with('success', 'Berhasil memproses data absensi.');
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
    //untuk perubaahn skema kerja
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

        if ($request->kendala === 'Human Error') {
            $jumlahHE = absensi_noRecord::where('id_karyawan', $request->id_karyawan)
                ->where('kendala', 'Human Error')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            if ($jumlahHE >= 3) {
                return back()->withErrors(['kendala' => 'Pengajuan dengan kendala "Human Error" hanya diperbolehkan maksimal 3 kali dalam sebulan.'])->withInput();
            }
        }

        $file = $request->file('bukti_gambar');
        $ext = $file->getClientOriginalExtension();
        $filename = 'bukti_' . now()->format('Y_m_d_H_i_s') . '.' . $ext;
        $destinationPath = public_path('pengajuan_klaim');

        $file->move($destinationPath, $filename);

        $fotoPath = 'pengajuan_klaim/' . $filename;

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
    public function approveSchemeWork(Request $request)
    {
        $this->validate($request, [
            'approval'       => 'required|integer|in:1,2',
            'id_absen'       => 'required|integer',
            'id_karyawan'    => 'required|integer',
            'waktu_masuk'    => $request->approval == 1 ? 'required|date_format:H:i' : '',
            'waktu_pulang'   => $request->approval == 1 ? 'required|date_format:H:i' : '',
        ]);

        $jenis_PK = absensi_noRecord::where('id_karyawan', $request->id_karyawan)
            ->where('id_absen', $request->id_absen)
            ->first();

        if (!$jenis_PK) {
            return redirect()->back()->withErrors('Data tidak ditemukan.');
        }

        $jenis_PK->approval = $request->approval;
        if ($request->filled('alasan_approval')) {
            $jenis_PK->alasan_approval = $request->alasan_approval;
        }
        $jenis_PK->approval_date = now();

        $absen = AbsensiKaryawan::where('id', $jenis_PK->id_absen)->first();
        // hanya jika disetujui
      if ($request->approval == 1) {

    if ($absen) {
        $waktuDiizinkan = \Carbon\Carbon::createFromFormat('H:i', $request->waktu_masuk);
        $waktuAbsen = \Carbon\Carbon::createFromFormat('H:i', $absen->jam_masuk);

        // Jika absen masih dalam toleransi <= 59 menit dari waktu diizinkan
        if ($waktuAbsen->lte($waktuDiizinkan->copy()->addMinutes(59))) {
            $absen->waktu_keterlambatan = "00:00:00";
        }

        $absen->save();
    }

    // Simpan waktu masuk & pulang yang diizinkan ke absensi_noRecord
    $jenis_PK->waktu_masuk = $request->waktu_masuk;
    $jenis_PK->waktu_pulang = $request->waktu_pulang;
}


        $jenis_PK->save();

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


        $statusMessage = $request->approval == 1 ? "Telah Disetujui Oleh HRD" : "Ditolak Oleh HRD";

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

        return redirect('/pengajuan-klaim?tabel=schema_work')->with('success', 'Berhasil memproses data absensi.');
    }

    public function pengajuanKlaimExcel(Request $request)
    {
        $jenisPK = $request->input('jenis_PK');
        $filename = 'pengajuan-klaim-' . now()->format('Y_m_d_H_i') . '-' . $jenisPK . '.xlsx';

        return Excel::download(new pengajuanKlaimExport($jenisPK), $filename);
    }

    public function pengajuanKlaimPDF(Request $request)
    {
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
}
