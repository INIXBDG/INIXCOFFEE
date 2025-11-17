<?php

namespace App\Http\Controllers;

use App\Models\detailExpenseHub;
use App\Models\expenseHub;
use App\Models\karyawan;
use App\Models\RKM;
use App\Models\trackingExpenseHub;
use App\Models\User;
use App\Notifications\ExpanseHubNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class ExpenseHubController extends Controller
{
    public function index()
    {
        return view('expensehub.index');
    }

    public function PDF(Request $request)
    {
        $id = $request->input('id');

        $expenseHubData = expenseHub::with(['karyawan', 'detailExpenseHub'])
            ->where('id', $id)
            ->first();

        if (!$expenseHubData) {
            abort(404, 'Data tidak ditemukan');
        }

        $data = [
            'tanggal_pengajuan' => Carbon::parse($expenseHubData->created_at)->locale('id')->translatedFormat('l, d F Y'),
            'nama_pengaju'      => $expenseHubData->karyawan->nama_lengkap,
            'ttd'               => $expenseHubData->karyawan->ttd,
            'jabatan'           => $expenseHubData->karyawan->jabatan,
            'detail'            => $expenseHubData->detailExpenseHub->map(function ($detail) {
                return [
                    'nama_pengajuan'  => $detail->nama_pengajuan,
                    'jumlah'          => $detail->jumlah,
                    'harga_pengajuan' => 'Rp ' . number_format($detail->harga_pengajuan, 0, ',', '.'),
                    'keterangan'      => $detail->keterangan,
                ];
            }),
            'total_harga' => 'Rp ' . number_format(
                $expenseHubData->detailExpenseHub->sum(function ($detail) {
                    return $detail->jumlah * $detail->harga_pengajuan;
                }),
                0,
                ',',
                '.'
            ),
        ];

        if ($expenseHubData->karyawan->divisi == 'Sales & Marketing') {
            $finance = karyawan::where('jabatan', 'SPV Sales')->latest()->first();
        } else if ($expenseHubData->karyawan->divisi == 'Office') {
            $finance = karyawan::where('jabatan', 'GM')->latest()->first();
        }
        $gm = karyawan::where('jabatan', 'GM')->latest()->first();

        return view('exports.expenseHub', compact('data', 'finance', 'gm'));
    }

    public function invoice($id)
    {
        $expenseHub = expenseHub::with('karyawan')->findOrFail($id);
        return view('expensehub.invoice', compact('expenseHub'));
    }

    public function updateInvoice(Request $request, $id)
    {
        $post = expenseHub::with('trackingExpenseHub')->findOrFail($id);

        if ($request->hasFile('invoice')) {
            if ($post->invoice) {
                Storage::delete('public/' . $post->invoice);
            }

            $file = $request->file('invoice');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $directory = 'expensehub';
            $path = $file->storeAs($directory, $filename, 'public');

            $latestTracking = $post->trackingExpenseHub->sortByDesc('tanggal')->first();

            if ($latestTracking && $latestTracking->tracking == 'Pencairan Sudah Selesai') {
                $status = 'Selesai';
                trackingExpenseHub::create([
                    'id_expenseHub' => $id,
                    'tracking' => $status,
                    'tanggal' => now()
                ]);
                $post->update([
                    'invoice' => $path,
                ]);
            } else {
                $post->update([
                    'invoice' => $path,
                ]);
            }
        } else {
            return redirect()->route('expensehub.index')
                ->with('error', 'Invoice gagal diupload.');
        }

        return redirect()->route('expensehub.index')
            ->with('success', 'Invoice berhasil disimpan.');
    }

    public function create()
    {
        $id_user = auth()->user()->id;
        $karyawan = karyawan::where('id', $id_user)->first();

        $rkm = RKM::pluck('id');

        return view('expensehub.create', compact('karyawan', 'rkm'));
    }

    public function approved(Request $request)
    {
        $request->validate([
            'status_input' => 'required|in:0,1',
            'id_expense' => 'required|exists:expense_hubs,id',
            'keterangan' => 'nullable|string|max:500',
            'status_finance' => 'nullable|string|max:255',
        ]);

        $userJabatan = auth()->user()->jabatan;
        $statusInput = $request->input('status_input');
        $id = $request->input('id_expense');
        $keterangan = $request->input('keterangan');
        $statusFinance = $request->input('status_finance');

        if ($statusFinance && $userJabatan !== 'Finance & Accounting') {
            return redirect()->back()->with('error', 'Aksi tidak diizinkan.');
        }

        $expenseHub = expenseHub::findOrFail($id);
        $oldStatus = $expenseHub->status;
        $newStatus = $oldStatus;

        if ($statusInput === '1') {
            if ($oldStatus == 0) {
                $newStatus = 1;
            } elseif ($oldStatus == 1 && $userJabatan === 'Finance & Accounting') {
                $newStatus = 2;
            } elseif ($oldStatus == 2 && $userJabatan === 'Finance & Accounting' && $statusFinance === 'Selesai') {
                $newStatus = 3;
            }
        } else {
            $newStatus = 4;
        }

        $expenseHub->status = $newStatus;
        $expenseHub->save();

        $trackingMessage = '';
        if ($statusInput === '1') {
            if ($newStatus == 1) {
                $trackingMessage = "SPV Sales telah menyetujui pengajuan anda, sedang ditinjau oleh Finance & Accounting.";
            } elseif (in_array($newStatus, [2, 3])) {
                $trackingMessage = $statusFinance ?? 'Status diperbarui oleh Finance.';
            }
        } else {
            $trackingMessage = "Pengajuan anda tidak disetujui.";
        }

        $tracking = new trackingExpenseHub();
        $tracking->id_expenseHub = $expenseHub->id;
        $tracking->tracking = $trackingMessage;
        $tracking->tanggal = now();
        $tracking->keterangan = $statusInput === '0' ? $keterangan : null;
        $tracking->save();

        if ($oldStatus !== $newStatus) {
            $type = 'Approved Expense Hub';
            $path = '/expense-hub/detail/' . $expenseHub->id;
            $karyawanPemohon = $expenseHub->karyawan;

            $data = [
                'id_karyawan' => $karyawanPemohon->id,
                'tipe' => $expenseHub->tipe,
                'id_expense_hub' => $expenseHub->id,
                'status_baru' => $newStatus,
                'tanggal_pengajuan' => $expenseHub->created_at->format('D, d M Y'),
            ];

            $usersToNotify = collect([$karyawanPemohon->user]);

            if ($statusInput === '1' && $newStatus == 1) {
                $finance = Karyawan::where('jabatan', 'Finance & Accounting')->first();
                if ($finance?->user) {
                    $usersToNotify->push($finance->user);
                }
            }

            foreach ($usersToNotify as $user) {
                if ($user) {
                    NotificationFacade::send($user, new ExpanseHubNotification($data, $path, $type));
                }
            }
        }

        return redirect()->back()->with('success', 'Status pengajuan berhasil diperbarui.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_karyawan' => 'required|string|max:255',
            'tipe' => 'required|string|max:255',
            'barang.nama_pengajuan.*' => 'nullable|string|max:255',
            'barang.qty.*' => 'nullable|string',
            'barang.harga_pengajuan.*' => 'nullable|string',
            'barang.keterangan.*' => 'nullable|string',
            'id_rkm' => 'nullable',
        ]);

        $nama_pengajuan = $request->input('barang.nama_pengajuan', []);
        $qty = $request->input('barang.qty', []);
        $harga_pengajuan = $request->input('barang.harga_pengajuan', []);
        $keterangan = $request->input('barang.keterangan', []);

        foreach ($harga_pengajuan as $harga) {
            if (strpos($harga, ',') !== false) {
                return redirect()->back()->with('error', 'Jangan menggunakan koma saat mengisi harga barang!');
            }
        }

        $expenseHub = ExpenseHub::create([
            'id_karyawan' => $request->id_karyawan,
            'tipe' => $request->tipe,
            'status' => '0',
            'id_rkm' => $request->id_rkm,
        ]);

        $pengajuanData = [];

        for ($i = 0; $i < count($nama_pengajuan); $i++) {
            $pengajuanData[] = [
                'id_expenseHub' => $expenseHub->id,
                'nama_pengajuan' => $nama_pengajuan[$i] ?? '',
                'jumlah' => $qty[$i] ?? '',
                'harga_pengajuan' => $harga_pengajuan[$i] ?? '',
                'keterangan' => $keterangan[$i] ?? '',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($pengajuanData)) {
            DetailExpenseHub::insert($pengajuanData);
        }

        TrackingExpenseHub::create([
            'id_expenseHub' => $expenseHub->id,
            'tracking' => 'Diajukan dan sedang ditinjau oleh SPV Sales',
            'tanggal' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $karyawan = Karyawan::findOrFail($request->id_karyawan);
        $divisi = $karyawan->divisi;
        $jabatan = $karyawan->jabatan;

        $finance = Karyawan::where('jabatan', 'Finance & Accounting')->first();
        $SPVSales = Karyawan::where('jabatan', 'SPV Sales')->first();

        $users = [];

        switch ($jabatan) {
            case 'SPV Sales':
            case 'Finance & Accounting':
                if ($SPVSales) {
                    $users[] = $SPVSales->kode_karyawan;
                }
                break;
            default:
                switch ($divisi) {
                    case 'Sales & Marketing':
                        if ($SPVSales) {
                            $users[] = $SPVSales->kode_karyawan;
                        }
                        break;
                    case 'Office':
                        if ($finance) {
                            $users[] = $finance->kode_karyawan;
                        }
                        break;
                }
                break;
        }

        $userList = User::whereHas('karyawan', function ($query) use ($users) {
            $query->whereIn('kode_karyawan', $users);
        })->get();

        $data = [
            'id_karyawan' => $request->id_karyawan,
            'tipe' => $request->tipe,
            'tanggal_pengajuan' => now()
        ];

        $type = 'Pengajuan ExpenseHub';
        $path = '/expense-hub/index';

        foreach ($userList as $user) {
            NotificationFacade::send($user, new ExpanseHubNotification($data, $path, $type));
        }

        return redirect()->route('expensehub.index')->with('success', 'berhasil mengajukan!');
    }

    public function get()
    {
        $dataExpenseHub = expenseHub::with(['karyawan', 'detailExpenseHub', 'trackingExpenseHub' => function ($q) {
            $q->latest();
        }])->get();

        $data = $dataExpenseHub->map(function ($item) {
            $latestTracking = $item->trackingExpenseHub->first();

            return [
                'id' => $item->id,
                'tanggal_pengajuan' => $item->created_at->format('D, d M Y'),
                'nama_karyawan' => $item->karyawan->nama_lengkap ?? '-',
                'divisi' => $item->karyawan->divisi ?? '-',
                'tipe' => $item->tipe,
                'tracking' => $latestTracking->tracking ?? '-',
                'status' => $item->status,
                'detail' => $item->detailExpenseHub->map(function ($detail) {
                    return [
                        'nama_pengajuan' => $detail->nama_pengajuan,
                        'jumlah' => $detail->jumlah,
                        'harga_pengajuan' => $detail->harga_pengajuan,
                        'keterangan' => $detail->keterangan,
                    ];
                }),
            ];
        });

        return response()->json($data);
    }

    public function destroy($id)
    {
        $dataExpenseHub = expenseHub::find($id);

        if (!$dataExpenseHub) {
            return redirect()->back()->with('error', 'Data tidak ditemukan!');
        }

        detailExpenseHub::where('id_expenseHub', $dataExpenseHub->id)->delete();
        trackingExpenseHub::where('id_expenseHub', $dataExpenseHub->id)->delete();

        $dataExpenseHub->delete();

        return redirect()->back()->with('success', 'Berhasil menghapus data!');
    }

    public function show($id)
    {
        $dataExpenseHub = expenseHub::with(['karyawan', 'detailExpenseHub', 'trackingExpenseHub', 'rkm'])->where('id', $id)->first();

        if (!$dataExpenseHub) {
            abort(404);
        }

        $dataTerabruTracking = $dataExpenseHub->trackingExpenseHub->sortByDesc('tanggal')->first();
        $data = [
            'id' => $dataExpenseHub->id,
            'tanggal_pengajuan' => Carbon::parse($dataExpenseHub->created_at)->locale('id')->translatedFormat('l, d F Y'),
            'nama_karyawan' => $dataExpenseHub->karyawan->nama_lengkap ?? '-',
            'divisi' => $dataExpenseHub->karyawan->divisi ?? '-',
            'jabatan' => $dataExpenseHub->karyawan->jabatan,
            'tipe' => $dataExpenseHub->tipe,
            'id_rkm' => $dataExpenseHub->id_rkm,
            'status' => $dataTerabruTracking->tracking,
            'invoice' => $dataExpenseHub->invoice,
            'materi' => $dataExpenseHub->rkm->materi->nama_materi,
            'perusahaan' => $dataExpenseHub->rkm->perusahaan->nama_perusahaan,
            'tanggal_mulai' =>Carbon::parse($dataExpenseHub->rkm->tanggal_awal)->locale('id')->translatedFormat('l, d F Y'),
            'tanggal_selesai' =>Carbon::parse($dataExpenseHub->rkm->tanggal_akhir)->locale('id')->translatedFormat('l, d F Y'),
            'detail' => $dataExpenseHub->detailExpenseHub->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'nama_pengajuan' => $detail->nama_pengajuan,
                    'jumlah' => $detail->jumlah,
                    'harga_pengajuan' => $detail->harga_pengajuan,
                    'keterangan' => $detail->keterangan,
                ];
            }),
            'tracking' => $dataExpenseHub->trackingExpenseHub->map(function ($tracking) {
                return [
                    'tracking' => $tracking->tracking,
                    'tanggal' => Carbon::parse($tracking->tanggal)->locale('id')->translatedFormat('l, d F Y'),
                    'keterangan' => $tracking->keterangan ?? '-',
                ];
            }),
        ];

        return view('expensehub.show', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_detail_pengajuan' => 'array',
            'id_detail_pengajuan.*' => 'nullable|exists:detail_expense_hubs,id',
            'nama_barang' => 'array',
            'nama_barang.*' => 'required|string|max:255',
            'qty' => 'array',
            'qty.*' => 'required|integer|min:1',
            'harga' => 'array',
            'harga.*' => 'required|numeric|min:0',
            'keterangan' => 'array',
            'keterangan.*' => 'nullable|string',
            'deleted_ids' => 'nullable|string',
        ]);

        $data = expenseHub::findOrFail($id);

        if ($request->filled('deleted_ids')) {
            $deletedIds = array_filter(explode(',', $request->deleted_ids));
            if (!empty($deletedIds)) {
                detailExpenseHub::whereIn('id', $deletedIds)->delete();
            }
        }

        // Proses update atau create item detail
        if ($request->has('id_detail_pengajuan')) {
            foreach ($request->id_detail_pengajuan as $index => $detailId) {
                $nama_barang = $request->nama_barang[$index] ?? null;
                $qty = $request->qty[$index] ?? null;
                $harga = $request->harga[$index] ?? null;
                $keterangan = $request->keterangan[$index] ?? null;

                if (is_null($detailId)) {
                    detailExpenseHub::create([
                        'id_expenseHub' => $id,
                        'nama_pengajuan' => $nama_barang,
                        'jumlah' => $qty,
                        'harga_pengajuan' => $harga,
                        'keterangan' => $keterangan,
                    ]);
                } else {
                    $detail = detailExpenseHub::findOrFail($detailId);
                    $detail->update([
                        'nama_pengajuan' => $nama_barang,
                        'jumlah' => $qty,
                        'harga_pengajuan' => $harga,
                        'keterangan' => $keterangan,
                    ]);
                }
            }
        }

        $status = "Terjadi perubahan data Barang";
        trackingExpenseHub::create([
            'id_expenseHub' => $id, // Pastikan nama kolom sesuai
            'tracking' => $status,
            'tanggal' => now()
        ]);

        return redirect()->route('expensehub.show', $id)->with('success', 'Data Berhasil diperbarui.');
    }
}
