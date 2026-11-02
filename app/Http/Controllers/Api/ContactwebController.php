<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\ContactWebNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class ContactwebController extends Controller
{
    public function store(Request $request)
    {
        try {
            Log::info('Request received', $request->all());

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'instansi' => 'required|string|max:255',
                'no_wa' => 'required|string|max:15',
                'pesan' => 'required|string',
            ]);

            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'instansi' => $request->instansi,
                'no_wa' => $request->no_wa,
                'pesan' => $request->pesan,
            ];

            Log::info('Fetching Karyawan data');
            $users = User::whereIn('jabatan', ['Tim Digital', 'Customer Care', 'Project Administrator & Business Support'])->get();
            Log::info('Users found', ['count' => $users->count(), 'users' => $users->toArray()]);

            if ($users->isEmpty()) {
                Log::warning('No users found with jabatan Tim Digital or Customer Care');
                return response()->json(['message' => 'Pesan diterima, tetapi tidak ada penerima notifikasi'], 200);
            }

            // Definisikan tipe dan path (sesuaikan dengan rute dashboard Anda, misal '/contact-messages')
            $type = 'Pesan Contact Us Website INIXINDO';
            $path = 'https://inixindobdg.co.id';

            foreach ($users as $user) {
                Log::info('Notifying user', ['user_id' => $user->id, 'username' => $user->username ?? 'N/A']);

                // Ambil ID penerima untuk Private Channel
                $receiverId = $user->id;

                // Kirim notifikasi dengan parameter yang mirip seperti pengajuan barang
                NotificationFacade::send($user, new ContactWebNotification($data, $path, $type, $receiverId));
            }

            return response()->json([
                'message' => 'Pesan berhasil dikirim!',
                'data' => $data
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error in ContactController@store', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Terjadi kesalahan server: ' . $e->getMessage()], 500);
        }
    }
}
