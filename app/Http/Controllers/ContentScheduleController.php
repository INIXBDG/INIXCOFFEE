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
        $selectedYear = $request->input('year', date('Y'));

        $schedules = ContentSchedule::orderByRaw('upload_date IS NULL DESC, upload_date DESC')->get();

        $totalContent = $schedules->count();
        $uploadedSchedules = $schedules->whereNotNull('upload_date');
        $uploadedCount = $uploadedSchedules->count();
        $pendingSchedules = $schedules->whereNull('upload_date');
        $pendingCount = $pendingSchedules->count();
        $completionRate = $totalContent > 0 ? round(($uploadedCount / $totalContent) * 100, 1) : 0;

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
            ->filter()
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [date('Y')];
        }

        if ($selectedYear !== 'all' && !in_array($selectedYear, $availableYears)) {
            $selectedYear = $availableYears[0];
        }

        // Distribusi bentuk konten
        $allForms = ['Feed', 'Reels', 'Tiktok', 'Story', 'Youtube'];
        $formStats = [];
        $formChartLabels = [];
        $formChartData = [];
        $formColors = [
            'Feed' => '#198754',      // green
            'Reels' => '#ffc107',     // warning/amber
            'Tiktok' => '#212529',    // dark
            'Story' => '#0dcaf0',     // cyan
            'Youtube' => '#dc3545',   // red
        ];

        foreach ($allForms as $form) {
            $count = $schedules->where('content_form', $form)->count();
            $uploaded = $uploadedSchedules->where('content_form', $form)->count();
            $formStats[$form] = [
                'total' => $count,
                'uploaded' => $uploaded,
                'pending' => $count - $uploaded,
                'color' => $formColors[$form] ?? '#6c757d',
                'percentage' => $totalContent > 0 ? round(($count / $totalContent) * 100, 1) : 0,
            ];
            $formChartLabels[] = $form;
            $formChartData[] = $count;
        }

        // Tren publikasi bulanan (Januari - Desember) untuk tahun yang dipilih
        $monthlyTrend = [];
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        for ($m = 1; $m <= 12; $m++) {
            $monthKey = str_pad($m, 2, '0', STR_PAD_LEFT);
            $monthCount = $uploadedSchedules->filter(function ($item) use ($selectedYear, $monthKey) {
                if (!$item->upload_date) return false;
                if ($selectedYear === 'all') {
                    return $item->upload_date->format('m') === $monthKey;
                }
                return $item->upload_date->format('Y') == $selectedYear && $item->upload_date->format('m') === $monthKey;
            })->count();
            $monthlyTrend[] = $monthCount;
        }

        // Analitik Talent (frekuensi kemunculan talent pada konten)
        $talentCounts = [];
        foreach ($schedules as $item) {
            if (!empty($item->talents)) {
                $talents = explode(',', $item->talents);
                foreach ($talents as $t) {
                    $name = trim($t);
                    if (!empty($name)) {
                        $talentCounts[$name] = ($talentCounts[$name] ?? 0) + 1;
                    }
                }
            }
        }
        arsort($talentCounts);
        $topTalents = array_slice($talentCounts, 0, 8, true);
        $totalActiveTalents = count($talentCounts);

        $uniqueTalents = $this->getAllUniqueTalents();

        // Antrean konten menunggu upload (Pending Queue)
        $pendingQueue = $pendingSchedules->values()->take(6);

        // Konten yang baru saja tayang / di-upload
        $recentUploaded = $uploadedSchedules->sortByDesc('upload_date')->values()->take(5);

        return view('schedules.dashboard', compact(
            'schedules',
            'uniqueTalents',
            'totalContent',
            'uploadedCount',
            'pendingCount',
            'completionRate',
            'thisMonthCount',
            'tiktokCount',
            'tiktokPercentage',
            'availableYears',
            'selectedYear',
            'allForms',
            'formStats',
            'formChartLabels',
            'formChartData',
            'monthlyTrend',
            'monthNames',
            'topTalents',
            'totalActiveTalents',
            'pendingQueue',
            'recentUploaded'
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
