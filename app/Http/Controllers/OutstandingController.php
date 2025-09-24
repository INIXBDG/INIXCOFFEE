<?php

namespace App\Http\Controllers;

use App\Models\jabatan;
use App\Models\karyawan;
use App\Models\outstanding;
use App\Models\RKM;
use App\Models\trackingOutstanding;
use App\Models\User;
use App\Notifications\OutstandingNotification;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification as NotificationFacade;

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
                    'net_sales' => $request->net_sales,
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

                NotificationFacade::send($notifiedUsers, new OutstandingNotification($data, '/outstanding'));
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
        $type = $request->input('type', 'semua'); // default 'semua'
        $user = auth()->user();
        $idSales = $user->jabatan == 'SPV Sales' ? '' : $user->id_sales;

        $query = outstanding::with('rkm', 'rkm.perusahaan', 'rkm.materi', 'tracking_outstanding')
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
                NotificationFacade::send($user, new OutstandingNotification($data, $path));
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
        //validate form
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
        ]);
        // return $request->all();
        $post = outstanding::findOrFail($id);
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

        // Gunakan switch untuk mengatur nilai  
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
            default:
                $status_tracking['invoice'] = 0;
                $status_tracking['faktur_pajak'] = 0;
                $status_tracking['dokumen_tambahan'] = 0;
                $status_tracking['konfir_cs'] = 0;
                $status_tracking['tracking_dokumen'] = 0;
                $status_tracking['no_resi'] = 0;
                $status_tracking['konfir_pic'] = 0;
                $status_tracking['pembayaran'] = 0;
                break;
        }

        // Cek entri di trackingOutstanding  
        $tracking = trackingOutstanding::where('id_outstanding', $post->id)->first();

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
                'id_outstanding' => $post->id,
                'status_resi' => $request->status_resi ?? '-',
                'status_pic' => $request->status_pic ?? '-',
            ]));
        }

        if ($request->status_pembayaran == '1') {
            $rkm = RKM::where('id', $request->id_rkm)->with('perusahaan', 'materi')->first();

            // Tandai notifikasi terkait sebagai dibaca (set read_at)
            DB::table('notifications')
                ->where('type', 'App\Notifications\OutstandingNotification')
                ->whereJsonContains('data->message->nama_perusahaan', $rkm->perusahaan->nama_perusahaan) // Sesuaikan dengan struktur data Anda
                ->whereJsonContains('data->message->nama_materi', $rkm->materi->nama_materi) // Sesuaikan dengan struktur data Anda
                ->whereJsonContains('data->message->due_date', $request->due_date) // Sesuaikan dengan struktur data Anda
                ->update(['read_at' => Carbon::now()]);
        }

        return redirect()->route('outstanding.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    public function destroy($id)
    {
        $post = outstanding::findOrFail($id);

        $post->delete();

        return redirect()->route('outstanding.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }
}
