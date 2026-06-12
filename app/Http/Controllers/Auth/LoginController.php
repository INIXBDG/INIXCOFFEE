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
        $todayDate = $now->day;

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
        $pagiMulai = Carbon::today()->setTime(4, 0, 0);
        $pagiSelesai = Carbon::today()->setTime(8, 0, 0);
        $soreMulai = Carbon::today()->setTime(15, 0, 0);
        $soreSelesai = Carbon::today()->setTime(19, 0, 0);

        if (!$now->between($pagiMulai, $pagiSelesai) && !$now->between($soreMulai, $soreSelesai)) {
            $targetShift = null;
        } elseif ($userAlreadyHasShift2) {
            $targetShift = 'Shift 2';
        } elseif ($shift1TakenByOther) {
            $targetShift = 'Shift 2';
        } else {
            if ($now->between($pagiMulai, $pagiSelesai)) {
                $targetShift = 'Shift 1';
            } else {
                $targetShift = 'Shift 2';
            }
        }

        $isEndOfWeek = $now->isSaturday() || $now->isSunday();
        $isEndOfMonth = $now->day == $now->daysInMonth;

        $tipeAktif = [];
        if ($isEndOfMonth && $isEndOfWeek) {
            $tipeAktif = ['Bulanan', 'Mingguan', 'Harian'];
        } elseif ($isEndOfMonth) {
            $tipeAktif = ['Bulanan', 'Harian'];
        } elseif ($isEndOfWeek) {
            $tipeAktif = ['Mingguan', 'Harian'];
        } else {
            $tipeAktif = ['Harian'];
        }

        $kategori = KategoriDaftarTugas::whereIn('Tipe', $tipeAktif)->get();

        foreach ($kategori as $kat) {
            $shouldActivate = false;
            $deadline = null;

            if ($kat->Tipe === 'Harian') {
                if (empty($kat->tipe_turunan) || $kat->tipe_turunan === $targetShift) {
                    $shouldActivate = true;
                    $deadline = $today;
                }
            } elseif ($kat->Tipe === 'Bulanan') {
                $targetDate = $kat->tipe_turunan ? (int) $kat->tipe_turunan : 1;
                if ($todayDate == $targetDate) {
                    $shouldActivate = true;
                    $deadline = $now->copy()->setDay($targetDate)->toDateString();
                }
            } elseif ($kat->Tipe === 'Mingguan') {
                $hariMap = ['Saturday' => 'Sabtu', 'Sabtu' => 'Sabtu', 'Minggu' => 'Minggu', 'Sunday' => 'Minggu'];
                $hariIni = $now->dayName;
                $shiftHariIni = $hariMap[$hariIni] ?? null;

                if (empty($kat->tipe_turunan) || $kat->tipe_turunan === $shiftHariIni) {
                    $shouldActivate = true;
                    $deadline = $this->hitungDeadlineMingguan($kat->tipe_turunan);
                }
            }

            if ($shouldActivate) {
                $exists = KontrolTugas::where('id_karyawan', $karyawanId)->where('id_DaftarTugas', $kat->id)->whereDate('Deadline_Date', $deadline)->exists();

                if (!$exists) {
                    KontrolTugas::create([
                        'id_karyawan' => $karyawanId,
                        'id_DaftarTugas' => $kat->id,
                        'status' => 0,
                        'Deadline_Date' => $deadline,
                    ]);
                }
            }
        }
    }

    private function hitungDeadline($tipe, $tipe_turunan = null)
    {
        $now = now();

        return match ($tipe) {
            'Harian' => $now->toDateString(),

            'Mingguan' => $this->hitungDeadlineMingguan($tipe_turunan),

            'Bulanan' => $this->hitungDeadlineBulanan($tipe_turunan),

            'Quartal' => $now->addMonths(3)->endOfMonth()->toDateString(),
            'Semester' => $now->addMonths(6)->endOfMonth()->toDateString(),
            'Tahunan' => $now->endOfYear()->toDateString(),
            default => $now->toDateString(),
        };
    }

    private function hitungDeadlineMingguan($shift = null)
    {
        $now = now();

        if ($shift === 'Sabtu') {
            return $now->copy()->next(Carbon::SATURDAY)->toDateString();
        }

        if ($shift === 'Minggu') {
            return $now->copy()->next(Carbon::SUNDAY)->toDateString();
        }

        return $now->copy()->endOfWeek()->toDateString();
    }

    private function hitungDeadlineBulanan($tanggal = null)
    {
        $now = now();
        $targetDate = $tanggal ? (int) $tanggal : 1;

        if ($now->day > $targetDate) {
            return $now->copy()->addMonth()->setDay($targetDate)->toDateString();
        }

        return $now->copy()->setDay($targetDate)->toDateString();
    }
}
