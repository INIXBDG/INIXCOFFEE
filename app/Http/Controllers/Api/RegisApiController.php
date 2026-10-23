<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\karyawan;
use App\Models\User;
use App\Models\Perusahaan;
use App\Models\Peserta;
use App\Models\Registrasi;
use App\Models\RKM;
use App\Services\MoodleService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use App\Notifications\RegistrasiNotification;


class RegisApiController extends Controller
{
    public function kelasBerjalan()
    {
        $now = CarbonImmutable::now()->locale('id_ID');

        // Mendefinisikan batas waktu secara eksplisit: Awal minggu lalu hingga akhir minggu ini
        // $startOfWeek = $now->subWeek()->startOfWeek()->format('Y-m-d');
        // $startOfWeek = $now->addWeeks(1)->startOfWeek()->format('Y-m-d');
        // $endOfWeek = $now->addWeeks(1)->endOfWeek()->format('Y-m-d');
        $startOfWeek = $now->startOfWeek()->format('Y-m-d');
        $endOfWeek = $now->endOfWeek()->format('Y-m-d');

        // Mengambil data RKM menggunakan metode agregasi seperti fungsi showMonth
        $rows = RKM::with(['materi', 'peluang', 'perusahaan'])
                ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
                ->whereBetween('r_k_m_s.tanggal_awal', [$startOfWeek, $endOfWeek])
                ->whereNot('metode_kelas', 'Exam Only')
                ->where(function ($query) {
                    $query->whereDoesntHave('peluang')
                        ->orWhereHas('peluang', function ($q) {
                            $q->where('tahap', 'merah');
                        });
                })
                ->select(
                    DB::raw('GROUP_CONCAT(r_k_m_s.id SEPARATOR ", ") AS id'),
                    DB::raw('GROUP_CONCAT(r_k_m_s.id SEPARATOR ", ") AS id_all'),
                    'r_k_m_s.materi_key',
                    'materis.nama_materi',
                    'r_k_m_s.ruang',
                    'r_k_m_s.metode_kelas',
                    'r_k_m_s.event',
                    'r_k_m_s.tanggal_awal',
                    'r_k_m_s.tanggal_akhir'
                )
                ->groupBy(
                    'r_k_m_s.materi_key',
                    'materis.nama_materi',
                    'r_k_m_s.ruang',
                    'r_k_m_s.metode_kelas',
                    'r_k_m_s.event',
                    'r_k_m_s.tanggal_awal',
                    'r_k_m_s.tanggal_akhir'
                )
                ->orderBy('r_k_m_s.tanggal_awal', 'asc')
                ->get();

        // return $rows;

        $combinedData = [];

        foreach ($rows as $row) {
            // Memetakan relasi data instruktur, sales, dan perusahaan
            $sales_ids = explode(', ', $row->sales_all);
            $perusahaan_ids = explode(', ', $row->perusahaan_all);
            $instruktur_ids = explode(', ', $row->instruktur_all);

            $row->instruktur = karyawan::whereIn('kode_karyawan', $instruktur_ids)->get();
            $row->sales = karyawan::whereIn('kode_karyawan', $sales_ids)->get();
            $row->perusahaan = Perusahaan::whereIn('id', $perusahaan_ids)->get();

            // Pembuatan string id_baru

            // ganti 3 baris explode + id_baru dengan:
            $tgl    = Carbon::parse($row->tanggal_awal);
            $metode = strtolower(str_replace(' ', '', $row->metode_kelas));
            $id_baru = $row->materi_key . 'ib' . $tgl->format('d') . 'ie' . $tgl->format('Y') . 'ie' . $tgl->format('m') . 'im' . $metode; 

            // Penyusunan format akhir data
            $combinedData[] = [
                'id_baru'       => $id_baru,
                'id_all'       => $row->id_all,
                'materi_key'    => $row->materi_key,
                'nama_materi'   => $row->nama_materi,
                'tanggal_awal'  => $row->tanggal_awal,
                'tanggal_akhir' => $row->tanggal_akhir,
                'total_pax'     => $row->total_pax,
                'isi_pax'       => $row->total_isi_pax ?? 0,
                'metode_kelas'  => $row->metode_kelas,
                'ruang'         => $row->ruang,
                'items'         => [$row->toArray()], // Dibungkus array sesuai kebutuhan struktur
            ];
        }

        $data = $combinedData;
        return response()->json([
            'data' => $data
        ]);
    }

