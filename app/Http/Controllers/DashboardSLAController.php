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
use App\Models\User; // <-- Pastikan namespace model User Anda benar

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

    // =========================================================================
    // FUNGSI UTAMA 2: DASHBOARD PER USER (GABUNGAN)
    // =========================================================================
    // Terima $team dari rute
    public function dashboardUser(Request $request, $team)
    {
        $dateRange = $this->validateAndParseDates($request);

        // --- KONDISI DINAMIS ---
        // 1. Definisikan SEMUA mapping
        $allPicMaps = [
            'programmer' => [
                'ardhan' => 'Ardhan',
                'donna' => 'Donna',
                'juliet' => 'Juli',
                'stepanusberkatsinaga' => 'Stefan',
                'sergiomosesriyanto' => 'Sergio',
                'vickyryandysaputra' => 'Vicky',

            ],
            'tech-support' => [
                'eggiherlambang' => 'Eggi',
                'naufal' => 'Naufal',
                'ferdi' => 'Ferdi',
                'ardhan' => 'Ardhan',
            ]
        ];

        // Pilih mapping dan keperluan berdasarkan $team
        $userToPicMap = $allPicMaps[$team] ?? [];
        $keperluan = ($team === 'programmer') ? '%Programming%' : '%Technical Support%';
        // -----------------------

        $allUsernames = array_keys($userToPicMap);

        // 2. Ambil data User
        $users = User::whereIn('username', $allUsernames)->with('karyawan')->get();

        // 3. Buat mapping (Username -> Nama Lengkap)
        $usernameToNamaLengkapMap = [];
        foreach ($users as $user) {
            $usernameToNamaLengkapMap[$user->username] = $user->karyawan->nama_lengkap ?? ($user->name ?? $user->username);
        }

        // 4. Buat mapping final: (PIC (lowercase) -> Nama Lengkap)
        $picLookupMap = [];
        foreach ($userToPicMap as $username => $picName) {
            $displayName = $usernameToNamaLengkapMap[$username] ?? $picName;
            $picLookupMap[strtolower($picName)] = $displayName;
        }

        // 5. Ambil Data Mentah
        $picNamesForQuery = array_values($userToPicMap);
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
            ->whereIn('pic', $picNamesForQuery)
            ->whereNotNull('tanggal_selesai')
            ->where('keperluan', 'LIKE', $keperluan) // <-- Gunakan variabel dinamis
            ->whereBetween('created_at', [$dateRange['startDate'], $dateRange['endDate']])
            ->get();

        // 6. Kelompokkan tiket per user
        $ticketsByUser = [];
        foreach ($rawTickets as $ticket) {
            $picKey = strtolower($ticket->pic);
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
                // Key 'nama_programmer' kita biarkan, JS sudah menanganinya
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

    // =========================================================================
    // FUNGSI UTAMA 3: DASHBOARD INSIDEN KRITIS (GABUNGAN)
    // =========================================================================
    // Terima $team dari rute
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
            $isTargetRole = $handler && $handler->jabatan === $jabatan; // <-- Gunakan variabel dinamis
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
            $actualResponseHours = $this->calculateBusinessHours($timeBaru, $timePenanganan);
            $stats['sum_response_hours'] += $actualResponseHours;
            $responseMet = ($actualResponseHours <= 1);
            if ($responseMet)
                $stats['response_met']++;

            // 2. Kalkulasi SLA Resolusi (Penanganan -> Selesai)
            $stats['resolution_count']++;
            $actualResolutionHours = $this->calculateBusinessHours($timePenanganan, $timeSelesai);
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

    // =========================================================================
    // FUNGSI UTAMA 5: DASHBOARD TIM DIGITAL (TICKETING + KONTEN)
    // =========================================================================
    public function dashboardDigital(Request $request)
    {
        $dateRange = $this->validateAndParseDates($request);

        // --- BAGIAN A: SLA TICKETING (Tim Digital) ---
        $keperluan = '%Tim Digital%';

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

        // 1. Generate Periode Mingguan dalam Semester Terpilih
        $period = CarbonPeriod::create($dateRange['startDate'], '1 week', $dateRange['endDate']);

        foreach ($period as $date) {
            $startOfWeek = $date->copy()->startOfWeek();
            $endOfWeek = $date->copy()->endOfWeek();

            // Batasi agar tidak melebihi range filter (jika filter parsial)
            if ($startOfWeek < $dateRange['startDate'])
                $startOfWeek = $dateRange['startDate'];
            if ($endOfWeek > $dateRange['endDate'])
                $endOfWeek = $dateRange['endDate'];

            // Skip jika start > end (edge case akhir periode)
            if ($startOfWeek > $endOfWeek)
                continue;

            $contentStats['total_weeks']++;

            // 2. Hitung Upload pada Minggu Tersebut
            $uploadCount = ContentSchedule::whereBetween('upload_date', [$startOfWeek, $endOfWeek])
                ->count();

            $contentStats['total_content'] += $uploadCount;

            // 3. Cek Target (Minimal 3)
            $isMet = $uploadCount >= 3;
            if ($isMet) {
                $contentStats['weeks_met']++;
            } else {
                $contentStats['weeks_missed']++;
            }

            $contentStats['weekly_details'][] = [
                'week_range' => $startOfWeek->format('d M') . ' - ' . $endOfWeek->format('d M Y'),
                'count' => $uploadCount,
                'status' => $isMet ? 'Met' : 'Missed',
                'target' => 3
            ];
        }

        // --- BAGIAN C: FINALISASI KPI GABUNGAN ---
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

    // =========================================================================
    // HELPER 2: PROSESOR SLA TIKET (HIGH, MEDIUM, LOW)
    // =========================================================================
    // (Fungsi ini 100% dapat digunakan kembali, tidak perlu diubah)
    private function processTicketSla($tickets)
    {
        $stats = [
            'total_tickets' => 0,
            'response_count' => 0, // Jumlah tiket yg punya data respon
            'response_met' => 0,
            'resolution_met' => 0,
            'sum_response_hours' => 0,
            'sum_resolution_hours' => 0,
            'priority_count' => ['High' => 0, 'Medium' => 0, 'Low' => 0, 'Other' => 0],
        ];

        foreach ($tickets as $ticket) {
            $stats['total_tickets']++;

            // Tentukan Prioritas
            $priority = 'Other';

            // Standarisasi string dari database untuk akurasi komparasi
            $tingkatKesulitan = strtolower(trim($ticket->tingkat_kesulitan ?? ''));
            $kategori = strtolower(trim($ticket->kategori ?? ''));

            if (in_array($tingkatKesulitan, ['major', 'moderate'])) {
                $priority = 'High';
            } elseif ($kategori == 'request') {
                $priority = 'Low';
            } elseif (in_array($tingkatKesulitan, ['minor', 'normal', '']) || $kategori == 'error (aplikasi)') {
                $priority = 'Medium';
            }

            $stats['priority_count'][$priority]++;

            // Parsing Waktu
            $start = Carbon::parse($ticket->created_at, $this->timezone);

            // Asumsi 'tanggal_selesai' & 'jam_selesai' PASTI ada (karena query)
            $resolution = Carbon::parse($ticket->tanggal_selesai . ' ' . $ticket->jam_selesai, $this->timezone);

            // Hitung Jam Kerja Aktual Resolusi
            $actualResolutionHours = $this->calculateBusinessHours($start, $resolution);
            $stats['sum_resolution_hours'] += $actualResolutionHours;

            // Hitung Jam Kerja Aktual Respon (JIKA ADA)
            if (!empty($ticket->tanggal_response) && !empty($ticket->jam_response)) {
                $stats['response_count']++;
                $response = Carbon::parse($ticket->tanggal_response . ' ' . $ticket->jam_response, $this->timezone);
                $actualResponseHours = $this->calculateBusinessHours($start, $response);
                $stats['sum_response_hours'] += $actualResponseHours;

                // Cek Kepatuhan SLA Response
                if (
                    ($priority == 'High' && $actualResponseHours <= 4) ||
                    ($priority == 'Medium' && $actualResponseHours <= 8) ||
                    ($priority == 'Low' && $actualResponseHours <= 16)
                ) {
                    $stats['response_met']++;
                }
            }

            // Cek Kepatuhan SLA Resolusi
            if (
                ($priority == 'High' && $actualResolutionHours <= 24) ||
                ($priority == 'Medium' && $actualResolutionHours <= 40) ||
                ($priority == 'Low')
            ) // Low/Request dianggap 'met'
            {
                $stats['resolution_met']++;
            }
        }
        return $stats;
    }

    private function calculateBusinessHours(Carbon $start, Carbon $end)
    {
        // Jika waktu selesai sebelum mulai, return 0
        if ($end <= $start) {
            return 0;
        }

        $totalBusinessMinutes = 0;
        $current = $start->copy();

        // 1. Tangani Hari Pertama (Partial)
        if ($current->dayOfWeek !== Carbon::SATURDAY && $current->dayOfWeek !== Carbon::SUNDAY) {
            // Tentukan jam mulai kalkulasi pada hari pertama
            $dayStart = $current->copy()->hour($this->businessStartHour)->minute(0)->second(0);
            // Tentukan jam selesai kalkulasi pada hari pertama
            $dayEnd = $current->copy()->hour($this->businessEndHour)->minute(0)->second(0);

            if ($current < $dayStart) {
                // Jika tiket dibuat sebelum jam kerja, mulai hitung dari jam kerja
                $current = $dayStart;
            }

            // Tentukan batas akhir perhitungan di hari pertama
            $endOfFirstDay = ($end < $dayEnd) ? $end : $dayEnd;

            if ($current < $endOfFirstDay) {
                if ($current->hour < $this->businessEndHour) {
                    $totalBusinessMinutes += $current->diffInMinutes($endOfFirstDay);
                }
            }
        }

        // Pindahkan 'current' ke awal hari berikutnya
        $current->addDay()->startOfDay();

        // 2. Tangani Hari-Hari Penuh (Full Days)
        while ($current < $end->copy()->startOfDay()) {
            if ($current->isWeekday()) {
                $totalBusinessMinutes += ($this->businessEndHour - $this->businessStartHour) * 60;
            }
            $current->addDay();
        }

        // 3. Tangani Hari Terakhir (Partial)
        if ($end->isWeekday()) {
            $dayStart = $end->copy()->hour($this->businessStartHour)->minute(0)->second(0);
            $dayEnd = $end->copy()->hour($this->businessEndHour)->minute(0)->second(0);
            $startOfLastDay = $dayStart;
            $endOfLastDay = ($end < $dayEnd) ? $end : $dayEnd;

            if ($startOfLastDay < $endOfLastDay) {
                if ($endOfLastDay->hour >= $this->businessStartHour) {
                    $totalBusinessMinutes += $startOfLastDay->diffInMinutes($endOfLastDay);
                }
            }
        }

        // Koreksi untuk kasus di mana start dan end di hari yang sama
        if ($start->isSameDay($end)) {
            $dayStart = $start->copy()->hour($this->businessStartHour)->minute(0)->second(0);
            $dayEnd = $start->copy()->hour($this->businessEndHour)->minute(0)->second(0);
            $calcStart = ($start < $dayStart) ? $dayStart : $start;
            $calcEnd = ($end > $dayEnd) ? $dayEnd : $end;
            if ($calcStart < $calcEnd && $calcStart->isWeekday() && $calcStart->hour < $this->businessEndHour && $calcEnd->hour >= $this->businessStartHour) {
                return $calcStart->diffInMinutes($calcEnd) / 60.0;
            } else {
                return 0;
            }
        }

        // Kembalikan dalam jam (float)
        return $totalBusinessMinutes / 60.0;
    }
}
