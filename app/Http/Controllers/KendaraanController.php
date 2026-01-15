<?php

namespace App\Http\Controllers;

use App\Models\karyawan;
use App\Models\KondisiKendaraan;
use App\Models\User;
use App\Notifications\KondisiKendaraan as NotificationsKondisiKendaraan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class KendaraanController extends Controller
{

    public function indexKondisi()
    {
        $kondisi = KondisiKendaraan::with('user.karyawan')->get();
        return view('office.kendaraan.indexKondisi', compact('kondisi'));
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
        ]);

        $kondisi = KondisiKendaraan::create($validated);

        $penerima = User::where('jabatan', 'Finance & Accounting')->get();
        $karyawan = Karyawan::findOrFail($request->user_id);

        $data = [
            'user' => $karyawan->nama_lengkap,
            'kendaraan' => $request->jenis_kendaraan,
            'tanggal_pemeriksaan' => $request->tanggal_pemeriksaan,
        ];

        $path = '/office/kendaraan/detail/kondisi/' . $kondisi->id;

        Notification::send(
            $penerima,
            new NotificationsKondisiKendaraan($data, $path)
        );


        return redirect()
            ->back()
            ->with('success', 'Data kondisi kendaraan berhasil disimpan.');
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
        ]);

        $kondisi->update($validated);

        return redirect()
            ->back()
            ->with('success', 'Data kondisi kendaraan berhasil diperbarui.');
    }

    public function deleteKondisi($id)
    {
        $kondisi = KondisiKendaraan::findOrFail($id);
        $kondisi->delete();

        return redirect()
            ->back()
            ->with('success', 'Data kondisi kendaraan berhasil dihapus.');
    }
}
