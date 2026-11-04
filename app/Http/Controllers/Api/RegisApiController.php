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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use App\Notifications\RegistrasiNotification;


class RegisApiController extends Controller
{
    public function kelasBerjalan()
    {
        $now = CarbonImmutable::now('Asia/Jakarta')->locale('id_ID');

        $startOfWeek = $now->startOfWeek()->format('Y-m-d');
        $endOfWeek   = $now->endOfWeek()->format('Y-m-d');

        $rows = RKM::with(['materi', 'peluang', 'perusahaan'])
            ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
            ->whereBetween('r_k_m_s.tanggal_awal', [$startOfWeek, $endOfWeek])
            ->whereNot('r_k_m_s.metode_kelas', 'Exam Only')
            ->where(function ($query) {
                $query->whereDoesntHave('peluang')
                    ->orWhereHas('peluang', function ($q) {
                        $q->where('tahap', 'merah');
                    });
            })
            ->select(
                DB::raw('GROUP_CONCAT(r_k_m_s.id SEPARATOR ", ") AS id'),
                DB::raw('GROUP_CONCAT(r_k_m_s.id SEPARATOR ", ") AS id_all'),
                // Tambahkan kembali agregasi yang dipakai di foreach bawah:
                DB::raw('GROUP_CONCAT(DISTINCT r_k_m_s.sales_key SEPARATOR ", ") AS sales_all'),
                DB::raw('GROUP_CONCAT(DISTINCT r_k_m_s.perusahaan_key SEPARATOR ", ") AS perusahaan_all'),
                DB::raw('GROUP_CONCAT(DISTINCT r_k_m_s.instruktur_key SEPARATOR ", ") AS instruktur_all'),
                DB::raw('SUM(r_k_m_s.pax) AS total_pax'),
                'r_k_m_s.materi_key',
                'materis.nama_materi',
                'r_k_m_s.ruang',
                'r_k_m_s.metode_kelas',
                'r_k_m_s.event',
                // Pastikan diambil sebagai string murni YYYY-MM-DD dari DB
                DB::raw('DATE_FORMAT(r_k_m_s.tanggal_awal, "%Y-%m-%d") AS tanggal_awal_raw'),
                DB::raw('DATE_FORMAT(r_k_m_s.tanggal_akhir, "%Y-%m-%d") AS tanggal_akhir_raw'),
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

        $combinedData = [];

        foreach ($rows as $row) {
            // Mengambil tanggal murni tanpa terpengaruh konversi UTC Laravel
            $tglAwal  = $row->tanggal_awal_raw ?? Carbon::parse($row->getRawOriginal('tanggal_awal'))->format('Y-m-d');
            $tglAkhir = $row->tanggal_akhir_raw ?? Carbon::parse($row->getRawOriginal('tanggal_akhir'))->format('Y-m-d');

            // Memetakan relasi data instruktur, sales, dan perusahaan (dengan pengaman jika kosong)
            $sales_ids      = $row->sales_all ? explode(', ', $row->sales_all) : [];
            $perusahaan_ids = $row->perusahaan_all ? explode(', ', $row->perusahaan_all) : [];
            $instruktur_ids = $row->instruktur_all ? explode(', ', $row->instruktur_all) : [];

            $row->instruktur = karyawan::whereIn('kode_karyawan', $instruktur_ids)->get();
            $row->sales      = karyawan::whereIn('kode_karyawan', $sales_ids)->get();
            $row->perusahaan = Perusahaan::whereIn('id', $perusahaan_ids)->get();

            // Pembuatan string id_baru menggunakan tanggal yang sudah aman
            $tgl     = Carbon::createFromFormat('Y-m-d', $tglAwal, 'Asia/Jakarta');
            $metode  = strtolower(str_replace(' ', '', $row->metode_kelas));
            $id_baru = $row->materi_key . 'ib' . $tgl->format('d') . 'ie' . $tgl->format('Y') . 'ie' . $tgl->format('m') . 'im' . $metode;

            // Ubah ke array lalu timpa tanggalnya agar di dalam 'items' juga tidak mundur 1 hari
            $rowArray = $row->toArray();
            $rowArray['tanggal_awal']  = $tglAwal;
            $rowArray['tanggal_akhir'] = $tglAkhir;

            // Penyusunan format akhir data
            $combinedData[] = [
                'id_baru'       => $id_baru,
                'id_all'        => $row->id_all,
                'materi_key'    => $row->materi_key,
                'nama_materi'   => $row->nama_materi,
                'tanggal_awal'  => $tglAwal,
                'tanggal_akhir' => $tglAkhir,
                'total_pax'     => $row->total_pax ?? 0,
                'isi_pax'       => $row->total_isi_pax ?? 0,
                'metode_kelas'  => $row->metode_kelas,
                'ruang'         => $row->ruang,
                'items'         => [$rowArray],
            ];
        }

        return response()->json([
            'data' => $combinedData
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
        $email = strtolower(trim((string) $request->input('email')));

        $peserta = Peserta::with('perusahaan')
            ->whereRaw('LOWER(TRIM(email)) = ?', [$email])
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
            'id'           => $id,
        ]);

        $email = strtolower(trim((string) $request->email));

        // Guard 1: Atomic Lock per email (lintas request)
        $lockKey = 'registrasi_store_lock:' . md5($email);
        $lock = Cache::lock($lockKey, 60);

        if (!$lock->get()) {
            Log::warning('Duplicate concurrent registration request blocked by lock', [
                'email' => $email,
                'id'    => $id,
            ]);

            return response()->json([
                'status' => 'error',
                'title'  => 'Mohon Tunggu',
                'text'   => 'Pendaftaran Anda sedang diproses, mohon tidak menekan tombol berulang kali.',
            ], 429);
        }

        try {
            // 1. Cari peserta awal (untuk menentukan perusahaan_key jika dari request kosong)
            $pesertaAwal = Peserta::whereRaw('LOWER(TRIM(email)) = ?', [$email])->first();
            $perusahaan_key = $request->perusahaan_key ?? ($pesertaAwal->perusahaan_key ?? null);

            // 2. Cari RKM dari id_rkm atau fallback kode URL $id
            $rkm = null;
            if ($request->filled('id_rkm')) {
                $rkm = RKM::with('perusahaan', 'materi', 'sales', 'instruktur')
                    ->find($request->id_rkm);
            }

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

                        try {
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
                        } catch (\Exception $e) {
                            Log::warning('Format tanggal pada parameter URL RKM tidak valid', [
                                'id_url' => $id,
                                'error'  => $e->getMessage(),
                            ]);
                        }
                    }
                }
            }

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

