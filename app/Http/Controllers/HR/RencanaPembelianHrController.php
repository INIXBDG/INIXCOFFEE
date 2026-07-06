<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PembelianHr;
use App\Models\TrackingPembelianHr;
use App\Models\DetailPembelianHr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Kegiatan;
use App\Models\karyawan;
use App\Models\JurnalAkuntansi;
use App\Models\PengajuanBarang;

class RencanaPembelianHrController extends Controller
{

    public function index()
    {
        $kegiatan = Kegiatan::all();
        $karyawans = Karyawan::all();
        $drivers = karyawan::where('jabatan', 'Driver')
            ->where(function ($query) {
                $query->whereDoesntHave('pickupDriver')->orWhereHas('pickupDriver', function ($q) {
                    $q->whereIn('status_driver', ['Selesai, Driver Ready']);
                });
            })
            ->get();
        
        $dibatalkan = PembelianHr::with('tracking', 'tracking.karyawan', 'details')->where('status_pembelian', 'Dibatalkan')->paginate(10);
        $user = auth()->user();

        $rencanasRaw = PembelianHr::with([
            'details',
            'tracking.karyawan'
        ])
        ->where('status_pembelian', 'Rencana')
        ->when(!in_array($user->jabatan, ['HRD', 'GM']), function ($query) use ($user) {
            $query->where('id_karyawan', $user->id);
        })
        ->get()
        ->map(function ($item) {
            $item->source = 'pembelian_hr';
            $item->status = $item->status_pembelian;
            return $item;
        });

        $pembelianRaw = PembelianHr::with([
            'details',
            'tracking.karyawan'
        ])
        ->where('status_pembelian', 'Terlaksana')
        ->when(!in_array($user->jabatan, ['HRD', 'GM']), function ($query) use ($user) {
            $query->where('id_karyawan', $user->id);
        })
        ->get()
        ->map(function ($item) {
            $item->source = 'pembelian_hr';
            $item->status = $item->status_pembelian;
            return $item;
        });

        $kegiatanRencanaRaw = Kegiatan::with([
            'pengajuan_barang.detail',
            'rincian'
        ])
        ->where('tipe', 'pembelian')
        ->where('status','!=','selesai')
        ->get()
        ->map(function($item){

            $details = collect();

            foreach ($item->rincian as $rincian) {

                $details->push((object)[
                    'jenis' => 'rincian',
                    'nama_barang' => $rincian->hal,
                    'qty' => $rincian->qty,
                    'harga' => $rincian->harga_satuan,
                    'url' => null,
                    'keterangan' => $rincian->rincian,
                    'tanggal' => $rincian->tanggal
                ]);
            }

            foreach ($item->pengajuan_barang as $pengajuan) {

                foreach ($pengajuan->detail as $barang) {

                    $details->push((object)[
                        'jenis' => 'barang',
                        'nama_barang' => $barang->nama_barang,
                        'qty' => $barang->qty,
                        'harga' => $barang->harga,
                        'url' => null,
                        'keterangan' => $barang->keterangan,
                        'tanggal' => $pengajuan->tanggal_pencairan,
                    ]);
                }
            }

            return (object)[
                'id' => $item->id,
                'source' => 'kegiatan',
                'periode' => Carbon::parse($item->waktu_kegiatan)->translatedFormat('F Y'),
                'details' => $details,
                'tracking' => collect(),
                'kategori' => 'Kegiatan',
                'status_pembelian' => $item->status,
                'created_at' => $item->created_at,
                'menunggu' => $item->menunggu,
                'approved' => $item->approved,
                'pencairan' => $item->pencairan,
                'selesai' => $item->selesai,
                'invoice' => $item->pengajuan_barang->pluck('invoice')->filter()->implode(', '),
                'no_kk' => $item->pengajuan_barang->pluck('no_kk')->filter()->implode(', '),
            ];
        });

        $kegiatanPembelianRaw = Kegiatan::with([
            'pengajuan_barang.detail',
            'rincian'
        ])
        ->where('tipe', 'pembelian')
        ->where('status','selesai')
        ->get()
        ->map(function($item){

            $details = collect();

            foreach ($item->rincian as $rincian) {

                $details->push((object)[
                    'jenis' => 'rincian',
                    'nama_barang' => $rincian->hal,
                    'qty' => $rincian->qty,
                    'harga' => $rincian->harga_satuan,
                    'url' => null,
                    'keterangan' => $rincian->rincian,
                    'tanggal' => $rincian->tanggal
                ]);
            }

            foreach ($item->pengajuan_barang as $pengajuan) {

                foreach ($pengajuan->detail as $barang) {

                    $details->push((object)[
                        'jenis' => 'barang',
                        'nama_barang' => $barang->nama_barang,
                        'qty' => $barang->qty,
                        'harga' => $barang->harga,
                        'url' => null,
                        'keterangan' => $barang->keterangan,
                        'tanggal' => $pengajuan->tanggal_pencairan,
                    ]);
                }
            }

            return (object)[
                'id' => $item->id,
                'source' => 'kegiatan',
                'periode' => Carbon::parse($item->waktu_kegiatan)->translatedFormat('F Y'),
                'details' => $details,
                'tracking' => collect(),
                'kategori' => 'Kegiatan',
                'status_pembelian' => $item->status,
                'created_at' => $item->created_at,
                'menunggu' => $item->menunggu,
                'approved' => $item->approved,
                'pencairan' => $item->pencairan,
                'selesai' => $item->selesai,
                'invoice' => $item->pengajuan_barang->pluck('invoice')->filter()->implode(', '),
                'no_kk' => $item->pengajuan_barang->pluck('no_kk')->filter()->implode(', '),
            ];
        });

        if (in_array($user->jabatan, ['HRD', 'GM'])) {
            $rencanas = $rencanasRaw
                ->concat($kegiatanRencanaRaw)
                ->sortBy('periode')
                ->values();
    
            $pembelian = $pembelianRaw
                ->concat($kegiatanPembelianRaw)
                ->sortBy('periode')
                ->values();
        } else {
            $rencanas = $rencanasRaw
                ->sortBy('periode')
                ->values();
    
            $pembelian = $pembelianRaw
                ->sortBy('periode')
                ->values();
        }

        $extends = 'layouts.app';
        $section = 'content';

        return view('office.rab.index', compact('kegiatan', 'drivers', 'pembelian', 'rencanas', 'dibatalkan', 'extends', 'section', 'karyawans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'no_kk' => 'nullable', 
            'kategori' => 'required',
            'periode' => 'required|in:Q1,Q2,Q3,Q4',
            'items' => 'required|array|min:1',
            'items.*.nama_barang' => 'required',  
            'items.*.qty' => 'required|integer|min:1', 
            'items.*.harga' => 'required|numeric',
            'items.*.url' => 'nullable',  
            'items.*.keterangan' => 'nullable',
        ]);

        DB::beginTransaction();
        try {
            $pembelian = PembelianHr::create([
                'no_kk' => $validated['no_kk'],
                'status_pembelian' => 'Rencana',
                'periode' => $request->periode,
                'kategori' => $request->kategori,
                'id_karyawan' => auth()->user()->karyawan->id
            ]);

            foreach ($validated['items'] as $item) {
                DetailPembelianHr::create([
                    'id_pembelian' => $pembelian->id,
                    'nama_barang' => $item['nama_barang'],
                    'qty' => $item['qty'],
                    'harga' => (int) $item['harga'],
                    'url' => $item['url'] ?? null,
                    'keterangan' => $item['keterangan'] ?? null,
                ]);
            }

            $auth = auth()->user()->karyawan;
            TrackingPembelianHr::create([
                'id_pembelian' => $pembelian->id,
                'tracking' => $auth->nama_lengkap . ' telah membuat rencana pembelian',
                'id_karyawan' => $auth->id
            ]);

            DB::commit();
            return back()->with('success', 'Rencana pembelian berhasil dibuat');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rencana pembelian hr (store): ' . $e->getMessage());
            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'no_kk' => 'nullable',
            'kategori' => 'required',
            'periode' => 'required|in:Q1,Q2,Q3,Q4',
            'status_pembelian' => 'nullable',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:detail_pembelian_hrs,id',
            'items.*.nama_barang' => 'required',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.harga' => 'required|numeric',
            'items.*.url' => 'nullable',
            'items.*.keterangan' => 'nullable',
            'deleted_ids' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $pembelian = PembelianHr::findOrFail($id);

            $updateData = [
                'no_kk' => $validated['no_kk'],
                'kategori' => $request->kategori,
                'periode' => $request->periode,
            ];

            if (!empty($validated['status_pembelian'])) {
                $updateData['status_pembelian'] = $validated['status_pembelian'];
            }

            $pembelian->update($updateData);

            if ($request->filled('deleted_ids')) {
                $deletedIds = array_filter(explode(',', $request->deleted_ids));
                DetailPembelianHr::whereIn('id', $deletedIds)->delete();
            }

            foreach ($validated['items'] as $item) {
                if (!empty($item['id'])) {
                    $detail = DetailPembelianHr::findOrFail($item['id']);
                    $detail->update([
                        'nama_barang' => $item['nama_barang'],
                        'qty' => $item['qty'],
                        'harga' => (int) $item['harga'],
                        'url' => $item['url'] ?? null,
                        'keterangan' => $item['keterangan'] ?? null,
                    ]);
                } else {
                    DetailPembelianHr::create([
                        'id_pembelian' => $pembelian->id,
                        'nama_barang' => $item['nama_barang'],
                        'qty' => $item['qty'],
                        'harga' => (int) $item['harga'],
                        'url' => $item['url'] ?? null,
                        'keterangan' => $item['keterangan'] ?? null,
                    ]);
                }
            }

            $auth = auth()->user()->karyawan;
            TrackingPembelianHr::create([
                'id_pembelian' => $pembelian->id,
                'tracking' => $auth->nama_lengkap . ' telah merubah rencana pembelian',
                'id_karyawan' => $auth->id
            ]);
            
            DB::commit();
            return back()->with('success', 'Data berhasil diperbaharui');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rencana pembelian hr (update): ' . $e->getMessage());
            return back()->with('error', 'Gagal memperbaharui data: ' . $e->getMessage());
        }
    }

