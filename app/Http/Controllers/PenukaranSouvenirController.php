<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RKM;
use App\Models\souvenir;
use App\Models\Registrasi;
use App\Models\souvenirpeserta; // Pastikan huruf kecil/besar sesuai nama file Model Anda
use App\Models\PenukaranSouvenir;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class PenukaranSouvenirController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user || !$user->karyawan || !$user->karyawan->jabatan) {
            return redirect()->route('login');
        }

        $jabatan = $user->karyawan->jabatan;

        if ($jabatan !== 'Customer Care' && $jabatan !== 'GM') {
             return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        return view('penukaransouvenir.index');
    }

    public function getRiwayat($month, $year)
    {
        $userKaryawan = auth()->user()->karyawan;
        if (!$userKaryawan) {
            return response()->json(['data' => []], 401);
        }

        $jabatan = $userKaryawan->jabatan;

        if ($jabatan === 'Customer Care' || $jabatan === 'GM') {
        } else {
            return response()->json(['data' => []]);
        }

        $query = PenukaranSouvenir::with([
                    'rkm.materi',
                    'regist.peserta.perusahaan',
                    'souvenirOld',
                    'souvenirNew'
                ])
                ->whereMonth('tanggal_tukar', $month)
                ->whereYear('tanggal_tukar', $year);

        $data = $query->latest('tanggal_tukar')->get();

        return response()->json([
            'success' => true,
            'message' => 'List Riwayat Penukaran',
            'data' => $data,
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $karyawan = $user->karyawan;

        // Tentukan Rentang Waktu
        // startOfDay() dan endOfDay() memastikan jam 00:00:00 s/d 23:59:59 tercover
        $tanggalMulai = now()->subWeeks(2)->startOfDay();
        $tanggalAkhir = now()->addWeeks(2)->endOfDay();

        $rkms = RKM::with('materi')
                    ->where('metode_kelas', 'Offline')
                    ->where('event', 'Kelas')
                    ->whereHas('souvenirpeserta') // Hanya RKM yang sudah ada pembagian souvenir
                    ->whereBetween('tanggal_awal', [$tanggalMulai, $tanggalAkhir]) // Filter Tanggal
                    ->orderBy('tanggal_awal', 'desc')
                    ->get();

        $souvenirs = souvenir::where('stok', '>', 0)->get();

        return view('penukaransouvenir.create', compact('rkms', 'souvenirs', 'karyawan'));
    }

    public function getPesertaByRKM($rkmId)
    {
        try {
            $data = Registrasi::with(['peserta.perusahaan', 'souvenirpeserta.souvenir'])
                    ->where('id_rkm', $rkmId)
                    ->whereHas('souvenirpeserta') // Pastikan relasi ada
                    ->get()
                    ->map(function ($regist) {
                        // Ambil objek relasi ke variabel dulu untuk pengecekan
                        $sp = $regist->souvenirpeserta;
                        $souvenir = $sp ? $sp->souvenir : null;

                        return [
                            'id_regist' => $regist->id,
                            'nama_peserta' => $regist->peserta->nama ?? 'Tanpa Nama',
                            'instansi' => $regist->peserta->perusahaan->nama_perusahaan ?? '-',

                            // Gunakan Null Coalescing (??) atau Optional Chaining (?->)
                            'id_souvenir_lama' => $sp->id_souvenir ?? null,
                            'nama_souvenir_lama' => $souvenir->nama_souvenir ?? 'Data Souvenir Hilang',
                        ];
                    });

            return response()->json($data);

        } catch (\Exception $e) {
            // Ini akan mengirim pesan error asli ke Console Browser agar mudah didebug
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        // ... (Validasi request tetap sama) ...
        $request->validate([
            'id_rkm' => 'required|exists:r_k_m_s,id',
            'id_regist' => 'required|exists:registrasis,id',
            'id_souvenir_baru' => 'required|exists:souvenirs,id',
        ]);

        DB::beginTransaction();
        try {
            // ... (Logika bisnis penukaran stok tetap sama) ...

            // 1. Lock & Get Data
            $souvenirPeserta = souvenirpeserta::with(['regist.peserta', 'souvenir'])
                                ->where('id_regist', $request->id_regist)
                                ->where('id_rkm', $request->id_rkm)
                                ->lockForUpdate()
                                ->firstOrFail();

            $idSouvenirLama = $souvenirPeserta->id_souvenir;
            $namaSouvenirLama = $souvenirPeserta->souvenir->nama_souvenir ?? 'Tidak Diketahui';
            $idSouvenirBaru = $request->id_souvenir_baru;

            if ($idSouvenirLama == $idSouvenirBaru) {
                return redirect()->back()->with('error', 'Souvenir pengganti sama dengan souvenir saat ini.');
            }

            // 2. Update Stok Lama
            $souvenirLamaStore = souvenir::lockForUpdate()->find($idSouvenirLama);
            if ($souvenirLamaStore) $souvenirLamaStore->increment('stok', 1);

            // 3. Update Stok Baru
            $souvenirBaruStore = souvenir::lockForUpdate()->find($idSouvenirBaru);
            if (!$souvenirBaruStore || $souvenirBaruStore->stok < 1) {
                throw new \Exception("Stok souvenir pengganti habis.");
            }
            $souvenirBaruStore->decrement('stok', 1);

            $souvenirPeserta->update([
                'id_souvenir' => $idSouvenirBaru,
                'updated_at' => now()
            ]);

            PenukaranSouvenir::create([
                'id_rkm' => $request->id_rkm,
                'id_regist' => $request->id_regist,
                'id_souvenir_lama' => $idSouvenirLama,
                'id_souvenir_baru' => $idSouvenirBaru,
                'tanggal_tukar' => now(),
            ]);

            $namaPeserta = $souvenirPeserta->regist->peserta->nama ?? 'Peserta';
            $dataNotif = [
                'nama_peserta'  => $namaPeserta,
                'souvenir_lama' => $namaSouvenirLama,
                'souvenir_baru' => $souvenirBaruStore->nama_souvenir,
                'tanggal_tukar' => now()->format('Y-m-d H:i:s'),
            ];

            $path = route('penukaransouvenir.index');
            $tipe = 'Penukaran Souvenir';

            // B. Kirim ke GM (Iterasi agar receiverId masuk ke constructor)
            $usersGM = User::whereHas('karyawan', function($q) {
                $q->where('jabatan', 'GM');
            })->get();

            foreach ($usersGM as $gm) {
                $gm->notify(new \App\Notifications\PenukaranSouvenirNotification(
                    $dataNotif,
                    $path,
                    $tipe,
                    $gm->id // Receiver ID untuk channel
                ));
            }

            // C. Kirim ke User Penginput (Diri Sendiri)
            $userSelf = auth()->user();
            if ($userSelf) {
                $userSelf->notify(new \App\Notifications\PenukaranSouvenirNotification(
                    $dataNotif,
                    $path,
                    $tipe,
                    $userSelf->id
                ));
            }
            DB::commit();

            return redirect()->route('penukaransouvenir.index')
                ->with('success', 'Berhasil menukar souvenir. Notifikasi terkirim.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
}