            // Validasi kelengkapan data relasi RKM untuk mencegah QueryException (Column cannot be null)
            if (empty($rkm->instruktur_key) || empty($rkm->materi_key) || empty($rkm->sales_key)) {
                Log::warning('Registrasi ditolak: Data atribut wajib RKM belum lengkap', [
                    'id_rkm'         => $rkm->id,
                    'materi_key'     => $rkm->materi_key,
                    'instruktur_key' => $rkm->instruktur_key,
                    'sales_key'      => $rkm->sales_key,
                ]);

                return response()->json([
                    'status' => 'error',
                    'title'  => 'Kelas Belum Siap',
                    'text'   => 'Jadwal kelas ini belum memiliki kelengkapan data instruktur/materi. Silakan hubungi admin.',
                ], 422);
            }

            // =========================================================================
            // 3. SATU TRANSAKSI CEPAT (< 100ms): KUNCI RKM, BUAT PESERTA & REGISTRASI
            // =========================================================================
            $hasil = DB::transaction(function () use ($request, $email, $perusahaan_key, $rkm) {
                // Kunci baris RKM terlebih dahulu sebagai gerbang utama
                $lockedRkm = RKM::where('id', $rkm->id)->lockForUpdate()->first();

                if (!$lockedRkm) {
                    return ['status' => 'not_found'];
                }

                // Cek ulang kelengkapan instruktur di dalam lock untuk antisipasi race condition perubahan RKM
                if (empty($lockedRkm->instruktur_key)) {
                    return ['status' => 'incomplete_rkm'];
                }

                // Ambil semua ID peserta yang memiliki email ini
                $pesertaList = Peserta::whereRaw('LOWER(TRIM(email)) = ?', [$email])
                    ->lockForUpdate()
                    ->get();

                $peserta = $pesertaList->first();

                // Cek apakah SALAH SATU dari ID peserta ber-email ini sudah terdaftar di RKM ini
                if ($pesertaList->isNotEmpty()) {
                    $sudahAda = Registrasi::whereIn('id_peserta', $pesertaList->pluck('id'))
                        ->where('id_rkm', $lockedRkm->id)
                        ->first();

                    if ($sudahAda) {
                        return ['status' => 'duplicate', 'registrasi' => $sudahAda];
                    }
                }

                // Cek kapasitas kelas
                if ($lockedRkm->isi_pax <= 0) {
                    return ['status' => 'full'];
                }

                // Buat peserta baru jika belum ada, atau update jika sudah ada
                if (!$peserta) {
                    $peserta = Peserta::create([
                        'nama'           => $request->nama,
                        'jenis_kelamin'  => $request->jenis_kelamin,
                        'email'          => $email,
                        'no_hp'          => $request->no_hp,
                        'alamat'         => $request->alamat,
                        'perusahaan_key' => $perusahaan_key,
                        'tanggal_lahir'  => $request->tanggal_lahir,
                        'checkregist'    => $request->checkregist,
                    ]);
                } else {
                    $peserta->update([
                        'perusahaan_key' => $perusahaan_key,
                        'checkregist'    => $request->checkregist,
                    ]);
                }

                // Buat Registrasi sekarang juga (sebelum panggil Moodle)
                $registrasi = Registrasi::create([
                    'id_rkm'        => $lockedRkm->id,
                    'id_peserta'    => $peserta->id,
                    'id_materi'     => $lockedRkm->materi_key,
                    'id_instruktur' => $lockedRkm->instruktur_key,
                    'id_sales'      => $lockedRkm->sales_key,
                ]);

                $lockedRkm->decrement('isi_pax');

                return [
                    'status'     => 'created',
                    'peserta'    => $peserta,
                    'registrasi' => $registrasi,
                    'sisa_pax'   => $lockedRkm->fresh()->isi_pax,
                ];
            });