    public function updateInvoice(Request $request, $id) 
    {
        $request->validate([
            'invoice' => 'required|file|mimes:pdf,jpg,jpeg,png' 
        ]);

        try {
            $pembelian = PembelianHr::findOrFail($id);
    
            if ($pembelian->invoice) {
                Storage::disk('public')->delete($pembelian->invoice);
            }
    
            $path = $request->file('invoice')->store('invoice', 'public');
    
            $pembelian->update([
                'invoice' => $path
            ]);
    
            return back()->with('success', 'Berhasil mengupload invoice');
        } catch (\Exception $e) {       
            Log::error('Error upload invoice hr: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengupload file');
        }
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id_pembelian' => 'required|exists:pembelian_hrs,id',
            'status' => 'required',
            'alasan_dibatalkan' => 'required_if:status,Dibatalkan'
        ]);

        try {
            $pembelian = PembelianHr::findOrFail($request->id_pembelian);

            if($request->status === 'Terlaksana' && (!$pembelian->invoice || !$pembelian->no_kk)) {
                return back()->with('error', 'Lengkapi data nomor kk dan invoice untuk update ke terlaksana!');
            }

            $updateData = [
                'status_pembelian' => $request->status,
                'alasan_dibatalkan' => $request->alasan_dibatalkan
            ];

            $pembelian->update($updateData);

            $auth = auth()->user()->karyawan;
            TrackingPembelianHr::create([
                'id_pembelian' => $pembelian->id,
                'tracking' => $auth->nama_lengkap . ' telah mengupdate status rencana pembelian menjadi ' . $request->status,
                'id_karyawan' => $auth->id
            ]);

            return back()->with('success', 'Status pembelian berhasil diubah');
        } catch (\Exception $e) {
            Log::error('Error update status hr: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengubah status');
        }
    }

    public function delete($id)
    {
        try {
            PembelianHr::findOrFail($id)->delete();
            return back()->with('success', 'Data pembelian berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error update status hr: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus data');
        }
    }

     public function getJurnalAkuntansi(Request $request)
    {
        $pembuat = $request->pembuat;

        $jurnalAkuntansi = JurnalAkuntansi::whereNotNull('id_pengajuan_barang')->get();

        $data = $jurnalAkuntansi->map(function ($jurnal) use ($pembuat) {

            $idsPengajuan = is_array($jurnal->id_pengajuan_barang)
                ? $jurnal->id_pengajuan_barang
                : json_decode($jurnal->id_pengajuan_barang, true);

            $pengajuanBarang = PengajuanBarang::whereIn('id', $idsPengajuan ?? [])
                ->where('id_karyawan', $pembuat)
                ->with(['detail','karyawan'])
                ->get();

            if ($pengajuanBarang->isEmpty()) {
                return null;
            }

            $namaBarang = $pengajuanBarang
                ->flatMap(fn($p) => $p->detail->pluck('nama_barang'))
                ->implode(', ');

            return [
                'id_jurnal'            => $jurnal->id,
                'id_pengajuan'  => $idsPengajuan,
                'nama_barang'          => $namaBarang,
                'total_harga'          => $jurnal->kredit,
                'tanggal'              => $jurnal->tanggal_transaksi,
                'diajukan_oleh'        => optional($pengajuanBarang->first()->karyawan)->nama_lengkap,
            ];

        })->filter()->values();

        return response()->json([
            'data' => $data
        ]);
    }

    public function storeRekap(Request $request) 
    {
        $request->validate([
            'items.*.id_pengajuan' => 'required|exists:pengajuanbarangs,id',
            'items.*.id_jurnal' => 'required|exists:jurnal_akuntansis,id',
            'kategori' => 'required',
            'periode' => 'required|in:Q1,Q2,Q3,Q4',
        ]);

        DB::beginTransaction();
        try{

            foreach($request->items as $item){
                $jurnal = JurnalAkuntansi::findOrFail($item['id_jurnal']);

                $pengajuan = PengajuanBarang::with([
                    'detail',
                    'karyawan'
                ])->findOrFail($item['id_pengajuan']);

                $exists = PembelianHr::where('id_pengajuan', $pengajuan->id)->exists();

                if ($exists) {
                    continue;
                }

                $pembelian = PembelianHr::create([
                    'no_kk'=>$jurnal->nomor_kk,
                    'id_karyawan'=>$pengajuan->id_karyawan,
                    'status_pembelian'=>'Terlaksana',
                    'periode'=>$request->periode,
                    'kategori'=>$request->kategori,
                    'invoice'=>$pengajuan->invoice,
                    'id_pengajuan'=>$pengajuan->id
                ]);

                foreach($pengajuan->detail as $detail){
                    DetailPembelianHr::create([
                        'id_pembelian'=>$pembelian->id,
                        'nama_barang'=>$detail->nama_barang,
                        'qty'=>$detail->qty,
                        'harga'=>$detail->harga,
                        'keterangan'=>$detail->keterangan,
                    ]);
                }

                TrackingPembelianHr::create([
                    'id_pembelian'=>$pembelian->id,
                    'tracking'=>$pengajuan->karyawan->nama_lengkap.' telah membuat rencana pembelian',
                    'id_karyawan'=>$pengajuan->id_karyawan,
                    'created_at'=>$pengajuan->created_at,
                    'updated_at'=>$pengajuan->updated_at,
                ]);
            }

            DB::commit();
            return back()->with('success','Rencana pembelian berhasil dibuat.');
        } catch(\Exception $e){

            DB::rollBack();
            Log::error($e);
            return back()->with('error','Gagal membuat rekap.');
        }
    }
}