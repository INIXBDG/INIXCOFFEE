<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tickets;
use App\Models\karyawan;
use Illuminate\Support\Facades\Auth;

class CheckPendingSurvey
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $karyawan = karyawan::find($user->karyawan_id);

            if ($karyawan) {
                // Cari tiket yang sudah selesai tapi belum disurvei berdasarkan nama karyawan
                $pendingTicket = Tickets::where('nama_karyawan', $karyawan->nama_lengkap)
                    ->where('status', 'Selesai')
                    ->where('is_surveyed', false)
                    ->first();

                // Jika ada tiket pending dan pengguna tidak sedang berada di rute survei
                if ($pendingTicket && !$request->routeIs('survey.*')) {
                    return redirect()->route('surveykepuasan.index', ['ticket_id' => $pendingTicket->ticket_id])
                            ->with('warning', 'Anda wajib mengisi survey untuk tiket ' . $pendingTicket->ticket_id . ' yang telah selesai.');
                }
            }
        }

        return $next($request);
    }
}