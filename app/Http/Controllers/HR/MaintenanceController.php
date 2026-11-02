<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\JurnalAkuntansi;
use App\Models\Maintenance;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MaintenanceController extends Controller
{
    private const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    private const BULAN_FULL = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    private const TX_COLS = ['No', 'No. KK', 'Tanggal', 'Section', 'Jenis', 'Keterangan', 'Biaya'];

    /* =====================================================================
     |  HALAMAN UTAMA + CRUD
     * ===================================================================== */

    public function index(Request $request)
    {
        $tahun = (int) $request->input('tahun', date('Y'));

        $rows = $this->filteredQuery($request)->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->get();

        $kendaraan = $rows->where('section', Maintenance::KENDARAAN)->values();
        $kantor = $rows->where('section', Maintenance::KANTOR)->values();

        $jenisList = Maintenance::whereNotNull('jenis_maintenance')->distinct()->orderBy('jenis_maintenance')->pluck('jenis_maintenance');

        $tahunList = Maintenance::selectRaw('YEAR(tanggal) as y')->distinct()->pluck('y')->push($tahun)->push((int) date('Y'))->unique()->sortDesc()->values();

        $bulanFull = self::BULAN_FULL;

        return view('HR.Maintenance.index', compact('kendaraan', 'kantor', 'jenisList', 'tahunList', 'tahun', 'bulanFull'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['bulan'] = Carbon::parse($data['tanggal'])->month;

        Maintenance::create($data);

        return redirect()->back()->with('success', 'Data maintenance berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $item = Maintenance::findOrFail($id);
        $data = $request->validate($this->rules());
        $data['bulan'] = Carbon::parse($data['tanggal'])->month;

        $item->update($data);

        return redirect()->back()->with('success', 'Data maintenance berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Maintenance::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Data maintenance berhasil dihapus.');
    }

    private function rules(): array
    {
        return [
            'tanggal' => 'required|date',
            'jenis_maintenance' => 'required|string|max:150',
            'section' => 'required|in:' . implode(',', Maintenance::SECTIONS),
            'biaya' => 'required|numeric|min:0',
            'no_kk' => 'nullable|string|max:50',
            'keterangan' => 'nullable|string',
        ];
    }

    private function filteredQuery(Request $request)
    {
        return Maintenance::query()->whereYear('tanggal', (int) $request->input('tahun', date('Y')))->when($request->filled('section'), fn($q) => $q->where('section', $request->section))->when($request->filled('jenis'), fn($q) => $q->where('jenis_maintenance', $request->jenis))->when($request->filled('bulan'), fn($q) => $q->where('bulan', (int) $request->bulan));
    }

    /* =====================================================================
     |  AMBIL DATA DARI JURNAL AKUNTANSI (salin nilai, tanpa relasi)
     * ===================================================================== */

    public function jurnalList(Request $request)
    {
        $q = JurnalAkuntansi::query()
            ->when($request->filled('start_date'), fn($x) => $x->whereDate('tanggal_transaksi', '>=', $request->start_date))
            ->when($request->filled('end_date'), fn($x) => $x->whereDate('tanggal_transaksi', '<=', $request->end_date))
            ->when($request->filled('q'), function ($x) use ($request) {
                $s = '%' . $request->q . '%';
                $x->where(fn($w) => $w->where('keterangan', 'like', $s)->orWhere('nomor_kk', 'like', $s));
            })
            ->where(fn($w) => $w->where('debit', '>', 0)->orWhere('kredit', '>', 0));

        $rows = $q->orderBy('tanggal_transaksi', 'desc')->orderBy('id', 'desc')->limit(500)->get();

        $existing = Maintenance::whereNotNull('no_kk')
            ->get(['no_kk', 'tanggal'])
            ->map(fn($m) => $m->no_kk . '|' . $m->tanggal->year)
            ->flip();

        $data = $rows
            ->map(function ($j) use ($existing) {
                $ket = $j->keterangan;
                if (is_string($ket) && str_starts_with(trim($ket), '[')) {
                    $d = json_decode($ket, true);
                    if (is_array($d)) {
                        $ket = implode(' | ', $d);
                    }
                } elseif (is_array($ket)) {
                    $ket = implode(' | ', $ket);
                }

                $tgl = Carbon::parse($j->tanggal_transaksi);
                $biaya = (float) ($j->kredit > 0 ? $j->kredit : $j->debit);

                return [
                    'id' => $j->id,
                    'no_kk' => $j->nomor_kk,
                    'tanggal' => $tgl->format('Y-m-d'),
                    'keterangan' => (string) $ket,
                    'biaya' => $biaya,
                    'sudah' => $j->nomor_kk ? $existing->has($j->nomor_kk . '|' . $tgl->year) : false,
                ];
            })
            ->values();

        return response()->json(['data' => $data, 'limited' => $rows->count() >= 500]);
    }

    public function importJurnal(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.tanggal' => 'required|date',
            'items.*.biaya' => 'required|numeric|min:0',
            'items.*.jenis_maintenance' => 'required|string|max:150',
            'items.*.section' => 'required|in:' . implode(',', Maintenance::SECTIONS),
            'items.*.no_kk' => 'nullable|string|max:50',
            'items.*.keterangan' => 'nullable|string',
        ]);

        $saved = 0;
        $skipped = 0;

        DB::transaction(function () use ($request, &$saved, &$skipped) {
            foreach ($request->items as $it) {
                $tgl = Carbon::parse($it['tanggal']);

                if (!empty($it['no_kk']) && Maintenance::where('no_kk', $it['no_kk'])->whereYear('tanggal', $tgl->year)->exists()) {
                    $skipped++;
                    continue;
                }

                Maintenance::create([
                    'bulan' => $tgl->month,
                    'jenis_maintenance' => $it['jenis_maintenance'],
                    'tanggal' => $tgl->format('Y-m-d'),
                    'biaya' => $it['biaya'],
                    'keterangan' => $it['keterangan'] ?? null,
                    'no_kk' => $it['no_kk'] ?? null,
                    'section' => $it['section'],
                ]);
                $saved++;
            }
        });

        return response()->json([
            'success' => true,
            'message' => "{$saved} data berhasil ditambahkan" . ($skipped ? ", {$skipped} dilewati karena No. KK sudah ada." : '.'),
        ]);
    }

    /* =====================================================================
     |  ANALITIK (cards + grafik)
     * ===================================================================== */

    public function analytics(Request $request)
    {
        $tahun = (int) $request->input('tahun', date('Y'));
        $all = $this->allRows($request);
        $cur = $this->inYear($all, $tahun);
        $prev = $this->inYear($all, $tahun - 1);

        $total = (float) $cur->sum('biaya');
        $prevTotal = (float) $prev->sum('biaya');
        $top = $cur->sortByDesc('biaya')->first();

        $months = [];
        foreach (range(1, 12) as $m) {
            $c = $cur->filter(fn($r) => $r->tanggal->month === $m);
            $months[] = [
                'label' => self::BULAN[$m - 1],
                'total' => (float) $c->sum('biaya'),
                'kendaraan' => (float) $c->where('section', Maintenance::KENDARAAN)->sum('biaya'),
                'kantor' => (float) $c->where('section', Maintenance::KANTOR)->sum('biaya'),
                'prev' => (float) $prev->filter(fn($r) => $r->tanggal->month === $m)->sum('biaya'),
                'count' => $c->count(),
            ];
        }

        $quarters = [];
        foreach (range(1, 4) as $q) {
            $inQ = fn($r) => (int) ceil($r->tanggal->month / 3) === $q;
            $c = $cur->filter($inQ);
            $quarters[] = [
                'label' => 'Q' . $q,
                'total' => (float) $c->sum('biaya'),
                'kendaraan' => (float) $c->where('section', Maintenance::KENDARAAN)->sum('biaya'),
                'kantor' => (float) $c->where('section', Maintenance::KANTOR)->sum('biaya'),
                'prev' => (float) $prev->filter($inQ)->sum('biaya'),
                'count' => $c->count(),
            ];
        }

        $years = $all
            ->groupBy(fn($r) => $r->tanggal->year)
            ->map(
                fn($c, $y) => [
                    'year' => (int) $y,
                    'total' => (float) $c->sum('biaya'),
                    'kendaraan' => (float) $c->where('section', Maintenance::KENDARAAN)->sum('biaya'),
                    'kantor' => (float) $c->where('section', Maintenance::KANTOR)->sum('biaya'),
                    'count' => $c->count(),
                ],
            )
            ->sortKeys()
            ->values();

        $jenis = $cur
            ->groupBy('jenis_maintenance')
            ->map(
                fn($c, $j) => [
                    'jenis' => $j,
                    'total' => (float) $c->sum('biaya'),
                    'count' => $c->count(),
                ],
            )
            ->sortByDesc('total')
            ->values();

        return response()->json([
            'tahun' => $tahun,
            'total' => $total,
            'prev_total' => $prevTotal,
            'growth' => $prevTotal > 0 ? round((($total - $prevTotal) / $prevTotal) * 100, 1) : null,
            'count' => $cur->count(),
            'avg' => $cur->count() ? $total / $cur->count() : 0,
            'max' => $top
                ? [
                    'biaya' => $top->biaya,
                    'jenis' => $top->jenis_maintenance,
                    'no_kk' => $top->no_kk,
                    'tanggal' => $top->tanggal->format('d-m-Y'),
                ]
                : null,
            'kendaraan' => (float) $cur->where('section', Maintenance::KENDARAAN)->sum('biaya'),
            'kantor' => (float) $cur->where('section', Maintenance::KANTOR)->sum('biaya'),
            'months' => $months,
            'quarters' => $quarters,
            'years' => $years,
            'jenis' => $jenis,
        ]);
    }

    /* =====================================================================
     |  DETAIL (setiap card & grafik punya penyajian sendiri)
     * ===================================================================== */

    public function detail(Request $request)
    {
        $tahun = (int) $request->input('tahun', date('Y'));
        $all = $this->allRows($request);
        $cur = $this->inYear($all, $tahun);
        $prev = $this->inYear($all, $tahun - 1);
        $key = $request->input('key');

        $payload = match ($request->input('type')) {
            'total' => $this->dTotal($cur, $prev, $tahun),
            'count' => $this->dCount($cur, $tahun),
            'average' => $this->dAverage($cur, $tahun),
            'max' => $this->dMax($cur, $tahun),
            'growth' => $this->dGrowth($cur, $prev, $tahun),
            'section' => $this->dSection($cur, $tahun, (string) $key),
            'month' => $this->dMonth($cur, $prev, $tahun, (int) $key),
            'quarter' => $this->dQuarter($cur, $prev, $tahun, (int) $key),
            'year' => $this->dYear($all, (int) $key),
            'jenis' => $this->dJenis($cur, $tahun, (string) $key),
            default => abort(404),
        };

        return response()->json($payload);
    }

    private function dTotal($cur, $prev, $tahun): array
    {
        $by = $this->monthTotals($cur);
        $total = array_sum($by);
        $pt = (float) $prev->sum('biaya');
        $active = count(array_filter($by));

        $cum = [];
        $s = 0;
        foreach ($by as $v) {
            $s += $v;
            $cum[] = $s;
        }

        $rows = [];
        foreach ($by as $i => $v) {
            $rows[] = [self::BULAN_FULL[$i], $this->rp($v), $total > 0 ? round(($v / $total) * 100, 1) . '%' : '0%', $i === 0 ? '-' : $this->pct($v, $by[$i - 1])];
        }

        return $this->pack("Total biaya maintenance {$tahun}", 'Akumulasi dan kontribusi tiap bulan', [["Total {$tahun}", $this->rp($total)], ['Total ' . ($tahun - 1), $this->rp($pt)], ['Selisih', $this->rp($total - $pt)], ['Rata-rata per bulan aktif', $this->rp($active ? $total / $active : 0)]], ['type' => 'area', 'labels' => self::BULAN, 'series' => [['name' => 'Akumulasi biaya', 'data' => $cum]], 'money' => true], ['Bulan', 'Biaya', 'Kontribusi', 'vs bulan lalu'], $rows);
    }

    private function dCount($cur, $tahun): array
    {
        $g = $cur
            ->groupBy('jenis_maintenance')
            ->map(
                fn($c, $j) => [
                    'jenis' => $j,
                    'n' => $c->count(),
                    'sum' => (float) $c->sum('biaya'),
                ],
            )
            ->sortByDesc('n')
            ->values();

        $perBulan = collect(range(1, 12))->mapWithKeys(fn($m) => [$m => $cur->filter(fn($r) => $r->tanggal->month === $m)->count()]);
        $busy = $perBulan->sortDesc()->keys()->first();

        return $this->pack("Jumlah transaksi {$tahun}", 'Sebaran transaksi berdasarkan jenis maintenance', [['Total transaksi', $cur->count()], ['Jenis maintenance', $g->count()], ['Bulan tersibuk', $cur->count() ? self::BULAN_FULL[$busy - 1] . ' (' . $perBulan[$busy] . ')' : '-'], ['Transaksi punya No. KK', $cur->whereNotNull('no_kk')->count()]], ['type' => 'bar', 'horizontal' => true, 'labels' => $g->pluck('jenis')->all(), 'series' => [['name' => 'Transaksi', 'data' => $g->pluck('n')->all()]], 'money' => false], ['Jenis', 'Jumlah', 'Total biaya', 'Rata-rata'], $g->map(fn($x) => [$x['jenis'], $x['n'], $this->rp($x['sum']), $this->rp($x['sum'] / $x['n'])])->all());
    }

    private function dAverage($cur, $tahun): array
    {
        $v = $cur->pluck('biaya')->sort()->values()->all();
        $n = count($v);

        if ($n === 0) {
            return $this->pack("Rata-rata biaya {$tahun}", 'Belum ada data', [], null, [], []);
        }

        $avg = array_sum($v) / $n;
        $med = $n % 2 ? $v[($n - 1) / 2] : ($v[$n / 2 - 1] + $v[$n / 2]) / 2;
        $sd = sqrt(array_sum(array_map(fn($x) => ($x - $avg) ** 2, $v)) / $n);
        $min = $v[0];
        $max = $v[$n - 1];

        $bins = 5;
        $w = ($max - $min) / $bins ?: 1;
        $counts = array_fill(0, $bins, 0);
        foreach ($v as $x) {
            $counts[min($bins - 1, (int) floor(($x - $min) / $w))]++;
        }
        $labels = [];
        for ($i = 0; $i < $bins; $i++) {
            $labels[] = $this->short($min + $i * $w) . ' - ' . $this->short($min + ($i + 1) * $w);
        }

        $rows = $cur->groupBy('jenis_maintenance')->map(fn($c, $j) => [$j, $c->count(), $this->rp($c->avg('biaya')), $this->rp($c->max('biaya'))])->sortByDesc(fn($r) => $r[1])->values()->all();

        return $this->pack("Rata-rata biaya per transaksi {$tahun}", 'Statistik dan distribusi nilai transaksi', [['Rata-rata', $this->rp($avg)], ['Median', $this->rp($med)], ['Terkecil', $this->rp($min)], ['Terbesar', $this->rp($max)], ['Simpangan baku', $this->rp($sd)]], ['type' => 'bar', 'labels' => $labels, 'series' => [['name' => 'Jumlah transaksi', 'data' => $counts]], 'money' => false], ['Jenis', 'Transaksi', 'Rata-rata', 'Tertinggi'], $rows);
    }

    private function dMax($cur, $tahun): array
    {
        $top = $cur->sortByDesc('biaya')->take(10)->values();
        $total = (float) $cur->sum('biaya');
        $first = $top->first();

        return $this->pack("Transaksi terbesar {$tahun}", '10 transaksi dengan biaya tertinggi', [['Terbesar', $first ? $this->rp($first->biaya) : '-'], ['Jenis', $first->jenis_maintenance ?? '-'], ['No. KK', $first->no_kk ?? '-'], ['Porsi dari total', $first && $total > 0 ? round(($first->biaya / $total) * 100, 1) . '%' : '-']], ['type' => 'bar', 'horizontal' => true, 'labels' => $top->map(fn($r) => $r->jenis_maintenance . ' (' . $r->tanggal->format('d/m') . ')')->all(), 'series' => [['name' => 'Biaya', 'data' => $top->pluck('biaya')->all()]], 'money' => true], self::TX_COLS, $this->txRows($top, false));
    }

    private function dGrowth($cur, $prev, $tahun): array
    {
        $a = $this->monthTotals($cur);
        $b = $this->monthTotals($prev);
        $rows = [];
        foreach ($a as $i => $v) {
            $rows[] = [self::BULAN_FULL[$i], $this->rp($v), $this->rp($b[$i]), $this->pct($v, $b[$i]), $i ? $this->pct($v, $a[$i - 1]) : '-'];
        }
        $peak = array_search(max($a), $a);

        return $this->pack("Pertumbuhan biaya {$tahun} vs " . ($tahun - 1), 'Perbandingan tahun ke tahun (YoY) dan bulan ke bulan (MoM)', [['Pertumbuhan tahunan', $this->pct(array_sum($a), array_sum($b))], ['Bulan tertinggi', max($a) > 0 ? self::BULAN_FULL[$peak] . ' (' . $this->rp(max($a)) . ')' : '-'], ['Bulan lebih mahal dari tahun lalu', count(array_filter(array_keys($a), fn($i) => $a[$i] > $b[$i])) . ' dari 12']], ['type' => 'line', 'labels' => self::BULAN, 'series' => [['name' => "Tahun {$tahun}", 'data' => $a], ['name' => 'Tahun ' . ($tahun - 1), 'data' => $b]], 'money' => true], ['Bulan', "{$tahun}", (string) ($tahun - 1), 'YoY', 'MoM'], $rows);
    }

    private function dSection($cur, $tahun, string $key): array
    {
        abort_unless(in_array($key, Maintenance::SECTIONS), 404);

        $c = $cur->where('section', $key);
        $total = (float) $c->sum('biaya');
        $all = (float) $cur->sum('biaya');
        $g = $c->groupBy('jenis_maintenance')->map(fn($x) => (float) $x->sum('biaya'))->sortDesc();

        $rows = [];
        foreach (range(1, 12) as $m) {
            $x = $c->filter(fn($r) => $r->tanggal->month === $m);
            $rows[] = [self::BULAN_FULL[$m - 1], $x->count(), $this->rp($x->sum('biaya')), $total > 0 ? round(($x->sum('biaya') / $total) * 100, 1) . '%' : '0%'];
        }

        return $this->pack("{$key} {$tahun}", 'Komposisi jenis dan sebaran bulanan', [['Total biaya', $this->rp($total)], ['Transaksi', $c->count()], ['Porsi dari seluruh biaya', $all > 0 ? round(($total / $all) * 100, 1) . '%' : '0%'], ['Rata-rata per transaksi', $this->rp($c->count() ? $total / $c->count() : 0)]], ['type' => 'donut', 'labels' => $g->keys()->all(), 'series' => $g->values()->all(), 'money' => true], ['Bulan', 'Transaksi', 'Biaya', 'Kontribusi'], $rows);
    }

    private function dMonth($cur, $prev, $tahun, int $m): array
    {
        abort_unless($m >= 1 && $m <= 12, 404);

        $c = $cur->filter(fn($r) => $r->tanggal->month === $m);
        $p = $prev->filter(fn($r) => $r->tanggal->month === $m);
        $g = $c->groupBy('jenis_maintenance')->map(fn($x) => (float) $x->sum('biaya'))->sortDesc();
        $t = (float) $c->sum('biaya');

        return $this->pack(self::BULAN_FULL[$m - 1] . " {$tahun}", 'Seluruh transaksi pada bulan ini', [['Total bulan ini', $this->rp($t)], ['Bulan sama tahun lalu', $this->rp($p->sum('biaya'))], ['Perubahan', $this->pct($t, (float) $p->sum('biaya'))], ['Kendaraan / Kantor', $this->rp($c->where('section', Maintenance::KENDARAAN)->sum('biaya')) . ' / ' . $this->rp($c->where('section', Maintenance::KANTOR)->sum('biaya'))]], ['type' => 'bar', 'labels' => $g->keys()->all(), 'series' => [['name' => 'Biaya', 'data' => $g->values()->all()]], 'money' => true], self::TX_COLS, $this->txRows($c));
    }

    private function dQuarter($cur, $prev, $tahun, int $q): array
    {
        abort_unless($q >= 1 && $q <= 4, 404);

        $ms = range(($q - 1) * 3 + 1, ($q - 1) * 3 + 3);
        $c = $cur->filter(fn($r) => in_array($r->tanggal->month, $ms));
        $p = $prev->filter(fn($r) => in_array($r->tanggal->month, $ms));
        $t = (float) $c->sum('biaya');

        return $this->pack("Quarter {$q} {$tahun}", self::BULAN_FULL[$ms[0] - 1] . ' - ' . self::BULAN_FULL[$ms[2] - 1], [["Total Q{$q}", $this->rp($t)], ["Q{$q} " . ($tahun - 1), $this->rp($p->sum('biaya'))], ['Perubahan', $this->pct($t, (float) $p->sum('biaya'))], ['Kendaraan / Kantor', $this->rp($c->where('section', Maintenance::KENDARAAN)->sum('biaya')) . ' / ' . $this->rp($c->where('section', Maintenance::KANTOR)->sum('biaya'))]], ['type' => 'bar', 'labels' => array_map(fn($m) => self::BULAN[$m - 1], $ms), 'series' => [['name' => "Tahun {$tahun}", 'data' => array_map(fn($m) => (float) $c->filter(fn($r) => $r->tanggal->month === $m)->sum('biaya'), $ms)], ['name' => 'Tahun ' . ($tahun - 1), 'data' => array_map(fn($m) => (float) $p->filter(fn($r) => $r->tanggal->month === $m)->sum('biaya'), $ms)]], 'money' => true], self::TX_COLS, $this->txRows($c));
    }

    private function dYear($all, int $year): array
    {
        $c = $this->inYear($all, $year);
        $prev = $this->inYear($all, $year - 1);
        $t = (float) $c->sum('biaya');
        $qs = fn($sec) => array_map(fn($q) => (float) $c->where('section', $sec)->filter(fn($r) => (int) ceil($r->tanggal->month / 3) === $q)->sum('biaya'), range(1, 4));

        $rows = $c->groupBy('jenis_maintenance')->map(fn($x, $j) => [$j, $x->count(), $this->rp($x->sum('biaya')), $t > 0 ? round(($x->sum('biaya') / $t) * 100, 1) . '%' : '0%'])->sortByDesc(fn($r) => $r[1])->values()->all();

        return $this->pack("Tahun {$year}", 'Rincian per quarter dan per jenis', [['Total', $this->rp($t)], ['Transaksi', $c->count()], ['vs tahun sebelumnya', $this->pct($t, (float) $prev->sum('biaya'))], ['Porsi kendaraan', $t > 0 ? round(($c->where('section', Maintenance::KENDARAAN)->sum('biaya') / $t) * 100, 1) . '%' : '0%']], ['type' => 'bar', 'stacked' => true, 'labels' => ['Q1', 'Q2', 'Q3', 'Q4'], 'series' => [['name' => 'Kendaraan', 'data' => $qs(Maintenance::KENDARAAN)], ['name' => 'Kantor', 'data' => $qs(Maintenance::KANTOR)]], 'money' => true], ['Jenis', 'Transaksi', 'Biaya', 'Porsi'], $rows);
    }

    private function dJenis($cur, $tahun, string $key): array
    {
        $c = $cur->where('jenis_maintenance', $key)->values();

        return $this->pack("{$key} {$tahun}", 'Riwayat dan tren jenis maintenance ini', [['Total biaya', $this->rp($c->sum('biaya'))], ['Transaksi', $c->count()], ['Rata-rata', $this->rp($c->avg('biaya') ?? 0)], ['Terakhir', $c->max('tanggal') ? $c->max('tanggal')->format('d-m-Y') : '-']], ['type' => 'line', 'labels' => self::BULAN, 'series' => [['name' => $key, 'data' => $this->monthTotals($c)]], 'money' => true], self::TX_COLS, $this->txRows($c));
    }

    /* =====================================================================
     |  HELPER
     * ===================================================================== */

    private function allRows(Request $request): Collection
    {
        return Maintenance::query()->when($request->filled('section'), fn($q) => $q->where('section', $request->section))->orderBy('tanggal')->get();
    }

    private function inYear(Collection $c, int $y): Collection
    {
        return $c->filter(fn($r) => $r->tanggal->year === $y)->values();
    }

    private function monthTotals(Collection $c): array
    {
        return array_map(fn($m) => (float) $c->filter(fn($r) => $r->tanggal->month === $m)->sum('biaya'), range(1, 12));
    }

    private function txRows(Collection $c, bool $sortDate = true): array
    {
        $c = $sortDate ? $c->sortBy('tanggal')->values() : $c->values();

        return $c->map(fn($r, $i) => [$i + 1, $r->no_kk ?: '-', $r->tanggal->format('d-m-Y'), $r->section, $r->jenis_maintenance, $r->keterangan ?: '-', $this->rp($r->biaya)])->all();
    }

    private function pack(string $title, string $sub, array $stats, ?array $chart, array $cols, array $rows): array
    {
        return [
            'title' => $title,
            'subtitle' => $sub,
            'stats' => array_map(fn($s) => ['label' => $s[0], 'value' => $s[1]], $stats),
            'chart' => $chart,
            'columns' => $cols,
            'rows' => $rows,
        ];
    }

    private function rp($n): string
    {
        return 'Rp ' . number_format((float) $n, 0, ',', '.');
    }

    private function short($n): string
    {
        return $n >= 1000000 ? round($n / 1000000, 1) . ' jt' : round($n / 1000) . ' rb';
    }

    private function pct($a, $b): string
    {
        if ($b == 0) {
            return $a > 0 ? 'Baru' : '-';
        }
        $d = round((($a - $b) / $b) * 100, 1);
        return ($d > 0 ? '+' : '') . $d . '%';
    }

    /* =====================================================================
     |  EXPORT / TEMPLATE / IMPORT EXCEL
     * ===================================================================== */

    public function export_pdf(Request $request)
    {
        $rows = $this->filteredQuery($request)->orderBy('tanggal')->get();
        $tahun = (int) $request->input('tahun', date('Y'));

        $pdf = Pdf::loadView('HR.Maintenance.export_pdf', [
            'kendaraan' => $rows->where('section', Maintenance::KENDARAAN)->values(),
            'kantor' => $rows->where('section', Maintenance::KANTOR)->values(),
            'tahun' => $tahun,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("Data_Maintenance_{$tahun}.pdf");
    }

    public function export_excel(Request $request)
    {
        $rows = $this->filteredQuery($request)->orderBy('tanggal')->get();

        $ss = new Spreadsheet();
        $sh = $ss->getActiveSheet()->setTitle('Data Maintenance');

        $heads = ['NO', 'BULAN', 'JENIS MAINTENANCE', 'TANGGAL', 'BIAYA', 'KETERANGAN', 'NO. KK', 'SECTION'];
        foreach ($heads as $i => $h) {
            $sh->setCellValue(chr(65 + $i) . '1', $h);
        }
        $sh->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        $r = 2;
        foreach ($rows as $i => $x) {
            $sh->setCellValue("A{$r}", $i + 1);
            $sh->setCellValue("B{$r}", self::BULAN_FULL[$x->bulan - 1] ?? '');
            $sh->setCellValue("C{$r}", $x->jenis_maintenance);
            $sh->setCellValue("D{$r}", $x->tanggal->format('d-m-Y'));
            $sh->setCellValue("E{$r}", $x->biaya);
            $sh->setCellValue("F{$r}", $x->keterangan);
            $sh->setCellValue("G{$r}", $x->no_kk);
            $sh->setCellValue("H{$r}", $x->section);
            $sh->getStyle("E{$r}")
                ->getNumberFormat()
                ->setFormatCode('#,##0');
            $r++;
        }

        $sh->setCellValue("D{$r}", 'TOTAL');
        $sh->setCellValue("E{$r}", $rows->sum('biaya'));
        $sh->getStyle("D{$r}:E{$r}")
            ->getFont()
            ->setBold(true);
        $sh->getStyle("E{$r}")
            ->getNumberFormat()
            ->setFormatCode('#,##0');

        foreach (range('A', 'H') as $col) {
            $sh->getColumnDimension($col)->setAutoSize(true);
        }

        return response()->streamDownload(
            function () use ($ss) {
                $writer = new Xlsx($ss);
                $writer->save('php://output');
            },
            'Data_Maintenance_' . date('YmdHis') . '.xlsx',
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }

    public function template_excel()
    {
        $ss = new Spreadsheet();
        $sh = $ss->getActiveSheet();

        $heads = ['TANGGAL (DD-MM-YYYY)', 'JENIS MAINTENANCE', 'SECTION', 'BIAYA', 'KETERANGAN', 'NO. KK'];
        foreach ($heads as $i => $h) {
            $sh->setCellValue(chr(65 + $i) . '1', $h);
        }
        $sh->getStyle('A1:F1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E0E0']],
        ]);

        $sh->fromArray(['10-07-2026', 'Servis Berkala', 'Maintenance Kendaraan', 150000, 'Ganti oli', 'KK-0001'], null, 'A2');
        $sh->fromArray(['12-07-2026', 'Service AC', 'Maintenance Kantor', 250000, 'Cuci AC ruang meeting', 'KK-0002'], null, 'A3');
        foreach (range('A', 'F') as $col) {
            $sh->getColumnDimension($col)->setAutoSize(true);
        }

        return response()->streamDownload(
            function () use ($ss) {
                $writer = new Xlsx($ss);
                $writer->save('php://output');
            },
            'Template_Import_Maintenance.xlsx',
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }

    public function import(Request $request)
    {
        $request->validate(['file_excel' => 'required|file|mimes:xlsx,xls,csv']);

        try {
            $sheet = IOFactory::load($request->file('file_excel')->getRealPath())->getActiveSheet();
            $data = $sheet->toArray(null, true, false, false);

            $norm = fn($v) => preg_replace('/[^a-z0-9]/', '', strtolower((string) $v));
            $aliases = [
                'tanggal' => ['tanggal', 'tgl'],
                'jenis' => ['jenismaintenance', 'jenis', 'kategori', 'item'],
                'section' => ['section', 'seksi', 'kelompok'],
                'biaya' => ['biaya', 'harga', 'nominal', 'total'],
                'keterangan' => ['keterangan', 'catatan', 'deskripsi'],
                'no_kk' => ['nokk', 'nomorkk', 'kk'],
            ];

            // Cari baris header terbaik di 10 baris pertama
            $bestRow = null;
            $bestMap = [];
            $bestHits = 0;
            foreach (array_slice($data, 0, 10, true) as $ri => $row) {
                $map = [];
                foreach ($aliases as $key => $list) {
                    foreach ($row as $ci => $cell) {
                        $h = $norm($cell);
                        if ($h === '' || in_array($ci, $map, true)) {
                            continue;
                        }
                        foreach ($list as $a) {
                            if (str_starts_with($h, $a)) {
                                $map[$key] = $ci;
                                continue 3;
                            }
                        }
                    }
                }
                if (count($map) > $bestHits) {
                    $bestHits = count($map);
                    $bestMap = $map;
                    $bestRow = $ri;
                }
            }

            if ($bestRow === null || !isset($bestMap['tanggal'], $bestMap['jenis'])) {
                throw new \Exception('Header tidak ditemukan. Minimal harus ada kolom Tanggal dan Jenis Maintenance.');
            }

            $get = fn($row, $k) => isset($bestMap[$k]) ? $row[$bestMap[$k]] ?? null : null;
            $count = 0;
            $skipped = 0;

            DB::transaction(function () use ($data, $bestRow, $get, &$count, &$skipped) {
                foreach (array_slice($data, $bestRow + 1) as $row) {
                    $jenis = trim((string) $get($row, 'jenis'));
                    $tgl = $this->parseDate($get($row, 'tanggal'));

                    if ($jenis === '' && $tgl === null) {
                        continue;
                    } // baris kosong
                    if ($jenis === '' || $tgl === null) {
                        $skipped++;
                        continue;
                    }

                    $biaya = $get($row, 'biaya');
                    $biaya = is_numeric($biaya) ? (float) $biaya : (float) preg_replace('/[^0-9]/', '', (string) $biaya);

                    $sec = (string) $get($row, 'section');
                    $sec = stripos($sec, 'kendara') !== false ? Maintenance::KENDARAAN : Maintenance::KANTOR;

                    Maintenance::create([
                        'bulan' => Carbon::parse($tgl)->month,
                        'jenis_maintenance' => $jenis,
                        'tanggal' => $tgl,
                        'biaya' => $biaya,
                        'keterangan' => $get($row, 'keterangan'),
                        'no_kk' => $get($row, 'no_kk') ? trim((string) $get($row, 'no_kk')) : null,
                        'section' => $sec,
                    ]);
                    $count++;
                }
            });

            if ($count === 0) {
                throw new \Exception('Tidak ada baris valid (Tanggal dan Jenis Maintenance wajib terisi).');
            }

            return redirect()
                ->route('HR.maintenance.index')
                ->with('success', "Berhasil mengimpor {$count} data" . ($skipped ? " ({$skipped} baris dilewati)." : '.'));
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }

    private function parseDate($v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }

        try {
            if (is_numeric($v)) {
                return XlDate::excelToDateTimeObject($v)->format('Y-m-d');
            }

            $v = trim((string) $v);
            return preg_match('/^\d{1,2}-\d{1,2}-\d{4}$/', $v) ? Carbon::createFromFormat('d-m-Y', $v)->format('Y-m-d') : Carbon::parse($v)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
