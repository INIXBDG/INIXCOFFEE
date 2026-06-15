<?php

namespace App\Http\Controllers;

use App\Models\karyawan;
use App\Models\KoordinasiOfficeBoy;
use App\Models\TrackingKoordinasiOfficeBoy;
use Illuminate\Http\Request;
use Mockery\Expectation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KoordinasiOfficeBoyController extends Controller
{
    
    public function index()
    {
        $officeBoy = karyawan::where('jabatan', 'Office Boy')->get();

        return view('office.koordinasiOfficeBoy.index', compact('officeBoy'));
    }

    private function telegramSender($telegramPayload, $tipe) {
        try {
            if ($tipe === 'create') {
                Http::withHeaders([
                    'Accept' => 'application/json',
                    'X-Webhook-Secret' => 'RAHASIA_KITA' // Opsional: Untuk keamanan
                ])->timeout(60)->post('https://250d-202-138-248-36.ngrok-free.app/api/new-koordinasi-ob-notification', $telegramPayload);
            } elseif($tipe === 'response') {
                Http::withHeaders([
                    'Accept' => 'application/json',
                    'X-Webhook-Secret' => 'RAHASIA_KITA' // Opsional: Untuk keamanan
                ])->timeout(60)->post('https://250d-202-138-248-36.ngrok-free.app/api/koordinasi-ob-update-status', $telegramPayload);
            }
        } catch (\Exception $e) {
            // Log error jika Laravel A down
            Log::error("Gagal mengirim webhook: " . $e->getMessage());
        }
    }

    public function getData()
    {
        $koordinasis = KoordinasiOfficeBoy::with('tracking', 'pembuat', 'karyawan')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'message' => 'Data koordinasi office boy',
            'data' => $koordinasis
        ]);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'nama_tugas' => 'required|string',
            'karyawan' => 'required|exists:karyawans,id',
            'deadline' => 'required|date|after:now',
            'catatan' => 'nullable',
        ]);

        if (!in_array(Auth()->user()->jabatan, ['HRD', 'GM', 'Office Boy'])) {
            abort(403, 'Akses ditolak.');
        }

        try {
            $create = KoordinasiOfficeBoy::create([
                'nama_tugas' => $request->nama_tugas,
                'karyawan' => $request->karyawan,
                'deadline' => $request->deadline,
                'catatan' => $request->catatan ?? null,
                'created_by' => Auth()->user()->id,
                'status' => 'Menunggu Konfirmasi',
            ]);

            TrackingKoordinasiOfficeBoy::create([
                'koordinasi_id' => $create->id,
                'status' => Auth()->user()->karyawan->nama_lengkap . ' telah membuat koordinasi OB',
                'updated_by' => Auth()->user()->id
            ]);

            $namaOb = karyawan::findOrFail($create->karyawan);
            $telegramPayload = [
                'title' => '🔔 Koordinasi OB Baru',
                'id_pengajuan' => $create->id,
                'nama_tugas' => $create->nama_tugas,
                'creator_name' => Auth()->user()->karyawan->nama_lengkap,
                'ob_name' => $namaOb->nama_lengkap,
                'deadline' => $create->deadline,
                'status' => 'Menunggu Konfirmasi',
                'catatan' => $create->catatan ?? '-'
            ];

            $this->telegramSender($telegramPayload, 'create');

            return back()->with('success', 'Koordinasi berhasil dibuat');
        } catch (Expectation $e) {
            Log::error('Error Koordinasi Ob : ', $e);

            return back()->with('error', 'Terjadi kesalahan pada proses');
        }

    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:koordinasi_office_boys,id',
            'nama_tugas' => 'required|string',
            'karyawan' => 'required|exists:karyawans,id',
            'deadline' => 'required|date|after:now',
            'catatan' => 'nullable',
        ]);

        try {

            $koordinasi = KoordinasiOfficeBoy::findOrFail($request->id);
            $koordinasi->update([
                'nama_tugas' => $request->nama_tugas ?? $koordinasi->nama_tugas,
                'karyawan' => $request->karyawan ?? $koordinasi->karyawan,
                'deadline' => $request->deadline ?? $koordinasi->deadline,
                'catatan' => $request->catatan ?? $koordinasi->catatan,
            ]);
    
            TrackingKoordinasiOfficeBoy::create([
                'koordinasi_id' => $koordinasi->id,
                'status' => Auth()->user()->karyawan->nama_lengkap . ' telah mengupdate koordinasi OB',
                'updated_by' => Auth()->user()->id
            ]);

            return back()->with('success', 'Koordinasi berhasil di Update');
        } catch (Expectation $e) {
            Log::error('Error Koordinasi Ob : ', $e);

            return back()->with('error', 'Terjadi kesalahan pada proses');
        }

    }
    
    public function delete(string $id)
    {
        try {

            $data = KoordinasiOfficeBoy::findOrFail($id);

            TrackingKoordinasiOfficeBoy::create([
                'koordinasi_id' => $data->id,
                'status' => Auth()->user()->karyawan->nama_lengkap . ' telah menghapus koordinasi OB',
                'updated_by' => Auth()->user()->id
            ]);

            $data->delete();

            return response()->json(['message' => 'Data berhasil dihapus']);

        } catch (Expectation $e) {
            Log::error('Error Koordinasi Ob : ', $e);

            return back()->with('error', 'Terjadi kesalahan pada proses');
        }
    }

    public function updateStatus($action, $id)
    {
        try {
            $data = KoordinasiOfficeBoy::findOrFail($id);

            if ($action === 'terima') {
                $data->update([
                    'status' => 'Dikerjakan'
                ]);

                TrackingKoordinasiOfficeBoy::create([
                    'koordinasi_id' => $data->id,
                    'status' => 'Tugas sedang dikerjakan',
                    'updated_by' => $data->karyawan
                ]);

                $namaOb = karyawan::findOrFail($data->karyawan);
                $telegramPayload = [
                    'title' => '✅ Tugas Diterima',
                    'id_pengajuan' => $data->id,
                    'ob_name' => $namaOb->nama_lengkap,
                    'status' => 'Dikerjakan'
                ];

                $this->telegramSender($telegramPayload, 'response');
            } elseif ($action === 'selesai') {
                $data->update([
                    'status' => 'Selesai'
                ]);

                TrackingKoordinasiOfficeBoy::create([
                    'koordinasi_id' => $data->id,
                    'status' => 'Tugas selesai',
                    'updated_by' => $data->karyawan
                ]);

                $namaOb = karyawan::findOrFail($data->karyawan);
                $telegramPayload = [
                    'title' => '🏁 Tugas Selesai',
                    'id_pengajuan' => $data->id,
                    'ob_name' => $namaOb->nama_lengkap,
                    'status' => 'Selesai'
                ];

                $this->telegramSender($telegramPayload, 'response');
            }

        } catch (Expectation $e) {
            Log::error('Error update status koordinasi ob : ', $e);
        }
    }

    public function updateFromTelegram(Request $request) {
         if (
            $request->header('X-Webhook-Secret')
            !== 'RAHASIA_KITA'
        ) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $data = $request->all();
            $action = $data['action'];
            $id = $data['id'];

            $this->updateStatus($action, $id);
            Log::info("Update status koordinasi from telegram webhook");
        } catch (Expectation $e) {
            Log::error("Update From Telegram Error | Id Koordinasi : $id | Action : $action | Error : $e");
        }
    }
}
