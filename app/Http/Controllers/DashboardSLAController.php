<?php

namespace App\Http\Controllers;
// Pastikan semua 'use' statement ini ada dan benar
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Tickets;
use App\Models\laporanInsiden;
use App\Models\YearMapping;
use App\Models\EventTodo;
use App\Models\ContentSchedule;
use Carbon\CarbonPeriod;
use App\Models\trackingLaporanInsiden;
use App\Models\karyawan;
use App\Models\User;
use App\Models\HariLibur;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DashboardSLAController extends Controller
{
    /**
     * Timezone default untuk semua kalkulasi Carbon.
     */
    protected $timezone = 'Asia/Jakarta';
    /**
     * Jam mulai kerja (misal: 08:00).
     */
    protected $businessStartHour = 8;
    /**
     * Jam selesai kerja (misal: 17:00).
     */
    protected $businessEndHour = 17;

    /**
     * Ambil daftar hari libur (Format: Y-m-d).
     * Saran: Ganti dengan Query ke tabel database, contoh: Holiday::pluck('date')->toArray();
     */
    private function getHolidays()
    {
        $tahun = date('Y');
        $cacheKey = 'holidays_db_' . $tahun;

        // Gunakan cache agar tidak query ke database berulang kali untuk setiap hitungan SLA
        return Cache::remember($cacheKey, now()->addDays(30), function () use ($tahun) {
            return HariLibur::where('year', $tahun)
                ->pluck('tanggal')
                ->toArray();
        });
    }

    /**
     * Cek apakah hari ini adalah hari kerja (Bukan Sabtu/Minggu, dan bukan Hari Libur)
     */
    private function isWorkingDay(Carbon $date, $holidays)
    {
        // isWeekday() = true jika Senin-Jumat
        // in_array() = true jika tanggal ada di daftar libur. Kita pakai ! (NOT)
        return $date->isWeekday() && !in_array($date->format('Y-m-d'), $holidays);
    }

    // =========================================================================
    // HELPER: VALIDASI DAN PARSING TANGGAL
    // =========================================================================
    /**
     * Validasi input tanggal dan mengembalikannya sebagai objek Carbon.
     */
    private function validateAndParseDates(Request $request)
    {
        // --- LOGIKA BARU UNTUK SEMESTER ---
        $now = Carbon::now($this->timezone);

        if ($now->month <= 6) {
            // Semester 1 (Jan - Jun)
            $defaultStartDate = $now->copy()->month(1)->startOfMonth()->startOfDay(); // 1 Jan
            $defaultEndDate = $now->copy()->month(6)->endOfMonth()->endOfDay();   // 30 Jun
        } else {
            // Semester 2 (Jul - Dec)
            $defaultStartDate = $now->copy()->month(7)->startOfMonth()->startOfDay(); // 1 Jul
            $defaultEndDate = $now->copy()->month(12)->endOfMonth()->endOfDay();  // 31 Des
        }
        // --- SELESAI LOGIKA SEMESTER ---

        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            // Gunakan default SEMESTER jika validasi gagal
            return [
                'startDate' => $defaultStartDate,
                'endDate' => $defaultEndDate,
                'filters' => [
                    'start' => $defaultStartDate->toDateTimeString(),
                    'end' => $defaultEndDate->toDateTimeString(),
                    'error' => $validator->errors()->first()
                ]
            ];
        }

        // Gunakan input jika valid, atau default SEMESTER jika kosong
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'), $this->timezone)->startOfDay()
            : $defaultStartDate;

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'), $this->timezone)->endOfDay()
            : $defaultEndDate;

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filters' => ['start' => $startDate->toDateTimeString(), 'end' => $endDate->toDateTimeString()]
        ];
    }

    private function getSlaRules()
    {
        return [
            // --- TAHAP PERENCANAAN (H-30) ---
            'Link Pendaftaran' => ['offset' => -30, 'stage' => 'Perencanaan', 'desc' => 'Max H-30'],
            'Pemateri' => ['offset' => -30, 'stage' => 'Perencanaan', 'desc' => 'Fixasi H-30'],

            // --- TAHAP PERSIAPAN (H-14 s/d H-3) ---
            'Flyer' => ['offset' => -14, 'stage' => 'Persiapan', 'desc' => 'Max H-14'],
            'Background Zoom' => ['offset' => -7, 'stage' => 'Persiapan', 'desc' => 'Max H-7'],
            'Akun E-Learning' => ['offset' => -7, 'stage' => 'Persiapan', 'desc' => 'Max H-7'],
            'Teknis & Ruangan' => ['offset' => -3, 'stage' => 'Persiapan', 'desc' => 'Max H-3'],
            'MC' => ['offset' => -3, 'stage' => 'Persiapan', 'desc' => 'Naskah H-3'],
            'Moderator' => ['offset' => -3, 'stage' => 'Persiapan', 'desc' => 'Briefing H-3'],

            // --- TAHAP FINALISASI (H-1) ---
            'Blast Link Zoom' => ['offset' => -1, 'stage' => 'Finalisasi', 'desc' => 'H-1 Pagi'],
            'Link Zoom & Youtube' => ['offset' => -1, 'stage' => 'Finalisasi', 'desc' => 'Ready H-1'],

            // --- TAHAP PELAPORAN (H+7) ---
            'Sertifikat Webinar' => ['offset' => 2, 'stage' => 'Pelaporan', 'desc' => 'Max H+2'],
            'Link Feed Back' => ['offset' => 7, 'stage' => 'Pelaporan', 'desc' => 'Data H+7'],
        ];
    }

    // =========================================================================
    // FUNGSI UTAMA 1: DASHBOARD TIM (GABUNGAN)
    // =========================================================================
    // Terima $team dari rute (misal: 'programmer' atau 'tech-support')
    public function dashboardTim(Request $request, $team)
    {
        $dateRange = $this->validateAndParseDates($request);

        // --- KONDISI DINAMIS ---
        $keperluan = ($team === 'programmer') ? '%Programming%' : '%Technical Support%';
        // -----------------------

        // 1. Ambil Data Mentah
        $rawTickets = DB::table('tickets')
            ->select(
                'id',
                'created_at',
                'kategori',
                'tingkat_kesulitan',
                'tanggal_response',
                'jam_response',
                'tanggal_selesai',
                'jam_selesai'
            )
            ->where('keperluan', 'LIKE', $keperluan) // <-- Gunakan variabel dinamis
            ->whereNotNull('tanggal_selesai')
            ->whereBetween('created_at', [$dateRange['startDate'], $dateRange['endDate']])
            ->get();

        // 2. Proses Kalkulasi SLA
        $stats = $this->processTicketSla($rawTickets);

        // 3. Finalisasi KPI
        $total = $stats['total_tickets'];
        $kpi = [
            'sla_response_compliance' => ($total > 0) ? ($stats['response_met'] / $total) * 100 : 0,
            'sla_resolution_compliance' => ($total > 0) ? ($stats['resolution_met'] / $total) * 100 : 0,
            'avg_response_time' => ($stats['response_count'] > 0) ? $stats['sum_response_hours'] / $stats['response_count'] : 0,
            'avg_resolution_time' => ($total > 0) ? $stats['sum_resolution_hours'] / $total : 0,
            'total_tickets' => $total,
            'tickets_by_priority' => $stats['priority_count'],
            'filters' => $dateRange['filters']
        ];

        return response()->json($kpi);
    }

    public function dashboardUser(Request $request, $team)
    {
        $dateRange = $this->validateAndParseDates($request);

        $allPicMaps = [
            'programmer' => [
                'ardhan' => ['Ardhan'],
                'donna' => ['Donna'],
                'juliet' => ['Juli'],
                'stepanusberkatsinaga' => ['Stefan', 'Stepanus Berkat Sinaga'],
                'sergiomosesriyanto' => ['Sergio'],
                'vickyryandysaputra' => ['Vicky'],
            ],
            'tech-support' => [
                'eggiherlambang' => ['Eggi'],
                'naufal' => ['Naufal'],
                'ferdi' => ['Ferdi'],
                'ardhan' => ['Ardhan'],
            ]
        ];

        // Pilih mapping dan keperluan berdasarkan $team
        $userToPicMap = $allPicMaps[$team] ?? [];
        $keperluan = ($team === 'programmer') ? '%Programming%' : '%Technical Support%';

        $allUsernames = array_keys($userToPicMap);

        // 2. Ambil data User beserta relasi Karyawan
        $users = [];
        if (!empty($allUsernames)) {
            $users = User::query()
                ->whereIn('username', $allUsernames, 'and', false)
                ->with('karyawan')
                ->get();
        }

        // 3. Buat mapping (Username -> Nama Lengkap) dari tabel Users/Karyawan
        $usernameToNamaLengkapMap = [];
        foreach ($users as $user) {
            $usernameToNamaLengkapMap[$user->username] = $user->karyawan->nama_lengkap ?? ($user->name ?? $user->username);
        }

        // 4. Bangun Array Query & Lookup Map untuk grouping
        $picNamesForQuery = [];
        $picLookupMap = [];

        foreach ($userToPicMap as $username => $picNamesArray) {
            // Tentukan nama tampilan utama (Nama lengkap, atau fallback ke variasi nama pertama)
            $displayName = $usernameToNamaLengkapMap[$username] ?? $picNamesArray[0];

            foreach ($picNamesArray as $picName) {
                // Kumpulkan semua variasi nama untuk dimasukkan ke WhereIn Query
                $picNamesForQuery[] = $picName;

                // Petakan setiap variasi nama (lowercase) ke satu Nama Tampilan yang sama
                // Contoh: 'stefan' -> 'Stepanus Berkat Sinaga' & 'stepanus berkat sinaga' -> 'Stepanus Berkat Sinaga'
                $picLookupMap[strtolower($picName)] = $displayName;
            }
        }

        // 5. Ambil Data Mentah dari database menggunakan array variasi nama
        $rawTickets = DB::table('tickets')
            ->select(
                'pic',
                'created_at',
                'kategori',
                'tingkat_kesulitan',
                'tanggal_response',
                'jam_response',
                'tanggal_selesai',
                'jam_selesai'
            )
            ->whereIn('pic', $picNamesForQuery) // Akan mencari 'Stefan' DAN 'Stepanus Berkat Sinaga'
            ->whereNotNull('tanggal_selesai')
            ->where('keperluan', 'LIKE', $keperluan)
            ->where('created_at', '>=', $dateRange['startDate'])
            ->where('created_at', '<=', $dateRange['endDate'])
            ->get();

        // 6. Kelompokkan tiket per user berdasarkan Nama Tampilan
        $ticketsByUser = [];
        foreach ($rawTickets as $ticket) {
            $picKey = strtolower(trim($ticket->pic));
            $namaLengkap = $picLookupMap[$picKey] ?? 'Lainnya';
            $ticketsByUser[$namaLengkap][] = $ticket;
        }

        // 7. Proses SLA untuk setiap user
        $kpiPerUser = [];
        foreach ($ticketsByUser as $nama => $tickets) {
            if ($nama === 'Lainnya')
                continue;

            $stats = $this->processTicketSla($tickets);
            $total = $stats['total_tickets'];
            $kpiPerUser[$nama] = [
                'nama_programmer' => $nama,
                'sla_response_compliance' => ($total > 0) ? ($stats['response_met'] / $total) * 100 : 0,
                'sla_resolution_compliance' => ($total > 0) ? ($stats['resolution_met'] / $total) * 100 : 0,
                'avg_response_time' => ($stats['response_count'] > 0) ? $stats['sum_response_hours'] / $stats['response_count'] : 0,
                'avg_resolution_time' => ($total > 0) ? $stats['sum_resolution_hours'] / $total : 0,
                'total_tickets' => $total,
                'tickets_by_priority' => $stats['priority_count'],
            ];
        }

        ksort($kpiPerUser);

        return response()->json([
            'kpi' => array_values($kpiPerUser),
            'filters' => $dateRange['filters']
        ]);
    }
    public function dashboardKritis(Request $request, $team)
    {
        $dateRange = $this->validateAndParseDates($request);

        // --- KONDISI DINAMIS ---
        $jabatan = ($team === 'programmer') ? 'Programmer' : 'Technical Support';
        // -----------------------

        // 1. Ambil Insiden
        $insidenSelesai = laporanInsiden::whereHas('tracking', function ($q) {
            $q->whereIn('status', ['Selesai', 'Tidak Ditangani']);
        })
            ->with(['tracking' => fn($q) => $q->orderBy('id', 'asc'), 'tracking.karyawan'])
            ->whereBetween('created_at', [$dateRange['startDate'], $dateRange['endDate']])
            ->get();

        // 2. Proses Kalkulasi SLA di PHP
        $stats = [
            'total_insiden' => 0,
            'total_tidak_ditangani' => 0,
            'response_count' => 0,
            'response_met' => 0,
            'resolution_count' => 0,
            'resolution_met' => 0,
            'sum_response_hours' => 0,
            'sum_resolution_hours' => 0,
        ];
        $listInsiden = [];

        // Ambil data hari libur SEBELUM masuk ke dalam foreach
        $holidays = $this->getHolidays();

        foreach ($insidenSelesai as $insiden) {
            $trackingEvents = $insiden->tracking;
            $eventBatal = $trackingEvents->firstWhere('status', 'Tidak Ditangani');
            if ($eventBatal) {
                $stats['total_tidak_ditangani']++;
                continue;
            }

            $eventBaru = $trackingEvents->firstWhere('status', 'Baru');
            $eventPenanganan = $trackingEvents
                ->where('id', '>', $eventBaru ? $eventBaru->id : 0)
                ->firstWhere('status', 'Dalam Penanganan');
            $eventSelesai = $trackingEvents
                ->where('id', '>', $eventPenanganan ? $eventPenanganan->id : 0)
                ->firstWhere('status', 'Selesai');

            if (!$eventBaru || !$eventPenanganan || !$eventSelesai)
                continue;

            // Cek 'responder' di event 'Dalam Penanganan'
            $handler = $eventPenanganan->karyawan;

            // --- KONDISI DINAMIS ---
            $isTargetRole = $handler && $handler->jabatan === $jabatan;
            if (!$isTargetRole) {
                continue;
            }
            // -----------------------

            // --- Mulai Kalkulasi ---
            $stats['total_insiden']++;
            $timeBaru = $this->getTrackingTimestamp($eventBaru);
            $timePenanganan = $this->getTrackingTimestamp($eventPenanganan);
            $timeSelesai = $this->getTrackingTimestamp($eventSelesai);

            if (!$timeBaru || !$timePenanganan || !$timeSelesai) {
                $stats['total_insiden']--;
                continue;
            }

            // 1. Kalkulasi SLA Respon (Baru -> Penanganan)
            $stats['response_count']++;
            // Sisipkan $holidays di sini
            $actualResponseHours = $this->calculateBusinessHours($timeBaru, $timePenanganan, $holidays);
            $stats['sum_response_hours'] += $actualResponseHours;
            $responseMet = ($actualResponseHours <= 1);
            if ($responseMet)
                $stats['response_met']++;

            // 2. Kalkulasi SLA Resolusi (Penanganan -> Selesai)
            $stats['resolution_count']++;
            // Sisipkan $holidays di sini
            $actualResolutionHours = $this->calculateBusinessHours($timePenanganan, $timeSelesai, $holidays);
            $stats['sum_resolution_hours'] += $actualResolutionHours;
            $resolutionMet = ($actualResolutionHours <= 8);
            if ($resolutionMet)
                $stats['resolution_met']++;

            $listInsiden[] = [
                'id' => $insiden->id,
                'laporan' => $insiden->deskripsi,
                'sla_resolution_met' => $resolutionMet,
                'actual_resolution_hours' => $actualResolutionHours,
                'sla_response_met' => $responseMet,
                'actual_response_hours' => $actualResponseHours,
                'responder' => $handler->nama_lengkap ?? 'N/A',
            ];
        }

        // 3. Finalisasi KPI
        $total = $stats['total_insiden'];
        $kpi = [
            'sla_response_compliance' => ($total > 0) ? ($stats['response_met'] / $total) * 100 : 0,
            'sla_resolution_compliance' => ($total > 0) ? ($stats['resolution_met'] / $total) * 100 : 0,
            'avg_response_time' => ($stats['response_count'] > 0) ? $stats['sum_response_hours'] / $stats['response_count'] : 0,
            'avg_resolution_time' => ($stats['resolution_count'] > 0) ? $stats['sum_resolution_hours'] / $stats['resolution_count'] : 0,
            'total_insiden' => $total,
            'total_tidak_ditangani' => $stats['total_tidak_ditangani'],
            'filters' => $dateRange['filters'],
            'total_responded' => $stats['response_count'],
            'total_resolved' => $stats['resolution_count'],
        ];

        return response()->json(['kpi' => $kpi, 'details' => $listInsiden]);
    }

    // =========================================================================
    // FUNGSI UTAMA 4: DASHBOARD SLA EVENT / WEBINAR (BARU)
    // =========================================================================
    public function dashboardEventSla(Request $request, $mappingId)
    {
        // 1. Ambil Data Event Utama (D-Day)
        $mapping = YearMapping::with('eventDetail')->findOrFail($mappingId);
        $dDay = Carbon::parse($mapping->planned_date, $this->timezone)->startOfDay();

        // 2. Ambil Checklist Aktual dari Database (EventTodo + Todo)
        $eventTodos = EventTodo::with('todo')
            ->where('year_mapping_id', $mappingId)
            ->get()
            ->keyBy(function ($item) {
                // Key array berdasarkan nama task agar mudah dicocokkan dengan Rules
                return $item->todo->task_name;
            });

        // 3. Ambil Aturan SLA
        $slaRules = $this->getSlaRules();

        // 4. Proses Kalkulasi
        $stats = [
            'total_tasks' => 0,
            'completed_tasks' => 0,
            'on_time' => 0,
            'late' => 0,
            'overdue' => 0,
        ];

        $details = [];

        // Loop berdasarkan ATURAN, bukan berdasarkan data transaksi.
        // Agar item SLA tetap muncul di dashboard meskipun belum ada di checklist user.
        foreach ($slaRules as $taskName => $rule) {
            $stats['total_tasks']++;

            // Hitung Target Date
            $targetDate = $dDay->copy()->addDays($rule['offset'])->endOfDay();

            // Cek Realisasi (Apakah user sudah punya checklist ini?)
            $actualItem = $eventTodos->get($taskName);

            $status = 'Pending';
            $isDone = false;
            $actualDateStr = '-';
            $actualDateObj = null;
            $pic = '-';

            if ($actualItem) {
                $pic = $actualItem->pic ?? '-'; // Ambil PIC dari EventTodo

                if ($actualItem->is_checked) {
                    $isDone = true;
                    // Gunakan updated_at sebagai waktu penyelesaian
                    $actualDateObj = Carbon::parse($actualItem->updated_at, $this->timezone);
                    $actualDateStr = $actualDateObj->format('d M Y');
                    $stats['completed_tasks']++;

                    // Logika Status: On Time vs Late
                    if ($actualDateObj->lte($targetDate)) {
                        $status = 'On Time';
                        $stats['on_time']++;
                    } else {
                        $status = 'Late';
                        $stats['late']++;
                    }
                } else {
                    // Belum diceklis, cek deadline
                    if (Carbon::now($this->timezone)->gt($targetDate)) {
                        $status = 'Overdue';
                        $stats['overdue']++;
                    } else {
                        $status = 'On Progress';
                    }
                }
            } else {
                // Item ada di Rules tapi tidak ada di EventTodo (Mungkin Todo Master dihapus/ubah)
                $status = 'Not Found'; // Atau anggap Overdue/Pending
            }

            // Grouping data untuk View JSON
            $details[$rule['stage']][] = [
                'activity' => $taskName,
                'pic' => $pic,
                'sla_label' => $rule['desc'], // Label teks H-Min
                'target_date' => $targetDate->format('d M Y'),
                'actual_date' => $actualDateStr,
                'status' => $status,
                // Hitung selisih hari (opsional untuk sorting/indikator)
                'days_diff' => $isDone ? $actualDateObj->diffInDays($targetDate, false) : 0
            ];
        }

        // 5. Finalisasi KPI Percentage
        $total = $stats['total_tasks'];
        $kpi = [
            'completion_rate' => ($total > 0) ? ($stats['completed_tasks'] / $total) * 100 : 0,
            'sla_compliance' => ($stats['completed_tasks'] > 0) ? ($stats['on_time'] / $stats['completed_tasks']) * 100 : 0,
            'total_late' => $stats['late'],
            'total_overdue' => $stats['overdue'],
            'event_title' => $mapping->eventDetail->title ?? 'Judul Belum Diset',
            'event_date' => $dDay->format('d M Y')
        ];

        return response()->json([
            'kpi' => $kpi,
            'details' => $details
        ]);
    }

    public function overallEventSla(Request $request)
    {
        $mappings = \App\Models\YearMapping::with('eventDetail')->where('year', date('Y'))->orderBy('month')->get();

        $stats = [
            'total_tasks' => 0,
            'completed_tasks' => 0,
            'on_time' => 0,
            'late' => 0,
            'overdue' => 0,
        ];

        $slaRules = $this->getSlaRules();
        $eventList = [];

        foreach ($mappings as $mapping) {
            $mappingId = $mapping->id;
            $dDay = \Carbon\Carbon::parse($mapping->planned_date, $this->timezone)->startOfDay();

            $eventTodos = \App\Models\EventTodo::with('todo')
                ->where('year_mapping_id', $mappingId)
                ->get()
                ->keyBy(function ($item) {
                    return $item->todo->task_name;
                });

            $eventStats = [
                'total_tasks' => 0,
                'completed_tasks' => 0,
                'on_time' => 0,
                'late' => 0,
                'overdue' => 0,
            ];

            foreach ($slaRules as $taskName => $rule) {
                $eventStats['total_tasks']++;
                $targetDate = $dDay->copy()->addDays($rule['offset'])->endOfDay();
                $actualItem = $eventTodos->get($taskName);

                if ($actualItem) {
                    if ($actualItem->is_checked) {
                        $eventStats['completed_tasks']++;
                        $actualDateObj = \Carbon\Carbon::parse($actualItem->updated_at, $this->timezone);
                        if ($actualDateObj->lte($targetDate)) {
                            $eventStats['on_time']++;
                        } else {
                            $eventStats['late']++;
                        }
                    } else {
                        if (\Carbon\Carbon::now($this->timezone)->gt($targetDate)) {
                            $eventStats['overdue']++;
                        }
                    }
                }
            }

            $evtTotal = $eventStats['total_tasks'];
            $evtCompRate = ($evtTotal > 0) ? ($eventStats['completed_tasks'] / $evtTotal) * 100 : 0;
            $evtSlaComp = ($eventStats['completed_tasks'] > 0) ? ($eventStats['on_time'] / $eventStats['completed_tasks']) * 100 : 0;

            // Add to overall stats
            $stats['total_tasks'] += $eventStats['total_tasks'];
            $stats['completed_tasks'] += $eventStats['completed_tasks'];
            $stats['on_time'] += $eventStats['on_time'];
            $stats['late'] += $eventStats['late'];
            $stats['overdue'] += $eventStats['overdue'];

            $eventList[] = [
                'id' => $mapping->id,
                'month_name' => \Carbon\Carbon::createFromDate(null, $mapping->month)->translatedFormat('F'),
                'theme' => $mapping->theme ?? 'Tema Belum Set',
                'event_title' => $mapping->eventDetail->title ?? 'Belum Diset',
                'planned_date' => $dDay->format('d M Y'),
                'completion_rate' => $evtCompRate,
                'sla_compliance' => $evtSlaComp,
                'total_late' => $eventStats['late'],
                'total_overdue' => $eventStats['overdue'],
            ];
        }

        $total = $stats['total_tasks'];
        $kpi = [
            'completion_rate' => ($total > 0) ? ($stats['completed_tasks'] / $total) * 100 : 0,
            'sla_compliance' => ($stats['completed_tasks'] > 0) ? ($stats['on_time'] / $stats['completed_tasks']) * 100 : 0,
            'total_late' => $stats['late'],
            'total_overdue' => $stats['overdue'],
            'event_title' => 'Semua Event Webinar ' . date('Y'),
            'event_date' => date('Y')
        ];

        return response()->json([
            'kpi' => $kpi,
            'events' => $eventList
        ]);
    }

    public function dashboardDigital(Request $request)
    {
        $dateRange = $this->validateAndParseDates($request);

        $keperluan = '%Tim Digital%';

        // --- BAGIAN A: SLA TICKETING (Tim Digital) ---
        // 1. Ambil Data Tiket
        $rawTickets = DB::table('tickets')
            ->select(
                'id',
                'created_at',
                'kategori',
                'tingkat_kesulitan',
                'tanggal_response',
                'jam_response',
                'tanggal_selesai',
                'jam_selesai'
            )
            ->where('keperluan', 'LIKE', $keperluan)
            ->whereNotNull('tanggal_selesai')
            ->whereBetween('created_at', [$dateRange['startDate'], $dateRange['endDate']])
            ->get();

        // 2. Kalkulasi SLA Tiket (Menggunakan Helper Existing)
        $ticketStats = $this->processTicketSla($rawTickets);

        // --- BAGIAN B: SLA KONTEN (Target 3 Upload/Minggu) ---
        $contentStats = [
            'total_weeks' => 0,
            'weeks_met' => 0,
            'weeks_missed' => 0,
            'total_content' => 0,
            'weekly_details' => []
        ];

        $holidays = $this->getHolidays(); // Panggil daftar hari libur

        $uploadCount = 0;

        // 1. Mulai iterasi tepat dari hari Senin di sekitar startDate
        $currentDate = $dateRange['startDate']->copy()->startOfWeek();

        // Lakukan looping selama tanggal awal minggu (Senin) masih berada di dalam atau sama dengan bulan/semester filter
        while ($currentDate <= $dateRange['endDate']) {
            $startOfWeek = $currentDate->copy();
            $endOfWeek = $currentDate->copy()->endOfWeek(); // Tetap hari Minggu (utuh)

            if ($startOfWeek < $dateRange['startDate']->copy()->startOfDay()) {
                $currentDate->addWeek();
                continue;
            }

            // TIDAK ADA LAGI PEMOTONGAN TANGGAL.
            // endOfWeek dibiarkan utuh melewati bulan sekalipun (misal: 28 Sep - 04 Oct).

            $contentStats['total_weeks']++;

            // --- Logika Hitung Hari Aktif & Target Dinamis ---
            $activeWorkingDays = 0;
            $tempDate = $startOfWeek->copy();

            // Hitung ada berapa hari kerja nyata di periode 1 minggu utuh ini
            while ($tempDate <= $endOfWeek) {
                if ($this->isWorkingDay($tempDate, $holidays)) {
                    $activeWorkingDays++;
                }
                $tempDate->addDay();
            }

            // Jika range-nya hanya akhir pekan/libur panjang, lewati minggu ini
            if ($activeWorkingDays == 0) {
                $contentStats['total_weeks']--;
                $currentDate->addWeek();
                continue;
            }

            // Hitung Target Proporsional
            $dynamicTarget = (int) round((3 / 5) * $activeWorkingDays);
            if ($dynamicTarget < 1 && $activeWorkingDays > 0) {
                $dynamicTarget = 1;
            }
            // ----------------------------------------------------------------

            // PERBAIKAN: Gunakan format jam 00:00:00 s/d 23:59:59 untuk akurasi Datetime
            $startDateString = $startOfWeek->copy()->startOfDay()->format('Y-m-d H:i:s');
            $endDateString = $endOfWeek->copy()->endOfDay()->format('Y-m-d H:i:s');

            // Query murni menggunakan Between (tanpa intervensi whereMonth)
            // sehingga jika $endDateString tembus ke bulan depan, tetap terbaca utuh.
            $uploadCount = ContentSchedule::whereBetween('upload_date', [$startDateString, $endDateString])->count();

            $contentStats['total_content'] += $uploadCount;

            $isMet = $uploadCount >= $dynamicTarget;

            if ($isMet) {
                $contentStats['weeks_met']++;
            } else {
                $contentStats['weeks_missed']++;
            }

            $contentStats['weekly_details'][] = [
                'week_range' => $startOfWeek->format('d M') . ' - ' . $endOfWeek->format('d M Y'),
                'count' => $uploadCount,
                'status' => $isMet ? 'Met' : 'Missed',
                'target' => $dynamicTarget,
                'active_days' => $activeWorkingDays
            ];

            // Lanjut bergeser ke hari Senin berikutnya
            $currentDate->addWeek();
        }

        $totalTickets = $ticketStats['total_tickets'];

        $kpi = [
            // KPI Ticketing
            'ticket_response_compliance' => ($totalTickets > 0) ? ($ticketStats['response_met'] / $totalTickets) * 100 : 0,
            'ticket_resolution_compliance' => ($totalTickets > 0) ? ($ticketStats['resolution_met'] / $totalTickets) * 100 : 0,
            'avg_resolution_time' => ($totalTickets > 0) ? $ticketStats['sum_resolution_hours'] / $totalTickets : 0,
            'total_tickets' => $totalTickets,

            // KPI Konten
            'content_sla_compliance' => ($contentStats['total_weeks'] > 0) ? ($contentStats['weeks_met'] / $contentStats['total_weeks']) * 100 : 0,
            'total_content_uploaded' => $contentStats['total_content'],
            'total_weeks_evaluated' => $contentStats['total_weeks'],
            'weeks_met' => $contentStats['weeks_met'],

            // Filter Info
            'filters' => $dateRange['filters']
        ];

        return response()->json([
            'kpi' => $kpi,
            'content_details' => $contentStats['weekly_details']
        ]);
    }

    private function getTrackingTimestamp($trackingEvent)
    {
        if (!$trackingEvent) {
            return null;
        }

        try {
            // Prioritas 1: Gunakan field tanggal & waktu eksplisit
            if (!empty($trackingEvent->tanggal_response) && !empty($trackingEvent->waktu_response)) {
                return Carbon::parse($trackingEvent->tanggal_response . ' ' . $trackingEvent->waktu_response, $this->timezone);
            }

            // Prioritas 2: Fallback ke created_at
            if (!empty($trackingEvent->created_at)) {
                return Carbon::parse($trackingEvent->created_at, $this->timezone);
            }

            return null;
        } catch (\Exception $e) {
            // Tangani jika parsing gagal
            return null;
        }
    }

    private function processTicketSla($tickets)
    {
        // 1. Ambil daftar hari libur di sini
        $holidays = $this->getHolidays();

        $stats = [
            'total_tickets' => 0,
            'response_count' => 0,
            'response_met' => 0,
            'resolution_met' => 0,
            'sum_response_hours' => 0,
            'sum_resolution_hours' => 0,
            'priority_count' => ['High' => 0, 'High-Extended' => 0, 'Medium' => 0, 'Low' => 0, 'Other' => 0],
        ];

        foreach ($tickets as $ticket) {
            $stats['total_tickets']++;

            // Tentukan Prioritas
            $priority = 'Other';

            // Standarisasi string (sudah ada di kode Anda sebelumnya)
            $tingkatKesulitan = strtolower(trim($ticket->tingkat_kesulitan ?? ''));
            $kategori = strtolower(trim($ticket->kategori ?? ''));

            // Blok logika yang baru
            if ($kategori === 'error (aplikasi)' && $tingkatKesulitan === 'major') {
                $priority = 'High-Extended'; // Prioritas khusus 1 minggu
            } elseif (in_array($tingkatKesulitan, ['major', 'moderate'])) {
                $priority = 'High';
            } elseif ($kategori === 'request') {
                $priority = 'Low';
            } elseif (in_array($tingkatKesulitan, ['minor', 'normal', '']) || $kategori === 'error (aplikasi)') {
                $priority = 'Medium';
            }

            $stats['priority_count'][$priority]++;

            // Parsing Waktu
            $start = Carbon::parse($ticket->created_at, $this->timezone);

            // Asumsi 'tanggal_selesai' & 'jam_selesai' PASTI ada (karena query)
            $resolution = Carbon::parse($ticket->tanggal_selesai . ' ' . $ticket->jam_selesai, $this->timezone);

            // Hitung Jam Kerja Aktual Resolusi dengan parameter hari libur
            $actualResolutionHours = $this->calculateBusinessHours($start, $resolution, $holidays);
            $stats['sum_resolution_hours'] += $actualResolutionHours;

            if (!empty($ticket->tanggal_response) && !empty($ticket->jam_response)) {
                $stats['response_count']++;
                $response = Carbon::parse($ticket->tanggal_response . ' ' . $ticket->jam_response, $this->timezone);
                $actualResponseHours = $this->calculateBusinessHours($start, $response, $holidays);
                $stats['sum_response_hours'] += $actualResponseHours;

                if (
                    (in_array($priority, ['High', 'High-Extended']) && $actualResponseHours <= 4) ||
                    ($priority == 'Medium' && $actualResponseHours <= 8) ||
                    ($priority == 'Low' && $actualResponseHours <= 16)
                ) {
                    $stats['response_met']++;
                }
            }

            if (
                ($priority == 'High' && $actualResolutionHours <= 24) ||
                ($priority == 'High-Extended' && $actualResolutionHours <= 45) ||
                ($priority == 'Medium' && $actualResolutionHours <= 40) ||
                ($priority == 'Low')
            )
            {
                $stats['resolution_met']++;
            }
        }
        return $stats;
    }

    private function calculateBusinessHours(Carbon $start, Carbon $end, $holidays = [])
    {
        if ($end <= $start) return 0;

        $totalBusinessMinutes = 0;
        $current = $start->copy();

        // 1. Tangani Hari Pertama
        if ($this->isWorkingDay($current, $holidays)) { // <-- Ganti pengecekan hari pertama
            $dayStart = $current->copy()->hour($this->businessStartHour)->minute(0)->second(0);
            $dayEnd = $current->copy()->hour($this->businessEndHour)->minute(0)->second(0);

            if ($current < $dayStart) $current = $dayStart;
            $endOfFirstDay = ($end < $dayEnd) ? $end : $dayEnd;

            if ($current < $endOfFirstDay && $current->hour < $this->businessEndHour) {
                $totalBusinessMinutes += $current->diffInMinutes($endOfFirstDay);
            }
        }

        $current->addDay()->startOfDay();

        // 2. Tangani Hari-Hari Penuh (Full Days)
        while ($current < $end->copy()->startOfDay()) {
            if ($this->isWorkingDay($current, $holidays)) { // <-- Ganti isWeekday()
                $totalBusinessMinutes += ($this->businessEndHour - $this->businessStartHour) * 60;
            }
            $current->addDay();
        }

        // 3. Tangani Hari Terakhir (Partial)
        if ($this->isWorkingDay($end, $holidays)) { // <-- Ganti isWeekday()
            $dayStart = $end->copy()->hour($this->businessStartHour)->minute(0)->second(0);
            $dayEnd = $end->copy()->hour($this->businessEndHour)->minute(0)->second(0);
            $startOfLastDay = $dayStart;
            $endOfLastDay = ($end < $dayEnd) ? $end : $dayEnd;

            if ($startOfLastDay < $endOfLastDay && $endOfLastDay->hour >= $this->businessStartHour) {
                $totalBusinessMinutes += $startOfLastDay->diffInMinutes($endOfLastDay);
            }
        }

        // 4. Koreksi di Hari yang Sama
        if ($start->isSameDay($end)) {
            $dayStart = $start->copy()->hour($this->businessStartHour)->minute(0)->second(0);
            $dayEnd = $start->copy()->hour($this->businessEndHour)->minute(0)->second(0);
            $calcStart = ($start < $dayStart) ? $dayStart : $start;
            $calcEnd = ($end > $dayEnd) ? $dayEnd : $end;

            if ($calcStart < $calcEnd && $this->isWorkingDay($calcStart, $holidays) && $calcStart->hour < $this->businessEndHour && $calcEnd->hour >= $this->businessStartHour) { // <-- Ganti isWeekday()
                return $calcStart->diffInMinutes($calcEnd) / 60.0;
            } else {
                return 0;
            }
        }

        return $totalBusinessMinutes / 60.0;
    }

    public function index()
    {
        if (!auth()->user()->karyawan || (auth()->user()->karyawan->divisi !== 'IT Service Management' && !auth()->user()->hasAnyRole(['Koordinator ITSM', 'Programmer', 'Technical Support', 'Tim Digital', 'Super Admin']))) {
            abort(403, 'Unauthorized action.');
        }
        return view('itsm.sla');
    }
}
