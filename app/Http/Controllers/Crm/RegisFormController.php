<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Deskripsi;
use App\Models\karyawan;
use App\Models\KetentuanForm;
use App\Models\Materi;
use App\Models\Peluang;
use App\Models\Perusahaan;
use App\Models\RegisForm;
use App\Models\RKM;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class RegisFormController extends Controller
{
    public function index($id)
    {
        $lead = Peluang::with('perusahaan', 'aktivitas', 'rkm', 'materiRelation')
            ->findOrFail($id);
        $ketentuan = KetentuanForm::all();
        $ttdauth = karyawan::where('id', auth()->id())->value('ttd');
        $ttdSPV = karyawan::where('jabatan', 'SPV Sales')->value('ttd');
        $ttd = [
            'ttd_user' => $ttdauth,
            'ttd_spv'  => $ttdSPV,
        ];
        return view('crm.regisform.regis', compact('lead', 'ketentuan'));
    }

    public function indexPenawaran()
    {
        $user = Auth::user();
        $sales = karyawan::where('id', $user->id)->first();
        $perusahaan = Perusahaan::where('sales_key', $sales->kode_karyawan)->get();
        $perusahaans = Perusahaan::all();
        $materi = Materi::all();
        $ketentuan = KetentuanForm::all();
        $deskripsi = Deskripsi::first();
        $users = Karyawan::whereIn('jabatan', ['Sales', 'Adm Sales', 'Spv Sales'])
            ->where('status_aktif', '1')
            ->get();
        // dd($users);
        $exam = listexam::all();

        $month = Carbon::now()->month;
        $year  = Carbon::now()->year;

        // Daftar angka Romawi untuk bulan
        $romanMonths = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];

        // Ambil bulan romawi berdasarkan bulan sekarang
        $romanMonth = $romanMonths[$month];

        // Gabungkan ke format nomormu
        $no = "000/MKT-" . Auth::user()->id_sales . "-INIX/BDG/" . $romanMonth . "/" . $year;
        // dd($deskripsi);
        return view('crm.regisform.penawaran', compact('sales', 'perusahaan', 'materi', 'ketentuan', 'deskripsi', 'no', 'users', 'perusahaans', 'exam'));
    }

    public function upload(Request $request)
    {
        $data = $request->validate([
            'id_peluang' => 'required|integer',
            'pdf'        => 'required|file|mimes:pdf|max:20480',
        ]);

        $file = $data['pdf'];
        $prefix = now()->format('d-m-Y'); // contoh: 27-08-2025

        // Generate path baru
        $storedPath = $file->storeAs(
            "pdf/$prefix",                // folder = pdf/27-08-2025
            Str::uuid() . '.pdf',         // nama file unik
            'public'                      // disk
        );

        // ✅ Cek RegisForm
        $existing = RegisForm::where('id_peluang', $data['id_peluang'])->first();

        if ($existing) {
            if (Storage::disk('public')->exists($existing->path)) {
                Storage::disk('public')->delete($existing->path);
            }

            $existing->update([
                'name' => $file->getClientOriginalName(),
                'path' => $storedPath,
            ]);
        } else {
            RegisForm::create([
                'id_peluang' => $data['id_peluang'],
                'name'       => $file->getClientOriginalName(),
                'path'       => $storedPath,
            ]);
        }

        // ✅ Cek juga di RKM
        $peluang = Peluang::find($data['id_peluang']);
        if ($peluang && $peluang->id_rkm) {
            $rkm = RKM::find($peluang->id_rkm);
            if ($rkm) {
                // kalau sudah ada file lama, hapus
                if ($rkm->registrasi_form && Storage::disk('public')->exists($rkm->registrasi_form)) {
                    Storage::disk('public')->delete($rkm->registrasi_form);
                }

                // update dengan file baru
                $rkm->update([
                    'registrasi_form' => $storedPath
                ]);
            }
        }

        return back()->with('success', 'PDF berhasil diupload');
    }

    public function ketentuan()
    {
        $data = KetentuanForm::all();
        $deskripsiData = Deskripsi::first();
        return view('crm.regisform.ketentuan', compact('data', 'deskripsiData'));
    }

    public function storeKetentuan(Request $request)
    {
        $data = new KetentuanForm();
        $data->ketentuan = $request->ketentuan;
        $data->save();
        return back();
    }

    public function updateKetentuan($id, Request $request)
    {
        $data = KetentuanForm::findOrFail($id);
        $data->ketentuan = $request->ketentuan;
        $data->update();
        return back();
    }

    public function deleteKetentuan($id)
    {
        $data = KetentuanForm::where('id', $id)->first();
        $data->delete();
        return back();
    }

    public function storeDeskripsi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'deskripsi' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $deskripsi = Deskripsi::create([
            'deskripsi' => $request->deskripsi
        ]);

        return back();
    }

    public function updateDeskripsi(Request $request, $id)
    {
        $deskripsi = Deskripsi::find($id);

        if (!$deskripsi) {
            return response()->json([
                'success' => false,
                'message' => 'Deskripsi not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'deskripsi' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $deskripsi->update([
            'deskripsi' => $request->deskripsi
        ]);

        return back();
    }

    public function deleteDeskripsi($id)
    {
        $deskripsi = Deskripsi::find($id);

        if (!$deskripsi) {
            return response()->json([
                'success' => false,
                'message' => 'Deskripsi not found'
            ], 404);
        }

        $deskripsi->delete();

        return back();
    }
}
