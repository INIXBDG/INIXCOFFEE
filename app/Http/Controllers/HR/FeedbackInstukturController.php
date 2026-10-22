<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Feedback;
use App\Models\Registrasi;
use App\Models\RKM;
use App\Models\souvenir;
use App\Models\Nilaifeedback;
use App\Exports\FeedbackInstrukturExport;
use Barryvdh\DomPDF\Facade\Pdf;
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
            'periodLabel' => $this->getPeriodLabel(
                $report['tahun'],
                $report['jenis_periode'],
                $report['periode']
            ),
            'printedAt' => Carbon::now()->translatedFormat('l, d F Y H:i'),
        ])
            ->setPaper('a4', 'landscape')
            ->download('rekap-feedback-instruktur-' . $report['tahun'] . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $report = $this->getExportReport($request);
        $rows = collect($report['per_instruktur'])
            ->filter(fn ($item) =>
                !empty($item['kode_karyawan']) &&
                trim($item['kode_karyawan']) !== '-' &&
                $item['total_feedback'] > 0 &&
                $item['total_penilaian'] > 0
            )
            ->values()
            ->map(fn ($item, $index) => [
                'no' => $index + 1,
                'kode_karyawan' => $item['kode_karyawan'],
                'nama' => $item['nama'],
                'peran' => $this->getRoleLabel($item['peran']),
                'total_feedback' => $item['total_feedback'],
                'rata_rata' => $item['rata_rata'],
            ]);

        return Excel::download(
            new FeedbackInstrukturExport($rows->all()),
            'rekap-feedback-instruktur-' . $report['tahun'] . '.xlsx'
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
            'year' => ['required', 'integer', 'min:2023', 'max:' . date('Y')],
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
            return 'Kuartal ' . $period . ' ' . $year;
        }

        return 'Januari - Desember ' . $year;
    }

    private function getRoleLabel(string $role): string
    {
        return [
            'instruktur' => 'Instruktur',
            'instruktur2' => 'Instruktur Kedua',
            'asisten' => 'Asisten',
        ][$role] ?? $role;
    }

