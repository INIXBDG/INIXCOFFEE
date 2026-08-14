<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\karyawan;
use App\Models\KontrolTugas;
use App\Models\KategoriDaftarTugas;
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
        $karyawan = $user->karyawan ?? Karyawan::where('id', $user->id)->first();

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

        $shiftTasks = KontrolTugas::with('KategoriDaftarTugas')
            ->where('Deadline_Date', $today)
            ->whereHas('KategoriDaftarTugas', function($q) {
                $q->whereIn('tipe_turunan', ['Shift 1', 'Shift 2']);
            })
            ->get();

        $shift1UserId = $shiftTasks->firstWhere('KategoriDaftarTugas.tipe_turunan', 'Shift 1')->id_karyawan ?? null;
        $shift2UserId = $shiftTasks->firstWhere('KategoriDaftarTugas.tipe_turunan', 'Shift 2')->id_karyawan ?? null;

        if (!$shift1UserId) {
            if ($hour >= 4) {
                $this->generateTasksForShift($karyawan->id, 'Shift 1', $today);
                return redirect($this->redirectTo)->with('success', 'Anda otomatis mengambil Shift 1.');
            }

            Cache::put("pending_shift_{$karyawan->id}", [
                'shift' => 1,
                'date' => $today,
                'message' => 'Anda login sebelum jam 4 pagi. Setujui untuk mengambil Shift 1.'
            ], now()->addHours(12));

            return redirect($this->redirectTo)->with('info', 'Mohon konfirmasi pengambilan Shift 1 Anda.');
        }

        if ($shift1UserId && $shift1UserId !== $karyawan->id && !$shift2UserId) {
            if ($hour >= 16) {
                $this->generateTasksForShift($karyawan->id, 'Shift 2', $today);
                return redirect($this->redirectTo)->with('success', 'Anda otomatis mengambil Shift 2.');
            } else {
                Cache::put("pending_shift_{$karyawan->id}", [
                    'shift' => 2, 
                    'date' => $today,
                    'message' => 'Shift 1 sudah diambil. Setujui untuk mengambil Shift 2.'
                ], now()->addHours(12));
                
                return redirect($this->redirectTo)->with('info', 'Mohon konfirmasi pengambilan Shift 2 Anda.');
            }

            Cache::put("pending_shift_{$karyawan->id}", [
                'shift' => 2,
                'date' => $today,
                'message' => 'Shift 1 sudah diambil. Setujui untuk mengambil Shift 2.'
            ], now()->addHours(12));

            return redirect($this->redirectTo)->with('info', 'Mohon konfirmasi pengambilan Shift 2 Anda.');
        }

        return redirect($this->redirectTo);
    }

    // private function sendShiftConfirmation($karyawan, $shiftNumber, $date)
    // {
    //     Notification::send($karyawan, new ShiftConfirmationNotification($shiftNumber, $date));
    // }
}
