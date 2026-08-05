<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\Karyawan;
use App\Models\KontrolTugas;
use App\Models\KategoriDaftarTugas;
use App\Models\AbsensiKaryawan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ShiftConfirmationNotification;
use Illuminate\Support\Facades\Cache;
use App\Traits\ShiftGenerator;

class LoginController extends Controller
{
    use AuthenticatesUsers, ShiftGenerator;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username()
    {
        return 'username';
    }

    protected function authenticated(Request $request, $user)
    {
        $karyawan = $user->karyawan ?? Karyawan::where('user_id', $user->id)->first();

        if ($karyawan && $karyawan->jabatan === 'Office Boy') {
            return $this->handleShiftAssignment($karyawan);
        }

        return redirect($this->redirectTo);
    }

    private function handleShiftAssignment($karyawan)
    {
        $now = Carbon::now('Asia/Jakarta');
        $hour = $now->hour;
        $today = $now->toDateString();

        $shift1Task = KontrolTugas::whereDate('Deadline_Date', $today)
            ->whereHas('KategoriDaftarTugas', fn($q) => $q->where('tipe_turunan', 'Shift 1'))
            ->first();
            
        $shift2Task = KontrolTugas::whereDate('Deadline_Date', $today)
            ->whereHas('KategoriDaftarTugas', fn($q) => $q->where('tipe_turunan', 'Shift 2'))
            ->first();

        $shift1UserId = $shift1Task ? $shift1Task->id_karyawan : null;
        $shift2UserId = $shift2Task ? $shift2Task->id_karyawan : null;

        // ATURAN 1: Shift 1 Kosong
        if (!$shift1UserId) {
            if ($hour >= 4) {
                $this->generateTasksForShift($karyawan->id, 'Shift 1', $today);
                return redirect($this->redirectTo)->with('success', 'Anda otomatis mengambil Shift 1.');
            } else {
                // Simpan ke Cache selama 12 jam (Tanpa Migration!)
                Cache::put("pending_shift_{$karyawan->id}", [
                    'shift' => 1, 
                    'date' => $today,
                    'message' => 'Anda login sebelum jam 4 pagi. Setujui untuk mengambil Shift 1.'
                ], now()->addHours(12));
                
                return redirect($this->redirectTo)->with('info', 'Mohon konfirmasi pengambilan Shift 1 Anda.');
            }
        }

        // ATURAN 2: Shift 1 sudah diambil orang lain, tawarkan Shift 2
        if ($shift1UserId && $shift1UserId !== $karyawan->id && !$shift2UserId) {
            if ($hour >= 16) {
                $this->generateTasksForShift($karyawan->id, 'Shift 2', $today);
                return redirect($this->redirectTo)->with('success', 'Anda otomatis mengambil Shift 2.');
            } else {
                // Simpan ke Cache
                Cache::put("pending_shift_{$karyawan->id}", [
                    'shift' => 2, 
                    'date' => $today,
                    'message' => 'Shift 1 sudah diambil. Setujui untuk mengambil Shift 2.'
                ], now()->addHours(12));
                
                return redirect($this->redirectTo)->with('info', 'Mohon konfirmasi pengambilan Shift 2 Anda.');
            }
        }

        return redirect($this->redirectTo);
    }

    private function sendShiftConfirmation($karyawan, $shiftNumber, $date)
    {
        Notification::send($karyawan, new ShiftConfirmationNotification($shiftNumber, $date));
    }
}