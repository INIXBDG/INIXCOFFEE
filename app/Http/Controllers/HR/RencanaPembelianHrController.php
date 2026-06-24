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

class RencanaPembelianHrController extends Controller
{
    private function generateNoKK()
    {
        $lastPembelian = PembelianHr::latest('id')->first();
        
        if (!$lastPembelian) {
            return 'KK-0001';
        }
        
        $lastKK = $lastPembelian->no_kk;
        if (preg_match('/KK-(\d+)/', $lastKK, $matches)) {
            $number = (int) $matches[1];
            $nextNumber = $number + 1;
            return 'KK-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        }
        
        return 'KK-0001';
    }

    public function index()
    {
        $lastKK = PembelianHr::latest('created_at')->first();
        $pembelian = PembelianHr::with('tracking', 'tracking.karyawan', 'details')->where('status_pembelian', 'Terlaksana')->paginate(10);
        $rencanas = PembelianHr::with('tracking', 'tracking.karyawan', 'details')->where('status_pembelian', 'Rencana')->paginate(10);
        $dibatalkan = PembelianHr::with('tracking', 'tracking.karyawan', 'details')->where('status_pembelian', 'Dibatalkan')->paginate(10);
        $nextKK = $this->generateNoKK();

        return view('HR.rencanaPembelian.index', compact('pembelian', 'rencanas', 'dibatalkan', 'lastKK', 'nextKK'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'no_kk' => 'required', 
            'items' => 'required|array|min:1',
            'items.*.nama_barang' => 'required',  
            'items.*.kategori' => 'required', 
            'items.*.qty' => 'required|integer|min:1', 
            'items.*.harga' => 'required|numeric',
            'items.*.keterangan' => 'nullable',
        ]);

        DB::beginTransaction();
        try {
            $pembelian = PembelianHr::create([
                'no_kk' => $validated['no_kk'],
                'status_pembelian' => 'Rencana',
            ]);

            foreach ($validated['items'] as $item) {
                DetailPembelianHr::create([
                    'id_pembelian' => $pembelian->id,
                    'nama_barang' => $item['nama_barang'],
                    'kategori' => $item['kategori'],
                    'qty' => $item['qty'],
                    'harga' => (int) $item['harga'],
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
            'no_kk' => 'required',
            'status_pembelian' => 'nullable',
            'tanggal_pembelian' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:detail_pembelian_hrs,id',
            'items.*.nama_barang' => 'required',
            'items.*.kategori' => 'required',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.harga' => 'required|numeric',
            'items.*.keterangan' => 'nullable',
            'deleted_ids' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $pembelian = PembelianHr::findOrFail($id);

            $updateData = [
                'no_kk' => $validated['no_kk'],
            ];

            if (!empty($validated['status_pembelian'])) {
                $updateData['status_pembelian'] = $validated['status_pembelian'];
            }

            if (!empty($validated['tanggal_pembelian'])) {
                $updateData['tanggal_pembelian'] = $validated['tanggal_pembelian'];
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
                        'kategori' => $item['kategori'],
                        'qty' => $item['qty'],
                        'harga' => (int) $item['harga'],
                        'keterangan' => $item['keterangan'] ?? null,
                    ]);
                } else {
                    DetailPembelianHr::create([
                        'id_pembelian' => $pembelian->id,
                        'nama_barang' => $item['nama_barang'],
                        'kategori' => $item['kategori'],
                        'qty' => $item['qty'],
                        'harga' => (int) $item['harga'],
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
            'invoice' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048' 
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
            'status' => 'required'
        ]);

        try {
            $pembelian = PembelianHr::findOrFail($request->id_pembelian);

            $updateData = [
                'status_pembelian' => $request->status
            ];

            if ($request->status === 'Terlaksana' && empty($pembelian->tanggal_pembelian)) {
                $updateData['tanggal_pembelian'] = Carbon::now()->format('Y-m-d');
            }

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
            return back()->with('success', 'dData pembelian berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error update status hr: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus data');
        }
    }
}