            // Tangani hasil validasi transaksi
            if ($hasil['status'] === 'not_found') {
                return response()->json([
                    'status' => 'error',
                    'title'  => 'Mohon Maaf',
                    'text'   => 'Data kelas (RKM) tidak ditemukan.',
                ], 404);
            }

            if ($hasil['status'] === 'incomplete_rkm') {
                return response()->json([
                    'status' => 'error',
                    'title'  => 'Kelas Belum Siap',
                    'text'   => 'Jadwal kelas ini belum memiliki instruktur yang ditugaskan. Silakan hubungi admin.',
                ], 422);
            }

            if ($hasil['status'] === 'duplicate') {
                return response()->json([
                    'status' => 'error',
                    'title'  => 'Mohon Maaf',
                    'text'   => 'Anda sudah terdaftar di kelas ini',
                ], 422);
            }

            if ($hasil['status'] === 'full') {
                return response()->json([
                    'status' => 'error',
                    'title'  => 'Mohon Maaf',
                    'text'   => 'Kapasitas peserta sudah penuh',
                ], 422);
            }

            $peserta    = $hasil['peserta'];
            $registrasi = $hasil['registrasi'];
            $idKaryawan = $request->id_karyawan;

            Log::info('Registration created swiftly, scheduling background Moodle & Notification', [
                'registration_id' => $registrasi->id,
                'participant_id'  => $peserta->id,
                'sisa_pax'        => $hasil['sisa_pax'],
            ]);

