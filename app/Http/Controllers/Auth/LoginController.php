<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\Karyawan;
use Carbon\Carbon;
use App\Models\KategoriDaftarTugas;
use App\Models\KontrolTugas;

class LoginController extends Controller
{
    use AuthenticatesUsers;

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
            $this->autoActivateTasks($karyawan->id);
        }

        return null;
    }

    private function autoActivateTasks($karyawanId)
    {
        $now = Carbon::now();
        $today = $now->toDateString();

        $shift1TakenByOther = KontrolTugas::whereDate('Deadline_Date', $today)
            ->where('id_karyawan', '!=', $karyawanId)
            ->whereHas('kategoriDaftarTugas', function ($q) {
                $q->where('Tipe', 'Harian')->where('tipe_turunan', 'Shift 1');
            })
            ->exists();

        $userAlreadyHasShift2 = KontrolTugas::where('id_karyawan', $karyawanId)
            ->whereDate('Deadline_Date', $today)
            ->whereHas('kategoriDaftarTugas', function ($q) {
                $q->where('Tipe', 'Harian')->where('tipe_turunan', 'Shift 2');
            })
            ->exists();

        $targetShift = null;
        $skipHarian = false;

        if ($userAlreadyHasShift2) {
            $skipHarian = true;
        } elseif ($shift1TakenByOther) {
            $targetShift = 'Shift 2';
        } else {
            $targetShift = 'Shift 1';
        }

        $isEndOfWeek = $now->isSaturday();
        $isEndOfMonth = $now->day == $now->daysInMonth;

        if ($isEndOfMonth && $isEndOfWeek) {
            $tipeAktif = ['Bulanan', 'Mingguan'];
            $skipHarian = true;
        } elseif ($isEndOfMonth) {
            $tipeAktif = ['Bulanan', 'Harian'];
        } elseif ($isEndOfWeek) {
            $tipeAktif = ['Mingguan', 'Harian'];
        } else {
            $tipeAktif = ['Harian'];
        }

        $kategoriQuery = KategoriDaftarTugas::whereIn('Tipe', $tipeAktif);

        if (!$skipHarian && in_array('Harian', $tipeAktif)) {
            $kategoriQuery->where(function ($q) use ($targetShift) {
                $q->where('Tipe', '!=', 'Harian')->orWhere(function ($sub) use ($targetShift) {
                    if ($targetShift === 'Shift 1') {
                        $sub->whereNull('tipe_turunan')->orWhere('tipe_turunan', 'Shift 1');
                    } elseif ($targetShift === 'Shift 2') {
                        $sub->whereNull('tipe_turunan')->orWhere('tipe_turunan', 'Shift 2');
                    } else {
                        $sub->whereNull('tipe_turunan');
                    }
                });
            });
        } elseif (in_array('Harian', $tipeAktif)) {
            $kategoriQuery->where('Tipe', '!=', 'Harian');
        }

        $kategori = $kategoriQuery->get();

        foreach ($kategori as $kat) {
            $deadline = $this->hitungDeadline($kat->Tipe);

            $query = KontrolTugas::where('id_karyawan', $karyawanId)->where('id_DaftarTugas', $kat->id);

            if ($kat->Tipe === 'Harian') {
                $query->whereDate('Deadline_Date', $today);
            } else {
                $query->where('status', 0)->whereDate('Deadline_Date', $deadline);
            }

            if (!$query->exists()) {
                KontrolTugas::create([
                    'id_karyawan' => $karyawanId,
                    'id_DaftarTugas' => $kat->id,
                    'status' => 0,
                    'Deadline_Date' => $deadline,
                ]);
            }
        }
    }

    private function hitungDeadline($tipe)
    {
        return match ($tipe) {
            'Harian' => now()->toDateString(),
            'Mingguan' => now()->endOfWeek()->toDateString(),
            'Bulanan' => now()->endOfMonth()->toDateString(),
            'Quartal' => now()->addMonths(3)->endOfMonth()->toDateString(),
            'Semester' => now()->addMonths(6)->endOfMonth()->toDateString(),
            'Tahunan' => now()->endOfYear()->toDateString(),
            default => now()->toDateString(),
        };
    }
}
