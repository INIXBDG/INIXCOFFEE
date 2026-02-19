<?php

namespace App\Http\Controllers;

use App\Exports\RkmExport;
use App\Models\AbsensiPDF;
use App\Models\Certificate;
use App\Models\Invoice;
use App\Models\jabatan;
use App\Models\karyawan;
use App\Models\Outstanding;
use App\Models\outstanding as ModelsOutstanding;
use App\Models\Registrasi;
use App\Models\RKM;
use App\Models\SertifikatPDF;
use App\Models\trackingOutstanding;
use App\Models\User;
use App\Notifications\OutstandingNotification;
use App\Notifications\OutstandingPaNotification;
use App\Notifications\OutstandingSelesai;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Tcpdf\Fpdi;
use Mostafaznv\PdfOptimizer\Laravel\Facades\PdfOptimizer;
use Mostafaznv\PdfOptimizer\Enums\PdfSettings;
use Mostafaznv\PdfOptimizer\Enums\ColorConversionStrategy;
use Mostafaznv\PdfOptimizer\Laravel\Facade\PdfOptimizer as FacadePdfOptimizer;
use Mostafaznv\PdfOptimizer\PdfOptimizer as PdfOptimizerPdfOptimizer;
use Spatie\Browsershot\Browsershot;

class OutstandingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        // $materis = outstanding::all();

        // return view('outstanding.index', compact('materis'));
        return view('outstanding.index');
    }

    public function singkronDataOutstanding(Request $request)
    {
        $tanggal_awal = $request->input('tanggal_awal');
        $tanggal_akhir = $request->input('tanggal_akhir');

        Carbon::setWeekStartsAt(Carbon::MONDAY);
        Carbon::setWeekEndsAt(Carbon::SUNDAY);

        if (empty($tanggal_awal) || empty($tanggal_akhir)) {
            $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
            $endOfWeek = Carbon::now()->endOfWeek()->toDateString();
        } else {
            $startOfWeek = Carbon::parse($tanggal_awal)->toDateString();
            $endOfWeek = Carbon::parse($tanggal_akhir)->toDateString();
        }

        $existing = Outstanding::pluck('id_rkm')->toArray();

        $rkms = RKM::whereBetween('tanggal_awal', [$startOfWeek, $endOfWeek])
            ->where('status', '0')
            ->whereNotIn('id', $existing)
            ->get();

        if ($rkms->isEmpty()) {
            return redirect()->route('outstanding.index')->with(['info' => 'Tidak ada data baru untuk disinkronkan.']);
        }

        foreach ($rkms as $rkm) {
            $outstanding = Outstanding::create([
                'id_rkm' => $rkm->id,
                'due_date' => Carbon::parse($rkm->tanggal_awal)->addMonth()->toDateString(),
                'sales_key' => $rkm->sales_key,
                'net_sales' => $rkm->harga_jual
            ]);

            if (!$outstanding || !$outstanding->id) {
                return back()->with('error', 'Data tidak berhasil disimpan.');
            }

            $status_tracking = [
                'invoice' => 0,
                'faktur_pajak' => 0,
                'dokumen_tambahan' => 0,
                'konfir_cs' => 0,
                'tracking_dokumen' => 0,
                'no_resi' => 0,
                'konfir_pic' => 0,
                'pembayaran' => 0,
            ];

            $request_status = $request->status_tracking;
            $active_statuses = [
                'invoice' => ['invoice'],
                'faktur_pajak' => ['invoice', 'faktur_pajak'],
                'dokumen_tambahan' => ['invoice', 'faktur_pajak', 'dokumen_tambahan'],
                'konfir_cs' => ['invoice', 'faktur_pajak', 'dokumen_tambahan', 'konfir_cs'],
                'tracking_dokumen' => ['invoice', 'faktur_pajak', 'dokumen_tambahan', 'konfir_cs', 'tracking_dokumen'],
                'no_resi' => ['invoice', 'faktur_pajak', 'dokumen_tambahan', 'konfir_cs', 'tracking_dokumen', 'no_resi'],
                'konfir_pic' => ['invoice', 'faktur_pajak', 'dokumen_tambahan', 'konfir_cs', 'tracking_dokumen', 'no_resi', 'konfir_pic'],
                'pembayaran' => ['invoice', 'faktur_pajak', 'dokumen_tambahan', 'konfir_cs', 'tracking_dokumen', 'no_resi', 'konfir_pic', 'pembayaran'],
            ];

            if (array_key_exists($request_status, $active_statuses)) {
                foreach ($active_statuses[$request_status] as $status) {
                    $status_tracking[$status] = 1;
                }
            }

            trackingOutstanding::create(array_merge($status_tracking, [
                'id_outstanding' => $outstanding->id,
                'status_resi' => $request->status_resi ?? '-',
                'status_pic' => $request->status_pic ?? '-',
            ]));

            // Kirim Notifikasi
            $rkmData = RKM::with('perusahaan', 'materi')->find($rkm->id);
            if ($rkmData) {
                $data = [
                    'nama_materi' => $rkmData->materi->nama_materi,
                    'nama_perusahaan' => $rkmData->perusahaan->nama_perusahaan,
                    'due_date' => $outstanding->due_date,
                    // 'net_sales' => $request->net_sales,
                    'status_pembayaran' => $request->status_pembayaran,
                    'sales_key' => $rkmData->sales_key,
                ];

                $sales = $rkmData->sales_key;

                $Finance = Karyawan::where('jabatan', 'Finance & Accounting')->pluck('kode_karyawan')->toArray();
                $Offman = Karyawan::where('jabatan', 'Office Manager')->first();
                $kooroff = Karyawan::where('jabatan', 'Koordinator Office')->first();

                $users = array_merge(
                    $Finance,
                    [$Offman?->kode_karyawan],
                    [$kooroff?->kode_karyawan],
                    [$sales]
                );

                $users = array_filter($users);
                $notifiedUsers = User::whereHas('karyawan', function ($q) use ($users) {
                    $q->whereIn('kode_karyawan', $users);
                })->get();

                $receiverId = $notifiedUsers->id;
                NotificationFacade::send($notifiedUsers, new OutstandingNotification($data, '/outstanding', $receiverId));
            }
        }

        if (empty($tanggal_awal) || empty($tanggal_akhir)) {
            return redirect()->route('outstanding.index')->with(['success' => 'Data Outstanding berhasil disinkronkan! Harap isi detail data outstanding minggu ini.']);
        } else {
            $startOfWeek = Carbon::parse($tanggal_awal)->toDateString();
            $endOfWeek = Carbon::parse($tanggal_akhir)->toDateString();
            return redirect()->route('outstanding.index')->with(['success' => 'Data Outstanding berhasil disinkronkan untuk minggu ' . $startOfWeek . ' - ' . $endOfWeek]);
        }
    }

    public function getOutstandingLunas()
    {
        $users = auth()->user();
        $user = $users->id_sales;
        if ($users->jabatan == 'SPV Sales') {
            $user = '';
        }
        if ($user) {
            $outstanding = outstanding::with('rkm', 'rkm.perusahaan', 'rkm.materi', 'tracking_outstanding')
                ->where('status_pembayaran', '1')
                ->whereHas('rkm', function ($query) use ($user) {
                    $query->where('sales_key', $user);
                })
                ->get();
        } else {
            $outstanding = outstanding::with('rkm', 'rkm.perusahaan', 'rkm.materi', 'tracking_outstanding')->where('status_pembayaran', '1')->get();
        }
        return response()->json([
            'success' => true,
            'message' => 'List Outstanding Lunas',
            'data' => $outstanding,
        ]);
    }

    public function getOutstandingHutang(Request $request)
    {
        $type = $request->input('type', 'semua'); 
        $user = auth()->user();
        $idSales = $user->jabatan == 'SPV Sales' ? '' : $user->id_sales;

        $query = outstanding::with('rkm.invoice', 'rkm.perusahaan', 'rkm.materi', 'tracking_outstanding')
            ->where('status_pembayaran', '0');

        if ($idSales) {
            $query->whereHas('rkm', function ($q) use ($idSales) {
                $q->where('sales_key', $idSales);
            });
        }

        if ($type === 'minggu_ini') {
            $query->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()]);
        }

        $outstanding = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'List Outstanding Hutang',
            'data' => $outstanding,
        ]);
    }

    public function getOutstandingRKM($year, $month)
    {
        // Ambil semua id_rkm yang sudah ada di tabel outstanding
        $existingRKMs = Outstanding::pluck('id_rkm')->toArray();
        $user = auth()->user()->id_sales;
        if ($user) {
            // Ambil data RKM yang belum ada di tabel outstanding
            $outstanding = RKM::with('perusahaan', 'materi')
                ->whereYear('tanggal_awal', $year)
                ->whereMonth('tanggal_awal', $month)
                ->whereNotIn('id', $existingRKMs)
                ->where('sales_key', $user)
                ->get();
        } else {
            // Ambil data RKM yang belum ada di tabel outstanding
            $outstanding = RKM::with('perusahaan', 'materi')
                ->whereYear('tanggal_awal', $year)
                ->whereMonth('tanggal_awal', $month)
                ->whereNotIn('id', $existingRKMs)
                ->get();
        }

        return response()->json([
            'success' => true,
            'message' => 'List Outstanding RKM',
            'data' => $outstanding,
        ]);
    }

    public function getOutstandingPA()
    {
        $startDate = Carbon::now();
        $endDate   = Carbon::now()->addMonth();

        $rkm = RKM::with(['perhitunganNetSales', 'outstanding', 'perusahaan', 'materi'])
            ->whereHas('outstanding', function ($query) {
                $query->where('status_pembayaran', '1');
            })
            ->whereHas('perhitunganNetSales')
            ->whereBetween('tanggal_akhir', [$startDate, $endDate])
            ->get();

        return response()->json([
            'data' => $rkm
        ]);
    }

    public function detailPA($id)
    {
        $rkm = RKM::with(['perhitunganNetSales.peserta', 'outstanding', 'perusahaan', 'materi'])
            ->where('id', $id)
            ->whereHas('outstanding', function ($query) {
                $query->where('status_pembayaran', '1');
            })
            ->whereHas('perhitunganNetSales')
            ->firstOrFail();

        return view('outstanding.detailPA', compact('rkm'));
    }


    public function create()
    {
        return view('outstanding.create');
    }


    public function store(Request $request)
    {
        // Validasi form  
        $this->validate($request, [
            'id_rkm' => 'required',
            'net_sales' => 'nullable',
            'status_pembayaran' => 'required',
            'due_date' => 'required',
            'pic' => 'nullable',
            'tanggal_bayar' => 'nullable',
            'sales_key' => 'required',
        ]);

        // Simpan data outstanding  
        $outstanding = Outstanding::create([
            'id_rkm' => $request->id_rkm,
            'net_sales' => $request->net_sales,
            'status_pembayaran' => $request->status_pembayaran,
            'due_date' => $request->due_date,
            'pic' => $request->pic,
            'sales_key' => $request->sales_key,
            'tanggal_bayar' => $request->tanggal_bayar,
            'status' => $request->status,
        ]);

        // Inisialisasi status tracking  
        $status_tracking = [
            'invoice' => 0,
            'faktur_pajak' => 0,
            'dokumen_tambahan' => 0,
            'konfir_cs' => 0,
            'tracking_dokumen' => 0,
            'no_resi' => 0,
            'konfir_pic' => 0,
            'pembayaran' => 0,
        ];

        // Ambil status dari request  
        $request_status = $request->status_tracking;

        // Daftar status yang harus diaktifkan  
        $active_statuses = [
            'invoice' => ['invoice'],
            'faktur_pajak' => ['invoice', 'faktur_pajak'],
            'dokumen_tambahan' => ['invoice', 'faktur_pajak', 'dokumen_tambahan'],
            'konfir_cs' => ['invoice', 'faktur_pajak', 'dokumen_tambahan', 'konfir_cs'],
            'tracking_dokumen' => ['invoice', 'faktur_pajak', 'dokumen_tambahan', 'konfir_cs', 'tracking_dokumen'],
            'no_resi' => ['invoice', 'faktur_pajak', 'dokumen_tambahan', 'konfir_cs', 'tracking_dokumen', 'no_resi'],
            'konfir_pic' => ['invoice', 'faktur_pajak', 'dokumen_tambahan', 'konfir_cs', 'tracking_dokumen', 'no_resi', 'konfir_pic'],
            'pembayaran' => ['invoice', 'faktur_pajak', 'dokumen_tambahan', 'konfir_cs', 'tracking_dokumen', 'no_resi', 'konfir_pic', 'pembayaran'],
        ];

        // Atur status tracking berdasarkan request  
        if (array_key_exists($request_status, $active_statuses)) {
            foreach ($active_statuses[$request_status] as $status) {
                $status_tracking[$status] = 1;
            }
        }

        // Cek entri di trackingOutstanding  
        $tracking = trackingOutstanding::where('id_outstanding', $outstanding->id)->first();

        if ($tracking) {
            // Update entri yang ada  
            $tracking->update(array_merge($status_tracking, [
                'status_resi' => $request->status_resi ?? '-',
                'status_pic' => $request->status_pic ?? '-',
                'updated_at' => now(),
            ]));
        } else {
            // Buat entri baru  
            trackingOutstanding::create(array_merge($status_tracking, [
                'id_outstanding' => $outstanding->id,
                'status_resi' => $request->status_resi ?? '-',
                'status_pic' => $request->status_pic ?? '-',
            ]));
        }

        //CATATAN : JIKA MENGUPDATE UBAH DI MIGRATION TRACKING OUTSTANDINGNYA JUGA AGAR SELARAS ATAU TAMBAHKAN MIGRATE BARU


        // Ambil data RKM yang berkaitan
        $rkm = RKM::with('perusahaan', 'materi')->where('id', $request->id_rkm)->first(); // gunakan first() bukan get()

        if ($rkm) {
            $data = [
                'nama_materi' => $rkm->materi->nama_materi,
                'nama_perusahaan' => $rkm->perusahaan->nama_perusahaan,
                'due_date' => $request->due_date,
                'net_sales' => $request->net_sales,
                'status_pembayaran' => $request->status_pembayaran,
                'sales_key' => $rkm->sales_key,
            ];
            $sales = $rkm->sales_key;
            $Finance = Karyawan::where('jabatan', 'Finance & Accounting')->pluck('kode_karyawan')->toArray(); // Mengambil kode_karyawan dari semua karyawan Finance
            $Offman = Karyawan::where('jabatan', 'Office Manager')->first();
            $kooroff = karyawan::where('jabatan', 'Koordinator Office')->first();
            // $GM = Karyawan::where('jabatan', 'GM')->first();

            // Menggabungkan kode_karyawan dalam satu array, termasuk Office Manager dan GM
            $users = array_merge(
                $Finance, // Menambahkan semua kode_karyawan dari Finance ke array
                [$Offman ? $Offman->kode_karyawan : null],
                [$kooroff ? $kooroff->kode_karyawan : null],
                // [$GM ? $GM->kode_karyawan : null],
                [$sales ? $sales : null]
            );

            // Filter array untuk menghapus nilai null
            $users = array_filter($users);

            // Ambil user berdasarkan kode_karyawan yang sesuai
            $users = User::whereHas('karyawan', function ($query) use ($users) {
                $query->whereIn('kode_karyawan', $users);
            })->get();

            $path = '/outstanding';

            // Kirim notifikasi ke setiap user yang ditemukan
            foreach ($users as $user) {
                $receiverId = $user->id;
                NotificationFacade::send($user, new OutstandingNotification($data, $path, $receiverId));
            }
        }

        return redirect()->route('outstanding.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }


    public function edit($id)
    {
        $outstanding = outstanding::findOrFail($id);
        $tracking_outstanding = trackingOutstanding::where('id_outstanding', $id)->first();

        if ($tracking_outstanding == null || $tracking_outstanding == '') {
            $tracking_outstanding = [
                'invoice' => 0,
                'faktur_pajak' => 0,
                'dokumen_tambahan' => 0,
                'konfir_cs' => 0,
                'tracking_dokumen' => 0,
                'no_resi' => 0,
                'net_sales' => 0,
                'konfir_pic' => 0,
                'pembayaran' => 0,
                'status_resi' => '',
                'status_pic' => '',
            ];
        } else {
            $tracking_outstanding = [
                "id" => $tracking_outstanding->id,
                "id_outstanding" => $tracking_outstanding->id_outstanding,
                "invoice" => $tracking_outstanding->invoice,
                "faktur_pajak" => $tracking_outstanding->faktur_pajak,
                "dokumen_tambahan" => $tracking_outstanding->dokumen_tambahan,
                "konfir_cs" => $tracking_outstanding->konfir_cs,
                "tracking_dokumen" => $tracking_outstanding->tracking_dokumen,
                "no_resi" => $tracking_outstanding->no_resi,
                "konfir_pic" => $tracking_outstanding->konfir_pic,
                "pembayaran" => $tracking_outstanding->pembayaran,
                "status_resi" => $tracking_outstanding->status_resi,
                "status_pic" => $tracking_outstanding->status_pic,
                'net_sales' => $outstanding->net_sales,
                "created_at" => $tracking_outstanding->created_at,
                "updated_at" => $tracking_outstanding->updated_at
            ];
        }

        return view('outstanding.edit', compact('outstanding', 'tracking_outstanding'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'id_rkm'     => 'required',
            'net_sales'     => 'nullable',
            'no_regist'     => 'nullable',
            'no_invoice'     => 'nullable',
            'tanggal_bayar'     => 'nullable',
            'status_pembayaran'     => 'required',
            'due_date'     => 'required',
            'pic'     => 'required',
            'sales_key'     => 'required',
            'faktur_pajak' => 'nullable|file|max:2048',
        ]);

        $post = Outstanding::findOrFail($id);

        $fakturPath = null;
        if ($request->hasFile('faktur_pajak')) {
            $file = $request->file('faktur_pajak');
            $fakturPath = $file->store('faktur_pajak', 'public');
        }
        $dokumenTambahanPath = null;

        if ($request->hasFile('dokumen_tambahan_files')) {
            $files = $request->file('dokumen_tambahan_files');

            // Simpan sementara file ke storage/temp
            $tempPaths = [];
            foreach ($files as $file) {
                $path = $file->store('temp');
                $tempPaths[] = storage_path('app/' . $path);
            }

            $outputPath = storage_path('app/public/doc_tambahan/doc_tambahan' . time() . '.pdf');
            if (!file_exists(dirname($outputPath))) {
                mkdir(dirname($outputPath), 0777, true);
            }

            $pdf = new Fpdi();

            foreach ($tempPaths as $filePath) {
                $pageCount = $pdf->setSourceFile($filePath);

                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $tpl = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($tpl);
                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($tpl);
                }
            }

            // Simpan PDF hasil merge
            $pdf->Output($outputPath, 'F');

            // Hapus file sementara
            foreach ($tempPaths as $path) {
                if (file_exists($path)) {
                    unlink($path);
                }
            }

            // 🔥 Optimalkan PDF hasil gabungan
            $optimizedOutputPath = str_replace('.pdf', '_optimized.pdf', $outputPath);

            $result = FacadePdfOptimizer::fromDisk('local')
                ->open(str_replace(storage_path('app/'), '', $outputPath)) // Path relatif dari disk 'local'
                ->settings(PdfSettings::SCREEN) // Atau PREPRESS, PRINTER, dll
                ->colorConversionStrategy(ColorConversionStrategy::DEVICE_INDEPENDENT_COLOR)
                ->colorImageResolution(50) // Atur resolusi gambar untuk mengurangi ukuran
                ->optimize($optimizedOutputPath);

            // Jika optimasi berhasil, gunakan file hasil optimasi
            if ($result->status) {
                unlink($outputPath); // Hapus file asli yang belum dioptimalkan
                $dokumenTambahanPath = str_replace(storage_path('app/'), '', $optimizedOutputPath);
            } else {
                // Jika optimasi gagal, gunakan file asli
                $dokumenTambahanPath = str_replace(storage_path('app/'), '', $outputPath);
            }
        }

        if ($request->hasFile('pembayaran')) {
            $newFile = $request->file('pembayaran');
            $newFilePath = $newFile->store('temp');

            $oldFilePath = null;
            if ($post->path_dokumen_tambahan) {
                $oldFilePath = storage_path('app/' . $post->path_dokumen_tambahan);
            }

            $allTempPaths = [];
            if ($oldFilePath && file_exists($oldFilePath)) {
                $allTempPaths[] = $oldFilePath;
            }
            $allTempPaths[] = storage_path('app/' . $newFilePath);

            $outputPath = storage_path('app/public/doc_tambahan/doc_tambahan' . time() . '.pdf');
            if (!file_exists(dirname($outputPath))) {
                mkdir(dirname($outputPath), 0777, true);
            }

            $pdf = new Fpdi();

            foreach ($allTempPaths as $filePath) {
                $pageCount = $pdf->setSourceFile($filePath);

                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $tpl = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($tpl);
                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($tpl);
                }
            }

            $pdf->Output($outputPath, 'F');

            // Hapus file sementara (kecuali file lama dari database)
            foreach ($allTempPaths as $path) {
                if ($path !== $oldFilePath && file_exists($path)) {
                    unlink($path);
                }
            }

            $newFilePathToDelete = storage_path('app/' . $newFilePath);
            if (file_exists($newFilePathToDelete)) {
                unlink($newFilePathToDelete);
            }

            // 🔥 Optimalkan PDF hasil gabungan
            $optimizedOutputPath = str_replace('.pdf', '_optimized.pdf', $outputPath);

            $result = FacadePdfOptimizer::fromDisk('local')
                ->open(str_replace(storage_path('app/'), '', $outputPath))
                ->settings(PdfSettings::SCREEN)
                ->colorConversionStrategy(ColorConversionStrategy::DEVICE_INDEPENDENT_COLOR)
                ->colorImageResolution(50)
                ->optimize($optimizedOutputPath);

            if ($result->status) {
                if (file_exists($outputPath)) {
                    unlink($outputPath);
                }
                // Simpan path baru untuk update ke database
                $dokumenTambahanPath = str_replace(storage_path('app/'), '', $optimizedOutputPath);
            } else {
                // Jika optimasi gagal, gunakan file asli
                $dokumenTambahanPath = str_replace(storage_path('app/'), '', $outputPath);
            }
        }

        $post->update([
            'id_rkm'     => $request->id_rkm,
            'net_sales'     => $request->net_sales,
            'status_pembayaran'     => $request->status_pembayaran,
            'due_date'     => $request->due_date,
            'pic' => $request->pic,
            'sales_key' => $request->sales_key,
            'no_regist' => $request->no_regist,
            'no_invoice' => $request->no_invoice,
            'tanggal_bayar' => $request->tanggal_bayar,
            'path_faktur_pajak' => $fakturPath ?? $post->path_faktur_pajak,
            'path_dokumen_tambahan' => $dokumenTambahanPath ?? $post->path_dokumen_tambahan,
        ]);

        $status_tracking = [
            'invoice' => 0,
            'faktur_pajak' => 0,
            'dokumen_tambahan' => 0,
            'konfir_cs' => 0,
            'tracking_dokumen' => 0,
            'no_resi' => 0,
            'konfir_pic' => 0,
            'pembayaran' => 0,
        ];

        $request_status = $request->status_tracking;

        switch ($request_status) {
            case 'invoice':
                $status_tracking['invoice'] = 1;
                break;
            case 'faktur_pajak':
                $status_tracking['invoice'] = 1;
                $status_tracking['faktur_pajak'] = 1;
                break;
            case 'dokumen_tambahan':
                $status_tracking['invoice'] = 1;
                $status_tracking['faktur_pajak'] = 1;
                $status_tracking['dokumen_tambahan'] = 1;
                break;
            case 'konfir_cs':
                $status_tracking['invoice'] = 1;
                $status_tracking['faktur_pajak'] = 1;
                $status_tracking['dokumen_tambahan'] = 1;
                $status_tracking['konfir_cs'] = 1;
                break;
            case 'tracking_dokumen':
                $status_tracking['invoice'] = 1;
                $status_tracking['faktur_pajak'] = 1;
                $status_tracking['dokumen_tambahan'] = 1;
                $status_tracking['konfir_cs'] = 1;
                $status_tracking['tracking_dokumen'] = 1;
                break;
            case 'no_resi':
                $status_tracking['invoice'] = 1;
                $status_tracking['faktur_pajak'] = 1;
                $status_tracking['dokumen_tambahan'] = 1;
                $status_tracking['konfir_cs'] = 1;
                $status_tracking['tracking_dokumen'] = 1;
                $status_tracking['no_resi'] = 1;
                break;
            case 'konfir_pic':
                $status_tracking['invoice'] = 1;
                $status_tracking['faktur_pajak'] = 1;
                $status_tracking['dokumen_tambahan'] = 1;
                $status_tracking['konfir_cs'] = 1;
                $status_tracking['tracking_dokumen'] = 1;
                $status_tracking['no_resi'] = 1;
                $status_tracking['konfir_pic'] = 1;
                break;
            case 'pembayaran':
                $status_tracking['invoice'] = 1;
                $status_tracking['faktur_pajak'] = 1;
                $status_tracking['dokumen_tambahan'] = 1;
                $status_tracking['konfir_cs'] = 1;
                $status_tracking['tracking_dokumen'] = 1;
                $status_tracking['no_resi'] = 1;
                $status_tracking['konfir_pic'] = 1;
                $status_tracking['pembayaran'] = 1;
                break;
        }

        // Cek entri trackingOutstanding
        $tracking = trackingOutstanding::where('id_outstanding', $post->id)->first();
        if ($tracking) {
            $tracking->update(array_merge($status_tracking, [
                'status_resi' => $request->status_resi ?? '-',
                'status_pic' => $request->status_pic ?? '-',
                'updated_at' => now(),
            ]));
        } else {
            trackingOutstanding::create(array_merge($status_tracking, [
                'id_outstanding' => $post->id,
                'status_resi' => $request->status_resi ?? '-',
                'status_pic' => $request->status_pic ?? '-',
            ]));
        }

        // Update notifikasi jika status pembayaran 1
        if ($request->status_pembayaran == '1') {
            $rkm = RKM::where('id', $request->id_rkm)->with('perusahaan', 'materi')->first();
            DB::table('notifications')
                ->where('type', 'App\Notifications\OutstandingNotification')
                ->whereJsonContains('data->message->nama_perusahaan', $rkm->perusahaan->nama_perusahaan)
                ->whereJsonContains('data->message->nama_materi', $rkm->materi->nama_materi)
                ->whereJsonContains('data->message->due_date', $request->due_date)
                ->update(['read_at' => now()]);
        }

        if ($request->status_pembayaran == '1') {

            $rkm = RKM::with(['perusahaan', 'materi'])
                ->where('id', $post->id_rkm)
                ->first();

            $users = User::where('id_sales', $post->sales_key)
                ->where('jabatan', 'Finance & Accounting')
                ->get();

            $data = [
                'perusahaan' => $rkm->perusahaan->nama_perusahaan,
                'materi'     => $rkm->materi->nama_materi,
                'tgl_bayar'  => $post->tanggal_bayar,
                'no_invoice' => $post->no_invoice,
                'periode'    => $rkm->tanggal_awal . ' -> ' . $rkm->tanggal_akhir,
            ];

            NotificationFacade::send($users, new OutstandingSelesai($data));
        }


        if ($request->status_pembayaran == '1') {
            $rkm = RKM::with('outstanding', 'perusahaan', 'materi')->where('id', $post->id_rkm)->first();
            $penerima = User::where('jabatan', 'Finance & Accounting') // Cek apakah seharusnya 'Accounting'?
                ->where('status_akun', '1')
                ->get();

            $data = [
                'perusahaan' => $rkm->perusahaan->nama_perusahaan,
                'materi' => $rkm->materi->nama_materi,
                'periode' => $rkm->tanggal_awal . ' -> ' . $rkm->tanggal_akhir,
            ];

            $path = '/outstanding/' . $rkm->id . '/detail';
            NotificationFacade::send($penerima, new OutstandingPaNotification($data, $path));
        }

        return redirect()->route('outstanding.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    public function generatePdfPeserta($id)
    {
        $rkm = RKM::with(
            'instruktur',
            'instruktur2',
            'asisten',
            'materi',
            'perusahaan'
        )->findOrFail($id);

        $peserta = Registrasi::with('peserta')
            ->where('id_rkm', $id)
            ->get();

        $pdf = Pdf::loadView('outstanding.pdfPeserta', compact('rkm', 'peserta'));

        $fileName = 'peserta_rkm_' . $rkm->id . '.pdf';
        $path = 'public/rkm/' . $fileName;

        if ($rkm->pdf_peserta && Storage::exists($rkm->pdf_peserta)) {
            Storage::delete($rkm->pdf_peserta);
        }

        Storage::put($path, $pdf->output());

        $rkm->update([
            'pdf_peserta' => $path
        ]);

        return $path;
    }



    public function dokumenGabungan($id)
    {
        $outstanding = Outstanding::findOrFail($id);
        $invoice = Invoice::where('id_rkm', $outstanding->id_rkm)->first();
        $absensi = AbsensiPDF::where('id_rkm', $outstanding->id_rkm)->first();

        $filesToMerge = [];

        // 0. Invoice
        // if ($invoice) {
        //     $fakturPath = storage_path('app/' . $outstanding->path_faktur_pajak);
        //     if (file_exists($fakturPath)) {
        //         $filesToMerge[] = $fakturPath;
        //     }
        // }

        // 1. Faktur Pajak
        if ($outstanding->path_faktur_pajak) {
            $fakturPath = storage_path('app/' . $outstanding->path_faktur_pajak);
            if (file_exists($fakturPath)) {
                $filesToMerge[] = $fakturPath;
            }
        }

        // 1. Faktur Pajak
        if ($outstanding->path_faktur_pajak) {
            $fakturPath = storage_path('app/' . $outstanding->path_faktur_pajak);
            if (file_exists($fakturPath)) {
                $filesToMerge[] = $fakturPath;
            }
        }

        // 2. Dokumen Tambahan
        if ($outstanding->path_dokumen_tambahan) {
            $dokumenPath = storage_path('app/' . $outstanding->path_dokumen_tambahan);
            if (file_exists($dokumenPath)) {
                $filesToMerge[] = $dokumenPath;
            }
        }

        // 3. Absensi Peserta
        if ($absensi) {
            $absensis = storage_path('app/' . $absensi->pdf_path);
            if (file_exists($absensis)) {
                $filesToMerge[] = $absensis;
            }
        }

        // 4. PDF Peserta
        $rkm = RKM::find($outstanding->id_rkm);

        if ($rkm) {
            if (!$rkm->pdf_peserta || !Storage::exists($rkm->pdf_peserta)) {
                $this->generatePdfPeserta($rkm->id);
                $rkm->refresh();
            }

            $pesertaPdfPath = storage_path('app/' . $rkm->pdf_peserta);
            if (file_exists($pesertaPdfPath)) {
                $filesToMerge[] = $pesertaPdfPath;
            }
        }

        // 5. Sertifikat Peserta bro
        $certs = Certificate::where('rkm_id', $outstanding->id_rkm)->get();

        foreach ($certs as $cert) {
            if ($cert->pdf_path) {
                $certificate = storage_path('app/public/' . $cert->pdf_path);
                $filesToMerge[] = $certificate;
            }
        }

        // 6. Sertifikat Peserta input
        $certPeserta = SertifikatPDF::where('id_rkm', $outstanding->id_rkm)->get();

        foreach ($certPeserta as $cert) {
            if ($cert->pdf_path) {
                $holding = storage_path('app/' . $cert->pdf_path);
                $filesToMerge[] = $holding;
            }
        }

        if (empty($filesToMerge)) {
            return redirect()->back()->with('error', 'Tidak ada dokumen yang bisa digabung.');
        }

        // Path output
        $outputPath = storage_path('app/public/gabungan/gabungan_' . $id . '_' . time() . '.pdf');
        if (!is_dir(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0777, true);
        }

        $pdf = new Fpdi();

        foreach ($filesToMerge as $file) {
            $pageCount = $pdf->setSourceFile($file);
            for ($i = 1; $i <= $pageCount; $i++) {
                $tpl = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($tpl);

                $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
                $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl);
            }
        }

        $pdf->Output($outputPath, 'F');

        $optimizedPath = str_replace('.pdf', '_optimized.pdf', $outputPath);

        $result = FacadePdfOptimizer::fromDisk('local')
            ->open(str_replace(storage_path('app/'), '', $outputPath))
            ->settings(PdfSettings::SCREEN)
            ->colorConversionStrategy(ColorConversionStrategy::DEVICE_INDEPENDENT_COLOR)
            ->colorImageResolution(90)
            ->optimize($optimizedPath);

        $finalFile = $result->status ? $optimizedPath : $outputPath;

        $rkm = RKM::with('perusahaan')->find($outstanding->id_rkm);
        $namaPerusahaan = $rkm?->perusahaan?->nama_perusahaan ?? 'Outstanding';
        $fileName = 'Dokumen_Lengkap_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $namaPerusahaan) . '_' . $id . '.pdf';

        return response()->download($finalFile, $fileName)->deleteFileAfterSend(true);
    }

    private function terbilang($amount)
    {
        $bilangan = [
            '',
            'satu',
            'dua',
            'tiga',
            'empat',
            'lima',
            'enam',
            'tujuh',
            'delapan',
            'sembilan',
            'sepuluh',
            'sebelas'
        ];
        $satuan = ['', 'ribu', 'juta', 'miliar'];

        $amount = (int)$amount; // <-- Ini benar
        if ($amount < 0) return 'Minus ' . $this->terbilang(abs($amount));
        if ($amount == 0) return 'Nol rupiah';

        $words = '';
        $i = 0;
        while ($amount > 0) {
            $part = $amount % 1000;
            if ($part > 0) {
                $partWords = '';
                if ($part < 12) {
                    $partWords = $bilangan[$part];
                } elseif ($part < 20) {
                    $partWords = $bilangan[$part - 10] . ' belas';
                } elseif ($part < 100) {
                    $puluhan = floor($part / 10);
                    $satuan = $part % 10;
                    $partWords = $bilangan[$puluhan] . ' puluh ' . ($satuan > 0 ? $bilangan[$satuan] : '');
                } else {
                    $ratusan = floor($part / 100);
                    $sisa = $part % 100;
                    $partWords = $bilangan[$ratusan] . ' ratus ' . ($sisa > 0 ? $this->terbilang($sisa) : '');
                }
                $words = trim($partWords . ' ' . $satuan[$i] . ' ' . $words);
            }
            $amount = floor($amount / 1000);
            $i++;
        }
        return ucwords(trim($words)) . ' rupiah';
    }

    public function destroy($id)
    {
        $post = outstanding::findOrFail($id);

        $post->delete();

        return redirect()->route('outstanding.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }
}