            // =========================================================================
            // 4. JALANKAN MOODLE & NOTIFIKASI SETELAH RESPONSE DIKIRIM KE USER
            // =========================================================================
            dispatch(function () use ($rkm, $peserta, $registrasi, $idKaryawan) {
                // A. Proses Moodle
                try {
                    $moodle = app(MoodleService::class);
                    if ($moodle) {
                        $moodleUserId = $peserta->moodle_user_id;

                        if (!$moodleUserId) {
                            $moodleUserId = $moodle->syncUser($peserta);
                            if ($moodleUserId) {
                                $peserta->update(['moodle_user_id' => $moodleUserId]);
                            }
                        }

                        $courseId = $moodle->findCourse(
                            $rkm->materi?->nama_materi ?? '',
                            $rkm->perusahaan?->nama_perusahaan
                        );

                        if ($moodleUserId && $courseId !== null) {
                            $moodle->enroll((int) $moodleUserId, (int) $courseId);
                            $registrasi->update(['moodle_course_id' => $courseId]);
                        }
                    }
                } catch (\Throwable $e) {
                    Log::error('Background Moodle sync/enroll error: ' . $e->getMessage(), [
                        'participant_id'  => $peserta->id,
                        'registration_id' => $registrasi->id,
                        'exception'       => get_class($e),
                    ]);
                }

                // B. Proses Kirim Notifikasi
                try {
                    $this->kirimNotifikasi($rkm, $peserta, $idKaryawan);
                } catch (\Throwable $e) {
                    Log::error('Background kirimNotifikasi error: ' . $e->getMessage(), [
                        'registration_id' => $registrasi->id,
                        'exception'       => get_class($e),
                    ]);
                }
            })->afterResponse();

