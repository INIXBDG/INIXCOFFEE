<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AbsensiKaryawan;
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
        $today = $now->format('Y-m-d');
        $todayDate = $now->day;

        $shiftSudahAda = KontrolTugas::where('id_karyawan', $karyawanId)
            ->whereDate('Deadline_Date', $today)
            ->whereHas('kategoriDaftarTugas', function ($q) {
                $q->where('Tipe', 'Harian')->whereIn('tipe_turunan', ['Shift 1', 'Shift 2']);
            })
            ->with('kategoriDaftarTugas')
            ->first();

        if ($shiftSudahAda) {
            $targetShift = $shiftSudahAda->kategoriDaftarTugas->tipe_turunan;
        } else {
            $targetShift = $this->tentukanShiftHariIni($karyawanId, $today);
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
                // Filter berdasarkan shift
                if (empty($kat->tipe_turunan) || $kat->tipe_turunan === $targetShift) {
                    $shouldActivate = true;
                    $deadline = $today;
                }
            } elseif ($kat->Tipe === 'Bulanan') {
                $targetDate = $kat->tipe_turunan ? (int) $kat->tipe_turunan : 1;
                if ($todayDate == $targetDate) {
                    $shouldActivate = true;
                    $deadline = $now->copy()->setDay($targetDate)->format('Y-m-d');
                }
            } elseif ($kat->Tipe === 'Mingguan') {
                $hariMap = ['Saturday' => 'Sabtu', 'Sunday' => 'Minggu'];
                $hariIni = $now->dayName;
                $shiftHariIni = $hariMap[$hariIni] ?? null;

                if (empty($kat->tipe_turunan) || $kat->tipe_turunan === $shiftHariIni) {
                    $shouldActivate = true;
                    $deadline = $this->hitungDeadlineMingguan($kat->tipe_turunan);
                }
            }

            if ($shouldActivate) {
                $exists = KontrolTugas::where('id_karyawan', $karyawanId)
                    ->where('id_DaftarTugas', $kat->id)
                    ->whereDate('Deadline_Date', $deadline)
                    ->exists();

                if (!$exists) {
                    $nextUrutan = (KontrolTugas::max('urutan') ?? 0) + 1;

                    KontrolTugas::create([
                        'id_karyawan' => $karyawanId,
                        'id_DaftarTugas' => $kat->id,
                        'status' => 0,
                        'Deadline_Date' => $deadline,
                        'urutan' => $nextUrutan,
                    ]);
                }
            }
        }
    }

    private function tentukanShiftHariIni($karyawanId, $today)
    {
        $absensiHariIni = AbsensiKaryawan::where('id_karyawan', $karyawanId)
            ->where('tanggal', $today)
            ->first();

        if ($absensiHariIni && !empty($absensiHariIni->shift)) {
            return $absensiHariIni->shift == 1 ? 'Shift 1' : 'Shift 2';
        }

        $tugasAktif = KontrolTugas::where('id_karyawan', $karyawanId)
            ->whereDate('Deadline_Date', $today)
            ->whereHas('kategoriDaftarTugas', function ($q) {
                $q->where('Tipe', 'Harian')->whereIn('tipe_turunan', ['Shift 1', 'Shift 2']);
            })
            ->with('kategoriDaftarTugas')
            ->first();

        if ($tugasAktif) {
            return $tugasAktif->kategoriDaftarTugas->tipe_turunan;
        }

        $absensiKemarin = AbsensiKaryawan::where('id_karyawan', $karyawanId)
            ->where('tanggal', Carbon::yesterday('Asia/Jakarta')->toDateString())
            ->first();

        if ($absensiKemarin && !empty($absensiKemarin->shift)) {
            return $absensiKemarin->shift == 1 ? 'Shift 1' : 'Shift 2';
        }

        return 'Shift 1';
    }

    private function hitungDeadline($tipe, $tipe_turunan = null)
    {
        $now = Carbon::now();

        return match ($tipe) {
            'Harian' => $now->format('Y-m-d'),
            'Mingguan' => $this->hitungDeadlineMingguan($tipe_turunan),
            'Bulanan' => $this->hitungDeadlineBulanan($tipe_turunan),
            'Quartal' => $now->copy()->addMonths(3)->endOfMonth()->format('Y-m-d'),
            'Semester' => $now->copy()->addMonths(6)->endOfMonth()->format('Y-m-d'),
            'Tahunan' => $now->copy()->endOfYear()->format('Y-m-d'),
            default => $now->format('Y-m-d'),
        };
    }

    private function hitungDeadlineMingguan($shift = null)
    {
        $now = Carbon::now();

        if ($shift === 'Sabtu') {
            return $now->copy()->next(Carbon::SATURDAY)->format('Y-m-d');
        }

        if ($shift === 'Minggu') {
            return $now->copy()->next(Carbon::SUNDAY)->format('Y-m-d');
        }

        return $now->copy()->endOfWeek()->format('Y-m-d');
    }

    private function hitungDeadlineBulanan($tanggal = null)
    {
        $now = Carbon::now();
        $targetDate = $tanggal ? (int) $tanggal : 1;

        if ($now->day > $targetDate) {
            return $now->copy()->addMonth()->setDay($targetDate)->format('Y-m-d');
        }

        return $now->copy()->setDay($targetDate)->format('Y-m-d');
    }
}