<?php

namespace App\Http\Controllers\Webinar;

use App\Http\Controllers\Controller;
use App\Models\YearMapping;
use App\Models\QuarterEvent;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends Controller
{

    public function construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        return $this->renderTimeline($request, 'timeline.index');
    }

    public function indexAdmSales(Request $request)
    {
        return $this->renderTimeline($request, 'timeline.indexAdmSales');
    }

    private function renderTimeline(Request $request, string $view)
    {
        $year = $request->input('year', date('Y'));

        $requestedMonth = $request->input('month');

        if ($requestedMonth) {
            $startMonth = (int)$requestedMonth;
            $endMonth   = (int)$requestedMonth;
            $quarter    = (int)ceil($requestedMonth / 3);
        } else {
            $quarter    = (int)$request->input('quarter', ceil(date('n') / 3));
            $startMonth = ($quarter - 1) * 3 + 1;
            $endMonth   = $startMonth + 2;
        }

        // ID karyawan dari user yang login
        $karyawanId = auth()->user()->karyawan->id ?? null;

        // AMBIL DATA DARI DATABASE
        $mappings = YearMapping::where('year', $year)
            ->whereBetween('month', [$startMonth, $endMonth])
            ->with([
                'eventDetail',
                // Hanya timeline item milik karyawan yang login
                'timelineItems' => function ($q) use ($karyawanId) {
                    if ($karyawanId) {
                        $q->where('id_karyawan', $karyawanId);
                    } else {
                        $q->whereRaw('1 = 0'); // user tanpa data karyawan: tidak ada item
                    }
                },
            ])
            ->get()
            ->keyBy('month');

        $monthsData = [];

        // GENERATE DATA KALENDER BULANAN
        for ($m = $startMonth; $m <= $endMonth; $m++) {
            $dateObj = Carbon::create($year, $m, 1);
            $monthName = $dateObj->translatedFormat('F');
            $mapping = $mappings->get($m);

            $firstDayOfWeek = (int)$dateObj->dayOfWeekIso;
            $startPadding = ($firstDayOfWeek > 5) ? 0 : ($firstDayOfWeek - 1);

            $plannedDate = ($mapping && $mapping->planned_date)
                ? Carbon::parse($mapping->planned_date)->format('Y-m-d')
                : null;

            // Index item per tanggal, supaya tidak filter berulang di tiap hari
            $itemsByDate = $mapping
                ? $mapping->timelineItems->keyBy(fn ($item) => Carbon::parse($item->item_date)->format('Y-m-d'))
                : collect();

            $dates = [];
            $daysInMonth = $dateObj->daysInMonth;

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $currDate = Carbon::create($year, $m, $d);

                if ($currDate->isWeekend()) {
                    continue;
                }

                $dateStr = $currDate->format('Y-m-d');

                $dates[] = [
                    'day'       => $d,
                    'full_date' => $dateStr,
                    'item'      => $itemsByDate->get($dateStr),
                    'is_dday'   => $plannedDate === $dateStr,
                ];
            }

            $monthsData[$m] = [
                'name'             => $monthName,
                'mapping_id'       => $mapping->id ?? null,
                'theme'            => $mapping->theme ?? 'Tema Belum Diset',
                'event_detail'     => $mapping->eventDetail ?? null,
                'event_title'      => $mapping->eventDetail->title ?? null,
                'start_padding'    => $startPadding,
                'dates'            => $dates,
                'planned_date'     => $plannedDate,
                'planned_date_raw' => $plannedDate ?? '',
                'duration'         => $mapping->duration_minutes ?? 120,
            ];
        }

        $isSingleView = !empty($requestedMonth);

        return view($view, compact('year', 'quarter', 'monthsData', 'isSingleView'));
    }

    public function updateEvent(Request $request, $mappingId)
    {
        $user = auth()->user();

        if (!$user || !$user->can('manage-master-plan')) {
            return response()->json([
                'message' => 'Akses Ditolak: Anda tidak memiliki izin untuk mengubah Master Plan & Event.'
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