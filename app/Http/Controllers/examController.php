<?php

namespace App\Http\Controllers;

use App\Exports\rekapExamExport;
use App\Models\approvalexam;
use App\Models\changeexam;
use App\Models\eksam;
use App\Models\karyawan;
use App\Models\listexam;
use App\Models\RKM;
use App\Models\User;
use App\Notifications\ApprovalExamNotification;
use App\Notifications\BayarExamNotification;
use App\Notifications\PengajuanexamNotification;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification as NotificationFacade;

use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class examController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        // $rkm = RKM::with(['sales', 'materi', 'instruktur', 'perusahaan', 'instruktur2', 'asisten'])
        //     ->where('exam', '1')
        //     ->get();
        //     return $rkm;
        return view('exam.index');
    }

    public function getExam()
    {
        // Ambil semua id_rkm yang sudah ada di tabel exam
        $existingRKMs = eksam::pluck('id_rkm')->toArray();

        // Ambil data RKM yang memiliki 'exam' = 1, tetapi belum ada di tabel exam
        $rkm = RKM::with(['sales', 'materi', 'instruktur', 'perusahaan', 'instruktur2', 'asisten'])
            ->where('exam', '1')
            ->whereNotIn('id', $existingRKMs) // Mengecualikan id_rkm yang sudah ada di exam
            ->orderBy('tanggal_awal', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'List Registrasi',
            'data' => $rkm,
        ]);
    }


    public function getHistoriExam()
    {
        $rkm = eksam::with(['rkm'])->orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'message' => 'List Registrasi',
            'data' => $rkm,
        ]);
    }

    /**
     * create
     *
     * @return View
     */
    public function create($id)
    {
        $rkm = RKM::with('perusahaan', 'materi')->findOrFail($id);
        $kode_exam = listexam::all();
        // return $rkm;
        return view('exam.create', compact('rkm', 'kode_exam'));
    }

    private function generateInvoiceNumber(): string
    {
        $latestExam = eksam::orderBy('created_at', 'desc')->first();
        $currentDate = date('Ymd');
        $invoiceNumber = $currentDate . '-001';

        if ($latestExam) {
            $latestInvoiceNumber = $latestExam->invoice;
            $latestDate = substr($latestInvoiceNumber, 0, 8);

            if ($latestDate == $currentDate) {
                $latestSequence = (int)substr($latestInvoiceNumber, 9);
                $newSequence = str_pad($latestSequence + 1, 3, '0', STR_PAD_LEFT);
                $invoiceNumber = $currentDate . '-' . $newSequence;
            }
        }

        return $invoiceNumber;
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
        $harga_rupiah = preg_replace('/[^\d]/', '', $request->harga_rupiah);
        // $request->harga_rupiah = $harga_rupiah;
        $total = preg_replace('/[^\d]/', '', $request->total);
        $user = auth()->user()->id_sales;
        $harga = str_replace(',', '.', $request->harga);
        // return $user;

        // Remove any non-numeric characters except dots
        $harga = preg_replace('/[^\d.]/', '', $harga);

        // Assign the sanitized value back to the request
        $request->merge([
            'harga' => $harga,
            'total' => $total,
            'harga_rupiah' => $harga_rupiah,
        ]);
        // return $request->all();

        // return $user;
        try {
            $rkm = RKM::with('materi', 'perusahaan')->where('id', $request->id_rkm)->first();
            if($request->pax > $rkm->pax){
                return redirect()->back()->with('error', 'Pax tidak boleh lebih dari '. $rkm->pax);
            }
           $data = $request->validate([
                'tanggal_pengajuan' => 'required|date',
                'materi' => 'required|string|max:255',
                'id_rkm' => 'required|string|max:255',
                'perusahaan' => 'required|string|max:255',
                'mata_uang' => 'nullable|string',
                'harga' => 'nullable|string',
                'biaya_admin' => 'nullable|string',
                'harga_rupiah' => 'required|string',
                'kurs' => 'required|string',
                'pax' => 'required|integer',
                'total' => 'required|string',
                'kode_exam' => 'nullable|string',
            ]);
            // dd($request->harga_rupiah);

            $invoice = 'INV-' . $this->generateInvoiceNumber();
            // return $invoice;
            $status = 'Belum Approval SPV Sales';
            $exam = eksam::create([
                'tanggal_pengajuan' => $request->tanggal_pengajuan,
                'materi' => $request->materi,
                'id_rkm' => $request->id_rkm,
                'perusahaan' => $request->perusahaan,
                'mata_uang' => $request->mata_uang,
                'harga' => $request->harga,
                'biaya_admin' => $request->biaya_admin,
                'harga_rupiah' => $request->harga_rupiah,
                'kurs' => $request->kurs,
                'kurs_dollar' => $request->kurs_dollar,
                'pax' => $request->pax,
                'total_pax' => $request->pax,
                'total' => $request->total,
                'kode_exam' => $request->kode_exam,
                'status' => $request->status,
                'invoice' => $invoice
            ]);

            approvalexam::create([
                'id_exam' => $exam->id,
                'sales' => $rkm->sales_key,
                'spv_sales' => false,
                'technical_support' => false,
                'office_manager' => false,
                'status' => $status,
            ]);
            $data = [
                'nama_materi' => $rkm->materi->nama_materi,
                'nama_perusahaan' => $rkm->perusahaan->nama_perusahaan,
            ];
            $finance = karyawan::where('jabatan', 'Finance & Accounting')->first();
            $kooroff = karyawan::where('jabatan', 'Koordinator Office')->first();
            $Eduman = karyawan::where('jabatan', 'Education Manager')->first();
            $SPVSales = karyawan::where('jabatan', 'SPV Sales')->first();
            $GM = karyawan::where('jabatan', 'GM')->first();
            // Mengambil pengguna yang terlibat
            $users = array_map(function ($user) {
                return $user === '-' ? null : $user;
            }, [
                $rkm->sales_key,
                $Eduman->kode_karyawan,
                $finance->kode_karyawan,
                $kooroff->kode_karyawan,
                $SPVSales->kode_karyawan,
                $GM->kode_karyawan,
                'NF'  // GM
            ]);

            $users = User::whereHas('karyawan', function ($query) use ($users) {
                $query->whereIn('kode_karyawan', array_filter($users));
            })->get();

            $path = '/exam/'. $exam->id;
            
            foreach ($users as $user) {
               NotificationFacade::send($user, new PengajuanexamNotification($data, $path));
            }

            return redirect()->route('exam.index')->with(['success' => 'Data Berhasil Disimpan!']);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
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
        $rkm = eksam::with('rkm')->findOrFail($id);
        $exam = changeexam::where('id_exam', $id)->get();
        $approvalexam = approvalexam::where('id_exam', $id)->first();
        $biaya_admin = $rkm->biaya_admin * $rkm->kurs_dollar;
        $harga = $rkm->harga * $rkm->kurs;
        // $kurs_dollar = explode('.', $rkm->kurs_dollar)
        // return $rkm;

        return view('exam.show', compact('rkm', 'exam', 'approvalexam', 'biaya_admin', 'harga'));
    }

    /**
     * edit
     *
     * @param  mixed $id
     * @return View
     */
    public function edit(string $id)
    {
        //get post by ID
        $kode_exam = listexam::all();
        $exam = eksam::with('rkm', 'karyawan')->findOrFail($id);
        // return $exam;

        //render view with post
        return view('exam.edit', compact('exam', 'kode_exam'));
    }

    public function update(Request $request, $id)
    {
        // dd($request->all());
        $harga_rupiah = preg_replace('/[^\d]/', '', $request->harga_rupiah);
        $request->merge(['harga_rupiah' => $harga_rupiah]);
        $total = preg_replace('/[^\d]/', '', $request->total);
        $request->merge(['total' => $total]);
        // $user = auth()->user()->id_sales;
        $id_karyawan = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrFail($id_karyawan);
        $kode_karyawan = $karyawan->kode_karyawan;

        try {
            $rkm = RKM::where('id', $request->id_rkm)->first();
            if ($request->pax > $rkm->pax) {
                return redirect()->back()->with('error', 'Pax tidak boleh lebih dari ' . $rkm->pax);
            }

            $data = $request->validate([
                'tanggal_pengajuan' => 'required|date',
                'materi' => 'required|string|max:255',
                'id_rkm' => 'required|string|max:255',
                'perusahaan' => 'required|string|max:255',
                'kode_exam' => 'nullable|string',
                'mata_uang' => 'nullable|string',
                'harga' => 'nullable|numeric',
                'kurs' => 'nullable|numeric',
                'biaya_admin' => 'nullable|numeric',
                'kurs_dollar' => 'nullable|numeric',
                'harga_rupiah' => 'required|string',
                'pax' => 'required|integer',
                'total' => 'required|string',
                'keterangan' => 'nullable|string',
            ]);

            $exam = eksam::findOrFail($id);
            $exam->update([
                'tanggal_pengajuan' => $request->tanggal_pengajuan,
                'materi' => $request->materi,
                'id_rkm' => $request->id_rkm,
                'perusahaan' => $request->perusahaan,
                'kode_exam' => $request->kode_exam,
                'mata_uang' => $request->mata_uang,
                'harga' => $request->harga,
                'biaya_admin' => $request->biaya_admin,
                'harga_rupiah' => $request->harga_rupiah,
                'kurs' => $request->kurs,
                'kurs_dollar' => $request->kurs_dollar,
                'pax' => $request->pax,
                'total_pax' => $request->pax,
                'total' => $request->total,
                'keterangan' => $request->keterangan,
                'status' => $exam->status,
            ]);

            changeexam::create([
                'id_exam' => $exam->id,
                'keterangan' => $request->keterangan,
                'status' => '-',
                'kode_karyawan' => $kode_karyawan,
            ]);

            return redirect()->route('exam.index')->with(['success' => 'Data Berhasil Diperbarui!']);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }


    /**
     * destroy
     *
     * @param  mixed $post
     * @return void
     */
    public function destroy($id): RedirectResponse
    {
        $exam = eksam::findOrFail($id);
        $approval = approvalexam::where('id_exam',$exam->id)->get();
        $changeexam = changeexam::where('id_exam',$exam->id)->get();
        $exam->delete();
        $approval->delete();
        $changeexam->delete();

        return redirect()->route('exam.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }

    public function approvalexam($id)
    {
        $exam = eksam::with('rkm', 'karyawan')->findOrFail($id);
        $kode_exam = listexam::all();

        return view('exam.approval', compact('exam', 'kode_exam'));
    }

    public function sendapprovalexam(Request $request, $id)
    {
        // dd($request->all());
        $approval = approvalexam::where('id_exam', $id)->first();
        // return $approval;

        $id_karyawan = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrFail($id_karyawan);
        $kode_karyawan = $karyawan->kode_karyawan;
        $jabatan = $karyawan->jabatan;
        if ($jabatan == 'SPV Sales') {
            $status = 'Sudah Diapprove oleh SPV Sales';
            $keterangan = 'Proses';

            $approval->update([
                'id_exam' => $id,
                'spv_sales' => true,
                'technical_support' => false,
                'office_manager' => false,
                'status' => $status,
                'ttd_sales' => $kode_karyawan,
            ]);
            $data = eksam::findOrfail($id);
            // return $data;
            $finance = karyawan::where('jabatan', 'Finance & Accounting')->first();
            $kooroff = karyawan::where('jabatan', 'Koordinator Office')->first();
            $GM = karyawan::where('jabatan', 'GM')->first();

            $users = array_map(function ($user) {
                return $user === '-' ? null : $user;
            }, [
                $finance->kode_karyawan,
                $kooroff->kode_karyawan,
                $GM->kode_karyawan,
                $approval->sales
            ]);

            $users = User::whereHas('karyawan', function ($query) use ($users) {
                $query->whereIn('kode_karyawan', array_filter($users));
            })->get();

            $path = '/exam/'. $id;
            
            foreach ($users as $user) {
               NotificationFacade::send($user, new ApprovalExamNotification($data, $path));
            }
        }
        if ($jabatan == 'Office Manager' || $jabatan == 'GM' || $jabatan == 'Koordinator Office' || $jabatan == 'Finance & Accounting') {
            $exam = eksam::findOrFail($id);
            if($exam->kurs != $request->kurs || $exam->kurs_dollar != $request->kurs_dollar){
                $status = 'Sudah Dikonfirmasi dan Disesuaikan oleh ' . $jabatan;
                $keterangan = 'Proses';

                $exam->update([
                    'harga' => $request->harga,
                    'harga_rupiah' => $request->harga_rupiah,
                    'kurs' => $request->kurs,
                    'kurs_dollar' => $request->kurs_dollar,
                    'total' => $request->total,
                    'status' => $status,
                    'keterangan' => $keterangan,
                    'kode_karyawan' => $kode_karyawan
                ]);
            }else{
                $status = 'Sudah Diapprove oleh ' . $jabatan;
                $keterangan = 'Proses';
            }
            $approval->update([
                'id_exam' => $id,
                'spv_sales' => true,
                'technical_support' => false,
                'office_manager' => true,
                'status' => $status,
                'ttd_off' => $kode_karyawan,
            ]);

            $data = eksam::findOrfail($id);
            // return $data;
            $users = array_map(function ($user) {
                return $user === '-' ? null : $user;
            }, [
                'NF',
                $approval->sales
            ]);

            $users = User::whereHas('karyawan', function ($query) use ($users) {
                $query->whereIn('kode_karyawan', array_filter($users));
            })->get();

            $path = '/exam/'. $id;
            
            foreach ($users as $user) {
               NotificationFacade::send($user, new ApprovalExamNotification($data, $path));
            }
        }
        if ($jabatan == 'Technical Support') {
            $status = 'Sudah Dikonfirmasi oleh Technical Support';
            $keterangan = 'Selesai';
            $exam = eksam::findOrFail($id);

            if($exam->harga != $request->harga || $exam->biaya_admin != $request->biaya_admin){
                $status = 'Sudah Dikonfirmasi dan disesuaikan oleh Technical Support';
            $keterangan = 'Selesai';

                $exam->update([
                    'harga' => $request->harga,
                    'harga_rupiah' => $request->harga_rupiah,
                    'kurs' => $request->kurs,
                    'kurs_dollar' => $request->kurs_dollar,
                    'total' => $request->total,
                    'status' => $status,
                    'keterangan' => $keterangan,
                    'kode_karyawan' => $kode_karyawan
                ]);
            }else{
                $status = 'Sudah Dikonfirmasi oleh Technical Support';
                $keterangan = 'Selesai';
            }

            $approval->update([
                'id_exam' => $id,
                'spv_sales' => true,
                'technical_support' => true,
                'office_manager' => true,
                'status' => $status,
                'ttd_ts' => $kode_karyawan,
            ]);
            $data = eksam::findOrfail($id);
            // return $data;
            $users = array_map(function ($user) {
                return $user === '-' ? null : $user;
            }, [
                $approval->sales
            ]);

            $users = User::whereHas('karyawan', function ($query) use ($users) {
                $query->whereIn('kode_karyawan', array_filter($users));
            })->get();

            $path = '/exam/'. $id;
            
            foreach ($users as $user) {
               NotificationFacade::send($user, new ApprovalExamNotification($data, $path));
            }

            $finance = karyawan::where('jabatan', 'Finance & Accounting')->first();
            // return $finance;
            $users = array_map(function ($user) {
                return $user === '-' ? null : $user;
            }, [
                $approval->ttd_ts,
                $finance->kode_karyawan
            ]);

            $users = User::whereHas('karyawan', function ($query) use ($users) {
                $query->whereIn('kode_karyawan', array_filter($users));
            })->get();

            $path = '/exam/'. $id;
            
            foreach ($users as $user) {
               NotificationFacade::send($user, new BayarExamNotification($data, $path));
            }
        }

        changeexam::create([
            'id_exam' => $id,
            'keterangan' => $keterangan,
            'status' => $status,
            'kode_karyawan' => $kode_karyawan,
        ]);

        return redirect()->route('exam.show', $id);
    }

    public function invoice($id)
    {
        $data = eksam::with('rkm', 'kodeeksam', 'registexam', 'approvalexam')->findOrFail($id);
        $sales = karyawan::where('kode_karyawan', $data->approvalexam->sales)->first() ?? '-';
        if(!$data->approvalexam->ttd_sales){
            // $spv_sales = '-';
            $spv_sales = karyawan::where('jabatan', 'SPV Sales')->first();
        }else{
            $spv_sales = karyawan::where('kode_karyawan', $data->approvalexam->ttd_sales)->first();
        }
        if(!$data->approvalexam->ttd_off){
            // $office_manager = '-';
            $office_manager = karyawan::where('jabatan', 'Finance & Accounting')->first();
        }else{
            $office_manager = karyawan::where('kode_karyawan', $data->approvalexam->ttd_off)->first();
        }
        if(!$data->approvalexam->ttd_ts){
            // $technical_support = '-';
            $technical_support = karyawan::where('jabatan', 'Technical Support')->first();
        }else{
            $technical_support = karyawan::where('kode_karyawan', $data->approvalexam->ttd_ts)->first();
        }
        $biaya_admin = $data->biaya_admin * $data->kurs_dollar;
        $harga = $data->harga * $data->kurs;
        $totalharga = $harga * $data->pax;
        $totalbiayadmin = $biaya_admin * $data->pax;
        // return $spv_sales;
        return view('exam.invoice', compact('data', 'spv_sales', 'technical_support', 'office_manager', 'sales', 'harga', 'biaya_admin', 'totalharga', 'totalbiayadmin'));

    }


    public function rekapExam()
    {
        
        return view('exam.rekapexam');
    }

    public function getRekapExam($year, $month)
    {
        $rkm = eksam::with(['rkm', 'registexam', 'registexam.peserta', 'registexam.creditcard',  'registexam.hasilexam'])
        ->whereMonth('tanggal_pengajuan', $month)
        ->whereYear('tanggal_pengajuan', $year)
        ->orderBy('created_at', 'desc')
        ->get();
        return response()->json([
            'success' => true,
            'message' => 'Rekap Exam di ' . $month .'-'. $year,
            'data' => $rkm,
        ]);
    }

    public function rekapExamExportExcel($year, $month)
    {
        $rkm = eksam::with(['rkm', 'registexam', 'registexam.peserta', 'registexam.creditcard', 'registexam.hasilexam'])
        ->whereMonth('tanggal_pengajuan', $month)
        ->whereYear('tanggal_pengajuan', $year)
        ->orderBy('created_at', 'desc')
        ->get();
        $data = $rkm->flatMap(function ($item) {
            return $item->registexam->map(function ($reg) use ($item) {
                return [
                    'Invoice'               => $item->invoice,
                    'Tanggal Pengajuan'     => $item->tanggal_pengajuan,
                    'Nama Materi'           => $item->materi,
                    'Nama Perusahaan'       => $item->perusahaan,
                    'Kode Exam'             => $item->kode_exam,
                    'Pax'                   => $item->pax,
                    'Nama Peserta'          => $reg->peserta->nama ?? 'Belum Daftar',
                    'Tanggal Exam'          => $reg->tanggal_exam,
                    'Waktu Exam'            => $reg->pukul,
                    'Grade Exam'            => $reg->hasilexam->Hasil ?? 'Tidak Ada',
                    'Hasil Exam'            => $reg->hasilexam->keterangan ?? 'Tidak Ada',
                    'Kartu Kredit'          => $reg->creditcard->nama_pemilik ?? 'Belum Daftar',
                    'Mata Uang'             => $item->mata_uang,
                    'Harga'                 => $item->harga,
                    'Kurs Harga'            => $item->kurs,
                    'Biaya Admin'           => $item->biaya_admin,
                    'Kurs Biaya Admin'      => $item->kurs_dollar,
                    'Harga dalam Rupiah'    => $item->harga_rupiah,
                    'Total Harga dalam Rupiah'=> $item->total,

                ];
            });
        });

        return Excel::download(new rekapExamExport($data), 'Rekap Exam '.$year . '-'. $month.'.xlsx');
    }

        public function exportExcelKhusus(string $id)
    {
        // Ambil data menggunakan metode getFeedbackData yang sudah ada
        $post = $this->getFeedbackData($id);

        // Konfigurasi header Excel
        
    }
}
