<?php

namespace App\Http\Controllers\HR;

use App\Exports\FeedbackInstrukturExport;
use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Nilaifeedback;
use App\Models\RKM;
use App\Models\souvenir;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class FeedbackInstukturController extends Controller
{
    public function index()
    {
        // $sales = Feedback::where('kategori_feedback', 'Sales')->get();
        // $materi = Feedback::where('kategori_feedback', 'Materi')->get();
        // $pelayanan = Feedback::where('kategori_feedback', 'Pelayanan')->get();
        // $fasilitas = Feedback::where('kategori_feedback', 'Fasilitas Laboratium')->get();
        // $instruktur = Feedback::where('kategori_feedback', 'Instruktur')->get();
        // $jmlInstruktur = RKM::with('instruktur');
        // $umum = Feedback::where('kategori_feedback', 'Umum')->get();
        // $souvenir = souvenir::get();

        return view('HR.feedback_instruktur.index');
    }

    public function exportPdf(Request $request)
    {
        $report = $this->getExportReport($request);

        return Pdf::loadView('HR.feedback_instruktur.export_pdf', [
            'report' => $report,
            'months' => $this->getReportMonths($report),
            'periodLabel' => $this->getPeriodLabel(
                $report['tahun'],
                $report['jenis_periode'],
                $report['periode']
            ),
            'printedAt' => Carbon::now()->translatedFormat('l, d F Y H:i'),
        ])
            ->setPaper('a4', 'landscape')
            ->download('rekap-feedback-instruktur-'.$report['tahun'].'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $report = $this->getExportReport($request);
        $months = $this->getReportMonths($report);
        $rows = collect($report['per_instruktur'])
            ->filter(fn ($item) => ! empty($item['kode_karyawan']) &&
                trim($item['kode_karyawan']) !== '-' &&
                $item['total_feedback'] > 0 &&
                $item['total_penilaian'] > 0
            )
            ->values()
            ->map(fn ($item, $index) => [
                $index + 1,
                $item['kode_karyawan'],
                $item['nama'],
                ...array_map(
                    fn ($month) => $item['rata_rata_per_bulan'][$month] ?? '',
                    $months
                ),
                $item['rata_rata'],
            ])
            ->push([
                '',
                '',
                'Grand Total',
                ...array_map(
                    fn ($month) => $report['average_feedback_per_bulan'][$month] ?? '',
                    $months
                ),
                $report['average_feedback']['instruktur'] ?? 0,
            ]);

        return Excel::download(
            new FeedbackInstrukturExport(
                $rows->all(),
                array_map(
                    fn ($month) => Carbon::createFromDate(
                        $report['tahun'],
                        $month,
                        1
                    )->translatedFormat('F'),
                    $months
                )
            ),
            'rekap-feedback-instruktur-'.$report['tahun'].'.xlsx'
        );
    }

    private function getExportReport(Request $request): array
    {
        $type = $request->input('type', 'year');
        $periodRules = $type === 'month'
            ? ['required', 'integer', 'between:1,12']
            : ($type === 'quarter'
                ? ['required', 'integer', 'between:1,4']
                : ['nullable']);

        $filters = $request->validate([
            'year' => ['required', 'integer', 'min:2023', 'max:'.date('Y')],
            'type' => ['required', 'in:year,month,quarter'],
            'period' => $periodRules,
        ]);

        $response = $this->getDataFeedbackInstruktur(
            (int) $filters['year'],
            $filters['type'],
            $filters['period'] ?? null
        );

        if ($response->getStatusCode() !== 200) {
            abort(500, 'Gagal menyiapkan data export feedback instruktur.');
        }

        return $response->getData(true);
    }

    private function getPeriodLabel(int $year, string $type, ?int $period): string
    {
        if ($type === 'month') {
            return Carbon::createFromDate($year, $period, 1)->translatedFormat('F Y');
        }

        if ($type === 'quarter') {
            return 'Kuartal '.$period.' '.$year;
        }

        return 'Januari - Desember '.$year;
    }

    private function getReportMonths(array $report): array
    {
        if ($report['jenis_periode'] === 'month') {
            return [(int) $report['periode']];
        }

        if ($report['jenis_periode'] === 'quarter') {
            $firstMonth = (((int) $report['periode'] - 1) * 3) + 1;

            return range($firstMonth, $firstMonth + 2);
        }

        return range(1, 12);
    }

    private function getInstructorFeedbackSources(): array
    {
        return [
            'instruktur' => [
                'relation' => 'instruktur',
                'key' => 'instruktur_key',
                'fields' => ['I1', 'I2', 'I3', 'I4', 'I5', 'I6', 'I7', 'I8'],
            ],
            'instruktur2' => [
                'relation' => 'instruktur2',
                'key' => 'instruktur_key2',
                'fields' => ['I1b', 'I2b', 'I3b', 'I4b', 'I5b', 'I6b', 'I7b', 'I8b'],
            ],
            'asisten' => [
                'relation' => 'asisten',
                'key' => 'asisten_key',
                'fields' => ['I1as', 'I2as', 'I3as', 'I4as', 'I5as', 'I6as', 'I7as', 'I8as'],
            ],
        ];
    }

    public function getDataFeedbackInstruktur($year, $type = 'year', $period = null)
    {
        try {
            $query = Nilaifeedback::with([
                'rkm.instruktur',
                'rkm.instruktur2',
                'rkm.asisten',
                'regist',
                'rkm',
            ])
                ->whereHas('rkm', function ($query) use ($year, $type, $period) {
                    $query->whereYear('tanggal_awal', $year);

                    if ($type === 'month') {
                        $query->whereMonth('tanggal_awal', $period);
                    } elseif ($type === 'quarter') {
                        if (! in_array((int) $period, [1, 2, 3, 4], true)) {
                            throw new \InvalidArgumentException(
                                'Kuartal harus bernilai 1 sampai 4.'
                            );
                        }

                        $startMonth = (((int) $period - 1) * 3) + 1;

                        $startDate = Carbon::create(
                            (int) $year,
                            $startMonth,
                            1
                        )->startOfDay();

                        $endDate = $startDate->copy()
                            ->addMonths(3)
                            ->subSecond();

                        $query->whereBetween('tanggal_awal', [
                            $startDate,
                            $endDate,
                        ]);
                    } elseif ($type !== 'year') {
                        throw new \InvalidArgumentException(
                            'Periode harus month, quarter, atau year.'
                        );
                    }
                });

            $feedbacks = $query->get();

            $feedbackSources = $this->getInstructorFeedbackSources();
            $monthlyFeedbackValues = [];
            foreach ($feedbacks as $feedback) {
                if (! $feedback->rkm) {
                    continue;
                }

                $month = (int) Carbon::parse($feedback->rkm->tanggal_awal)->format('n');
                foreach ($feedbackSources as $source) {
                    foreach ($source['fields'] as $field) {
                        $value = $feedback->{$field};
                        if (is_numeric($value)) {
                            $monthlyFeedbackValues[$month][] = (float) $value;
                        }
                    }
                }
            }

            $allInstructorValues = collect($monthlyFeedbackValues)->flatten();
            $averageFeedbackPerMonth = collect($monthlyFeedbackValues)
                ->map(fn ($values) => round(collect($values)->avg(), 1));

            $averageFeedback = [
                'instruktur' => round($allInstructorValues->avg() ?? 0, 1),
            ];

            $rkmQuery = RKM::with(['instruktur', 'instruktur2', 'asisten'])
                ->whereYear('tanggal_awal', $year);

            if ($type === 'month') {
                $rkmQuery->whereMonth('tanggal_awal', $period);
            } elseif ($type === 'quarter') {
                $firstMonth = (((int) $period - 1) * 3) + 1;
                $startDate = Carbon::create((int) $year, $firstMonth, 1)->startOfDay();
                $endDate = $startDate->copy()->addMonths(3)->subSecond();
                $rkmQuery->whereBetween('tanggal_awal', [$startDate, $endDate]);
            }

            $perKelas = [];
            foreach ($rkmQuery->get() as $rkm) {
                $month = (int) Carbon::parse($rkm->tanggal_awal)->format('n');
                $instructorsInRkm = [];

                foreach ($feedbackSources as $source) {
                    $kode = trim((string) ($rkm->{$source['key']} ?? ''));
                    $karyawan = $rkm->{$source['relation']};

                    if ($kode === '' || $kode === '-' || ! $karyawan) {
                        continue;
                    }

                    $instructorsInRkm[$kode] = [
                        'nama' => $karyawan->nama
                            ?? $karyawan->nama_lengkap
                            ?? $kode,
                    ];
                }

                foreach ($instructorsInRkm as $kode => $instructor) {
                    if (! isset($perKelas[$kode])) {
                        $perKelas[$kode] = [
                            'kode_karyawan' => $kode,
                            'nama' => $instructor['nama'],
                            'total_kelas' => 0,
                            'kelas_per_bulan' => [],
                        ];
                    }

                    $perKelas[$kode]['total_kelas']++;
                    $perKelas[$kode]['kelas_per_bulan'][$month] =
                        ($perKelas[$kode]['kelas_per_bulan'][$month] ?? 0) + 1;
                }
            }

            $perKelas = collect($perKelas)
                ->sortBy('nama')
                ->values();
            $totalKelas = $perKelas->sum('total_kelas');
            $perKelas = $perKelas
                ->map(function ($instructor) use ($totalKelas) {
                    $instructor['persentase'] = $totalKelas > 0
                        ? round(($instructor['total_kelas'] / $totalKelas) * 100, 2)
                        : 0;

                    return $instructor;
                });

            $totalKelasPerBulan = [];
            foreach ($perKelas as $instructor) {
                foreach ($instructor['kelas_per_bulan'] as $month => $count) {
                    $totalKelasPerBulan[$month] =
                        ($totalKelasPerBulan[$month] ?? 0) + $count;
                }
            }

            $perInstruktur = [];

            foreach ($feedbacks as $feedback) {
                $rkm = $feedback->rkm;

                if (! $rkm) {
                    continue;
                }

                $instructorsInFeedback = [];

                foreach ($feedbackSources as $config) {
                    $kode = trim((string) ($rkm->{$config['key']} ?? ''));

                    if ($kode === '' || $kode === '-') {
                        continue;
                    }

                    $karyawan = $rkm->{$config['relation']};

                    if (! $karyawan) {
                        continue;
                    }

                    if (! isset($instructorsInFeedback[$kode])) {
                        $instructorsInFeedback[$kode] = [
                            'nama' => $karyawan->nama
                                ?? $karyawan->nama_lengkap
                                ?? $kode,
                            'nilai' => collect(),
                        ];
                    }

                    $instructorsInFeedback[$kode]['nilai'] = $instructorsInFeedback[$kode]['nilai']
                        ->merge(collect($config['fields'])
                            ->map(fn ($field) => $feedback->{$field})
                            ->filter(fn ($value) => $value !== null &&
                                $value !== '' &&
                                is_numeric($value)
                            ));
                }

                foreach ($instructorsInFeedback as $kode => $instructor) {
                    if ($instructor['nilai']->isEmpty()) {
                        continue;
                    }

                    if (! isset($perInstruktur[$kode])) {
                        $perInstruktur[$kode] = [
                            'kode_karyawan' => $kode,
                            'nama' => $instructor['nama'],
                            'peran' => 'instruktur',
                            'total_feedback' => 0,
                            'total_penilaian' => 0,
                            'total_nilai' => 0,
                            'per_bulan' => [],
                        ];
                    }

                    $month = (int) Carbon::parse($rkm->tanggal_awal)->format('n');
                    if (! isset($perInstruktur[$kode]['per_bulan'][$month])) {
                        $perInstruktur[$kode]['per_bulan'][$month] = [
                            'total_feedback' => 0,
                            'total_penilaian' => 0,
                            'total_nilai' => 0,
                        ];
                    }

                    $perInstruktur[$kode]['total_feedback']++;
                    $perInstruktur[$kode]['total_penilaian'] += $instructor['nilai']->count();
                    $perInstruktur[$kode]['total_nilai'] += $instructor['nilai']->sum();
                    $perInstruktur[$kode]['per_bulan'][$month]['total_feedback']++;
                    $perInstruktur[$kode]['per_bulan'][$month]['total_penilaian']
                        += $instructor['nilai']->count();
                    $perInstruktur[$kode]['per_bulan'][$month]['total_nilai']
                        += $instructor['nilai']->sum();
                }
            }

            // Finalisasi hasil per instruktur.
            $perInstruktur = collect($perInstruktur)
                ->map(function ($item) {
                    $item['rata_rata'] = $item['total_penilaian'] > 0
                        ? round(
                            $item['total_nilai'] / $item['total_penilaian'],
                            1
                        )
                        : 0;

                    $item['rata_rata_per_bulan'] = collect($item['per_bulan'])
                        ->map(fn ($month) => round(
                            $month['total_nilai'] / $month['total_penilaian'],
                            1
                        ));

                    unset($item['total_nilai'], $item['per_bulan']);

                    return $item;
                })
                ->sortBy('nama')
                ->values();

            return response()->json([
                'status' => 'success',
                'title' => 'Berhasil!',
                'tahun' => (int) $year,
                'jenis_periode' => $type,
                'periode' => $period,
                'total_feedback' => $feedbacks->count(),
                'average_feedback' => $averageFeedback,
                'average_feedback_per_bulan' => $averageFeedbackPerMonth,
                'per_instruktur' => $perInstruktur,
                'per_kelas' => $perKelas,
                'total_kelas_per_bulan' => $totalKelasPerBulan,
                'total_kelas' => $totalKelas,
                // 'data' => $feedbacks,
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Gagal mengambil feedback instruktur', [
                'tahun' => $year,
                'jenis_periode' => $type,
                'periode' => $period,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'title' => 'Gagal!',
                'message' => 'Terjadi kesalahan saat mengambil data feedback instruktur.',
            ], 500);
        }
    }

    public function getDetailRkmInstruktur($kode_karyawan, $peran, $year, $type = 'year', $period = null)
    {
        try {
            if ($peran !== 'instruktur') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Peran tidak valid.',
                ], 400);
            }

            $feedbackSources = $this->getInstructorFeedbackSources();

            $query = RKM::with(['materi', 'perusahaan', 'nilaifeedback'])
                ->where(function ($query) use ($feedbackSources, $kode_karyawan) {
                    foreach ($feedbackSources as $source) {
                        $query->orWhere($source['key'], $kode_karyawan);
                    }
                })
                ->whereYear('tanggal_awal', $year);

            if ($type === 'month') {
                $query->whereMonth('tanggal_awal', $period);
            } elseif ($type === 'quarter') {
                $startMonth = (((int) $period - 1) * 3) + 1;
                $startDate = Carbon::create((int) $year, $startMonth, 1)->startOfDay();
                $endDate = $startDate->copy()->addMonths(3)->subSecond();

                $query->whereBetween('tanggal_awal', [$startDate, $endDate]);
            }

            $rkms = $query->get();

            $data = $rkms->map(function ($rkm) use ($feedbackSources, $kode_karyawan) {
                $tglAwal = Carbon::parse($rkm->tanggal_awal)->translatedFormat('d M Y');
                $tglAkhir = Carbon::parse($rkm->tanggal_akhir)->translatedFormat('d M Y');
                $tanggalString = ($tglAwal === $tglAkhir) ? $tglAwal : $tglAwal.' - '.$tglAkhir;

                $allValues = collect();
                foreach ($rkm->nilaifeedback as $feedback) {
                    foreach ($feedbackSources as $source) {
                        if (($rkm->{$source['key']} ?? null) !== $kode_karyawan) {
                            continue;
                        }

                        foreach ($source['fields'] as $field) {
                            $value = $feedback->{$field};
                            if ($value !== null && $value !== '' && is_numeric($value)) {
                                $allValues->push((float) $value);
                            }
                        }
                    }
                }

                $rataRata = $allValues->isNotEmpty() ? round($allValues->avg(), 1) : 0;

                return [
                    'nama_materi' => $rkm->materi->nama_materi ?? $rkm->materi->nama ?? '-',
                    'nama_perusahaan' => $rkm->perusahaan->nama_perusahaan ?? $rkm->perusahaan->nama ?? '-',
                    'tanggal' => $tanggalString,
                    'metode_kelas' => $rkm->metode_kelas ?? '-',
                    'rata_rata_feedback' => $rataRata,
                ];
            });

            return response()->json([
                'status' => 'success',
                'peran_label' => 'Instruktur',
                'data' => $data,
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Gagal mengambil detail RKM instruktur', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memuat detail RKM.',
            ], 500);
        }
    }
}
