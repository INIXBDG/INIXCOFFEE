<?php

namespace App\Http\Controllers;

use App\Models\AbsensiKaryawan;
use App\Models\Kegiatan;
use App\Models\RincianKegiatan;
use App\Models\User;
use App\Notifications\KegiatanApproved;
use App\Notifications\KegiatanMenunggu;
use App\Notifications\KegiatanNotification;
use App\Notifications\KegiatanPencairan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class KegiatanController extends Controller
{
    public function index()
    {
        $kegiatan = Kegiatan::all();
        return view('office.rab.index', compact('kegiatan'));
    }

    public function show($id)
    {
        $kegiatan = Kegiatan::with('rincian')->findOrFail($id);
        $totalRincian = $kegiatan->rincian->sum('total');

        $karyawan = AbsensiKaryawan::with('karyawan')
            ->whereDate('tanggal', $kegiatan->waktu_kegiatan)
            ->get();
        return view('office.rab.show', compact('kegiatan', 'totalRincian', 'karyawan'));
    }

    public function storeKegiatan(Request $request)
    {
        $validated = $request->validate([
            'nama_kegiatan'   => 'required|string|max:255',
            'waktu_kegiatan'  => 'required|date',
            'lama_kegiatan'   => 'required|max:100',
            'pic'             => 'nullable|string|max:255',
            'status'          => 'nullable|in:Diajukan,Menunggu,Approved,Pencairan,Selesai',
        ]);

        $kegiatan = new Kegiatan();
        $kegiatan->nama_kegiatan  = $validated['nama_kegiatan'];
        $kegiatan->waktu_kegiatan = $validated['waktu_kegiatan'];
        $kegiatan->lama_kegiatan  = $validated['lama_kegiatan'];
        $kegiatan->pic            = $validated['pic'] ?? null;
        $kegiatan->status         = $validated['status'] ?? 'Diajukan';

        $kegiatan->save();

        $penerima = User::where('jabatan', 'GM')->where('status_akun', '1')->first();
        $data = [
            'nama_kegiatan' => $validated['nama_kegiatan'],
            'waktu_kegiatan' => $validated['waktu_kegiatan'],
            'lama_kegiatan' => $validated['lama_kegiatan'],
            'pic' => $validated['pic'],
        ];

        $path = '/office/kegiatan/show/' . $kegiatan->id;
        $type = 'Kegiatan Terbuat';

        Notification::send($penerima, new KegiatanNotification($data, $path, $type));

        return redirect()->back()->with('success', 'Kegiatan berhasil disimpan');
    }

    public function updateKegiatan(Request $request, $id)
    {
        $kegiatan = Kegiatan::findOrFail($id);

        $validated = $request->validate([
            'nama_kegiatan'   => 'required|string|max:255',
            'waktu_kegiatan'  => 'required|date',
            'lama_kegiatan'   => 'required|max:100',
            'pic'             => 'nullable|string|max:255',
            'status'          => 'nullable|in:Diajukan,Menunggu,Approved,Pencairan,Selesai',
        ]);

        $kegiatan->nama_kegiatan  =  $validated['nama_kegiatan'];
        $kegiatan->waktu_kegiatan = $validated['waktu_kegiatan'];
        $kegiatan->lama_kegiatan  = $validated['lama_kegiatan'];
        $kegiatan->pic            = $validated['pic'] ?? null;
        $kegiatan->status         = $validated['status'] ?? 'Diajukan';

        $kegiatan->save();

        return redirect()->back()->with('success', 'Kegiatan berhasil diupdate');
    }

    public function deleteKegiatan($id)
    {
        RincianKegiatan::where('id_kegiatan', $id)->delete();
        Kegiatan::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Kegiatan berhasil dihapus');
    }

    public function storeRincian(Request $request, $id)
    {
        $validated = $request->validate([
            'hal'   => 'required|string|max:255',
            'rincian'  => 'required|string|max:255',
            'qty'   => 'required|integer',
            'harga_satuan'             => 'nullable|numeric|min:0',
        ]);

        $total = $request->harga_satuan * $request->qty;

        $rincian = new RincianKegiatan();
        $rincian->id_kegiatan = $id;
        $rincian->hal  = $validated['hal'];
        $rincian->rincian = $validated['rincian'];
        $rincian->qty  = $validated['qty'];
        $rincian->harga_satuan            = $validated['harga_satuan'];
        $rincian->total    = $total;

        $rincian->save();

        return redirect()->back()->with('success', 'Rincian berhasil disimpan');
    }

    public function updateRincian(Request $request, $id)
    {
        $rincian = RincianKegiatan::findOrFail($id);
        $validated = $request->validate([
            'hal'   => 'required|string|max:255',
            'rincian'  => 'required|string|max:255',
            'qty'   => 'required|integer',
            'harga_satuan'  => 'nullable|numeric|min:0',
        ]);
        $total = $request->harga_satuan * $request->qty;

        $rincian->id_kegiatan = $rincian->id_kegiatan;
        $rincian->hal  = $validated['hal'];
        $rincian->rincian = $validated['rincian'];
        $rincian->qty  = $validated['qty'];
        $rincian->harga_satuan            = $validated['harga_satuan'];
        $rincian->total    = $total;

        $rincian->save();

        return redirect()->back()->with('success', 'Rincian berhasil diupdate');
    }

    public function deleteRincian($id)
    {
        RincianKegiatan::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Rincian berhasil didelete');
    }

    public function gm(Request $request, $id)
    {
        $kegiatan = Kegiatan::findOrFail($id);
        $kegiatan->status = $request->status;
        if ($request->status === 'Approved') {
            $kegiatan->approved = Carbon::now();
        } elseif ($request->status === 'Menunggu') {
            $kegiatan->menunggu = Carbon::now();
        }
        $kegiatan->save();

        if ($request->status === 'Approved') {
            $penerima = User::whereIn('jabatan', [
                'Finance & Accounting',
                'HRD'
            ])
                ->where('status_akun', '1')
                ->get();

            $data = [
                'status' => $request->status,
                'kegiatan' => $kegiatan->nama_kegiatan,
            ];

            $path = '/office/kegiatan/show/' . $kegiatan->id;

            if ($penerima->isNotEmpty()) {
                Notification::send($penerima, new KegiatanApproved($data, $path));
            }
        }

        if ($request->status === 'Menunggu') {
            $user = User::where('jabatan', 'HRD')
                ->where('status_akun', '1')
                ->get();


            $data = [
                'status' => $request->status,
                'kegiatan' => $kegiatan->nama_kegiatan,
            ];

            $path = '/office/kegiatan/show/' . $kegiatan->id;

            Notification::send($user, new KegiatanMenunggu($data, $path));
        }

        return redirect()->back()->with('success', 'Status berhasil diupdate');
    }

    public function finance(Request $request, $id)
    {
        $kegiatan = Kegiatan::findOrFail($id);
        $kegiatan->status = $request->status;
        $kegiatan->pencairan = Carbon::now();
        $kegiatan->save();

        $penerima = User::where('jabatan', 'HRD')
            ->where('status_akun', '1')
            ->get();

        $data = [
            'status' => $request->status,
            'kegiatan' => $kegiatan->nama_kegiatan,

        ];

        $path = '/office/kegiatan/show/' . $kegiatan->id;

        Notification::send($penerima, new KegiatanPencairan($data, $path));
        return redirect()->back()->with('success', 'Status berhasil diupdate');
    }

    public function selesai(Request $request, $id)
    {
        $kegiatan = Kegiatan::findOrFail($id);
        $kegiatan->status = $request->status;
        $kegiatan->selesai = Carbon::now();
        $kegiatan->save();
        return redirect()->back()->with('success', 'Status berhasil diupdate');
    }
}