            // Response langsung dikirim detik ini juga
            return response()->json([
                'status' => 'success',
                'title'  => 'Selamat!',
                'text'   => 'Anda telah berhasil mendaftar kelas ini',
            ], 200);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database QueryException saat store registrasi', [
                'email'      => $email,
                'id_url'     => $id,
                'sql_state'  => $e->errorInfo[0] ?? null,
                'error_code' => $e->errorInfo[1] ?? null,
                'message'    => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'title'  => 'Gagal Menyimpan Data',
                'text'   => 'Terjadi kesalahan pada basis data saat memproses pendaftaran. Silakan hubungi admin.',
            ], 500);

        } catch (\Throwable $e) {
            Log::error('Unhandled exception saat store registrasi', [
                'email'     => $email,
                'id_url'    => $id,
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ]);

            return response()->json([
                'status' => 'error',
                'title'  => 'Mohon Maaf',
                'text'   => 'Terjadi kesalahan sistem internal. Silakan coba beberapa saat lagi.',
            ], 500);

        } finally {
            $lock->release();
        }
    }

    // public function store(Request $request, $id)
    // {
    //     Log::info('Store registration started', [
    //         'request_data' => $request->all(),
    //         'id'           => $id,
    //     ]);

    //     $email = strtolower(trim((string) $request->email));

    //     // Guard 1: Atomic Lock per email (lintas request)
    //     $lockKey = 'registrasi_store_lock:' . md5($email);
    //     $lock = Cache::lock($lockKey, 60);

    //     if (!$lock->get()) {
    //         Log::warning('Duplicate concurrent registration request blocked by lock', [
    //             'email' => $email,
    //             'id'    => $id,
    //         ]);

    //         return response()->json([
    //             'status' => 'error',
    //             'title'  => 'Mohon Tunggu',
    //             'text'   => 'Pendaftaran Anda sedang diproses, mohon tidak menekan tombol berulang kali.',
    //         ], 429);
    //     }

    //     try {
    //         // 1. Cari peserta awal (untuk menentukan perusahaan_key jika dari request kosong)
    //         $pesertaAwal = Peserta::whereRaw('LOWER(TRIM(email)) = ?', [$email])->first();
    //         $perusahaan_key = $request->perusahaan_key ?? ($pesertaAwal->perusahaan_key ?? null);

    //         // 2. Cari RKM dari id_rkm atau fallback kode URL $id
    //         $rkm = null;
    //         if ($request->filled('id_rkm')) {
    //             $rkm = RKM::with('perusahaan', 'materi', 'sales', 'instruktur')
    //                 ->find($request->id_rkm);
    //         }

    //         if (!$rkm && $perusahaan_key) {
    //             $array = explode('ib', $id);
    //             $materi_key = $array[0] ?? null;

    //             if (isset($array[1])) {
    //                 $parts = explode('im', $array[1], 2);
    //                 $tanggal_part = $parts[0];
    //                 $metode = $parts[1] ?? null;
    //                 $tanggal_rkm = explode('ie', $tanggal_part);

    //                 if (count($tanggal_rkm) >= 3) {
    //                     $hari  = str_pad($tanggal_rkm[0], 2, '0', STR_PAD_LEFT);
    //                     $tahun = $tanggal_rkm[1];
    //                     $bulan = str_pad($tanggal_rkm[2], 2, '0', STR_PAD_LEFT);

    //                     $tanggal_awal = Carbon::createFromFormat('Y-m-d', "$tahun-$bulan-$hari");
    //                     $tanggal_awal_str = $tanggal_awal->format('Y-m-d');
    //                     $tanggal_akhir_str = $tanggal_awal->copy()->addDays(2)->format('Y-m-d');

    //                     $rkm = RKM::with('perusahaan', 'materi', 'sales', 'instruktur')
    //                         ->where('materi_key', $materi_key)
    //                         ->where('perusahaan_key', $perusahaan_key)
    //                         ->whereBetween('tanggal_awal', [$tanggal_awal_str, $tanggal_akhir_str])
    //                         ->when($metode, function ($q) use ($metode) {
    //                             $q->whereRaw('LOWER(REPLACE(metode_kelas, " ", "")) = ?', [$metode]);
    //                         })
    //                         ->first();
    //                 }
    //             }
    //         }

    //         if (!$rkm) {
    //             Log::error('RKM tidak ditemukan saat store registrasi', [
    //                 'id_rkm_request' => $request->id_rkm,
    //                 'perusahaan_key' => $perusahaan_key,
    //                 'id_url'         => $id,
    //             ]);

    //             return response()->json([
    //                 'status' => 'error',
    //                 'title'  => 'Mohon Maaf',
    //                 'text'   => 'Data kelas (RKM) tidak ditemukan.',
    //             ], 404);
    //         }

    //         // =========================================================================
    //         // ✅ 3. SATU TRANSAKSI CEPAT (< 100ms): KUNCI RKM, BUAT PESERTA & REGISTRASI
    //         // =========================================================================
    //         // Karena RKM sudah PASTI ADA di database, lockForUpdate() pada RKM akan memaksa
    //         // semua request ke kelas yang sama mengantre satu per satu di level Database!
    //         $hasil = DB::transaction(function () use ($request, $email, $perusahaan_key, $rkm) {
    //             // Kunci baris RKM terlebih dahulu sebagai gerbang utama
    //             $lockedRkm = RKM::where('id', $rkm->id)->lockForUpdate()->first();

    //             // Ambil semua ID peserta yang memiliki email ini (antisipasi jika di DB sudah terlanjur ada email kembar)
    //             $pesertaList = Peserta::whereRaw('LOWER(TRIM(email)) = ?', [$email])
    //                 ->lockForUpdate()
    //                 ->get();

    //             $peserta = $pesertaList->first();

    //             // Cek apakah SALAH SATU dari ID peserta ber-email ini sudah terdaftar di RKM ini
    //             if ($pesertaList->isNotEmpty()) {
    //                 $sudahAda = Registrasi::whereIn('id_peserta', $pesertaList->pluck('id'))
    //                     ->where('id_rkm', $lockedRkm->id)
    //                     ->first();

    //                 if ($sudahAda) {
    //                     return ['status' => 'duplicate', 'registrasi' => $sudahAda];
    //                 }
    //             }

    //             // Cek kapasitas kelas
    //             if ($lockedRkm->isi_pax <= 0) {
    //                 return ['status' => 'full'];
    //             }

    //             // Buat peserta baru jika belum ada, atau update jika sudah ada
    //             if (!$peserta) {
    //                 $peserta = Peserta::create([
    //                     'nama'           => $request->nama,
    //                     'jenis_kelamin'  => $request->jenis_kelamin,
    //                     'email'          => $email,
    //                     'no_hp'          => $request->no_hp,
    //                     'alamat'         => $request->alamat,
    //                     'perusahaan_key' => $perusahaan_key,
    //                     'tanggal_lahir'  => $request->tanggal_lahir,
    //                     'checkregist'    => $request->checkregist,
    //                 ]);
    //             } else {
    //                 $peserta->update([
    //                     'perusahaan_key' => $perusahaan_key,
    //                     'checkregist'    => $request->checkregist,
    //                 ]);
    //             }

    //             // Buat Registrasi sekarang juga (sebelum panggil Moodle!)
    //             $registrasi = Registrasi::create([
    //                 'id_rkm'        => $lockedRkm->id,
    //                 'id_peserta'    => $peserta->id,
    //                 'id_materi'     => $lockedRkm->materi_key,
    //                 'id_instruktur' => $lockedRkm->instruktur_key,
    //                 'id_sales'      => $lockedRkm->sales_key,
    //             ]);

    //             $lockedRkm->decrement('isi_pax');

    //             return [
    //                 'status'     => 'created',
    //                 'peserta'    => $peserta,
    //                 'registrasi' => $registrasi,
    //                 'sisa_pax'   => $lockedRkm->fresh()->isi_pax,
    //             ];
    //         });

    //         // Tangani hasil validasi transaksi
    //         if ($hasil['status'] === 'duplicate') {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'title'  => 'Mohon Maaf',
    //                 'text'   => 'Anda sudah terdaftar di kelas ini',
    //             ], 422);
    //         }

    //         if ($hasil['status'] === 'full') {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'title'  => 'Mohon Maaf',
    //                 'text'   => 'Kapasitas peserta sudah penuh',
    //             ], 422);
    //         }

    //         $peserta    = $hasil['peserta'];
    //         $registrasi = $hasil['registrasi'];
    //         $idKaryawan = $request->id_karyawan;

    //         Log::info('Registration created swiftly, scheduling background Moodle & Notification', [
    //             'registration_id' => $registrasi->id,
    //             'participant_id'  => $peserta->id,
    //             'sisa_pax'        => $hasil['sisa_pax'],
    //         ]);

    //         // =========================================================================
    //         // ✅ 4. JALANKAN MOODLE & NOTIFIKASI SETELAH RESPONSE DIKIRIM KE USER
    //         // =========================================================================
    //         // Dengan dispatch()->afterResponse(), user langsung mendapat pesan "Selamat!"
    //         // dalam < 0.5 detik tanpa menunggu Moodle selesai loading.
    //         dispatch(function () use ($rkm, $peserta, $registrasi, $idKaryawan) {
    //             // A. Proses Moodle
    //             try {
    //                 $moodle = app(MoodleService::class);
    //                 if ($moodle) {
    //                     $moodleUserId = $peserta->moodle_user_id;

    //                     if (!$moodleUserId) {
    //                         $moodleUserId = $moodle->syncUser($peserta);
    //                         if ($moodleUserId) {
    //                             $peserta->update(['moodle_user_id' => $moodleUserId]);
    //                         }
    //                     }

    //                     $courseId = $moodle->findCourse(
    //                         $rkm->materi?->nama_materi ?? '',
    //                         $rkm->perusahaan?->nama_perusahaan
    //                     );

    //                     if ($moodleUserId && $courseId !== null) {
    //                         $moodle->enroll((int) $moodleUserId, (int) $courseId);
    //                         $registrasi->update(['moodle_course_id' => $courseId]);
    //                     }
    //                 }
    //             } catch (\Throwable $e) {
    //                 Log::error('Background Moodle sync/enroll error: ' . $e->getMessage(), [
    //                     'participant_id'  => $peserta->id,
    //                     'registration_id' => $registrasi->id,
    //                 ]);
    //             }

    //             // B. Proses Kirim Notifikasi
    //             try {
    //                 $this->kirimNotifikasi($rkm, $peserta, $idKaryawan);
    //             } catch (\Throwable $e) {
    //                 Log::error('Background kirimNotifikasi error: ' . $e->getMessage(), [
    //                     'registration_id' => $registrasi->id,
    //                 ]);
    //             }
    //         })->afterResponse();

    //         // Response langsung dikirim detik ini juga!
    //         return response()->json([
    //             'status' => 'success',
    //             'title'  => 'Selamat!',
    //             'text'   => 'Anda telah berhasil mendaftar kelas ini',
    //         ], 200);

    //     } finally {
    //         $lock->release();
    //     }
    // }

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
