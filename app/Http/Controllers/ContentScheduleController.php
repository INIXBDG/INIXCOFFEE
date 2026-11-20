<?php

namespace App\Http\Controllers;

use App\Models\ContentSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContentScheduleController extends Controller
{
    /**
     * Kumpulan default talent.
     */
    private function getAllUniqueTalents()
    {
        $defaultTalents = [
            'Hera', 'Savanna', 'Reni', 'Rara', 'Alfi', 'Nabila', 'Fia', 'Ani', 'Yanuar',
            'Adit', 'Luki', 'Sabdhan', 'Rustan', 'Wahyu', 'Sahrul', 'Pani', 'Yayat',
            'Stepan', 'Vicky', 'Sergio', 'Donna', 'Eggi', 'Ardhan', 'Julie', 'Ferdi',
            'Aulia', 'Alysia', 'Xepi', 'Rifa'
        ];

        $dbTalents = ContentSchedule::pluck('talents')->filter()->toArray();
        $allTalents = $defaultTalents;

        foreach ($dbTalents as $talentString) {
            $talentsArray = explode(',', $talentString);
            foreach ($talentsArray as $t) {
                $cleanTalent = trim($t);
                if (!empty($cleanTalent)) {
                    $allTalents[] = $cleanTalent;
                }
            }
        }

        $uniqueTalents = array_unique($allTalents);
        sort($uniqueTalents);

        return $uniqueTalents;
    }

    /**
     * Tampilan utama: Tabel Jadwal Konten Kreatif.
     */
    public function index()
    {
        $schedules = ContentSchedule::orderByRaw('upload_date IS NULL DESC, upload_date DESC')->get();
        $uniqueTalents = $this->getAllUniqueTalents();

        return view('schedules.index', compact('schedules', 'uniqueTalents'));
    }

    /**
     * Halaman tersendiri untuk Dashboard & Analitik Konten Harian.
     */
    public function dashboard(Request $request)
    {
        $selectedYear  = $request->input('year', date('Y'));
        $selectedMonth = $request->input('month', 'all');   // 'all' | '01'..'12'
        $selectedWeek  = $request->input('week', 'all');    // 'all' | 1..53 (ISO week)
        $periodMode    = $request->input('period', 'yearly'); // 'yearly' | 'monthly' | 'weekly'

        $schedules = ContentSchedule::orderByRaw('upload_date IS NULL DESC, upload_date DESC')->get();

        $totalContent     = $schedules->count();
        $uploadedSchedules = $schedules->whereNotNull('upload_date');
        $uploadedCount    = $uploadedSchedules->count();
        $pendingSchedules = $schedules->whereNull('upload_date');
        $pendingCount     = $pendingSchedules->count();
        $completionRate   = $totalContent > 0 ? round(($uploadedCount / $totalContent) * 100, 1) : 0;

        $thisMonthCount = $uploadedSchedules->filter(function ($item) {
            return $item->upload_date && $item->upload_date->format('Y-m') === now()->format('Y-m');
        })->count();

        $tiktokCount = $schedules->filter(function ($item) {
            return $item->is_tiktok || strtolower($item->content_form) === 'tiktok';
        })->count();

        $tiktokPercentage = $totalContent > 0 ? round(($tiktokCount / $totalContent) * 100, 1) : 0;

        // Tahun yang tersedia dari riwayat jadwal
        $availableYears = $uploadedSchedules
            ->map(fn($item) => $item->upload_date ? $item->upload_date->format('Y') : null)
            ->filter()->unique()->sortDesc()->values()->toArray();

        if (empty($availableYears)) {
            $availableYears = [date('Y')];
        }

        // Validasi year
        if ($selectedYear !== 'all' && !in_array($selectedYear, $availableYears)) {
            $selectedYear = $availableYears[0];
        }

        // Bulan yang tersedia berdasar year terpilih
        $availableMonths = [];
        $monthNamesMap   = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
                             '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
                             '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
        foreach ($uploadedSchedules as $item) {
            if (!$item->upload_date) continue;
            if ($selectedYear !== 'all' && $item->upload_date->format('Y') != $selectedYear) continue;
            $mk = $item->upload_date->format('m');
            if (!isset($availableMonths[$mk])) {
                $availableMonths[$mk] = $monthNamesMap[$mk] ?? $mk;
            }
        }
        ksort($availableMonths);
        if ($selectedMonth !== 'all' && !isset($availableMonths[$selectedMonth])) {
            $selectedMonth = 'all';
        }

        // Minggu yang tersedia berdasar year & month terpilih
        $availableWeeks = [];
        foreach ($uploadedSchedules as $item) {
            if (!$item->upload_date) continue;
            if ($selectedYear !== 'all' && $item->upload_date->format('Y') != $selectedYear) continue;
            if ($selectedMonth !== 'all' && $item->upload_date->format('m') !== $selectedMonth) continue;
            $wk = (int) $item->upload_date->format('W');
            if (!isset($availableWeeks[$wk])) {
                $start = \Carbon\Carbon::now()->setISODate($item->upload_date->format('Y'), $wk)->startOfWeek();
                $end   = $start->copy()->endOfWeek();
                $availableWeeks[$wk] = 'Minggu ke-'.$wk.' ('.$start->format('d M').' – '.$end->format('d M').')';
            }
        }
        ksort($availableWeeks);
        if ($selectedWeek !== 'all' && !isset($availableWeeks[(int)$selectedWeek])) {
            $selectedWeek = 'all';
        }

        // ── Filter uploaded berdasarkan periode ──────────────────────────────
        $filteredUploaded = $uploadedSchedules->filter(function ($item) use ($selectedYear, $selectedMonth, $selectedWeek) {
            if (!$item->upload_date) return false;
            if ($selectedYear !== 'all'  && $item->upload_date->format('Y') != $selectedYear)  return false;
            if ($selectedMonth !== 'all' && $item->upload_date->format('m') !== $selectedMonth) return false;
            if ($selectedWeek !== 'all'  && (int)$item->upload_date->format('W') !== (int)$selectedWeek) return false;
            return true;
        });

        // Distribusi bentuk konten (dari seluruh data, bukan filtered)
        $allForms      = ['Feed', 'Reels', 'Tiktok', 'Story', 'Youtube'];
        $formStats     = [];
        $formChartLabels = [];
        $formChartData   = [];
        $formColors    = [
            'Feed'    => '#198754',
            'Reels'   => '#ffc107',
            'Tiktok'  => '#212529',
            'Story'   => '#0dcaf0',
            'Youtube' => '#dc3545',
        ];

        foreach ($allForms as $form) {
            $count    = $schedules->where('content_form', $form)->count();
            $uploaded = $uploadedSchedules->where('content_form', $form)->count();
            $formStats[$form] = [
                'total'      => $count,
                'uploaded'   => $uploaded,
                'pending'    => $count - $uploaded,
                'color'      => $formColors[$form] ?? '#6c757d',
                'percentage' => $totalContent > 0 ? round(($count / $totalContent) * 100, 1) : 0,
            ];
            $formChartLabels[] = $form;
            $formChartData[]   = $count;
        }

        // ── Tren chart: mode sesuai periodMode ──────────────────────────────
        $trendLabels = [];
        $trendData   = [];
        $trendTitle  = 'Tren Publikasi Konten';

        if ($periodMode === 'weekly' && $selectedMonth !== 'all') {
            // Pecah per hari dalam bulan terpilih (atau dalam minggu terpilih)
            $yearInt  = $selectedYear === 'all' ? now()->year : (int)$selectedYear;
            $monthInt = (int)$selectedMonth;
            $daysInMonth = \Carbon\Carbon::create($yearInt, $monthInt)->daysInMonth;

            if ($selectedWeek !== 'all') {
                // Hanya tampilkan 7 hari pada minggu terpilih
                $weekStart = \Carbon\Carbon::now()->setISODate($yearInt, (int)$selectedWeek)->startOfWeek();
                for ($d = 0; $d < 7; $d++) {
                    $day = $weekStart->copy()->addDays($d);
                    $trendLabels[] = $day->format('D d');
                    $trendData[]   = $filteredUploaded->filter(function ($item) use ($day) {
                        return $item->upload_date && $item->upload_date->format('Y-m-d') === $day->format('Y-m-d');
                    })->count();
                }
                $trendTitle = 'Upload Harian – Minggu ke-'.(int)$selectedWeek;
            } else {
                // Tampilkan per hari dalam bulan
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $dayStr     = str_pad($d, 2, '0', STR_PAD_LEFT);
                    $trendLabels[] = $d;
                    $trendData[]   = $filteredUploaded->filter(function ($item) use ($yearInt, $monthInt, $d) {
                        return $item->upload_date
                            && (int)$item->upload_date->format('Y') === $yearInt
                            && (int)$item->upload_date->format('m') === $monthInt
                            && (int)$item->upload_date->format('d') === $d;
                    })->count();
                }
                $trendTitle = 'Upload Harian – '.$monthNamesMap[$selectedMonth].' '.$yearInt;
            }
        } elseif ($periodMode === 'monthly' && $selectedYear !== 'all') {
            // Per bulan dalam tahun terpilih
            $monthNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            for ($m = 1; $m <= 12; $m++) {
                $mk = str_pad($m, 2, '0', STR_PAD_LEFT);
                $trendLabels[] = $monthNames[$m - 1];
                $trendData[]   = $uploadedSchedules->filter(function ($item) use ($selectedYear, $mk) {
                    return $item->upload_date
                        && $item->upload_date->format('Y') == $selectedYear
                        && $item->upload_date->format('m') === $mk;
                })->count();
            }
            $trendTitle = 'Tren Upload per Bulan – Tahun '.$selectedYear;
        } else {
            // Default: per bulan (semua tahun atau tahun tertentu)
            $monthNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            for ($m = 1; $m <= 12; $m++) {
                $mk = str_pad($m, 2, '0', STR_PAD_LEFT);
                $trendLabels[] = $monthNames[$m - 1];
                $trendData[]   = $uploadedSchedules->filter(function ($item) use ($selectedYear, $mk) {
                    if (!$item->upload_date) return false;
                    if ($selectedYear === 'all') return $item->upload_date->format('m') === $mk;
                    return $item->upload_date->format('Y') == $selectedYear && $item->upload_date->format('m') === $mk;
                })->count();
            }
            $trendTitle = $selectedYear === 'all' ? 'Tren Upload Konten (Semua Tahun)' : 'Tren Upload per Bulan – Tahun '.$selectedYear;
        }

        // Analitik Talent
        $talentCounts = [];
        foreach ($schedules as $item) {
            if (!empty($item->talents)) {
                foreach (explode(',', $item->talents) as $t) {
                    $name = trim($t);
                    if ($name) $talentCounts[$name] = ($talentCounts[$name] ?? 0) + 1;
                }
            }
        }
        arsort($talentCounts);
        $topTalents         = array_slice($talentCounts, 0, 8, true);
        $totalActiveTalents = count($talentCounts);

        $uniqueTalents = $this->getAllUniqueTalents();

        $pendingQueue    = $pendingSchedules->values()->take(6);
        $recentUploaded  = $uploadedSchedules->sortByDesc('upload_date')->values()->take(5);

        return view('schedules.dashboard', compact(
            'schedules', 'uniqueTalents',
            'totalContent', 'uploadedCount', 'pendingCount', 'completionRate',
            'thisMonthCount', 'tiktokCount', 'tiktokPercentage',
            'availableYears', 'selectedYear',
            'availableMonths', 'selectedMonth',
            'availableWeeks', 'selectedWeek',
            'periodMode',
            'allForms', 'formStats', 'formChartLabels', 'formChartData',
            'trendLabels', 'trendData', 'trendTitle',
            'topTalents', 'totalActiveTalents',
            'pendingQueue', 'recentUploaded'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'content_form' => 'required|in:Reels,Youtube,Feed,Story,Tiktok',
            'talents'      => 'nullable|array',
            'talents.*'    => 'string',
            'description'  => 'nullable|string',
            'proof_script' => 'nullable|string',
            'proof_image'  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_tiktok'    => 'nullable',
        ]);

        if ($request->hasFile('proof_image')) {
            $filePath = $request->file('proof_image')->store('proofs', 'public');
            $validated['proof_image_path'] = $filePath;
        }

        $validated['talents'] = isset($validated['talents'])
            ? implode(',', $validated['talents'])
            : null;

        $validated['is_tiktok'] = $request->boolean('is_tiktok');

        ContentSchedule::create($validated);

        return redirect()->back()->with('success', 'Jadwal konten berhasil ditambahkan.');
    }

    public function markAsUploaded(ContentSchedule $contentSchedule)
    {
        $contentSchedule->update([
            'upload_date' => now(),
        ]);

        return redirect()->back()->with('success', 'Status berhasil diperbarui menjadi Uploaded.');
    }

    public function update(Request $request, ContentSchedule $contentSchedule)
    {
        $validated = $request->validate([
            'content_form' => 'in:Reels,Youtube,Feed,Story,Tiktok',
            'upload_date'  => 'date',
            'talents'      => 'array',
            'description'  => 'nullable|string',
            'proof_script' => 'nullable|string',
            'proof_image'  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_tiktok'    => 'nullable',
        ]);

        if ($request->hasFile('proof_image')) {
            if ($contentSchedule->proof_image_path && Storage::disk('public')->exists($contentSchedule->proof_image_path)) {
                Storage::disk('public')->delete($contentSchedule->proof_image_path);
            }

            $filePath = $request->file('proof_image')->store('proofs', 'public');
            $validated['proof_image_path'] = $filePath;
        }

        if ($request->has('talents')) {
            $validated['talents'] = implode(',', $request->talents);
        }

        if ($request->has('is_tiktok')) {
            $validated['is_tiktok'] = $request->boolean('is_tiktok');
        } else {
            $validated['is_tiktok'] = false;
        }

        $contentSchedule->update($validated);

        return redirect()->back()->with('success', 'Jadwal konten berhasil diperbarui.');
    }

    public function destroy(ContentSchedule $contentSchedule)
    {
        if ($contentSchedule->proof_image_path && Storage::disk('public')->exists($contentSchedule->proof_image_path)) {
            Storage::disk('public')->delete($contentSchedule->proof_image_path);
        }

        $contentSchedule->delete();

        return redirect()->back()->with('success', 'Jadwal konten berhasil dihapus.');
    }
}