    public function index($id)
    {
        $array = explode('ib', $id);
        $materi_key = $array[0];

        if (!isset($array[1])) {
            return response()->json([
                'status' => 'error',
                'title'  => 'Mohon Maaf',
                'text'   => 'Format link tidak valid',
            ], 400);
        }

        $parts = explode('im', $array[1], 2);
        $tanggal_part = $parts[0];
        $metode = $parts[1] ?? null;

        $tanggal_rkm = explode('ie', $tanggal_part);

        if (count($tanggal_rkm) < 3) {
            return response()->json([
                'status' => 'error',
                'title'  => 'Mohon Maaf',
                'text'   => 'Format tanggal tidak valid',
            ], 400);
        }

        $hari  = str_pad($tanggal_rkm[0], 2, '0', STR_PAD_LEFT);
        $tahun = $tanggal_rkm[1];
        $bulan = str_pad($tanggal_rkm[2], 2, '0', STR_PAD_LEFT);

        $tanggal_awal = Carbon::createFromFormat('Y-m-d', $tahun.'-'.$bulan.'-'.$hari);
        $tanggal_akhir = $tanggal_awal->copy()->addDays(2);
        $tanggal_awal_str = $tanggal_awal->format('Y-m-d');
        $tanggal_akhir_str = $tanggal_akhir->format('Y-m-d');

        // ✅ QUERY LENGKAP DIPASANG KEMBALI DI SINI
        $query = RKM::with(['materi', 'peluang', 'perusahaan'])
            ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
            ->leftJoin('perusahaans', 'r_k_m_s.perusahaan_key', '=', 'perusahaans.id')
            ->whereBetween('r_k_m_s.tanggal_awal', [$tanggal_awal_str, $tanggal_akhir_str])
            ->whereNot('metode_kelas', 'Exam Only')
            ->where('materis.id', $materi_key)
            ->select(
                DB::raw('GROUP_CONCAT(r_k_m_s.id SEPARATOR ", ") AS id'),
                DB::raw('GROUP_CONCAT(r_k_m_s.id SEPARATOR ", ") AS id_all'),
                DB::raw('GROUP_CONCAT(CONCAT(r_k_m_s.id, "|", IFNULL(r_k_m_s.perusahaan_key, ""), "|", IFNULL(perusahaans.nama_perusahaan, "")) SEPARATOR "||") AS pasangan'),
                'r_k_m_s.materi_key',
                'materis.nama_materi',
                'r_k_m_s.ruang',
                'r_k_m_s.metode_kelas',
                'r_k_m_s.event',
                'r_k_m_s.tanggal_awal'
            )
            ->groupBy(
                'r_k_m_s.materi_key',
                'materis.nama_materi',
                'r_k_m_s.ruang',
                'r_k_m_s.metode_kelas',
                'r_k_m_s.event',
                'r_k_m_s.tanggal_awal'
            );

        if ($metode) {
            $query->whereRaw('LOWER(REPLACE(r_k_m_s.metode_kelas, " ", "")) = ?', [$metode]);
        }

        $data = $query->orderBy('r_k_m_s.tanggal_awal', 'asc')->first();

        if (!$data) {
            return response()->json([
                'status' => 'error',
                'title'  => 'Mohon Maaf',
                'text'   => 'Kelas tidak ditemukan',
            ], 404);
        }

        $checkrkm = collect(explode('||', $data->pasangan ?? ''))
            ->filter() 
            ->map(function ($item) {
                $parts = explode('|', $item);

                return [
                    'id_rkm'          => $parts[0] ?? null,
                    'perusahaan_id'   => $parts[1] ?? null, 
                    'nama_perusahaan' => $parts[2] ?? null, 
                ];
            })
            ->values();

        $maxId = Peserta::max('id');
        $countPeserta = $maxId ? $maxId + 1 : 1;

        return response()->json([
            'status' => 'success',
            'data'   => [
                'kelas'         => $data,
                'count_peserta' => $countPeserta,
                'instruktur_key'=> $data->instruktur_key,
                'check_rkm'     => $checkrkm,
            ],
        ], 200);
    }

