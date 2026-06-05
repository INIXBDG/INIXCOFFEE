<?php

namespace App\Http\Controllers;

use App\Exports\PerbaikanKendaraanExport;
use App\Models\detailPengajuanBarang;
use App\Models\tracking_pengajuan_barang;
use App\Models\karyawan;
use App\Models\KondisiKendaraan;
use App\Models\PengajuanBarang;
use App\Models\PerbaikanKendaraan;
use App\Models\User;
use App\Models\vendorBengkel;
use App\Notifications\KondisiKendaraan as NotificationsKondisiKendaraan;
use App\Notifications\NotificationPerbaikanKendaraan;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as FacadesExcel;
use Illuminate\Support\Facades\Auth;

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
        // Load relasi agar akses tracking lebih efisien
        $perbaikan = PerbaikanKendaraan::with(['user.karyawan', 'vendor', 'pengajuanBarang.tracking'])->get();
        $vendor = vendorBengkel::all();
        return view('office.kendaraan.indexPerbaikan', compact('perbaikan', 'vendor'));
    }

    public function detailPerbaikan($id)
    {
        $perbaikan = PerbaikanKendaraan::with('user.karyawan', 'vendor')->findOrFail($id);
        $dataVendor = vendorBengkel::all();
        return view('office.kendaraan.updatePerbaikan', compact('perbaikan', 'dataVendor'));
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
            'vendor' => 'nullable',

            'bukti' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov,avi|max:20480',
        ], [
            'kendaraan.required' => 'Kendaraan wajib diisi.',
            'type_condition.required' => 'Tipe kondisi wajib diisi.',
            'type_vehicle_condition.required' => 'Tingkat kerusakan wajib diisi.',
            'type_repair.required' => 'Jenis Perbaikan wajib diisi.',
            'estimasi.required' => 'Estimasi harga wajib diisi.',
            'deskripsi_kondisi.required' => 'Deskripsi kondisi wajib diisi.',
        ]);

        $perbaikan = new PerbaikanKendaraan();
        $perbaikan->kendaraan = $request->kendaraan;
        $perbaikan->id_user = $request->id_user;
        $perbaikan->type_condition = $request->type_condition ?? 'Perawatan';
        $perbaikan->type_vehicle_condition = $request->type_vehicle_condition;
        $perbaikan->type_repair = $request->type_repair;
        $perbaikan->id_vendor = $request->vendor;

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
            'estimasi' => 'required|integer',
            'deskripsi_kondisi' => 'required|string',
            'tanggal_kejadian' => 'nullable|date',
            'waktu_kejadian' => 'nullable',
            'lokasi' => 'nullable|string',
            'tanggal_perbaikan' => 'nullable|date',
            'harga_akhir' => 'nullable|integer',
            'deskripsi_perbaikan' => 'nullable|string',
            'vendor' => 'nullable|exists:vendor_bengkels,id',
            'bukti' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov,avi|max:20480',
            'invoice' => 'nullable|file|mimes:jpg,jpeg,png,pdf,xls,xlsx,doc,docx|max:10240',
            // Field untuk PengajuanBarang
            'id_karyawan' => 'nullable|exists:users,id',
            'tipe' => 'nullable|string|max:255',
            'no_kk' => 'nullable|string|max:50',
            'tanggal_pencairan' => 'nullable|date',
            'status' => 'nullable|string',
        ]);

        // === 1. UPDATE DATA PERBAIKAN ===
        $perbaikan->kendaraan = $request->kendaraan;
        $perbaikan->type_condition = $request->type_condition;
        $perbaikan->type_vehicle_condition = $request->type_vehicle_condition;
        $perbaikan->type_repair = $request->type_repair;
        $perbaikan->estimasi = $request->estimasi;
        $perbaikan->deskripsi_kondisi = $request->deskripsi_kondisi;
        $perbaikan->deskripsi_perbaikan = $request->deskripsi_perbaikan;
        $perbaikan->tanggal_perbaikan = $request->tanggal_perbaikan;
        $perbaikan->id_vendor = $request->vendor;
        $perbaikan->harga_akhir = $request->filled('harga_akhir') ? (int) $request->harga_akhir : null;

        if ($request->type_condition === 'Kecelakaan') {
            $perbaikan->tanggal_kejadian = $request->tanggal_kejadian;
            $perbaikan->waktu_kejadian = $request->waktu_kejadian;
            $perbaikan->lokasi = $request->lokasi;
        } else {
            $perbaikan->tanggal_kejadian = null;
            $perbaikan->waktu_kejadian = null;
            $perbaikan->lokasi = null;
        }

        // Handle upload bukti
        if ($request->hasFile('bukti')) {
            if ($perbaikan->bukti && Storage::disk('public')->exists($perbaikan->bukti)) {
                Storage::disk('public')->delete($perbaikan->bukti);
            }
            $file = $request->file('bukti');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('perbaikan/bukti', $filename, 'public');
            $perbaikan->bukti = $path;
        }

        // Handle upload invoice
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

        // === 2. CEK ACTION: KIRIM PENGAJUAN BARANG ===
        if ($request->action === 'kirim_pengajuan') {
            if ($perbaikan->pengajuanbarangs_id) {
                return redirect()->back()->withErrors(['error' => 'Data perbaikan ini sudah terhubung dengan Pengajuan Barang dan tidak bisa diajukan ulang.']);
            }
            try {
                // A. Buat record PengajuanBarang
                $pengajuan = PengajuanBarang::create([
                    'id_karyawan' => $request->id_karyawan ?? Auth::id(),
                    'id_kegiatan' => null,
                    'id_tracking' => null,
                    'tipe' => $request->tipe ?? 'Perbaikan Kendaraan',
                    'invoice' => $perbaikan->invoice,
                    'no_kk' => $request->no_kk ?? 'KK-' . $perbaikan->id,
                    'tanggal_pencairan' => $request->tanggal_pencairan ?? null,
                    'tanggal_terima_finance' => null,
                ]);

                detailPengajuanBarang::create([
                    'id_pengajuan_barang' => $pengajuan->id,
                    'nama_barang' => 'Biaya Perbaikan Kendaraan (' . strtoupper($perbaikan->kendaraan) . ')',
                    'qty' => 1,
                    'harga' => $perbaikan->harga_akhir ?: $request->estimasi, 
                    'keterangan' => $perbaikan->deskripsi_kondisi ?? 'Estimasi biaya perbaikan kendaraan',
                ]);

                // C. Buat tracking awal
                $karyawanPemohon = Karyawan::find($pengajuan->id_karyawan);
                $divisi = $karyawanPemohon->divisi;
                $tipe = $request->tipe ?? 'Perbaikan Kendaraan';

                // Tentukan tracking awal sesuai divisi & tipe
                if ($divisi == 'Education') {
                    $trackingStatus = 'Diajukan dan Sedang Ditinjau oleh Education Manager';
                } elseif ($divisi == 'Office' && in_array($tipe, ['Makanan', 'Operasional'])) {
                    $trackingStatus = 'Diajukan dan Sedang Ditinjau oleh Finance';
                } elseif ($divisi == 'Office') {
                    $trackingStatus = 'Diajukan dan Sedang Ditinjau oleh General Manager';
                } elseif ($divisi == 'Sales & Marketing') {
                    $trackingStatus = 'Diajukan dan Sedang Ditinjau oleh SPV Sales';
                } elseif ($divisi == 'IT Service Management') {
                    $trackingStatus = 'Diajukan dan Sedang Ditinjau oleh Koordinator IT Service Management';
                } else {
                    $trackingStatus = 'Diajukan dan Sedang Ditinjau oleh General Manager'; // Fallback
                }

                $tracking = tracking_pengajuan_barang::create([
                    'id_pengajuan_barang' => $pengajuan->id,
                    'tracking' => $trackingStatus, // ✅ SESUAIKAN DENGAN ALUR EXISTING
                    'keterangan' => 'Pengajuan otomatis dari perbaikan kendaraan #' . $perbaikan->id,
                    'created_by' => Auth::id(),
                ]);

                // D. Link tracking ke PengajuanBarang
                $pengajuan->id_tracking = $tracking->id;
                $pengajuan->save();

                // E. Link PerbaikanKendaraan ke PengajuanBarang
                $perbaikan->pengajuanbarangs_id = $pengajuan->id;
                $perbaikan->save();

                // F. Kirim notifikasi
                $penerima = User::whereIn('jabatan', ['GM', 'Finance & Accounting'])->get();
                $karyawan = \App\Models\Karyawan::find($pengajuan->id_karyawan);

                $dataNotif = [
                    'user' => $karyawan->nama_lengkap ?? 'Driver',
                    'kendaraan' => $perbaikan->kendaraan,
                    'estimasi' => 'Rp ' . number_format($perbaikan->estimasi, 0, ',', '.'),
                    'tanggal' => now()->format('d M Y'),
                ];

                $path = '/office/kendaraan/detail/perbaikan/' . $perbaikan->id;
                $type = 'Pengajuan Perbaikan Kendaraan - Butuh Approval';

                foreach ($penerima as $user) {
                    Notification::send($user, new NotificationPerbaikanKendaraan($dataNotif, $path, $type, $user->id));
                }

                return redirect()->back()->with('success', 'Data diperbarui & Pengajuan Barang berhasil dikirim!');
            } catch (\Exception $e) {
                Log::error('Gagal kirim pengajuan: ' . $e->getMessage());
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['error' => 'Gagal kirim pengajuan: ' . $e->getMessage()]);
            }
        }

        return redirect()->back()->with('success', 'Data perbaikan kendaraan berhasil diperbarui.');
    }

    public function deletePerbaikan($id)
    {
        $perbaikan = PerbaikanKendaraan::findOrFail($id);

        if ($perbaikan->pengajuanbarangs_id) {
            $pengajuan = PengajuanBarang::find($perbaikan->pengajuanbarangs_id);
            if ($pengajuan) {
                // Hapus detail dan tracking agar tidak error foreign key
                detailPengajuanBarang::where('id_pengajuan_barang', $pengajuan->id)->delete();
                tracking_pengajuan_barang::where('id_pengajuan_barang', $pengajuan->id)->delete();
                $pengajuan->delete();
            }
        }

        $perbaikan->delete();

        return redirect()->back()->with('success', 'Data perbaikan kendaraan dan pengajuan barang terkait berhasil dihapus.');
    }

    // public function updateStatusPerbaikan(Request $request)
    // {
    //     $data = PerbaikanKendaraan::findOrFail($request->id);
    //     $newStatus = $request->status_tracking;

    //     if ($newStatus === 'setujui') {
    //         $newStatus = 'Telah Disetujui GM';
    //     } elseif ($newStatus === 'tolak') {
    //         $newStatus = 'Ditolak Oleh GM';
    //     }

    //     if ($data->isLinkedToPengajuan()) {
    //         tracking_pengajuan_barang::create([
    //             'id_pengajuan_barang' => $data->pengajuanbarangs_id,
    //             'tracking'            => $newStatus,
    //             'tanggal'             => now(),
    //             'created_by'          => Auth::id(),
    //         ]);
    //     }

    //     $data->update(['status' => $newStatus]);

    //     // 📢 LOGIKA NOTIFIKASI
    //     $users = [$data->user->karyawan->kode_karyawan ?? null];
    //     $path = '/office/kendaraan/detail/perbaikan/' . $data->id;
    //     $type = 'Update Status Perbaikan Kendaraan';

    //     if (str_contains($newStatus, 'Pencairan Sudah Selesai')) {
    //         $type = $data->invoice ? 'Pengajuan Perbaikan - Pencairan Selesai & Invoice Sudah Diunggah' : 'Segera Upload Bukti Pembelian/Invoice';
    //     } elseif (str_contains($newStatus, 'Finance')) {
    //         $type = 'Sedang Diproses Finance';
    //         $finance = \App\Models\Karyawan::where('jabatan', 'Finance & Accounting')->first();
    //         if ($finance) $users[] = $finance->kode_karyawan;
    //     } elseif (str_contains($newStatus, 'GM')) {
    //         $type = 'Menunggu Persetujuan GM';
    //     } elseif (str_contains($newStatus, 'Direksi')) {
    //         $type = 'Menunggu Persetujuan Direksi';
    //         $direksi = \App\Models\Karyawan::where('jabatan', 'Direktur')->first();
    //         if ($direksi) $users[] = $direksi->kode_karyawan;
    //     }

    //     $notifData = [
    //         'tanggal' => now(),
    //         'status' => $newStatus,
    //         'kendaraan' => $data->kendaraan,
    //     ];

    //     $userObjs = \App\Models\User::whereHas('karyawan', function ($query) use ($users) {
    //         $query->whereIn('kode_karyawan', array_filter($users));
    //     })->get();

    //     foreach ($userObjs as $user) {
    //         \Illuminate\Support\Facades\Notification::send(
    //             $user,
    //             new \App\Notifications\NotificationPerbaikanKendaraan($notifData, $path, $type, $user->id)
    //         );
    //     }

    //     return back()->with('success', 'Status berhasil diteruskan mengikuti alur pengajuan barang.');
    // }

    public function SelesaiPerbaikan(Request $request)
    {
        $data = PerbaikanKendaraan::findOrFail($request->id);

        // ✅ Ambil status "Selesai" dari tracking PengajuanBarang jika ada
        if ($data->pengajuanbarangs_id) {
            $latestTracking = tracking_pengajuan_barang::where('id_pengajuan_barang', $data->pengajuanbarangs_id)
                ->where('tracking', 'selesai')
                ->latest('id')
                ->first();

            // Jika tracking 'selesai' sudah ada di pengajuan barang, gunakan itu
            $statusFinal = $latestTracking ? $latestTracking->tracking : 'Selesai';
        } else {
            $statusFinal = 'Selesai';
        }

        // ✅ Update data perbaikan
        $updateData = [
            'status' => $statusFinal,
            'tanggal_perbaikan' => $request->tanggal_perbaikan,
            'deskripsi_perbaikan' => $request->deskripsi_perbaikan,
        ];

        // ✅ Handle upload invoice
        if ($request->hasFile('invoice')) {
            if ($data->invoice && \Storage::disk('public')->exists($data->invoice)) {
                \Storage::disk('public')->delete($data->invoice);
            }
            $file = $request->file('invoice');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('perbaikan/invoice', $filename, 'public');
            $updateData['invoice'] = $path;

            // ✅ Sync invoice ke PengajuanBarang jika ada relasi
            if ($data->pengajuanbarangs_id) {
                $pengajuanBarang = \App\Models\PengajuanBarang::find($data->pengajuanbarangs_id);
                if ($pengajuanBarang) {
                    $pengajuanBarang->update(['invoice' => $path]);
                }
            }
        }

        $data->update($updateData);

        $to = $data->user->karyawan->nama_lengkap ?? 'Driver';
        $path = '/office/kendaraan/detail/perbaikan/' . $data->id;
        $type = 'Perbaikan Kendaraan Selesai';
        $notifData = [
            'tanggal' => now(),
            'status' => $statusFinal,
            'kendaraan' => $data->kendaraan,
        ];

        $userObjs = \App\Models\User::whereHas('karyawan', function ($query) use ($data) {
            $query->where('kode_karyawan', $data->user->karyawan->kode_karyawan ?? null);
        })->get();

        foreach ($userObjs as $user) {
            \Illuminate\Support\Facades\Notification::send(
                $user,
                new \App\Notifications\NotificationPerbaikanKendaraan($notifData, $path, $type, $user->id)
            );
        }

        return back()->with('success', 'Perbaikan kendaraan berhasil diselesaikan.');
    }
    public function pdfExport(Request $request)
    {
        $from = $request->from;
        $to   = $request->to ?? Carbon::now();

        $query = PerbaikanKendaraan::with(['user.karyawan', 'vendor']);

        if ($from && $to) {
            $query->whereBetween('tanggal_kejadian', [
                \Carbon\Carbon::parse($from)->startOfDay(),
                \Carbon\Carbon::parse($to)->endOfDay(),
            ]);
        }

        $data = $query->get();

        $pdf = FacadePdf::loadView('office.kendaraan.pdf', compact('data', 'from', 'to'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
                'defaultFont'          => 'DejaVu Sans',
            ]);

        return $pdf->download(
            'laporan-perbaikan-kendaraan-' . now()->format('Ymd-His') . '.pdf'
        );
    }

    public function excelExport(Request $request)
    {
        $from = $request->get('from');
        $to   = $request->get('to');

        $export = new PerbaikanKendaraanExport($from, $to);

        $filename = 'laporan-perbaikan-kendaraan-' . now()->format('Ymd-His') . '.xlsx';

        return FacadesExcel::download($export, $filename);
    }
}
