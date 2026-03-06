<?php

namespace App\Http\Controllers;

use App\Models\cc;
use Illuminate\Http\Request;
use App\Models\registexam;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\Peserta;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\RKM;
use App\Models\eksam;
use App\Models\hasilexam;
use App\Models\karyawan;
use App\Models\Materi;
use App\Models\User;
use App\Notifications\BayarCCNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Str;

class registexamController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {

        return view('registexam.index');
    }

    public function getRegistrasiexam()
    {

        $registrasi = registexam::with('exam', 'peserta')->get();
        // $id_rkm = $registrasi->exam->id_rkm;
        // return $id_rkm;
        $results = [];

        foreach ($registrasi as $reg) {
            $id_rkm = $reg->exam->id_rkm;
            $rkm = RKM::with('materi')->where('id', $id_rkm)->first(); // Use first() to get a single model instance
            $results[] = [
                'id' => $reg->id,
                'id_peserta' => $reg->id_peserta,
                'id_exam' => $reg->id_exam,
                'email' => $reg->email,
                'kode_exam' => $reg->kode_exam,
                'tanggal_exam' => $reg->tanggal_exam,
                'pukul' => $reg->pukul,
                'nama_perguruan_tinggi' => $reg->nama_perguruan_tinggi,
                'alamat_perguruan_tinggi' => $reg->alamat_perguruan_tinggi,
                'jurusan' => $reg->jurusan,
                'invoice' => $reg->invoice,
                'tahun_lulus' => $reg->tahun_lulus,
                'created_at' => $reg->created_at,
                'updated_at' => $reg->updated_at,
                'exam' => $reg->exam,
                'peserta' => $reg->peserta,
                'rkm' => $rkm,
                'vendor' => $rkm->materi->vendor,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'List Registrasi',
            'data' => $results
        ]);
    }

    public function getRegistrasiexamByIdExam($id)
    {
        $registrasi = registexam::with('exam', 'peserta')->where('id_exam', $id)->get();
        $results = [];
        // dd($registrasi);  

        foreach ($registrasi as $reg) {
            $id_rkm = $reg->exam->id_rkm;
            $reg_id = strval($reg->id);
            // Debugging untuk memeriksa nilai  
            // dd($reg_id, $reg->id_peserta);  
            $hasilexam = hasilexam::where('id_registexam', $reg_id)->where('id_peserta', $reg->id_peserta)->first();

            $rkm = RKM::where('id', $id_rkm)->first(); // Use first() to get a single model instance  

            // Debugging untuk memeriksa hasil  
            // dd($hasilexam);  

            $results[] = [
                'id' => $reg->id,
                'id_hasilexam' => $hasilexam ? $hasilexam->id : '-', // Check if $hasilexam is null  
                'id_peserta' => $reg->id_peserta,
                'id_exam' => $reg->id_exam,
                'id_exam' => $reg->id_exam,
                'email' => $reg->email,
                'email_exam' => $reg->email_exam,
                'akun_exam' => $reg->akun_exam,
                'kode_exam' => $reg->kode_exam,
                'tanggal_exam' => $reg->tanggal_exam,
                'pukul' => $reg->pukul,
                'nama_perguruan_tinggi' => $reg->nama_perguruan_tinggi,
                'alamat_perguruan_tinggi' => $reg->alamat_perguruan_tinggi,
                'jurusan' => $reg->jurusan,
                'invoice' => $reg->invoice,
                'tahun_lulus' => $reg->tahun_lulus,
                'created_at' => $reg->created_at,
                'updated_at' => $reg->updated_at,
                'exam' => $reg->exam,
                'peserta' => $reg->peserta,
                'rkm' => $rkm,
                'hasilexam' => $hasilexam
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'List Registrasi',
            'data' => $results
        ]);
    }



    /**
     * create
     *
     * @return View
     */
    public function create()
    {
        // Get the maximum id from the Peserta table
        $maxId = Peserta::max('id');

        // Increment the maxId by 1 to get the next id
        $countPeserta = $maxId ? $maxId + 1 : 1;
        // return $nextId;


        return view('registexam.create', compact('countPeserta'));
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        //validate form
        // dd($request->all());
        // $rkms = RKM::where('id', $request->id_rkm)->where('perusahaan_key', $request->perusahaan_key)->first();
        // $peserta = Peserta::where('id', $request->id_peserta)->first();
        // $registrasi = registexam::where('id_peserta', $request->id_peserta)->where('id_rkm', $request->id_rkm)->first();
        // // dd($rkms);
        // if ($registrasi === null) {
        //     if ($peserta === null) {
        //         $peserta = Peserta::create([
        //             'nama'            => $request->nama,
        //             'jenis_kelamin'   => $request->jenis_kelamin,
        //             'email'           => $request->email,
        //             'no_hp'           => $request->no_hp,
        //             'alamat'          => $request->alamat,
        //             'perusahaan_key'  => $request->perusahaan_key,
        //             'tanggal_lahir'   => $request->tanggal_lahir
        //         ]);
        //     }
        //     if ($rkms === null) {
        //         return redirect()->route('registexam.index')->with(['error' => 'Mohon maaf peserta ini daftar dikelas yang salah!']);
        //     }
        //     if ($rkms->isi_pax === '0') {
        //         return redirect()->route('registexam.index')->with(['error' => 'Mohon maaf kapasitas kelas sudah penuh!']);
        //     }else{
        //         registexam::create([
        //             'id_rkm'        => $request->id_rkm,
        //             'id_peserta'    => $peserta->id,
        //             'id_materi'     => $rkms->materi_key,
        //             'id_instruktur' => $rkms->instruktur_key,
        //             'id_sales'      => $rkms->sales_key,
        //         ]);

        //         $rkms->update([
        //             'isi_pax' => $rkms->isi_pax - 1,
        //         ]);
        //     }
        // } else {
        //     return redirect()->route('registexam.index')->with(['error' => 'Mohon maaf anda sudah mendaftar kelas ini!']);
        // }

        return redirect()->route('registexam.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    /**
     * show
     *
     * @param  mixed $id
     * @return View
     */
    public function show(string $id): View
    {
        $post = registexam::findOrFail($id);

        return view('registexam.show', compact('post'));
    }

    /**
     * edit
     *
     * @param  mixed $id
     * @return View
     */
    public function edit(string $id): View
    {
        $materis = registexam::findOrFail($id);

        return view('registexam.edit', compact('materis'));
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
        $this->validate($request, [
            'id_rkm'     => 'required',
            'id_peserta'   => 'required',
            'id_materi'   => 'required',
        ]);

        $post = registexam::findOrFail($id);

        $post->update([
            'id_rkm'     => $request->id_rkm,
            'id_peserta'     => $request->id_peserta,
            'id_materi'     => $request->id_materi,

        ]);

        return redirect()->route('registexam.index')->with(['success' => 'Data Berhasil Diubah!']);
    }

    /**
     * destroy
     *
     * @param  mixed $post
     * @return void
     */
    public function destroy($id): RedirectResponse
    {
        $post = registexam::findOrFail($id);

        $post->delete();

        return redirect()->route('registexam.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }

    public function uploadInvoice($id)
    {
        $post = registexam::findOrFail($id);

        return view('registexam.uploadinvoice', compact('post'));
    }
    private function generateInvoiceNumber($id_exam, $kode_exam): string
    {
        $latestExam = registexam::where('id_exam', $id_exam)->orderBy('created_at', 'desc')->first();
        $currentDate = date('Ym');
        $sequence = '001'; // Default sequence number

        if ($latestExam && $latestExam->invoice) {
            // Ambil bagian terakhir dari nomor faktur sebelumnya yang merupakan urutan nomor
            $latestInvoiceNumber = $latestExam->invoice;
            $parts = explode('-', $latestInvoiceNumber);
            $latestSequence = (int) end($parts); // Ambil bagian terakhir dan konversi ke integer
            $newSequence = str_pad($latestSequence + 1, 3, '0', STR_PAD_LEFT);
            $sequence = $newSequence;
        }

        // Format nomor faktur baru
        $invoiceNumber = $currentDate . '-' . $id_exam . '-' . $kode_exam . '-' . $sequence;

        return $invoiceNumber;
    }

    public function uploadInvoicePost(Request $request, $id)
    {
        // dd($request->all());
        $post = registexam::findOrFail($id);
        $id_exam = $post->id_exam;
        $eksam = eksam::find($id_exam);
        $id_rkm = $eksam->id_rkm;
        $invoice = 'INV-' . $this->generateInvoiceNumber($id_exam, $id_rkm);

        // return $invoice;
        $this->validate($request, [
            'invoice' => 'required|mimes:pdf|max:2048',
        ]);

        // Menghapus file sebelumnya jika ada
        if ($post->invoice && file_exists(public_path('storage/invoiceexam/' . $post->invoice))) {
            unlink(public_path('storage/invoiceexam/' . $post->invoice));
        }

        $file = $request->file('invoice');
        $nama_file = $invoice . "." . "pdf";
        $file->move(public_path('storage/invoiceexam'), $nama_file);
        $post->invoice = $nama_file;
        $post->save();

        hasilexam::create([
            'id_peserta' => $request->id_peserta,
            'id_registexam' => $request->id_registexam,
        ]);

        return redirect()->route('registexam.index')->with(['success' => 'Invoice Berhasil Di Upload!']);
    }

    public function createHasilUjian($id)
    {
        // $post = hasilexam::findOrFail($id);
        // $id_peserta = $post->id_peserta;
        // $peserta = peserta::where('id', $id_peserta)->first();
        // $registexam = registexam::findOrFail($post->id_registexam);
        $post = registexam::findOrFail($id);
        $id_peserta = $post->id_peserta;
        $peserta = peserta::where('id', $id_peserta)->first();
        $hasilexam = hasilexam::where('id_registexam', $post->id)->first();

        return view('hasilexam.create', compact('post', 'peserta', 'hasilexam'));
    }

    public function showHasilUjian($id)
    {
        // return $id;
        $registexam = registexam::with('exam', 'creditcard')->findOrFail($id);
        $post = hasilexam::where('id_registexam', $id)->first();
        $id_peserta = $post->id_peserta;
        $id_registexam = $post->id_registexam;
        $registexam = registexam::with('exam', 'creditcard')->where('id', $id_registexam)->first();
        // return $registexam;
        $id_rkm = $registexam->exam->id_rkm;
        $rkm = RKM::where('id', $id_rkm)->first();
        $peserta = peserta::with('perusahaan')->where('id', $id_peserta)->first();
        // dd($post, $peserta, $registexam, $rkm);
        return view('hasilexam.show', compact('post', 'peserta', 'registexam', 'rkm'));
    }

    public function editHasilUjian($id)
    {
        $post = registexam::findOrFail($id);
        $hasilexam = hasilexam::findOrFail($post->id);
        $id_peserta = $post->id_peserta;
        $peserta = peserta::where('id', $id_peserta)->first();

        return view('hasilexam.edit', compact('post', 'peserta', 'registexam'));
    }

    // public function storeHasilUjian(Request $request, $id)  
    // {  
    //     // Debugging untuk melihat semua input  
    //     dd($request->all());  

    //     // Validasi input  
    //     $this->validate($request, [  
    //         'id_peserta' => 'nullable',  
    //         'id_registexam' => 'nullable',  
    //         'hasil' => 'nullable',  
    //         'keterangan' => 'nullable',  
    //         'pdf' => 'nullable|mimes:pdf|max:2048', // Ubah menjadi nullable  
    //     ]);  

    //     // Inisialisasi variabel untuk nama file PDF  
    //     $nama_file = null;  

    //     // Cek apakah ada file PDF yang diunggah  
    //     if ($request->hasFile('pdf')) {  
    //         $file = $request->file('pdf');  
    //         $nama_file = "Hasil_Exam_Peserta-" . $request->id_peserta . "-dengan_id-" . $request->id_registexam . ".pdf";  
    //         $file->move(public_path('storage/hasilexam'), $nama_file);  
    //     }  

    //     // Simpan data ke dalam database  
    //     hasilexam::create([  
    //         'id_peserta' => $request->id_peserta,  
    //         'id_registexam' => $id,  
    //         'hasil' => $request->hasil,  
    //         'keterangan' => $request->keterangan,  
    //         'pdf' => $nama_file, // Jika tidak ada file, ini akan menjadi null  
    //     ]);  

    //     return redirect()->route('registexam.index')->with(['success' => 'Data Berhasil Disimpan!']);  
    // }  


    public function updateHasilUjian(Request $request, $id)
    {
        // Validate the incoming request
        $this->validate($request, [
            'id_peserta' => 'nullable',
            'id_registexam' => 'nullable',
            'id_hasilexam' => 'nullable',
            'hasil' => 'nullable',
            'pdf' => 'nullable|mimes:pdf|max:2048',
        ]);
        // Attempt to find the existing record
        $hasilUjian = hasilexam::where('id_registexam', $request->id_registexam)->first();
        // dd($hasilUjian);
        $nama_file = $hasilUjian->pdf;

        if ($request->hasFile('pdf')) {
            $file = $request->file('pdf');
            $nama_file = "Hasil_Exam_Peserta-" . $request->id_peserta . "-dengan_id-" . $request->id_registexam . ".pdf";
            $file->move(public_path('storage/hasilexam'), $nama_file);
        }
        // Check if $hasilUjian is null
        if ($hasilUjian === null) {
            // Create a new record if it doesn't exist
            hasilexam::create([
                'id_peserta' => $request->id_peserta,
                'id_registexam' => $id,
                'hasil' => $request->hasil,
                'keterangan' => $request->keterangan,
                'pdf' => $nama_file, // Jika tidak ada file, ini akan menjadi null  
            ]);
        } else {
            // Update the existing record
            $hasilUjian->update([
                'id_peserta' => $request->input('id_peserta'),
                'id_registexam' => $request->input('id_registexam'),
                'hasil' => $request->input('hasil'),
                'keterangan' => $request->keterangan,
                'pdf' => $nama_file, // Jika tidak ada file, ini akan menjadi null  

            ]);
        }


        return redirect()->route('registexam.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }


    public function invoice($id)
    {
        $registexam = registexam::with('exam', 'peserta')->findOrFail($id);
        $data = eksam::with('rkm', 'kodeeksam', 'registexam', 'approvalexam')->findOrFail($id);
        $biaya_admin = $data->biaya_admin * $data->kurs_dollar;
        $harga = $data->harga * $data->kurs;
        $sales = karyawan::where('kode_karyawan', $data->approvalexam->sales)->first() ?? '-';
        if (!$data->approvalexam->ttd_sales) {
            // $spv_sales = '-';
            $spv_sales = karyawan::where('jabatan', 'SPV Sales')->first();
        } else {
            $spv_sales = karyawan::where('kode_karyawan', $data->approvalexam->ttd_sales)->first();
        }
        if (!$data->approvalexam->ttd_off) {
            // $office_manager = '-';
            $office_manager = karyawan::where('jabatan', 'Finance & Accounting')->first();
        } else {
            $office_manager = karyawan::where('kode_karyawan', $data->approvalexam->ttd_off)->first();
        }
        if (!$data->approvalexam->ttd_ts) {
            // $technical_support = '-';
            $technical_support = karyawan::where('jabatan', 'Technical Support')->first();
        } else {
            $technical_support = karyawan::where('kode_karyawan', $data->approvalexam->ttd_ts)->first();
        }
        $cc = cc::where('id', $registexam->cc)->first();
        // return $cc;
        return view('registexam.invoice', compact('data', 'registexam', 'spv_sales', 'technical_support', 'office_manager', 'sales', 'cc', 'harga', 'biaya_admin'));
    }

    public function createcc($id)
    {
        $post = registexam::findOrFail($id);
        $id_peserta = $post->id_peserta;
        $peserta = peserta::where('id', $id_peserta)->first();
        $ccs = cc::get();
        return view('registexam.cc', compact('post', 'peserta', 'ccs'));
    }
    public function storecc(Request $request, $id)
    {
        $this->validate($request, [
            'cc' => 'nullable',
        ]);

        $post = registexam::findOrFail($id);
        $cc = cc::findOrFail($request->cc);
        $exam = eksam::findOrFail($post->id_exam);
        // return $exam;

        $data = [
            'materi' => $exam->materi,
            'perusahaan' => $exam->perusahaan,
            'harga' => $exam->harga,
            'mata_uang' => $exam->mata_uang,
            'pax' => $exam->pax,
            'tanggal_pengajuan' => $exam->tanggal_pengajuan,
            'invoice' => $exam->invoice,
            'kurs' => $exam->kurs,
            'cc' => $cc->nama_pemilik
        ];
        $finance = karyawan::where('jabatan', 'Finance & Accounting')->first();
        // return $finance;
        $users = array_map(function ($user) {
            return $user === '-' ? null : $user;
        }, [
            $finance->kode_karyawan
        ]);

        $users = User::whereHas('karyawan', function ($query) use ($users) {
            $query->whereIn('kode_karyawan', array_filter($users));
        })->get();

        $path = '/exam/' . $id;

        foreach ($users as $user) {
            $receiverId = $user->id;
            NotificationFacade::send($user, new BayarCCNotification($data, $path, $receiverId));
        }
        // $post->update([
        //     'cc' => $request->cc,
        //     'status_pembayaran' => '0',
        // ]);


        return redirect()->route('registexam.invoice', ['id' => $id])->with(['success' => 'Data Berhasil Disimpan!']);
    }

    public function generateAbsensi(Request $request)
    {
        $validated = $request->validate([
            'materi_id'       => 'required',
            'tgl_exam'        => 'required|date',
            'peserta'         => 'required|array|min:1',
        ]);

        $materi = Materi::findOrFail($validated['materi_id']);
        $tgl_exam = $validated['tgl_exam'];

        $pesertas = collect($validated['peserta'])->map(function ($item, $index) use ($materi, $tgl_exam) {
            return [
                'no'           => $index + 1,
                'nama_lengkap' => $item['nama'] ?? 'N/A',
                'email'        => $item['email'] ?? 'N/A',
                'materi_nama'  => $materi->nama_materi,
                'tgl_exam'     => $tgl_exam,
            ];
        });

        $pdf = Pdf::loadView('registexam.absensi', [
            'pesertas' => $pesertas,
            'materi'   => $materi,
            'tgl_exam' => $tgl_exam
        ])
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);

        $filename = 'sertifikat-exam-' . Str::slug($materi->nama_materi) . '-' . now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }
}
