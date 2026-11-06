<?php

namespace App\Http\Controllers\office;

use App\Http\Controllers\Controller;
use App\Models\karyawan;
use App\Models\tagihanPerusahaan;
use App\Models\trackingTagihanPerusahaan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TagihanPerusahaanController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View Tagihan Perusahaan', ['only' => ['index', 'detailTagihanPerusahaan', 'dataTagihan']]);
        $this->middleware('permission:Store Tagihan Perusahaan', ['only' => ['storeTagihanPerusahaan']]);
        $this->middleware('permission:Update Tagihan Perusahaan', ['only' => ['updateTagihanPerusahaan']]);
        $this->middleware('permission:Delete Tagihan Perusahaan', ['only' => ['hapusTagihanPerusahaan']]);
    }

    public function index(Request $request)
    {
        $tahun = $request->get('tahun', now()->year);
        $bulan = $request->get('bulan');

        $query = trackingTagihanPerusahaan::with('tagihanPerusahaan')
            ->whereYear('tanggal_perkiraan_mulai', $tahun);

        if ($bulan) {
            $query->whereMonth('tanggal_perkiraan_mulai', $bulan);
        }

        $trackingTagihanPerusahaans = $query
            ->orderByDesc('is_urgent')
            ->orderBy('urgent_order')          
            ->orderBy('tanggal_perkiraan_mulai', 'desc')
            ->get();

        $listTahun = range(now()->year - 3, now()->year + 1);

        return view('office.tagihanPerusahaan.index', compact(
            'trackingTagihanPerusahaans', 'tahun', 'bulan', 'listTahun'
        ));
    }

    public function toggleUrgent($id)
    {
        $tracking = trackingTagihanPerusahaan::findOrFail($id);

        if (!$tracking->is_urgent) {
            // Ambil order terkecil yang ada, lalu kurangi 1 supaya jadi paling atas
            $minOrder = trackingTagihanPerusahaan::where('is_urgent', true)
                            ->min('urgent_order');

            $tracking->is_urgent = true;
            $tracking->urgent_order = is_null($minOrder) ? 1 : ($minOrder - 1);
        } else {
            $tracking->is_urgent = false;
            $tracking->urgent_order = null;
        }

        $tracking->save();
        Cache::forget('office_dashboard_tagihan');

        return response()->json([
            'success'   => true,
            'is_urgent' => (bool) $tracking->is_urgent
        ]);
    }

    public function reorderUrgent(Request $request)
    {
        $orders = $request->orders;

        foreach ($orders as $id => $order) {
            trackingTagihanPerusahaan::where('id', $id)
                ->where('is_urgent', true)
                ->update(['urgent_order' => $order]);
        }

        Cache::forget('office_dashboard_tagihan');

        return response()->json(['success' => true]);
    }

    public function updateInline(Request $request, $id)
    {
        $tracking = trackingTagihanPerusahaan::findOrFail($id);
        $tagihan  = tagihanPerusahaan::find($tracking->id_tagihan_perusahaan);

        $field = $request->field;

        // ===== Khusus Due Date (2 field) =====
        if ($field === 'due_date') {
            $mulai   = $request->tanggal_perkiraan_mulai;
            $selesai = $request->tanggal_perkiraan_selesai ?: $mulai;

            if (!$mulai) {
                return response()->json(['success' => false, 'message' => 'Tanggal mulai wajib'], 422);
            }

            $tracking->tanggal_perkiraan_mulai   = $mulai;
            $tracking->tanggal_perkiraan_selesai = $selesai;
            $tracking->save();

            if ($tagihan) {
                $tagihan->tanggal_perkiraan_mulai   = $mulai;
                $tagihan->tanggal_perkiraan_selesai = $selesai;
                $tagihan->save();
            }

            // Auto status (opsional)
            if ($tracking->tanggal_selesai) {
                $due = \Carbon\Carbon::parse($selesai);
                $realisasi = \Carbon\Carbon::parse($tracking->tanggal_selesai);
                if ($realisasi->gt($due) && $tracking->status !== 'selesai') {
                    $tracking->status = 'telat';
                    $tracking->save();
                } elseif ($realisasi->lte($due)) {
                    $tracking->status = 'selesai';
                    $tracking->save();
                }
            }

            Cache::forget('office_dashboard_tagihan');

            return response()->json(['success' => true]);
        }

        // ===== Field biasa =====
        $value = $request->value;

        $allowed = [
            'kegiatan', 'tipe', 'nominal', 'tracking', 'status',
            'keterangan', 'tanggal_selesai'
        ];

        if (!in_array($field, $allowed)) {
            return response()->json(['success' => false, 'message' => 'Field tidak diizinkan'], 422);
        }

        $tracking->$field = ($value === '' || $value === null) ? null : $value;

        if (in_array($field, ['kegiatan', 'tipe', 'nominal']) && $tagihan) {
            $tagihan->$field = $tracking->$field;
            $tagihan->save();
        }

        // Auto status
        if (in_array($field, ['tanggal_selesai', 'status'])) {
            $due = $tracking->tanggal_perkiraan_selesai
                ? \Carbon\Carbon::parse($tracking->tanggal_perkiraan_selesai)
                : \Carbon\Carbon::parse($tracking->tanggal_perkiraan_mulai);

            if ($tracking->tanggal_selesai) {
                $selesai = \Carbon\Carbon::parse($tracking->tanggal_selesai);
                if ($selesai->gt($due) && $tracking->status !== 'selesai') {
                    $tracking->status = 'telat';
                } elseif ($selesai->lte($due)) {
                    $tracking->status = 'selesai';
                }
            }
        }

        $tracking->save();
        Cache::forget('office_dashboard_tagihan');

        return response()->json([
            'success' => true,
            'status'  => $tracking->status,
            'value'   => $tracking->$field
        ]);
    }

    public function storeTagihanPerusahaan(Request $request)
    {
        $request->validate([
            'kegiatan' => 'required|string',
            'nominal' => 'nullable|numeric|min:0',
            'tipe' => 'required|in:bulanan,tahunan',
            'tanggal_perkiraan_mulai' => 'required|date',
            'tanggal_perkiraan_selesai' => 'nullable|date|after_or_equal:tanggal_perkiraan_mulai',
            'keterangan' => 'nullable|string',
        ]);

        $tagihan = tagihanPerusahaan::create([
            'kegiatan' => $request->kegiatan,
            'tipe' => $request->tipe,
            'nominal' => $request->nominal ?? null,
            'tanggal_perkiraan_mulai' => $request->tanggal_perkiraan_mulai,
            'tanggal_perkiraan_selesai' => $request->tanggal_perkiraan_selesai ?? $request->tanggal_perkiraan_mulai,
            'last_generate' => now(),
        ]);

        trackingTagihanPerusahaan::create([
            'kegiatan' => $request->kegiatan,
            'tipe' => $request->tipe,
            'id_tagihan_perusahaan' => $tagihan->id,
            'nominal' => $request->nominal ?? null,
            'keterangan' => $request->keterangan ?? null,
            'tracking' => 'Diajukan dan Sedang Ditinjau oleh Finance',
            'tanggal_perkiraan_mulai' => $request->tanggal_perkiraan_mulai,
            'tanggal_perkiraan_selesai' => $request->tanggal_perkiraan_selesai ?? $request->tanggal_perkiraan_mulai,
        ]);

        Cache::forget('office_dashboard_tagihan');

        return back()->with('success_tagihan', 'Tagihan perusahaan berhasil dibuat.');
    }

    public function detailTagihanPerusahaan($id)
    {
        $tagihan = trackingTagihanPerusahaan::with('tagihanPerusahaan')->findOrFail($id);

        return view('office.tagihanPerusahaan.detail', compact('tagihan'));
    }

    public function hapusTagihanPerusahaan($id)
    {
        $tracking = trackingTagihanPerusahaan::findOrFail($id);
        
        tagihanPerusahaan::where('id', $tracking->id_tagihan_perusahaan)->delete();
        $tracking->delete();

        Cache::forget('office_dashboard_tagihan');

        return response()->json(['success' => true]);
    }

    public function updateTagihanPerusahaan(Request $request, $id)
    {
        $tracking = trackingTagihanPerusahaan::findOrFail($id);
        $tagihan = tagihanPerusahaan::where('id', $tracking->id_tagihan_perusahaan)->first();

        $tagihan->update([
            'kegiatan' => $request->kegiatan ?? $tagihan->kegiatan,
            'tipe' => $request->tipe ?? $tagihan->tipe,
            'nominal' => $request->nominal ?? $tagihan->nominal,
            'tanggal_perkiraan_mulai' => $request->tanggal_perkiraan_mulai ?? $tagihan->tanggal_perkiraan_mulai,
            'tanggal_perkiraan_selesai' => $request->tanggal_perkiraan_selesai ?? $tagihan->tanggal_perkiraan_selesai,
        ]);

        $tracking->update([
            'nominal' => $request->nominal ?? $tracking->nominal,
            'status' => $request->status ?? $tracking->status,
            'keterangan' => $request->keterangan ?? $tracking->keterangan,
            'tracking' => $request->tracking ?? $tracking->tracking,
            'tanggal_selesai' => $request->tanggal_selesai ?? $tracking->tanggal_selesai,
            'tanggal_perkiraan_mulai' => $tagihan->tanggal_perkiraan_mulai,
            'tanggal_perkiraan_selesai' => $tagihan->tanggal_perkiraan_selesai ?? $tagihan->tanggal_perkiraan_selesai,
            'keterangan' => $request->keterangan ?? $tracking->keterangan,
            'kegiatan' => $request->kegiatan ?? $tagihan->kegiatan,
            'tipe' => $request->tipe ?? $tagihan->tipe,
        ]);

        // set status otomatis
        $dueDate = null;

        if ($tracking->tanggal_perkiraan_selesai) {
            $dueDate = Carbon::parse($tracking->tanggal_perkiraan_selesai);
        } else {
            $dueDate = Carbon::parse($tracking->tanggal_perkiraan_mulai);
        }

        if ($request->tanggal_selesai !== null && $dueDate < $request->tanggal_selesai && !in_array($request->status, ['selesai', 'pending'])) {
            $tracking->status = 'telat';
            $tracking->save();
        } elseif ($request->tanggal_selesai !== null && $dueDate >= $request->tanggal_selesai && $tracking->status) {
            $tracking->status = 'selesai';
            $tracking->save();
        }

        Cache::forget('office_dashboard_tagihan');

        return back()->with('success_tagihan', 'Tagihan berhasil diperbaharui.');
    }

    public function dataTagihan($id)
    {
        $tagihan = trackingTagihanPerusahaan::with('tagihanPerusahaan')->findOrFail($id);

        $data = [
            'id_tagihan' => $tagihan->id,
            'data' => [
                'kegiatan' => $tagihan->tagihanPerusahaan->kegiatan,
                'tipe' => $tagihan->tagihanPerusahaan->tipe,
                'nominal' => $tagihan->nominal,
                'status' => $tagihan->status,
                'keterangan' => $tagihan->keterangan,
                'tracking' => $tagihan->tracking,
                'tanggal_selesai' => $tagihan->tanggal_selesai,
                'tanggal_perkiraan_mulai' => $tagihan->tanggal_perkiraan_mulai,
                'tanggal_perkiraan_selesai' => $tagihan->tanggal_perkiraan_selesai,
            ],
        ];

        return response()->json($data);
    }
}
