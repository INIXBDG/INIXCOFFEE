<?php

namespace App\Http\Controllers\Webinar;

use App\Http\Controllers\Controller;
use App\Models\YearMapping;
use App\Models\QuarterEvent;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', date('Y'));

        $requestedMonth = $request->input('month');

        if ($requestedMonth) {
            $startMonth = (int)$requestedMonth;
            $endMonth   = (int)$requestedMonth;
            $quarter    = ceil($requestedMonth / 3);
        } else {
            $quarter    = (int)$request->input('quarter', ceil(date('n') / 3));
            $startMonth = ($quarter - 1) * 3 + 1;
            $endMonth   = $startMonth + 2;
        }

        // 3. AMBIL DATA DARI DATABASE
        $mappings = YearMapping::where('year', $year)
            ->whereBetween('month', [$startMonth, $endMonth])
            ->with(['eventDetail', 'timelineItems'])
            ->get()
            ->keyBy('month');

        $monthsData = [];

        // 4. GENERATE DATA KALENDER BULANAN
        for ($m = $startMonth; $m <= $endMonth; $m++) {
            $dateObj = Carbon::create($year, $m, 1);
            $monthName = $dateObj->translatedFormat('F');
            $mapping = $mappings->get($m);

            $firstDayOfWeek = (int)$dateObj->dayOfWeekIso;
            $startPadding = ($firstDayOfWeek > 5) ? 0 : ($firstDayOfWeek - 1);

            $dates = [];
            $daysInMonth = $dateObj->daysInMonth;

                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $currDate = Carbon::create($year, $m, $d);

                    if ($currDate->isWeekend()) {
                        continue;
                    }

                    $dateStr = $currDate->format('Y-m-d');

                    // Ambil aktivitas harian dengan filter yang lebih stabil
                    $dailyItem = $mapping ? $mapping->timelineItems->filter(function($item) use ($dateStr) {
                        // perbandingan menggunakan format string agar tidak ada isu objek Carbon
                        return Carbon::parse($item->item_date)->format('Y-m-d') === $dateStr;
                    })->first() : null;

                    // Cek status D-Day
                    $isDDay = false;
                    if ($mapping && $mapping->planned_date) {
                        if ($dateStr === Carbon::parse($mapping->planned_date)->format('Y-m-d')) {
                            $isDDay = true;
                        }
                    }

                    $dates[] = [
                        'day'       => $d,
                        'full_date' => $dateStr,
                        'item'      => $dailyItem,
                        'is_dday'   => $isDDay
                    ];
                }

            $monthsData[$m] = [
                'name'             => $monthName,
                'mapping_id'       => $mapping->id ?? null,
                'theme'            => $mapping->theme ?? 'Tema Belum Diset',
                'event_detail'     => $mapping->eventDetail ?? null,
                'start_padding'    => $startPadding,
                'dates'            => $dates,
                'planned_date_raw' => ($mapping && $mapping->planned_date) ? Carbon::parse($mapping->planned_date)->format('Y-m-d') : '',
                'duration'         => $mapping->duration_minutes ?? 120
            ];
        }

        $isSingleView = !empty($requestedMonth);

        // Kirim data ke View
        return view('timeline.index', compact('year', 'quarter', 'monthsData', 'isSingleView'));
    }

    public function updateEvent(Request $request, $mappingId)
    {
        if (!auth()->check() || auth()->user()->jabatan !== 'Tim Digital') {
            return response()->json([
                'message' => 'Akses Ditolak: Hanya Tim Digital yang dapat mengubah Master Plan & Event.'
            ], 403);
        }

        $validated = $request->validate([
            'theme' => 'required|string',
            'planned_date' => 'required|date',
            'duration_minutes' => 'required|integer',
            'title' => 'nullable|string',
            'speaker_name' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $mapping = YearMapping::findOrFail($mappingId);
        $mapping->update([
            'theme' => $validated['theme'],
            'planned_date' => $validated['planned_date'],
            'duration_minutes' => $validated['duration_minutes'],
            'month' => Carbon::parse($validated['planned_date'])->month
        ]);

        $event = QuarterEvent::updateOrCreate(
            ['year_mapping_id' => $mappingId],
            [
                'title' => $validated['title'],
                'speaker_name' => $validated['speaker_name'],
                'description' => $validated['description'],
            ]
        );

        return response()->json([
            'message' => 'Data berhasil diperbarui',
            'mapping' => $mapping,
            'event' => $event
        ]);
    }
}
