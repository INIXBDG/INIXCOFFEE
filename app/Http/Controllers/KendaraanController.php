<?php

namespace App\Http\Controllers;

use App\Models\detailPengajuanBarang;
use App\Models\tracking_pengajuan_barang;
use App\Models\karyawan;
use App\Models\KondisiKendaraan;
use App\Models\PengajuanBarang;
use App\Models\PerbaikanKendaraan;
use App\Models\User;
use App\Notifications\KondisiKendaraan as NotificationsKondisiKendaraan;
use App\Notifications\NotificationPerbaikanKendaraan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class KendaraanController extends Controller
{
    public function indexKondisi()
    {
        $latestPerKendaraan = PerbaikanKendaraan::select('kendaraan')->selectRaw('MAX(id) as max_id')->groupBy('kendaraan');

        $kendaraan = PerbaikanKendaraan::joinSub($latestPerKendaraan, 'latest', function ($join) {
            $join->on('perbaikan_kendaraans.id', '=', 'latest.max_id');
        })
            ->where(function ($query) {
                $query->where('type_condition', '!=', 'Kecelakaan')->orWhere('status', 'Selesai');
            })
            ->where(function ($query) {
                $query->where('type_vehicle_condition', '!=', ['Kerusakan Berat', 'Kerusakan Total'])->orWhere('status', 'Selesai');
            })
            ->pluck('perbaikan_kendaraans.kendaraan');

        if ($kendaraan->isEmpty()) {
            $kendaraan = collect(['H1', 'Innova']);
        }

        $kondisi = KondisiKendaraan::with('user.karyawan')->get();

        return view('office.kendaraan.indexKondisi', compact('kondisi', 'kendaraan'));
    }

    public function detailKondisi($id)
    {
        $kondisi = KondisiKendaraan::with('user.karyawan')->findOrFail($id);
        return view('office.kendaraan.updateKondisi', compact('kondisi'));
    }

    public function storeKondisi(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|string|max:255',
            'jenis_kendaraan' => 'required|in:Innova,H1',

            // Kondisi Fisik
            'fisik_baik' => 'required|boolean',
            'bersih' => 'required|boolean',
            'wiper_baik' => 'required|boolean',
            'klakson_baik' => 'required|boolean',
            'lampu_baik' => 'required|boolean',
            'tekanan_ban_baik' => 'required|boolean',
            'ban_baik' => 'required|boolean',
            'ban_cadangan_lengkap' => 'required|boolean',
            'setir_pedal_baik' => 'required|boolean',
            'catatan_kondisi' => 'nullable|string',

            // Mesin
            'oli_baik' => 'required|boolean',
            'radiator_baik' => 'required|boolean',
            'air_wiper_baik' => 'required|boolean',
            'minyak_rem_baik' => 'required|boolean',
            'aki_baik' => 'required|boolean',
            'catatan_mesin' => 'nullable|string',

            // Dokumen & Perlengkapan
            'dokumen_lengkap' => 'required|boolean',
            'jas_hujan_ada' => 'required|boolean',
            'pengharum_ada' => 'required|boolean',
            'catatan_perlengkapan' => 'nullable|string',

            // Fasilitas
            'ac_baik' => 'required|boolean',
            'audio_baik' => 'required|boolean',
            'charger_ada' => 'required|boolean',
            'air_minum_ada' => 'required|boolean',
            'tisu_ada' => 'required|boolean',
            'hand_sanitizer_ada' => 'required|boolean',
            'catatan_fasilitas' => 'nullable|string',

            // BBM & Tol
            'bbm_cukup' => 'required|boolean',
            'etol_aktif' => 'required|boolean',
            'tanggal_pemeriksaan' => 'required|date',

            // keluhan
            'keluhan' => 'nullable',
        ]);

        $kondisi = KondisiKendaraan::create($validated);

        if ($kondisi->keluhan != null) {
            $perbaikan = new PerbaikanKendaraan();
            $perbaikan->id_kondisi_kendaraan = $kondisi->id;
            $perbaikan->kendaraan = $kondisi->jenis_kendaraan;
            $perbaikan->id_user = $kondisi->user_id;
            $perbaikan->type_condition = 'Perawatan';
            $perbaikan->type_vehicle_condition = 'Kerusakan Ringan';
            $perbaikan->type_repair = 'Penggantian';
            $perbaikan->deskripsi_kondisi = $kondisi->keluhan;
            $perbaikan->status = 'Diajukan';
            $perbaikan->save();

            $penerimaPerbaikan = User::whereIn('jabatan', ['GM', 'Finance & Accounting'])->get();

            $karyawan = Karyawan::findOrFail($request->user_id);

            $data = [
                'user' => $karyawan->nama_lengkap,
                'kendaraan' => $request->jenis_kendaraan,
                'tanggal_pemeriksaan' => $request->tanggal_pemeriksaan,
            ];

            $path = '/office/kendaraan/detail/perbaikan/' . $perbaikan->id;

            $type = 'Pengajuan Perbaikan Kendaraan';

            foreach ($penerimaPerbaikan as $user) {
                $receiverId = $user->id;

                Notification::send($penerimaPerbaikan, new NotificationPerbaikanKendaraan($data, $path, $type, $receiverId));
            }
        }

        return redirect()->back()->with('success', 'Data kondisi kendaraan berhasil disimpan.');
    }

    public function updateKondisi(Request $request, $id)
    {
        $kondisi = KondisiKendaraan::findOrFail($id);

        $validated = $request->validate([
            'jenis_kendaraan' => 'required|in:Innova,H1',

            // Kondisi Fisik
            'fisik_baik' => 'required|boolean',
            'bersih' => 'required|boolean',
            'wiper_baik' => 'required|boolean',
            'klakson_baik' => 'required|boolean',
            'lampu_baik' => 'required|boolean',
            'tekanan_ban_baik' => 'required|boolean',
            'ban_baik' => 'required|boolean',
            'ban_cadangan_lengkap' => 'required|boolean',
            'setir_pedal_baik' => 'required|boolean',
            'catatan_kondisi' => 'nullable|string',

            // Mesin
            'oli_baik' => 'required|boolean',
            'radiator_baik' => 'required|boolean',
            'air_wiper_baik' => 'required|boolean',
            'minyak_rem_baik' => 'required|boolean',
            'aki_baik' => 'required|boolean',
            'catatan_mesin' => 'nullable|string',

            // Dokumen & Perlengkapan
            'dokumen_lengkap' => 'required|boolean',
            'jas_hujan_ada' => 'required|boolean',
            'pengharum_ada' => 'required|boolean',
            'catatan_perlengkapan' => 'nullable|string',

            // Fasilitas
            'ac_baik' => 'required|boolean',
            'audio_baik' => 'required|boolean',
            'charger_ada' => 'required|boolean',
            'air_minum_ada' => 'required|boolean',
            'tisu_ada' => 'required|boolean',
            'hand_sanitizer_ada' => 'required|boolean',
            'catatan_fasilitas' => 'nullable|string',

            // BBM & Tol
            'bbm_cukup' => 'required|boolean',
            'etol_aktif' => 'required|boolean',
            'tanggal_pemeriksaan' => 'required|date',

            // keluhan
            'keluhan' => 'nullable',
        ]);

        $kondisi->update($validated);

        return redirect()->back()->with('success', 'Data kondisi kendaraan berhasil diperbarui.');
    }

    public function deleteKondisi($id)
    {
        $kondisi = KondisiKendaraan::findOrFail($id);
        $kondisi->delete();

        return redirect()->back()->with('success', 'Data kondisi kendaraan berhasil dihapus.');
    }

    public function indexPerbaikan()
    {
        $perbaikan = PerbaikanKendaraan::with('user.karyawan')->get();
        return view('office.kendaraan.indexPerbaikan', compact('perbaikan'));
    }

    public function detailPerbaikan($id)
    {
        $perbaikan = PerbaikanKendaraan::with('user.karyawan')->findOrFail($id);
        return view('office.kendaraan.updatePerbaikan', compact('perbaikan'));
    }

    public function storePerbaikan(Request $request)
    {
        $validated = $request->validate([
            'kendaraan' => 'required|string|max:100',
            'id_user' => 'required|exists:users,id',
            'type_condition' => 'required|in:Perawatan,Kecelakaan',
            'type_vehicle_condition' => 'required|in:Kerusakan Ringan,Kerusakan Sedang,Kerusakan Berat,Kerusakan Total',
            'type_repair' => 'required|in:Penggantian,Peningkatan,Perbaikan,Perbaikan Total',
            'estimasi' => 'required',
            'deskripsi_kondisi' => 'required|string',
            'status' => 'sometimes|in:Diajukan,Diproses,Selesai,Ditolak',

            'bukti' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov,avi|max:20480',
        ]);

        $perbaikan = new PerbaikanKendaraan();
        $perbaikan->kendaraan = $request->kendaraan;
        $perbaikan->id_user = $request->id_user;
        $perbaikan->type_condition = $request->type_condition ?? 'Perawatan';
        $perbaikan->type_vehicle_condition = $request->type_vehicle_condition;
        $perbaikan->type_repair = $request->type_repair;

        if ($request->type_condition === 'Kecelakaan') {
            $perbaikan->tanggal_kejadian = $request->tanggal_kejadian;
            $perbaikan->waktu_kejadian = $request->waktu_kejadian;
            $perbaikan->lokasi = $request->lokasi;
        }

        $perbaikan->estimasi = $request->estimasi;
        $perbaikan->deskripsi_kondisi = $request->deskripsi_kondisi;
        $perbaikan->status = $request->status ?? 'Diajukan';

        if ($request->hasFile('bukti')) {
            $file = $request->file('bukti');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('perbaikan/bukti', $filename, 'public');
            $perbaikan->bukti = $path;
        }

        $perbaikan->save();

        $penerimaPerbaikan = User::whereIn('jabatan', ['GM', 'Finance & Accounting'])->get();

        $karyawan = Karyawan::findOrFail($request->id_user);

        $data = [
            'user' => $karyawan->nama_lengkap,
            'kendaraan' => $request->kendaraan,
            'tanggal_pemeriksaan' => now()->format('d-m-Y'),
        ];

        $path = '/office/kendaraan/detail/perbaikan/' . $perbaikan->id;
        $type = 'Pengajuan Perbaikan Kendaraan';

        foreach ($penerimaPerbaikan as $user) {
            Notification::send($user, new NotificationPerbaikanKendaraan($data, $path, $type, $user->id));
        }

        return back()->with('success', 'Pengajuan perbaikan berhasil dikirim.');
    }

    public function updatePerbaikan(Request $request, $id)
    {
        $perbaikan = PerbaikanKendaraan::findOrFail($id);

        $validated = $request->validate([
            'kendaraan' => 'required|string|max:100',
            'type_condition' => 'required|in:Perawatan,Kecelakaan',
            'type_vehicle_condition' => 'required|in:Kerusakan Ringan,Kerusakan Sedang,Kerusakan Berat,Kerusakan Total',
            'type_repair' => 'required|in:Penggantian,Peningkatan,Perbaikan,Perbaikan Total',
            'estimasi' => 'required',
            'deskripsi_kondisi' => 'required|string',

            'tanggal_kejadian' => 'nullable|date',
            'waktu_kejadian' => 'nullable',
            'lokasi' => 'nullable|string',

            'bukti' => 'nullable',

            'tanggal_perbaikan' => 'nullable|date',
            'deskripsi_perbaikan' => 'nullable|string',

            'invoice' => 'nullable'
        ]);

        $perbaikan->kendaraan = $request->kendaraan;
        $perbaikan->type_condition = $request->type_condition;
        $perbaikan->type_vehicle_condition = $request->type_vehicle_condition;
        $perbaikan->type_repair = $request->type_repair;
        $perbaikan->estimasi = $request->estimasi;
        $perbaikan->deskripsi_kondisi = $request->deskripsi_kondisi;
        $perbaikan->deskripsi_perbaikan = $request->deskripsi_perbaikan;
        $perbaikan->tanggal_perbaikan = $request->tanggal_perbaikan ;

        if ($request->type_condition === 'Kecelakaan') {
            $perbaikan->tanggal_kejadian = $request->tanggal_kejadian;
            $perbaikan->waktu_kejadian = $request->waktu_kejadian;
            $perbaikan->lokasi = $request->lokasi;
        } else {
            $perbaikan->tanggal_kejadian = null;
            $perbaikan->waktu_kejadian = null;
            $perbaikan->lokasi = null;
        }

        if ($request->hasFile('bukti')) {
            if ($perbaikan->bukti && Storage::disk('public')->exists($perbaikan->bukti)) {
                Storage::disk('public')->delete($perbaikan->bukti);
            }

            $file = $request->file('bukti');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('perbaikan/bukti', $filename, 'public');

            $perbaikan->bukti = $path;
        }

        if ($request->hasFile('invoice')) {
            if ($perbaikan->invoice && Storage::disk('public')->exists($perbaikan->invoice)) {
                Storage::disk('public')->delete($perbaikan->invoice);
            }

            $file = $request->file('invoice');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('perbaikan/invoice', $filename, 'public');

            $perbaikan->invoice = $path;
        }

        $perbaikan->save();



        return redirect()->back()->with('success', 'Data perbaikan kendaraan berhasil diperbarui.');
    }

    public function deletePerbaikan($id)
    {
        $perbaikan = PerbaikanKendaraan::findOrFail($id);
        $perbaikan->delete();

        return redirect()->back()->with('success', 'Data perbaikan kendaraan berhasil dihapus.');
    }

    public function updateStatusPerbaikan(Request $request)
    {
        $data = PerbaikanKendaraan::findOrFail($request->id);
        $statusChange = $request->status_tracking;
        if ($request->status_tracking === 'setujui') {
            $statusChange = 'Telah Disetujui GM';

        } elseif ($request->status_tracking === 'tolak') {
            $statusChange = 'Ditolak Oleh GM';
        } 

        if ($data->pengajuanbarangs_id) {
            $PengajuanBarang = PengajuanBarang::where('id', $data->pengajuanbarangs_id)->with('tracking')->latest()->first();

            $e = tracking_pengajuan_barang::create([
                'id_pengajuan_barang' => $PengajuanBarang->id,
                'tracking' => $statusChange,
                'tanggal' => now(),
            ]);
            $PengajuanBarang->update([
                'id_tracking' => $e->id,
            ]);
        } else if (!$data->pengajuanbarangs_id && $request->status_tracking === 'setujui') {
            $PengajuanBarang = PengajuanBarang::create([
                'tipe' => $request->tipe,
                'id_karyawan' => $data->id_user,
                'tipe' => 'Bengkel'
            ]);

            $detailPengajuanBarang = detailPengajuanBarang::create([
                'id_pengajuan_barang' => $PengajuanBarang->id,
                'nama_barang' => $data->type_condition . ' Mobil ' . $data->kendaraan,
                'qty' => '1',
                'harga' => $data->estimasi
            ]);

            if ($data->type_condition === 'Kecelakaan') {
                $detailPengajuanBarang->keterangan = 'Urgent';
            }

            $tracking_pengajuan_barang = tracking_pengajuan_barang::create([
                'id_pengajuan_barang' => $PengajuanBarang->id,
                'tracking' => $statusChange,
                'tanggal' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $PengajuanBarang->update([
                'id_tracking' => $tracking_pengajuan_barang->id,
            ]);

            $data->pengajuanbarangs_id = $PengajuanBarang->id;
            $data->status = $statusChange;

            $detailPengajuanBarang->save();
            $PengajuanBarang->save();
        }

        $data->status = $statusChange;
        $data->save();

        return back()->with('success', 'Status berhasil diperbarui');
    }

    public function SelesaiPerbaikan(Request $request)
    {
        $data = PerbaikanKendaraan::findOrFail($request->id);
        $data->status = 'Selesai';
        $data->tanggal_perbaikan = $request->tanggal_perbaikan;
        $data->deskripsi_perbaikan = $request->deskripsi_perbaikan;

        // update untuk pengajuan barang
        $PengajuanBarang = PengajuanBarang::where('id', $data->pengajuanbarangs_id)->first();
        $e = tracking_pengajuan_barang::create([
            'id_pengajuan_barang' => $PengajuanBarang->id,
            'tracking' => 'Selesai',
            'tanggal' => now(),
        ]);
        $PengajuanBarang->update([
            'id_tracking' => $e->id,
        ]);

        if ($request->hasFile('invoice')) {
            if ($data->invoice && Storage::disk('public')->exists($data->invoice)) {
                Storage::disk('public')->delete($data->invoice);
            }

            $file = $request->file('invoice');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('perbaikan/invoice', $filename, 'public');

            $data->invoice = $path;
            $PengajuanBarang->invoice = $path;
        }

        $data->save();
        $PengajuanBarang->save();

        return back()->with('success', 'Perbaikan kendaraan berhasil diselesaikan.');
    }
}