public function getDataFeedbackInstruktur($year,$type = 'year',$period = null)
{
    try {
        $query = Nilaifeedback::with([
            'rkm.instruktur',
            'rkm.instruktur2',
            'rkm.asisten',
            'regist',
            'rkm'
        ])
        ->whereHas('rkm', function ($query) use ($year, $type, $period) {
            $query->whereYear('tanggal_awal', $year);

            if ($type === 'month') {
                $query->whereMonth('tanggal_awal', $period);
            } elseif ($type === 'quarter') {
                if (!in_array((int) $period, [1, 2, 3, 4], true)) {
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
                    $endDate
                ]);
            } elseif ($type !== 'year') {
                throw new \InvalidArgumentException(
                    'Periode harus month, quarter, atau year.'
                );
            }
        });

        $feedbacks = $query->get();

        // return $feedbacks;

        // Menghitung rata-rata feedback setiap instruktur
        $averageFeedback = [
            'instruktur' => round(
                $feedbacks->pluck('I1')
                    ->merge($feedbacks->pluck('I2'))
                    ->merge($feedbacks->pluck('I3'))
                    ->merge($feedbacks->pluck('I4'))
                    ->merge($feedbacks->pluck('I5'))
                    ->merge($feedbacks->pluck('I6'))
                    ->merge($feedbacks->pluck('I7'))
                    ->merge($feedbacks->pluck('I8'))
                    ->filter(fn ($value) => $value !== null && $value !== '')
                    ->avg() ?? 0,
                1
            ),

            'instruktur2' => round(
                $feedbacks->pluck('I1b')
                    ->merge($feedbacks->pluck('I2b'))
                    ->merge($feedbacks->pluck('I3b'))
                    ->merge($feedbacks->pluck('I4b'))
                    ->merge($feedbacks->pluck('I5b'))
                    ->merge($feedbacks->pluck('I6b'))
                    ->merge($feedbacks->pluck('I7b'))
                    ->merge($feedbacks->pluck('I8b'))
                    ->filter(fn ($value) => $value !== null && $value !== '')
                    ->avg() ?? 0,
                1
            ),

            'asisten' => round(
                $feedbacks->pluck('I1as')
                    ->merge($feedbacks->pluck('I2as'))
                    ->merge($feedbacks->pluck('I3as'))
                    ->merge($feedbacks->pluck('I4as'))
                    ->merge($feedbacks->pluck('I5as'))
                    ->merge($feedbacks->pluck('I6as'))
                    ->merge($feedbacks->pluck('I7as'))
                    ->merge($feedbacks->pluck('I8as'))
                    ->filter(fn ($value) => $value !== null && $value !== '')
                    ->avg() ?? 0,
                1
            ),
        ];

        
        $perInstruktur = [];

        $instrukturFields = [
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
                'fields' => ['I1as', 'I2as', 'I3as', 'I4as', 'I5as', 'I6as', 'I7as'],
            ],
        ];

        foreach ($feedbacks as $feedback) {
            $rkm = $feedback->rkm;

            if (!$rkm) {
                continue;
            }

            foreach ($instrukturFields as $role => $config) {
                $kode = trim((string) ($rkm->{$config['key']} ?? ''));

                // Abaikan jika kode karyawan kosong atau "-"
                if ($kode === '' || $kode === '-') {
                    continue;
                }

                $karyawan = $rkm->{$config['relation']};

                // Abaikan jika data karyawan tidak ditemukan
                if (!$karyawan) {
                    continue;
                }

                $karyawan = $rkm->{$config['relation']};

                // Pisahkan berdasarkan identitas dan peran.
                $key = $role . '|' . $kode;

                if (!isset($perInstruktur[$key])) {
                    $perInstruktur[$key] = [
                        'kode_karyawan' => $kode,
                        'nama' => $karyawan->nama
                            ?? $karyawan->nama_lengkap
                            ?? $kode,
                        'peran' => $role,
                        'total_feedback' => 0,
                        'total_penilaian' => 0,
                        'total_nilai' => 0,
                        'nilai' => [],
                    ];
                }

                $nilaiFeedback = collect($config['fields'])
                    ->map(fn ($field) => $feedback->{$field})
                    ->filter(fn ($value) =>
                        $value !== null &&
                        $value !== '' &&
                        is_numeric($value)
                    );

                // Hitung rata-rata seluruh jawaban valid.
                if ($nilaiFeedback->isNotEmpty()) {
                    $perInstruktur[$key]['total_feedback']++;

                    $perInstruktur[$key]['total_penilaian']
                        += $nilaiFeedback->count();

                    $perInstruktur[$key]['total_nilai']
                        += $nilaiFeedback->sum();
                }
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

                unset($item['total_nilai'], $item['nilai']);

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
                'per_instruktur' => $perInstruktur,
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
            // Map peran ke kolom relasi/kunci RKM dan kolom feedback-nya
            $configPeran = [
                'instruktur' => [
                    'column' => 'instruktur_key',
                    'label' => 'Instruktur',
                    'fields' => ['I1', 'I2', 'I3', 'I4', 'I5', 'I6', 'I7', 'I8'],
                ],
                'instruktur2' => [
                    'column' => 'instruktur_key2',
                    'label' => 'Instruktur Kedua',
                    'fields' => ['I1b', 'I2b', 'I3b', 'I4b', 'I5b', 'I6b', 'I7b', 'I8b'],
                ],
                'asisten' => [
                    'column' => 'asisten_key',
                    'label' => 'Asisten',
                    'fields' => ['I1as', 'I2as', 'I3as', 'I4as', 'I5as', 'I6as', 'I7as'],
                ],
            ];

            if (!isset($configPeran[$peran])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Peran tidak valid.'
                ], 400);
            }

            $config = $configPeran[$peran];

            // Query RKM spesifik untuk peran ini
            $query = RKM::with(['materi', 'perusahaan', 'nilaifeedback'])
                ->where($config['column'], $kode_karyawan)
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

            $data = $rkms->map(function ($rkm) use ($config) {
                $tglAwal = Carbon::parse($rkm->tanggal_awal)->translatedFormat('d M Y');
                $tglAkhir = Carbon::parse($rkm->tanggal_akhir)->translatedFormat('d M Y');
                $tanggalString = ($tglAwal === $tglAkhir) ? $tglAwal : $tglAwal . ' - ' . $tglAkhir;

                // Hitung rata-rata nilai feedback spesifik untuk peran ini di RKM tersebut
                $allValues = collect();
                foreach ($rkm->nilaifeedback as $feedback) {
                    foreach ($config['fields'] as $field) {
                        $val = $feedback->{$field};
                        if ($val !== null && $val !== '' && is_numeric($val)) {
                            $allValues->push((float) $val);
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
                'peran_label' => $config['label'],
                'data' => $data
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Gagal mengambil detail RKM instruktur', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memuat detail RKM.'
            ], 500);
        }
    }
}
