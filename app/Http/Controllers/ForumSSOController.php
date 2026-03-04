<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class ForumSSOController extends Controller
{    
    protected $AbsensiKaryawanController;

    public function __construct(AbsensiKaryawanController $AbsensiKaryawanController)
    {
        $this->middleware('auth');
        $this->AbsensiKaryawanController = $AbsensiKaryawanController;
    }
    public function redirect()
    {
        $user = Auth::user();
        $karyawan = $user->karyawan;
        // dd($karyawan);

       $data = [
            'id'    => $user->id,
            'name'  => $user->username,
            'email' => $karyawan->email,
            'time'  => now()->timestamp,
        ];

        // dd($data);

        $payload = rtrim(strtr(
            base64_encode(json_encode($data)),
            '+/',
            '-_'
        ), '=');
        $signature = hash_hmac('sha256', $payload, config('services.forumium.secret'));

        $response = $this->AbsensiKaryawanController->cekip();
		// Mengambil data asli dari objek JsonResponse
		$cekipData = $response->getData(); 
		$cekip = $cekipData->success; // Mengambil value dari key 'success'
		//dd($cekip);
		 //dd([
         //    'payload_received' => $payload,
         //    'signature_received' => $signature,
         //    'secret' => config('services.forumium.url'),
         //]);     
        if ($cekip == 'Absen Normal') {
            return redirect(
                config('services.forumium.url')
                . '/sso/login?payload=' . urlencode($payload)
                . '&signature=' . $signature
            );
        }else{
            return redirect(
                config('services.forumium.url_luar')
                . '/sso/login?payload=' . urlencode($payload)
                . '&signature=' . $signature
            );
        }

    }

    public function logout()
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        $payload = base64_encode(json_encode([
            'time' => now()->timestamp,
        ]));

        $signature = hash_hmac(
            'sha256',
            $payload,
            config('services.forumium.secret')
        );

        return redirect('login');
    }

    public function logoutFromForumium(Request $request)
    {
        $payload   = urldecode($request->query('payload'));
        $signature = $request->query('signature');

        if (!$payload || !$signature) {
            abort(403);
        }

        // Validasi signature
        $expected = hash_hmac(
            'sha256',
            $payload,
            config('services.forumium.secret')
        );

        if (!hash_equals($expected, $signature)) {
            abort(403);
        }

        // Decode payload
        $data = json_decode(base64_decode($payload), true);

        // Anti replay
        if (now()->timestamp - $data['time'] > 60) {
            abort(403, 'SSO token expired');
        }

        // Logout Laravel utama
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        // 🔴 INI YANG KAMU TANYAKAN
        // Redirect ke halaman login Laravel utama
        return redirect()->route('login');
    }
}