    public function cekPeserta(Request $request)
    {
        $email = $request->input('email');

        $peserta = Peserta::with('perusahaan')
            ->where('email', $email)
            ->first();

        return response()->json([
            'peserta' => $peserta,
            'perusahaan_key' => $peserta?->perusahaan_key,
        ]);
    }

    public function cekRKM(Request $request)
    {
        $startDate = Carbon::now()->startOfWeek()->toDateString();
        $endDate = Carbon::now()->endOfWeek()->toDateString();
        $id = $request->input('id');
        $peserta = Registrasi::with('peserta', 'rkm', 'materi')
        ->where('id_peserta', $id)
        ->whereHas('rkm', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('tanggal_awal', [$startDate, $endDate]);
            $query->where('status', '0');
            $query->whereNull('deleted_at');
        })
        ->get();

        return response()->json(['rkm' => $peserta]);
    }

    public function store(Request $request, $id)
    {
        Log::info('Store registration started', [
            'request_data' => $request->all(),
            'id' => $id,
        ]);

        // Jika Moodle sedang dimatikan, set ke null (jangan diisi string 'hmm')
        $moodle = app(MoodleService::class);
        // $moodle = null;

        $email = $request->email;
        Log::info('Searching participant by email', ['email' => $email]);

        $peserta = Peserta::with('perusahaan')
            ->where('email', $email)
            ->first();

        Log::info('Participant search result', [
            'found' => $peserta ? true : false,
            'participant' => $peserta ? ['id' => $peserta->id, 'email' => $peserta->email] : null,
        ]);

        $perusahaan_key = $request->perusahaan_key ?? ($peserta->perusahaan_key ?? null);

        Log::info('Company key determined', ['company_key' => $perusahaan_key]);

        // ✅ 1. Ambil RKM dari id_rkm jika ada
        $rkm = null;
        if ($request->filled('id_rkm')) {
            $rkm = RKM::with('perusahaan', 'materi', 'sales', 'instruktur')
                ->find($request->id_rkm);
        }

        // ✅ 2. Fallback jika id_rkm kosong, cari berdasarkan kode $id URL + perusahaan_key
        if (!$rkm && $perusahaan_key) {
            $array = explode('ib', $id);
            $materi_key = $array[0] ?? null;

            if (isset($array[1])) {
                $parts = explode('im', $array[1], 2);
                $tanggal_part = $parts[0];
                $metode = $parts[1] ?? null;
                $tanggal_rkm = explode('ie', $tanggal_part);

                if (count($tanggal_rkm) >= 3) {
                    $hari  = str_pad($tanggal_rkm[0], 2, '0', STR_PAD_LEFT);
                    $tahun = $tanggal_rkm[1];
                    $bulan = str_pad($tanggal_rkm[2], 2, '0', STR_PAD_LEFT);

                    $tanggal_awal = Carbon::createFromFormat('Y-m-d', "$tahun-$bulan-$hari");
                    $tanggal_awal_str = $tanggal_awal->format('Y-m-d');
                    $tanggal_akhir_str = $tanggal_awal->copy()->addDays(2)->format('Y-m-d');

                    $rkm = RKM::with('perusahaan', 'materi', 'sales', 'instruktur')
                        ->where('materi_key', $materi_key)
                        ->where('perusahaan_key', $perusahaan_key)
                        ->whereBetween('tanggal_awal', [$tanggal_awal_str, $tanggal_akhir_str])
                        ->when($metode, function ($q) use ($metode) {
                            $q->whereRaw('LOWER(REPLACE(metode_kelas, " ", "")) = ?', [$metode]);
                        })
                        ->first();
                }
            }
        }

        // ✅ Pastikan RKM ditemukan sebelum lanjut
        if (!$rkm) {
            Log::error('RKM tidak ditemukan saat store registrasi', [
                'id_rkm_request' => $request->id_rkm,
                'perusahaan_key' => $perusahaan_key,
                'id_url'         => $id,
            ]);

            return response()->json([
                'status' => 'error',
                'title'  => 'Mohon Maaf',
                'text'   => 'Data kelas (RKM) tidak ditemukan.',
            ], 404);
        }

        Log::info('RKM found', [
            'rkm_id' => $rkm->id,
            'isi_pax' => $rkm->isi_pax,
            'materi_key' => $rkm->materi_key,
        ]);

        // ✅ Cek kapasitas
        if ($rkm->isi_pax <= 0) {
            Log::warning('Capacity full', ['rkm_id' => $rkm->id, 'current_pax' => $rkm->isi_pax]);

            return response()->json([
                'status' => 'error',
                'title' => 'Mohon Maaf',
                'text' => 'Kapasitas peserta sudah penuh',
            ], 500);
        }

        // ===============================
        // ✅ JIKA PESERTA BARU
        // ===============================
        if (!$peserta) {
            Log::info('Creating new participant', ['email' => $request->email]);

            $peserta = Peserta::create([
                'nama' => $request->nama,
                'jenis_kelamin' => $request->jenis_kelamin,
                'email' => $request->email,
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
                'perusahaan_key' => $perusahaan_key,
                'tanggal_lahir' => $request->tanggal_lahir,
                'checkregist' => $request->checkregist,
            ]);

            Log::info('New participant created', [
                'participant_id' => $peserta->id,
                'email' => $peserta->email,
                'name' => $peserta->nama,
            ]);

            // Sync ke Moodle (Hanya jalan jika $moodle tidak null)
            if ($moodle) {
                try {
                    Log::info('Starting Moodle sync for new participant', [
                        'participant_id' => $peserta->id,
                        'email' => $peserta->email,
                    ]);

                    $moodleUserId = $moodle->syncUser($peserta);

                    if ($moodleUserId) {
                        $peserta->update(['moodle_user_id' => $moodleUserId]);
                        Log::info('Local participant record updated with moodle_user_id', [
                            'participant_id' => $peserta->id,
                            'moodle_user_id' => $moodleUserId,
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error('Moodle sync error for new participant: '.$e->getMessage(), [
                        'participant_id' => $peserta->id,
                        'email' => $peserta->email,
                    ]);
                }
            }
        }
        // ===============================
        // ✅ JIKA PESERTA SUDAH ADA
        // ===============================
        else {
            Log::info('Existing participant found', [
                'participant_id' => $peserta->id,
                'email' => $peserta->email,
                'has_moodle_id' => $peserta->moodle_user_id ? true : false,
            ]);

            $peserta->update([
                'perusahaan_key' => $perusahaan_key,
            ]);

            // Lazy sync (Hanya jalan jika $moodle tidak null dan belum ada moodle_user_id)
            if ($moodle && !$peserta->moodle_user_id) {
                try {
                    $moodleUserId = $moodle->syncUser($peserta);

                    if ($moodleUserId) {
                        $peserta->update(['moodle_user_id' => $moodleUserId]);
                    }
                } catch (\Throwable $e) {
                    Log::error('Moodle lazy sync error: '.$e->getMessage(), [
                        'participant_id' => $peserta->id,
                        'email' => $peserta->email,
                    ]);
                }
            }

            // Cek sudah terdaftar
            $registrasi = Registrasi::where('id_peserta', $peserta->id)
                ->where('id_rkm', $rkm->id)
                ->first();

            if ($registrasi) {
                Log::warning('Participant already registered for this class', [
                    'participant_id' => $peserta->id,
                    'rkm_id' => $rkm->id,
                    'registration_id' => $registrasi->id,
                ]);

                return response()->json([
                    'status' => 'error',
                    'title' => 'Mohon Maaf',
                    'text' => 'Anda sudah terdaftar di kelas ini',
                ], 500);
            }
        }

        // ===============================
        // ✅ CARI & ENROLL KE MOODLE COURSE
        // ===============================
        $courseId = null;
        if ($moodle) {
            try {
                $courseId = $moodle->findCourse(
                    $rkm->materi?->nama_materi,
                    $rkm->perusahaan?->nama_perusahaan
                );

                if ($peserta->moodle_user_id && $courseId !== null) {
                    $moodle->enroll($peserta->moodle_user_id, $courseId);
                }
            } catch (\Throwable $e) {
                Log::error('Moodle enrollment error: '.$e->getMessage(), [
                    'participant_id' => $peserta->id,
                ]);
            }
        }

        // ===============================
        // ✅ BUAT REGISTRASI
        // ===============================
        Log::info('Creating registration record', [
            'rkm_id' => $rkm->id,
            'participant_id' => $peserta->id,
            'materi_key' => $rkm->materi_key,
            'instruktur_key' => $rkm->instruktur_key,
            'sales_key' => $rkm->sales_key,
        ]);

        $registrasi = Registrasi::create([
            'id_rkm' => $rkm->id,
            'id_peserta' => $peserta->id,
            'id_materi' => $rkm->materi_key,
            'id_instruktur' => $rkm->instruktur_key,
            'id_sales' => $rkm->sales_key,
        ]);

        if ($registrasi) {
            $registrasi->update([
                'moodle_course_id' => $courseId,
            ]);
            $this->kirimNotifikasi($rkm, $peserta, $request->id_karyawan);
        }

        $peserta->update([
            'checkregist' => $request->checkregist,
        ]);

        $rkm->decrement('isi_pax');
        Log::info('RKM capacity decremented', [
            'rkm_id' => $rkm->id,
            'new_capacity' => $rkm->fresh()->isi_pax,
        ]);

        return response()->json([
            'status' => 'success',
            'title' => 'Selamat!',
            'text' => 'Anda telah berhasil mendaftar kelas ini',
        ], 200);
    }

    private function kirimNotifikasi($rkm, $peserta, $idKaryawan): void
    {
        $kodeKaryawan = array_filter(array_unique([
            $rkm->instruktur_key,
            $rkm->sales_key,
        ]));

        $users = User::whereHas('karyawan', fn ($q) => $q->whereIn('kode_karyawan', $kodeKaryawan)
        )->get();

        if ($users->isEmpty()) {
            Log::warning('Notifikasi registrasi peserta tidak dikirim: penerima tidak ditemukan', [
                'id_rkm' => $rkm->id,
                'id_peserta' => $peserta->id,
                'kode_karyawan' => $kodeKaryawan,
            ]);

            return;
        }

        $type = 'Info Registrasi Peserta';
        $data = [
            'id_karyawan' => $idKaryawan,
            'tipe' => $type,
            'kelas' => $rkm->materi?->nama_materi,
            'perusahaan' => $rkm->perusahaan?->nama_perusahaan,
            'periode' => $rkm->tanggal_awal.' s/d '.$rkm->tanggal_akhir,
            'instruktur' => $rkm->instruktur?->nama_lengkap,
            'sales' => $rkm->sales?->nama_lengkap,
            'nama_peserta' => $peserta->nama,
            'email_peserta' => $peserta->email,
        ];

        try {
            foreach ($users as $user) {
                NotificationFacade::send($user, new RegistrasiNotification($data, $type, $user->id));
            }

            Log::info('Notifikasi registrasi peserta berhasil dikirim', [
                'id_rkm' => $rkm->id,
                'kelas' => $data['kelas'],
                'id_peserta' => $peserta->id,
                'nama_peserta' => $peserta->nama,
                'email_peserta' => $peserta->email,
                'penerima_ids' => $users->pluck('id')->all(),
                'jumlah' => $users->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal kirim notifikasi registrasi peserta: '.$e->getMessage(), [
                'id_rkm' => $rkm->id,
                'id_peserta' => $peserta->id,
            ]);
        }
    }
}
