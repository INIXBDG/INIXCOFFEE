<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\karyawan;
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

        $query = RKM::with(['materi', 'peluang', 'perusahaan'])
            // ... query tetap sama seperti sebelumnya ...
            ;

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
            ->filter() // Membuang string kosong jika pasangan bernilai null
            ->map(function ($item) {
                $parts = explode('|', $item);

                return [
                    'id_rkm'          => $parts[0] ?? null,
                    'perusahaan_id'   => $parts[1] ?? null, // Mencegah Undefined array key 1
                    'nama_perusahaan' => $parts[2] ?? null, // Mencegah Undefined array key 2
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

        // $moodle = app(MoodleService::class);
        $moodle = 'hmm';

        $email = $request->email;
        Log::info('Searching participant by email', ['email' => $email]);

        // ✅ Ambil 1 RKM dari request
        $rkm = RKM::with('perusahaan', 'materi', 'sales', 'instruktur')
            ->find($request->id_rkm);

        $peserta = Peserta::with('perusahaan')
            ->where('email', $email)
            ->first();

        Log::info('Participant search result', [
            'found' => $peserta ? true : false,
            'participant' => $peserta ? ['id' => $peserta->id, 'email' => $peserta->email] : null,
        ]);

        $perusahaan_key = $request->perusahaan_key ?? ($peserta->perusahaan_key ?? null);

        Log::info('Company key determined', ['company_key' => $perusahaan_key]);

        Log::info('RKM found', [
            'rkm_id' => $rkm->id,
            'isi_pax' => $rkm->isi_pax,
            'materi_key' => $rkm->materi_key,
        ]);

        // ✅ cek kapasitas
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

            // Sync ke Moodle
            try {
                Log::info('Starting Moodle sync for new participant', [
                    'participant_id' => $peserta->id,
                    'email' => $peserta->email,
                ]);

                $moodleUserId = $moodle->syncUser($peserta);

                Log::info('Moodle sync result', [
                    'participant_id' => $peserta->id,
                    'moodle_user_id' => $moodleUserId,
                    'success' => $moodleUserId ? true : false,
                ]);

                if ($moodleUserId) {
                    $peserta->update([
                        'moodle_user_id' => $moodleUserId,
                    ]);

                    Log::info('Local participant record updated with moodle_user_id', [
                        'participant_id' => $peserta->id,
                        'moodle_user_id' => $moodleUserId,
                    ]);
                } else {
                    Log::warning('Moodle sync failed for new participant', [
                        'participant_id' => $peserta->id,
                        'email' => $peserta->email,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Moodle sync error for new participant: '.$e->getMessage(), [
                    'participant_id' => $peserta->id,
                    'email' => $peserta->email,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
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

            // 🔥 lazy sync (kalau belum ada moodle_user_id)
            if (!$peserta->moodle_user_id) {
                Log::info('Lazy sync required for existing participant', [
                    'participant_id' => $peserta->id,
                    'email' => $peserta->email,
                ]);

                try {
                    $moodleUserId = $moodle->syncUser($peserta);

                    Log::info('Lazy sync result', [
                        'participant_id' => $peserta->id,
                        'moodle_user_id' => $moodleUserId,
                        'success' => $moodleUserId ? true : false,
                    ]);

                    if ($moodleUserId) {
                        $peserta->update([
                            'moodle_user_id' => $moodleUserId,
                        ]);

                        Log::info('Local participant record updated with moodle_user_id after lazy sync', [
                            'participant_id' => $peserta->id,
                            'moodle_user_id' => $moodleUserId,
                        ]);
                    } else {
                        Log::warning('Lazy Moodle sync failed for existing participant', [
                            'participant_id' => $peserta->id,
                            'email' => $peserta->email,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Moodle lazy sync error: '.$e->getMessage(), [
                        'participant_id' => $peserta->id,
                        'email' => $peserta->email,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            } else {
                Log::info('Participant already has moodle_user_id, skipping sync', [
                    'participant_id' => $peserta->id,
                    'moodle_user_id' => $peserta->moodle_user_id,
                ]);
            }

            // cek sudah terdaftar
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
            } else {
                Log::info('Participant not registered yet for this class', [
                    'participant_id' => $peserta->id,
                    'rkm_id' => $rkm->id,
                ]);
            }
        }

        $courseId = $moodle->findCourse($rkm->materi->nama_materi, $rkm->perusahaan->nama_perusahaan);

        Log::info('Moodle findCourse result', [
            'materi' => $rkm->materi->nama_materi,
            'perusahaan' => $rkm->perusahaan->nama_perusahaan,
            'course_id' => $courseId,
        ]);

        if ($courseId === null) {
            Log::warning('Course tidak ditemukan di Moodle, enroll akan dilewati', [
                'rkm_id' => $rkm->id,
                'materi' => $rkm->materi->nama_materi,
                'perusahaan' => $rkm->perusahaan->nama_perusahaan,
            ]);
        }

        // ===============================
        // ✅ ENROLL KE MOODLE COURSE
        // ===============================
        try {
            if ($peserta->moodle_user_id && $courseId !== null) {
                Log::info('Starting Moodle enrollment', [
                    'participant_id' => $peserta->id,
                    'moodle_user_id' => $peserta->moodle_user_id,
                    'course_id' => $courseId,
                ]);

                $enrolled = $moodle->enroll(
                    $peserta->moodle_user_id,
                    $courseId
                );

                Log::info('Moodle enrollment result', [
                    'participant_id' => $peserta->id,
                    'success' => $enrolled,
                ]);

                if (!$enrolled) {
                    Log::warning('Failed to enroll user to Moodle course', [
                        'participant_id' => $peserta->id,
                        'course_id' => $courseId,
                    ]);
                }
            } else {
                Log::warning('Enroll dilewati', [
                    'participant_id' => $peserta->id,
                    'has_moodle_user_id' => (bool) $peserta->moodle_user_id,
                    'has_course_id' => $courseId !== null,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Moodle enrollment error: '.$e->getMessage(), [
                'participant_id' => $peserta->id,
                'error' => $e->getMessage(),
            ]);
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

            Log::info('Registration record updated with Moodle enrollment status', [
                'registration_id' => $registrasi->id,
                'moodle_course_id' => $courseId,
            ]);
        } else {
            Log::warning('Failed to find registration record after creation', [
                'participant_id' => $peserta->id,
                'rkm_id' => $rkm->id,
            ]);
        }

        Log::info('Registration created successfully', [
            'registration_id' => $registrasi->id,
            'participant_id' => $peserta->id,
        ]);

        $peserta->update([
            'checkregist' => $request->checkregist,
        ]);

        Log::info('Participant checkregist updated', [
            'participant_id' => $peserta->id,
            'new_checkregist' => $request->checkregist,
        ]);

        $rkm->decrement('isi_pax');
        Log::info('RKM capacity decremented', [
            'rkm_id' => $rkm->id,
            'new_capacity' => $rkm->fresh()->isi_pax,
        ]);

        Log::info('Registration process completed successfully', [
            'participant_id' => $peserta->id,
            'rkm_id' => $rkm->id,
            'registration_id' => $registrasi->id,
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
