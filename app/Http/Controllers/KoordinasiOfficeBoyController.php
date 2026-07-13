<?php

namespace App\Http\Controllers;

use App\Models\karyawan;
use App\Models\KoordinasiOfficeBoy;
use App\Models\TrackingKoordinasiOfficeBoy;
use App\Services\KoordinasiOBService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KoordinasiOfficeBoyController extends Controller
{
    protected KoordinasiOBService $koordinasiService;

    public function __construct(KoordinasiOBService $koordinasiService)
    {
        $this->koordinasiService = $koordinasiService;
        $this->middleware('auth');
    }

    public function index()
    {
        $officeBoy = karyawan::where('jabatan', 'Office Boy')->get();

        return view('office.koordinasiOfficeBoy.index', compact('officeBoy'));
    }

    public function getData()
    {
        $koordinasis = KoordinasiOfficeBoy::with('tracking', 'pembuat', 'karyawan')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'message' => 'Data koordinasi office boy',
            'data' => $koordinasis,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_tugas' => 'required|string',
            'karyawan' => 'required|exists:karyawans,id',
            'deadline' => 'required|date',
            'catatan' => 'nullable',
        ]);

        if (!in_array(Auth()->user()->jabatan, ['HRD', 'GM', 'Office Boy', 'Customer Care'])) {
            abort(401);
        }

        try {
            $this->koordinasiService->createKoordinasi($request->only(['nama_tugas', 'karyawan', 'deadline', 'catatan']), Auth()->user()->id);

            return back()->with('success', 'Koordinasi berhasil dibuat');
        } catch (\Exception $e) {
            Log::error('Error Koordinasi OB:', ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan');
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:koordinasi_office_boys,id',
            'nama_tugas' => 'required|string',
            'karyawan' => 'required|exists:karyawans,id',
            'deadline' => 'required|date',
            'catatan' => 'nullable',
        ]);

        try {
            $this->koordinasiService->updateKoordinasi($request->id, $request->only(['nama_tugas', 'karyawan', 'deadline', 'catatan']), Auth()->user()->id);

            return back()->with('success', 'Koordinasi berhasil di Update');
        } catch (\Exception $e) {
            Log::error('Error Koordinasi Ob : ', ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan pada proses');
        }
    }

    public function delete(string $id)
    {
        try {
            $this->koordinasiService->deleteKoordinasi($id, Auth()->user()->id);
            return response()->json(['message' => 'Data berhasil dihapus']);
        } catch (\Exception $e) {
            Log::error('Error Koordinasi Ob : ', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Terjadi kesalahan'], 500);
        }
    }

    public function updateStatus($action, $id)
    {
        try {
            $this->koordinasiService->updateStatus($action, $id, Auth()->user()->id);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error update status:', ['message' => $e->getMessage()]);
            return response()->json(['success' => false], 500);
        }
    }

    public function updateFromTelegram(Request $request)
    {
        try {
            $data = $request->all();
            $action = $data['action'] ?? '';
            $id = $data['id'] ?? 0;

            Log::info('Update From Telegram Diterima', [
                'action' => $action,
                'id'     => $id
            ]);

            $this->updateStatus($action, $id);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Update From Telegram Error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);

            return response()->json(['success' => false], 500);
        }
    }
    public function webhook(Request $request)
    {
        Log::info('=== TELEGRAM WEBHOOK DITERIMA ===', [
            'all_data' => $request->all()
        ]);

        if (!$request->has('callback_query')) {
            Log::info('Webhook: Bukan callback_query');
            return response()->json(['success' => true]);
        }

        try {
            $callback = $request->input('callback_query');
            $callbackData = $callback['data'] ?? '';

            Log::info('Callback Data Diterima', [
                'data' => $callbackData,
                'from' => $callback['from'] ?? 'unknown'
            ]);

            $parts = explode(':', $callbackData);
            $action = $parts[0] ?? '';
            $id = isset($parts[1]) ? (int)$parts[1] : 0;

            Log::info("Memproses Action", ['action' => $action, 'id' => $id]);

            // Forward ke internal endpoint
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Accept' => 'application/json',
                    'X-Webhook-Secret' => 'RAHASIA_KITA',
                ])
                ->timeout(10)
                ->post('https://coffee.inixindobdg.co.id/api/koordinasi-ob/updateFromTelegram', [
                    'action' => $action,
                    'id' => $id,
                ]);

            Log::info('Forward ke updateFromTelegram selesai', [
                'status' => $response->status(),
                'body'   => $response->json()
            ]);

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('Webhook Error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);

            return response()->json(['success' => false], 500);
        }
    }
}
