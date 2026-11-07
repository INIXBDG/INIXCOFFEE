<?php

namespace App\Http\Controllers\office;

use App\Http\Controllers\Controller;
use App\Models\AnalysisFinanceChange;
use App\Models\AnalysisFinanceIndicator;
use App\Models\AnalysisFinanceInitiative;
use App\Models\AnalysisFinanceInternalProcess;
use App\Models\AnalysisFinanceQuarterlyReport;
use App\Models\AnalysisFinanceSetting;
use App\Models\trackingTagihanPerusahaan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalysisFinanceController extends Controller
{
    private const BULAN = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    private const TEMPLATE_HEADERS = [
        'internal'    => ['bulan', 'pengajuan_barang', 'bukti_kas', 'catatan'],
        'initiatives' => ['no', 'initiative', 'description', 'analysis_target', 'estimated_impact', 'status'],
        'quarterly'   => ['triwulan', 'pencapaian_persen', 'skor_kinerja', 'status', 'catatan', 'keterangan'],
        'skema'       => ['no', 'indikator', 'formula', 'target', 'bobot'],
        'overview'    => ['no', 'deskripsi', 'target', 'bobot'],
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    /* =====================================================================
     |  HALAMAN UTAMA
     * ===================================================================== */

    public function index(Request $request)
    {
        $now   = now();
        $tahun = (int) $request->get('tahun', $now->year);
        $mode  = in_array($request->get('mode'), ['bulan', 'q', 'tahun']) ? $request->get('mode') : 'q';
        $bulan = min(12, max(1, (int) $request->get('bulan', $now->month)));
        $q     = min(4, max(1, (int) $request->get('q', (int) ceil($now->month / 3))));

        [$sm, $em, $periodLabel] = match ($mode) {
            'bulan' => [$bulan, $bulan, self::BULAN[$bulan] . ' ' . $tahun],
            'tahun' => [1, 12, 'Tahun ' . $tahun],
            default => [($q - 1) * 3 + 1, $q * 3, 'TR ' . $q . ' ' . $tahun],
        };

        $ctx      = $this->context($tahun);
        $analysis = $this->buildKpi($ctx, $sm, $em);
        $quarters = $this->quarters($ctx, $tahun);

        $finMonthly = [];
        for ($m = $sm; $m <= $em; $m++) {
            $finMonthly[$m] = $ctx['monthly'][$m] + ['nama' => self::BULAN[$m]];
        }

        $endQ        = (int) ceil($em / 3);
        $initiatives = $ctx['initiatives'];

        $changes = AnalysisFinanceChange::where('tahun', $tahun)->where('quarter', $endQ)->orderBy('no')->orderBy('id')->get();

        return view('office.analysisFinance.index', [
            'tahun'       => $tahun,
            'mode'        => $mode,
            'bulan'       => $bulan,
            'q'           => $q,
            'sm'          => $sm,
            'em'          => $em,
            'endQ'        => $endQ,
            'periodLabel' => $periodLabel,
            'listTahun'   => range(now()->year - 3, now()->year + 1),
            'listBulan'   => self::BULAN,
            'setting'     => $ctx['setting'],
            'analysis'    => $analysis,
            'finMonthly'  => $finMonthly,
            'internal'    => $ctx['internal'],
            'initiatives' => $initiatives,
            'quarters'    => $quarters,
            'changes'     => $changes,
        ]);
    }

    /* =====================================================================
     |  KALKULASI
     * ===================================================================== */

    private function context(int $tahun): array
    {
        $setting  = AnalysisFinanceSetting::forYear($tahun);
        $tracking = trackingTagihanPerusahaan::whereYear('tanggal_perkiraan_mulai', $tahun)->get();
        $monthly  = $this->monthlyTagihan($tracking);

        return [
            'tahun'       => $tahun,
            'setting'     => $setting,
            'monthly'     => $monthly,
            'internal'    => $this->internalRows($tahun, $monthly),
            'initiatives' => AnalysisFinanceInitiative::where('tahun', $tahun)->orderBy('no')->orderBy('id')->get(),
            'ind_text'    => AnalysisFinanceIndicator::where('tahun', $tahun)->get()->keyBy('no'),
        ];
    }

    private function monthlyTagihan($tracking): array
    {
        $rows = [];
        for ($m = 1; $m <= 12; $m++) {
            $rows[$m] = ['total' => 0, 'telat' => 0, 'ontime' => 0, 'selesai' => 0, 'nominal' => 0];
        }

        $today = Carbon::today();

        foreach ($tracking as $t) {
            $m   = Carbon::parse($t->tanggal_perkiraan_mulai)->month;
            $due = Carbon::parse($t->tanggal_perkiraan_selesai ?: $t->tanggal_perkiraan_mulai)->startOfDay();

            $done = !empty($t->tanggal_selesai) || $t->status === 'selesai';
            $late = $t->status === 'telat'
                || (!empty($t->tanggal_selesai) && Carbon::parse($t->tanggal_selesai)->startOfDay()->gt($due))
                || (!$done && $due->lt($today));

            if (!($done || $late || $due->lt($today))) {
                continue;
            }

            $rows[$m]['total']++;
            $rows[$m]['nominal'] += (float) $t->nominal;
            if ($done) {
                $rows[$m]['selesai']++;
            }
            $late ? $rows[$m]['telat']++ : $rows[$m]['ontime']++;
        }

        return $rows;
    }

    private function internalRows(int $tahun, array $monthly): array
    {
        $manual = AnalysisFinanceInternalProcess::where('tahun', $tahun)->get()->keyBy('bulan');
        $rows   = [];

        for ($m = 1; $m <= 12; $m++) {
            $mn      = $manual->get($m);
            $shadowP = $monthly[$m]['total'];
            $shadowB = $monthly[$m]['selesai'];
            $inP     = $mn?->pengajuan_barang;
            $inB     = $mn?->bukti_kas;
            $p       = $inP !== null ? (int) $inP : $shadowP;
            $b       = $inB !== null ? (int) $inB : $shadowB;

            $rows[$m] = [
                'bulan'     => $m,
                'nama'      => self::BULAN[$m],
                'pengajuan' => $p,
                'bukti'     => $b,
                'persen'    => $p > 0 ? round($b / $p * 100, 1) : null,
                'shadow_p'  => $shadowP,
                'shadow_b'  => $shadowB,
                'in_p'      => $inP,
                'in_b'      => $inB,
                'p_manual'  => $inP !== null,
                'b_manual'  => $inB !== null,
                'catatan'   => $mn?->catatan,
            ];
        }

        return $rows;
    }

    private function buildKpi(array $ctx, int $sm, int $em): array
    {
        $s = $ctx['setting'];

        $tot = $tel = $p = $b = 0;
        for ($m = $sm; $m <= $em; $m++) {
            $tot += $ctx['monthly'][$m]['total'];
            $tel += $ctx['monthly'][$m]['telat'];
            $p   += $ctx['internal'][$m]['pengajuan'];
            $b   += $ctx['internal'][$m]['bukti'];
        }

        $ontime = $tot - $tel;
        $r1     = $tot > 0 ? $ontime / $tot * 100 : 0;
        $r2     = $p > 0 ? $b / $p * 100 : 0;

        $endQ  = (int) ceil($em / 3);
        $ideas = $ctx['initiatives']->filter(fn($i) => $i->analysis_target <= $endQ);
        $impl  = $ideas->where('is_implemented', true)->count();

        $defs = [
            [
                'no' => 1, 'perspektif' => 'Financial', 'class' => 'financial',
                'analysis' => 'Pembayaran Tagihan Tepat Waktu',
                'deskripsi' => 'Menjaga kelancaran arus kas & hubungan vendor',
                'formula' => 'Jumlah pembayaran tepat waktu ÷ Total pembayaran',
                'target' => (float) $s->target_tagihan, 'target_label' => '≥' . $this->trim($s->target_tagihan) . '%',
                'bobot' => (float) $s->bobot_tagihan, 'realisasi' => $r1, 'unit' => '%',
            ],
            [
                'no' => 2, 'perspektif' => 'Internal Process', 'class' => 'internal',
                'analysis' => 'Ketepatan Pencairan Dana Operasional',
                'deskripsi' => 'Memastikan operasional tim berjalan sesuai SLA',
                'formula' => '(Jumlah pencairan tepat waktu ÷ total permintaan) × 100%',
                'target' => (float) $s->target_pencairan, 'target_label' => $this->trim($s->target_pencairan) . '%',
                'bobot' => (float) $s->bobot_pencairan, 'realisasi' => $r2, 'unit' => '%',
            ],
            [
                'no' => 3, 'perspektif' => 'Learning & Growth', 'class' => 'learning',
                'analysis' => 'Inisiatif Efisiensi Keuangan',
                'deskripsi' => 'Inovasi efisiensi biaya operasional & sistem',
                'formula' => 'Jumlah Ide Efisiensi Diterapkan',
                'target' => (float) $s->target_ide, 'target_label' => '≥' . $s->target_ide . ' ide',
                'bobot' => (float) $s->bobot_ide, 'realisasi' => (float) $impl, 'unit' => ' ide',
            ],
        ];

        foreach ($defs as &$d) {
            $o = $ctx['ind_text']->get($d['no']);
            if ($o?->analysis)  { $d['analysis']  = $o->analysis; }
            if ($o?->deskripsi) { $d['deskripsi'] = $o->deskripsi; }
            if ($o?->formula)   { $d['formula']   = $o->formula; }
        }
        unset($d);

        $total = 0;
        foreach ($defs as &$d) {
            $d['ach']  = $d['target'] > 0 ? $d['realisasi'] / $d['target'] * 100 : 0;
            $d['skor'] = round($d['ach'] * $d['bobot'] / 100, 2);
            [$d['status'], $d['status_class']] = $this->statusLabel($d['ach']);
            $total += $d['skor'];
        }
        unset($d);

        $bobotTotal = array_sum(array_column($defs, 'bobot'));

        return [
            'indicators'  => $defs,
            'total'       => round($total, 2),
            'bobot_total' => $bobotTotal,
            'persen'      => $bobotTotal > 0 ? round($total / $bobotTotal * 100, 1) : 0,
            'fin'         => ['total' => $tot, 'telat' => $tel, 'ontime' => $ontime, 'persen' => round($r1, 1)],
            'ide'         => [
                'target' => (int) $s->target_ide,
                'total'  => $ideas->count(),
                'impl'   => $impl,
                'ach'    => $s->target_ide > 0 ? round($impl / $s->target_ide * 100, 1) : 0,
            ],
        ];
    }

    private function statusLabel(float $ach): array
    {
        return match (true) {
            $ach >= 125 => ['Melampaui', 'success'],
            $ach >= 100 => ['Tercapai', 'success'],
            $ach >= 90  => ['Mendekati', 'warning'],
            default     => ['Belum Tercapai', 'danger'],
        };
    }

    private function trim($v): string
    {
        return rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
    }

    private function quarters(array $ctx, int $tahun): array
    {
        $manual = AnalysisFinanceQuarterlyReport::where('tahun', $tahun)->get()->keyBy('quarter');
        $curQ   = (int) ceil(now()->month / 3);
        $out    = [];
        $prev   = null;

        for ($q = 1; $q <= 4; $q++) {
            $status = match (true) {
                $tahun < now()->year => 'Selesai',
                $tahun > now()->year => 'Proyeksi',
                $q < $curQ           => 'Selesai',
                $q === $curQ         => 'Berjalan',
                default              => 'Proyeksi',
            };

            if ($status === 'Proyeksi') {
                $bobot    = (float) ($ctx['setting']->bobot_tagihan + $ctx['setting']->bobot_pencairan + $ctx['setting']->bobot_ide);
                $autoPct  = 100.0;
                $autoSkor = $bobot;
            } else {
                $k        = $this->buildKpi($ctx, ($q - 1) * 3 + 1, $q * 3);
                $autoPct  = (float) $k['persen'];
                $autoSkor = (float) $k['total'];
            }

            $mn  = $manual->get($q);
            $pct = $mn?->nilai_persen !== null ? (float) $mn->nilai_persen : $autoPct;

            $out[] = [
                'quarter'     => $q,
                'label'       => 'TR ' . $q . ' ' . $tahun,
                'status'      => $mn?->status ?: $status,
                'pct'         => $pct,
                'skor'        => $mn?->skor_kinerja !== null ? (float) $mn->skor_kinerja : $autoSkor,
                'auto_pct'    => $autoPct,
                'auto_skor'   => $autoSkor,
                'auto_status' => $status,
                'in_pct'      => $mn?->nilai_persen,
                'in_skor'     => $mn?->skor_kinerja,
                'in_status'   => $mn?->status,
                'catatan'     => $mn?->catatan,
                'keterangan'  => $mn?->keterangan,
                'projection'  => ($mn?->status ?: $status) === 'Proyeksi',
                'delta'       => $prev !== null ? round($pct - $prev, 1) : null,
            ];

            $prev = $pct;
        }

        return $out;
    }

    /* =====================================================================
     |  SIMPAN
     * ===================================================================== */

    public function saveSetting(Request $request)
    {
        $data = $request->validate([
            'tahun'            => 'required|integer',
            'target_tagihan'   => 'required|numeric|min:0|max:100',
            'target_pencairan' => 'required|numeric|min:0|max:100',
            'target_ide'       => 'required|integer|min:0',
            'bobot_tagihan'    => 'required|numeric|min:0|max:100',
            'bobot_pencairan'  => 'required|numeric|min:0|max:100',
            'bobot_ide'        => 'required|numeric|min:0|max:100',
        ]);

        $sum = $data['bobot_tagihan'] + $data['bobot_pencairan'] + $data['bobot_ide'];
        if (abs($sum - 100) > 0.001) {
            return back()->withErrors(['bobot' => 'Total bobot harus 100% (saat ini ' . $sum . '%).'])->withInput();
        }

        AnalysisFinanceSetting::updateOrCreate(['tahun' => $data['tahun']], collect($data)->except('tahun')->all());

        return $this->done($data['tahun'], 'Target dan bobot berhasil disimpan.');
    }

    /** Simpan Point A & B. Hanya field yang dikirim yang diubah. */
    public function saveIndicators(Request $request)
    {
        $request->validate([
            'tahun'            => 'required|integer',
            'rows'             => 'required|array',
            'rows.*.indikator' => 'nullable|string|max:255',
            'rows.*.formula'   => 'nullable|string',
            'rows.*.deskripsi' => 'nullable|string',
            'rows.*.target'    => 'nullable|numeric|min:0',
            'rows.*.bobot'     => 'nullable|numeric|min:0|max:100',
        ]);

        if ($err = $this->applyIndicators((int) $request->tahun, $request->rows)) {
            return back()->withErrors(['bobot' => $err])->withInput();
        }

        return $this->done($request->tahun, 'Skema penilaian berhasil disimpan.');
    }

    private function applyIndicators(int $tahun, array $rows): ?string
    {
        $setting = AnalysisFinanceSetting::forYear($tahun);
        $map     = [
            1 => ['target_tagihan', 'bobot_tagihan'],
            2 => ['target_pencairan', 'bobot_pencairan'],
            3 => ['target_ide', 'bobot_ide'],
        ];

        $vals  = [];
        $bobot = 0;
        foreach ($map as $no => [$tCol, $bCol]) {
            $r = $rows[$no] ?? [];
            if (!$this->blank($r['target'] ?? null)) {
                $vals[$tCol] = $no === 3 ? (int) round((float) $r['target']) : (float) $r['target'];
            }
            if (!$this->blank($r['bobot'] ?? null)) {
                $vals[$bCol] = (float) $r['bobot'];
            }
            $bobot += $vals[$bCol] ?? (float) $setting->{$bCol};
        }

        if (abs($bobot - 100) > 0.001) {
            return 'Total bobot harus 100% (saat ini ' . $bobot . '%).';
        }

        DB::transaction(function () use ($tahun, $rows, $vals) {
            if ($vals) {
                AnalysisFinanceSetting::updateOrCreate(['tahun' => $tahun], $vals);
            }
            foreach ([1, 2, 3] as $no) {
                $r    = $rows[$no] ?? [];
                $text = [];
                foreach (['indikator' => 'analysis', 'formula' => 'formula', 'deskripsi' => 'deskripsi'] as $in => $col) {
                    if (array_key_exists($in, $r)) {
                        $text[$col] = $this->blank($r[$in]) ? null : trim($r[$in]);
                    }
                }
                if ($text) {
                    AnalysisFinanceIndicator::updateOrCreate(['tahun' => $tahun, 'no' => $no], $text);
                }
            }
        });

        return null;
    }

    public function saveInternalProcess(Request $request)
    {
        $request->validate([
            'tahun'                   => 'required|integer',
            'rows'                    => 'required|array',
            'rows.*.pengajuan_barang' => 'nullable|integer|min:0',
            'rows.*.bukti_kas'        => 'nullable|integer|min:0',
            'rows.*.catatan'          => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->rows as $bulan => $r) {
                if ($bulan < 1 || $bulan > 12) {
                    continue;
                }
                $this->upsertInternal((int) $request->tahun, (int) $bulan, $r['pengajuan_barang'] ?? null, $r['bukti_kas'] ?? null, $r['catatan'] ?? null);
            }
        });

        return $this->done($request->tahun, 'Data Internal Process berhasil disimpan.');
    }

    public function saveInitiatives(Request $request)
    {
        $request->validate([
            'tahun'                    => 'required|integer',
            'items'                    => 'nullable|array',
            'items.*.initiative'       => 'nullable|string|max:255',
            'items.*.analysis_target'  => 'nullable|integer|min:1|max:4',
            'items.*.estimated_impact' => 'nullable|string|max:255',
        ]);

        $items = collect($request->items ?? [])->map(fn($i) => [
            'initiative'       => trim($i['initiative'] ?? ''),
            'description'      => $i['description'] ?? null,
            'analysis_target'  => (int) ($i['analysis_target'] ?? 1),
            'estimated_impact' => $i['estimated_impact'] ?? null,
            'is_implemented'   => !empty($i['is_implemented']),
        ])->all();

        $this->replaceInitiatives((int) $request->tahun, $items);

        return $this->done($request->tahun, 'Daftar inisiatif berhasil disimpan.');
    }

    public function addInitiative(Request $request)
    {
        $d = $request->validate([
            'tahun'            => 'required|integer',
            'initiative'       => 'required|string|max:255',
            'description'      => 'nullable|string',
            'analysis_target'  => 'required|integer|min:1|max:4',
            'estimated_impact' => 'nullable|string|max:255',
        ]);

        AnalysisFinanceInitiative::create([
            'tahun'            => $d['tahun'],
            'no'               => (int) AnalysisFinanceInitiative::where('tahun', $d['tahun'])->max('no') + 1,
            'initiative'       => trim($d['initiative']),
            'description'      => $d['description'] ?? null,
            'analysis_target'  => $d['analysis_target'],
            'estimated_impact' => $d['estimated_impact'] ?? null,
            'is_implemented'   => $request->boolean('is_implemented'),
        ]);

        return $this->done($d['tahun'], 'Inisiatif ditambahkan.');
    }

    public function destroyInitiative(Request $request, $id)
    {
        $ini   = AnalysisFinanceInitiative::findOrFail($id);
        $tahun = $ini->tahun;
        $ini->delete();

        $no = 1;
        foreach (AnalysisFinanceInitiative::where('tahun', $tahun)->orderBy('no')->orderBy('id')->get() as $row) {
            $row->update(['no' => $no++]);
        }

        return $this->done($tahun, 'Inisiatif dihapus.');
    }

    public function saveChanges(Request $request)
    {
        $request->validate([
            'tahun'              => 'required|integer',
            'quarter'            => 'required|integer|min:1|max:4',
            'items'              => 'nullable|array',
            'items.*.keterangan' => 'nullable|string|max:255',
            'items.*.nilai_lalu' => 'nullable|numeric',
            'items.*.nilai_ini'  => 'nullable|numeric',
        ]);

        DB::transaction(function () use ($request) {
            $key = ['tahun' => (int) $request->tahun, 'quarter' => (int) $request->quarter];
            AnalysisFinanceChange::where($key)->delete();

            $no = 1;
            foreach ($request->items ?? [] as $i) {
                if ($this->blank($i['keterangan'] ?? null)) {
                    continue;
                }
                AnalysisFinanceChange::create($key + [
                    'no'         => $no++,
                    'keterangan' => trim($i['keterangan']),
                    'nilai_lalu' => $this->blank($i['nilai_lalu'] ?? null) ? null : (float) $i['nilai_lalu'],
                    'nilai_ini'  => $this->blank($i['nilai_ini'] ?? null) ? null : (float) $i['nilai_ini'],
                ]);
            }
        });

        return $this->done($request->tahun, 'Tabel persentase perubahan berhasil disimpan.');
    }

    public function saveQuarterly(Request $request)
    {
        $request->validate([
            'tahun'               => 'required|integer',
            'rows'                => 'required|array',
            'rows.*.nilai_persen' => 'nullable|numeric',
            'rows.*.skor_kinerja' => 'nullable|numeric',
            'rows.*.status'       => 'nullable|in:Selesai,Berjalan,Proyeksi',
            'rows.*.catatan'      => 'nullable|string',
            'rows.*.keterangan'   => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->rows as $q => $r) {
                if ($q < 1 || $q > 4) {
                    continue;
                }
                $this->upsertQuarter((int) $request->tahun, (int) $q, $r);
            }
        });

        return $this->done($request->tahun, 'Rekapitulasi triwulan berhasil disimpan.');
    }

    /* ---- helper persist ---- */

    private function blank($v): bool
    {
        return $v === null || trim((string) $v) === '';
    }

    private function upsertInternal(int $tahun, int $bulan, $p, $b, $catatan): void
    {
        $key = ['tahun' => $tahun, 'bulan' => $bulan];

        if ($this->blank($p) && $this->blank($b) && $this->blank($catatan)) {
            AnalysisFinanceInternalProcess::where($key)->delete();
            return;
        }

        AnalysisFinanceInternalProcess::updateOrCreate($key, [
            'pengajuan_barang' => $this->blank($p) ? null : (int) $p,
            'bukti_kas'        => $this->blank($b) ? null : (int) $b,
            'catatan'          => $this->blank($catatan) ? null : $catatan,
        ]);
    }

    private function upsertQuarter(int $tahun, int $q, array $r): void
    {
        $key  = ['tahun' => $tahun, 'quarter' => $q];
        $vals = [
            'nilai_persen' => $this->blank($r['nilai_persen'] ?? null) ? null : (float) $r['nilai_persen'],
            'skor_kinerja' => $this->blank($r['skor_kinerja'] ?? null) ? null : (float) $r['skor_kinerja'],
            'status'       => $this->blank($r['status'] ?? null) ? null : $r['status'],
            'catatan'      => $this->blank($r['catatan'] ?? null) ? null : $r['catatan'],
            'keterangan'   => $this->blank($r['keterangan'] ?? null) ? null : $r['keterangan'],
        ];

        if (count(array_filter($vals, fn($v) => $v !== null)) === 0) {
            AnalysisFinanceQuarterlyReport::where($key)->delete();
            return;
        }

        AnalysisFinanceQuarterlyReport::updateOrCreate($key, $vals);
    }

    private function replaceInitiatives(int $tahun, array $items): void
    {
        DB::transaction(function () use ($tahun, $items) {
            AnalysisFinanceInitiative::where('tahun', $tahun)->delete();

            $no = 1;
            foreach ($items as $i) {
                if ($this->blank($i['initiative'])) {
                    continue;
                }
                AnalysisFinanceInitiative::create([
                    'tahun'            => $tahun,
                    'no'               => $no++,
                    'initiative'       => $i['initiative'],
                    'description'      => $i['description'] ?? null,
                    'analysis_target'  => min(4, max(1, (int) ($i['analysis_target'] ?? 1))),
                    'estimated_impact' => $i['estimated_impact'] ?? null,
                    'is_implemented'   => (bool) ($i['is_implemented'] ?? false),
                ]);
            }
        });
    }

    private function done($tahun, string $msg)
    {
        return redirect()->route('analysis-finance.index', ['tahun' => $tahun] + request()->only('mode', 'bulan', 'q'))
            ->cookie('swal_success', $msg, 1);
    }

    /* =====================================================================
     |  IMPORT CSV  &  TEMPLATE
     * ===================================================================== */

    public function template(Request $request, string $type)
    {
        abort_unless(isset(self::TEMPLATE_HEADERS[$type]), 404);

        $tahun  = (int) $request->get('tahun', now()->year);
        $header = self::TEMPLATE_HEADERS[$type];
        $rows   = [];

        if ($type === 'internal') {
            $ctx = $this->context($tahun);
            foreach ($ctx['internal'] as $r) {
                $rows[] = [$r['nama'], $r['pengajuan'], $r['bukti'], $r['catatan']];
            }
        } elseif ($type === 'initiatives') {
            $list = AnalysisFinanceInitiative::where('tahun', $tahun)->orderBy('no')->get();
            foreach ($list as $i) {
                $rows[] = [$i->no, $i->initiative, $i->description, 'TR ' . $i->analysis_target, $i->estimated_impact, $i->is_implemented ? 'Diterapkan' : 'Belum'];
            }
            if (empty($rows)) {
                $rows[] = [1, 'Digitalisasi Reimbursement via Web Tool', 'Otomatisasi form klaim karyawan berbasis online', 'TR 1', 'Memangkas waktu approval sebesar 50%', 'Diterapkan'];
                $rows[] = [2, 'Contoh inisiatif belum berjalan', 'Deskripsi singkat', 'TR 4', 'Estimasi dampak', 'Belum'];
            }
        } elseif ($type === 'skema' || $type === 'overview') {
            $ctx = $this->context($tahun);
            foreach ($this->buildKpi($ctx, 1, 12)['indicators'] as $i) {
                $rows[] = $type === 'skema'
                    ? [$i['no'], $i['analysis'], $i['formula'], $i['target'], $i['bobot']]
                    : [$i['no'], $i['deskripsi'], $i['target'], $i['bobot']];
            }
        } else {
            $ctx = $this->context($tahun);
            foreach ($this->quarters($ctx, $tahun) as $r) {
                $rows[] = ['TR ' . $r['quarter'], $r['pct'], $r['skor'], $r['status'], $r['catatan'], $r['keterangan']];
            }
        }

        return response()->streamDownload(function () use ($header, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $header, ';');
            foreach ($rows as $r) {
                fputcsv($out, $r, ';');
            }
            fclose($out);
        }, "template-analysis-finance-{$type}-{$tahun}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request, string $type)
    {
        abort_unless(isset(self::TEMPLATE_HEADERS[$type]), 404);

        $request->validate([
            'tahun' => 'required|integer',
            'file'  => 'required|file|mimes:csv,txt|max:2048',
        ], [
            'file.mimes' => 'File harus berformat CSV. Simpan dari Excel dengan "Save As → CSV (Comma/Semicolon delimited)".',
        ]);

        $tahun = (int) $request->tahun;
        $rows  = $this->readCsv($request->file('file')->getRealPath());

        if (empty($rows)) {
            return back()->withErrors(['file' => 'File kosong atau header tidak terbaca. Gunakan template yang disediakan.']);
        }

        $missing = array_diff(array_slice(self::TEMPLATE_HEADERS[$type], 0, 1), array_keys($rows[0]));
        if ($missing) {
            return back()->withErrors(['file' => 'Kolom wajib tidak ditemukan: ' . implode(', ', $missing) . '. Gunakan template yang disediakan.']);
        }

        $count = 0;

        if ($type === 'internal') {
            DB::transaction(function () use ($rows, $tahun, &$count) {
                foreach ($rows as $r) {
                    $m = $this->parseMonth($r['bulan'] ?? '');
                    if (!$m) {
                        continue;
                    }
                    $this->upsertInternal($tahun, $m, $this->num($r['pengajuan_barang'] ?? null), $this->num($r['bukti_kas'] ?? null), $r['catatan'] ?? null);
                    $count++;
                }
            });
        } elseif ($type === 'initiatives') {
            $items = [];
            foreach ($rows as $r) {
                if ($this->blank($r['initiative'] ?? null)) {
                    continue;
                }
                $items[] = [
                    'initiative'       => trim($r['initiative']),
                    'description'      => $r['description'] ?? null,
                    'analysis_target'  => $this->parseQuarter($r['analysis_target'] ?? '') ?: 1,
                    'estimated_impact' => $r['estimated_impact'] ?? null,
                    'is_implemented'   => in_array(strtolower(trim($r['status'] ?? '')), ['diterapkan', 'ya', 'yes', 'y', '1', 'true', 'selesai', 'implemented'], true),
                ];
            }
            $this->replaceInitiatives($tahun, $items);
            $count = count($items);
        } elseif ($type === 'skema' || $type === 'overview') {
            $payload = [];
            foreach ($rows as $r) {
                $no = (int) ($r['no'] ?? 0);
                if ($no < 1 || $no > 3) {
                    continue;
                }
                $row = [];
                foreach (['indikator', 'formula', 'deskripsi'] as $k) {
                    if (array_key_exists($k, $r)) {
                        $row[$k] = $r[$k];
                    }
                }
                foreach (['target', 'bobot'] as $k) {
                    if (array_key_exists($k, $r)) {
                        $row[$k] = $this->num($r[$k]);
                    }
                }
                $payload[$no] = $row;
                $count++;
            }
            if ($err = $this->applyIndicators($tahun, $payload)) {
                return back()->withErrors(['file' => $err]);
            }
        } else {
            DB::transaction(function () use ($rows, $tahun, &$count) {
                foreach ($rows as $r) {
                    $q = $this->parseQuarter($r['triwulan'] ?? '');
                    if (!$q) {
                        continue;
                    }
                    $status = ucfirst(strtolower(trim($r['status'] ?? '')));
                    $this->upsertQuarter($tahun, $q, [
                        'nilai_persen' => $this->num($r['pencapaian_persen'] ?? null),
                        'skor_kinerja' => $this->num($r['skor_kinerja'] ?? null),
                        'status'       => in_array($status, ['Selesai', 'Berjalan', 'Proyeksi']) ? $status : null,
                        'catatan'      => $r['catatan'] ?? null,
                        'keterangan'   => $r['keterangan'] ?? null,
                    ]);
                    $count++;
                }
            });
        }

        return $this->done($tahun, "Import berhasil: {$count} baris diproses.");
    }

    private function readCsv(string $path): array
    {
        $h = fopen($path, 'r');
        if (!$h) {
            return [];
        }

        $first = fgets($h);
        if ($first === false) {
            fclose($h);
            return [];
        }
        $first = preg_replace('/^\xEF\xBB\xBF/', '', $first);
        $delim = substr_count($first, ';') >= substr_count($first, ',') ? ';' : ',';
        rewind($h);

        $header = fgetcsv($h, 0, $delim);
        if (!$header) {
            fclose($h);
            return [];
        }
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $header    = array_map(fn($c) => str_replace([' ', '-'], '_', strtolower(trim($c))), $header);

        $rows = [];
        while (($line = fgetcsv($h, 0, $delim)) !== false) {
            if (count(array_filter($line, fn($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }
            $line   = array_pad($line, count($header), null);
            $rows[] = array_combine($header, array_slice($line, 0, count($header)));
        }
        fclose($h);

        return $rows;
    }

    private function num($v)
    {
        if ($this->blank($v)) {
            return null;
        }
        $v = str_replace(['%', ' '], '', (string) $v);
        if (str_contains($v, ',') && str_contains($v, '.')) {
            $v = str_replace('.', '', $v);
        }
        $v = str_replace(',', '.', $v);

        return is_numeric($v) ? $v + 0 : null;
    }

    private function parseMonth($v): ?int
    {
        $v = strtolower(trim((string) $v));
        if (is_numeric($v) && $v >= 1 && $v <= 12) {
            return (int) $v;
        }
        foreach (self::BULAN as $n => $name) {
            $name = strtolower($name);
            if ($v === $name || $v === substr($name, 0, 3)) {
                return $n;
            }
        }

        return null;
    }

    private function parseQuarter($v): ?int
    {
        return preg_match('/[1-4]/', (string) $v, $m) ? (int) $m[0] : null;
    }
}