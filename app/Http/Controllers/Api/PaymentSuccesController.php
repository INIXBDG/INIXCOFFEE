<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use App\Notifications\PaymentSuccesfromWebNotification;
use App\Notifications\RequestPenawaranNotification;
use Illuminate\Support\Facades\Log;

class PaymentSuccesController extends Controller
{
    public function receivePayment(Request $request)
    {
        try {
            $recive = $request->validate([
                'nama' => 'required|string',
                'instansi' => 'nullable|string',
                'email' => 'required|email',
                'total_harga' => 'required|numeric',
                'cartItems' => 'required|array',
                'paymentResult' => 'required|array',
                'transaksi_uuid' => 'required|string'
            ]);

            $recive['tanggal'] = now();
            $data = $recive;

            // Hanya query 1 kali saja
            $users = User::whereIn('jabatan', ['Customer Care', 'Tim Digital', 'Project Administrator & Business Support'])->get();

            if ($users->isEmpty()) {
                Log::warning('Penerima notifikasi receivePayment tidak ditemukan.');
            }

            // Definisikan tipe dan path tujuan jika notifikasi diklik
            $type = 'Pembayaran dari Web';
            $path = 'https://inixindobdg.co.id'; // Sesuaikan dengan route dashboard/detail transaksi Anda

            foreach ($users as $user) {
                $receiverId = $user->id;
                NotificationFacade::send($user, new PaymentSuccesfromWebNotification($data, $path, $type, $receiverId));
            }

            return response()->json([
                'message' => 'Data pembayaran diterima dengan sukses dan notifikasi telah dikirim',
                'data' => $recive
            ], 200);

        } catch (\Exception $e) {
            Log::error('Gagal memproses receivePayment: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan server'], 500);
        }
    }

    public function requestPenawaran(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_materi' => 'required|string',
                'nama_lengkap' => 'nullable|string',
                'tipe' => 'nullable|string',
                'kelas' => 'nullable|string',
                'email' => 'required|email',
                'no_telepon' => 'required|numeric',
                'instansi' => 'required|string',
                'pax' => 'required|string',
                'id' => 'required|string',
            ]);

            $validated['tanggal'] = now();
            $data = $validated;

            // Hanya query 1 kali saja
            $users = User::whereIn('jabatan', ['Customer Care', 'Tim Digital', 'Project Administrator & Business Support'])->get();

            if ($users->isEmpty()) {
                Log::warning('Penerima notifikasi requestPenawaran tidak ditemukan.');
            }

            // Definisikan tipe dan path tujuan jika notifikasi diklik
            $type = 'Request Penawaran';
            $path = 'https://inixindobdg.co.id'; // Sesuaikan dengan route dashboard Anda

            foreach ($users as $user) {
                $receiverId = $user->id;
                NotificationFacade::send($user, new RequestPenawaranNotification($data, $path, $type, $receiverId));
            }

            return response()->json([
                'message' => 'Data permintaan penawaran diterima dengan sukses dan notifikasi telah dikirim',
                'data' => $validated
            ], 200);

        } catch (\Exception $e) {
            Log::error('Gagal memproses requestPenawaran: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan server'], 500);
        }
    }
}
