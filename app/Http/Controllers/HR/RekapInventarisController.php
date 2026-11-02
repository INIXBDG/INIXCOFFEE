<?php

namespace App\Http\Controllers\HR;

use App\Exports\RekapInventarisExport;
use App\Http\Controllers\Controller;
use App\Imports\RekapInventarisImport;
use App\Models\Inventaris;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class RekapInventarisController extends Controller
{
    private const BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    private const BULAN_SINGKAT = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    private const KATEGORI_FORM = ['Office', 'Kelas', 'Education', 'Sales/Tim Digital', 'ITSM', 'Cicilan Kendaraan'];

    private const KOLOM_TRANSAKSI = ['No', 'Tanggal', 'ID Barang', 'ID Inventaris', 'No. KK', 'Nama Barang', 'Qty', 'Kategori', 'Lokasi', 'Keterangan', 'Total Harga'];

    public function index()
    {
        $kategoris = Inventaris::whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');

        $defaultTahun = $this->tahunTerbaru();

        return view('HR.Rekap_Inventaris.index', [
            'kategoris' => $kategoris,
            'kategoriForm' => self::KATEGORI_FORM,
            'defaultTahun' => $defaultTahun,
            'tahunList' => $this->daftarTahun($defaultTahun),
        ]);
    }

    public function getRekapData(Request $request)
    {
        $tahun = $this->resolveTahun($request);

        $whole = $this->scoped($tahun, $request)->get();
        $wholePrev = $this->scoped($tahun - 1, $request)->get();
        $periode = $this->filterPeriode($whole, $request);
        $periodePrev = $this->filterPeriode($wholePrev, $request);

        $total = $this->sumHarga($whole);
        $prevTotal = $this->sumHarga($wholePrev);
        $count = $whole->count();
        $max = $whole->sortByDesc('total_harga')->first();

        $kategoriTotal = $whole
            ->groupBy(fn ($i) => $i->kategori ?: 'Lainnya')
            ->map(fn ($g) => $this->sumHarga($g))
            ->sortDesc();
        $topNama = $kategoriTotal->keys()->first();

        $totalPeriode = $this->sumHarga($periode);
        $months = $this->bulanan($whole, $wholePrev);

        return response()->json([
            'success' => true,
            'tahun' => $tahun,
            'periode' => $this->periodeLabel($request, $tahun),
            'periode_lalu' => $this->periodeLabel($request, $tahun - 1),
            'cards' => [
                'total' => $total,
                'qty' => (int) $whole->sum('qty'),
                'count' => $count,
                'avg' => $count ? $total / $count : 0,
                'max' => $max ? ['nama' => $max->name ?: '-', 'total' => (float) $max->total_harga] : null,
                'top_kategori' => $topNama === null ? null : [
                    'nama' => (string) $topNama,
                    'total' => (float) $kategoriTotal->first(),
                    'persen' => $total > 0 ? round($kategoriTotal->first() / $total * 100, 1) : 0,
                ],
                'growth' => $this->persen($total, $prevTotal),
                'prev_total' => $prevTotal,
            ],
            'tab1' => $this->rekap($periode, fn ($i) => $i->kategori ?: 'Lainnya', fn ($k) => (string) $k, $totalPeriode),
            'tab2' => $this->rekap($periode, fn ($i) => $this->bulan($i), fn ($k) => self::BULAN[(int) $k - 1] . ' ' . $tahun, $totalPeriode, true),
            'tabLokasi' => $this->rekap($periode, fn ($i) => $i->ruangan ?: 'Lainnya', fn ($k) => (string) $k, $totalPeriode),
            'months' => $months,
            'quarters' => $this->kuartalan($months),
            'years' => $this->tahunan($request),
            'komposisi' => [
                'labels' => $kategoriTotal->keys()->map(fn ($k) => (string) $k)->values()->all(),
                'values' => $kategoriTotal->values()->map(fn ($v) => (float) $v)->all(),
            ],
            'jenis' => $whole
                ->groupBy(fn ($i) => $i->name ?: 'Tanpa nama')
                ->map(fn ($g, $nama) => [
                    'nama' => (string) $nama,
                    'total' => $this->sumHarga($g),
                    'qty' => (int) $g->sum('qty'),
                    'count' => $g->count(),
                ])
                ->sortByDesc('total')
                ->take(10)
                ->values()
                ->all(),
            'chart_kategori' => $this->bandingkan($periode, $periodePrev, fn ($i) => $i->kategori ?: 'Lainnya'),
            'chart_lokasi' => $this->bandingkan($periode, $periodePrev, fn ($i) => $i->ruangan ?: 'Lainnya'),
        ]);
    }

    public function getDetailData(Request $request)
    {
        $tipe = (string) $request->input('tipe');
        $nilai = (string) $request->input('nilai');
        $tahun = $tipe === 'year' && is_numeric($nilai) ? (int) $nilai : $this->resolveTahun($request);
        $sub = $this->subJudul($request, $tahun);

        $data = $this->scoped($tahun, $request)->get();
        $prev = $this->scoped($tahun - 1, $request)->get();

        $periodeData = $this->filterPeriode($data, $request);
        $periodePrev = $this->filterPeriode($prev, $request);
        $subPeriode = $this->rentangBulan($request) ? $sub . ' | ' . $this->periodeLabel($request, $tahun, false) : $sub;

        $detail = match ($tipe) {
            'total' => $this->detailTotal($data, $prev, $sub),
            'qty' => $this->detailQty($data, $sub),
            'count' => $this->detailCount($data, $sub),
            'average' => $this->detailAverage($data, $sub),
            'max' => $this->detailMax($data, $sub),
            'top_kategori' => $this->detailTopKategori($data, $prev, $sub),
            'growth' => $this->detailGrowth($data, $prev, $tahun, $sub),
            'month' => $this->detailBulan($data, $prev, $tahun, (int) $nilai, $sub),
            'quarter' => $this->detailQuarter($data, $prev, $tahun, (int) $nilai, $sub),
            'year' => $this->detailYear($data, $prev, $tahun, $sub),
            'kategori', 'lokasi' => $this->detailKelompok($tipe, $nilai, $periodeData, $periodePrev, $tahun, $subPeriode),
            'jenis' => $this->detailKelompok($tipe, $nilai, $data, $prev, $tahun, $sub),
            default => null,
        };

        if ($detail === null) {
            return response()->json(['success' => false, 'message' => 'Jenis detail tidak dikenali.'], 422);
        }

        return response()->json(['success' => true] + $detail);
    }

    public function exportPdf(Request $request)
    {
        $tahun = $this->resolveTahun($request);

        $items = $this->withPeriode($this->scoped($tahun, $request), $request)
            ->orderBy('waktu_pembelian')
            ->get();

        $mappedData = $items->map(function ($item) {
            return (object) [
                'idbarang' => $item->idbarang,
                'idinventaris' => $item->idinventaris,
                'tanggal_beli' => $item->waktu_pembelian,
                'no_kk' => $item->no_kk,
                'nama_barang' => $item->name,
                'kategori' => $item->kategori,
                'lokasi' => $item->ruangan,
                'harga' => $item->total_harga,
            ];
        });

        $periode = $this->periodeLabel($request, $tahun, false);

        $pdf = Pdf::loadView('HR.Rekap_Inventaris.export_pdf', [
            'data' => $mappedData,
            'periode' => $periode,
        ]);

        return $pdf->download('Rekap_Inventaris_' . str_replace(' ', '_', $periode) . '.pdf');
    }

    public function getLokasi($kategori)
    {
        return response()->json(
            Inventaris::where('kategori', $kategori)
                ->whereNotNull('ruangan')
                ->where('ruangan', '!=', '')
                ->distinct()
                ->orderBy('ruangan')
                ->pluck('ruangan')
                ->values()
        );
    }

    public function getJenis($lokasi)
    {
        return response()->json(
            Inventaris::where('ruangan', $lokasi)
                ->whereNotNull('name')
                ->where('name', '!=', '')
                ->distinct()
                ->orderBy('name')
                ->pluck('name', 'name')
        );
    }

    public function export(Request $request)
    {
        return Excel::download(new RekapInventarisExport($request), 'rekap_inventaris.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            $import = new RekapInventarisImport();
            Excel::import($import, $request->file('file'));

            return back()->with('success', 'Berhasil mengimpor ' . $import->getImportedCount() . ' data rekap inventaris.');
        } catch (\Exception $e) {
            Log::error($e);
            return back()->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }

    public function syncFromInventaris()
    {
        return back()->with('success', 'Sinkronisasi tidak diperlukan karena data Rekap sudah terhubung langsung dengan data Inventaris utama.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'qty' => 'required|integer|min:1',
            'total_harga' => 'required|numeric|min:0',
            'waktu_pembelian' => 'required|date',
            'idbarang' => 'nullable|string|max:255',
            'idinventaris' => 'nullable|string|max:255',
            'ruangan' => 'nullable|string|max:255',
            'no_kk' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            $kode = substr(str_pad(strtoupper(substr($request->kategori, 0, 3)), 3, 'X'), 0, 3);

            $data = array_filter(
                $request->only(['name', 'kategori', 'qty', 'total_harga', 'waktu_pembelian', 'idbarang', 'idinventaris', 'ruangan', 'no_kk', 'deskripsi']),
                fn ($v) => $v !== null && $v !== ''
            );

            Inventaris::create(array_merge($data, [
                'kodebarang' => $kode,
                'type' => in_array($request->kategori, ['ITSM', 'Sales/Tim Digital'], true) ? 'E' : 'NE',
                'harga_beli' => $request->total_harga / $request->qty,
                'satuan' => 'unit',
                'kondisi' => 'baik',
            ]));

            return back()->with('success', 'Berhasil menambahkan data rekap inventaris.');
        } catch (\Exception $e) {
            Log::error($e);
            return back()->withInput()->with('error', 'Gagal menambahkan data: ' . $e->getMessage());
        }
    }

    private function tahunTerbaru(): int
    {
        $latest = Inventaris::whereNotNull('waktu_pembelian')->orderByDesc('waktu_pembelian')->first();

        return $latest ? Carbon::parse($latest->waktu_pembelian)->year : (int) date('Y');
    }

    private function daftarTahun(int $default): Collection
    {
        return Inventaris::whereNotNull('waktu_pembelian')
            ->selectRaw('YEAR(waktu_pembelian) as y')
            ->distinct()
            ->pluck('y')
            ->push($default, (int) date('Y'))
            ->map(fn ($y) => (int) $y)
            ->unique()
            ->sortDesc()
            ->values();
    }

    private function resolveTahun(Request $request): int
    {
        return $request->filled('tahun') ? (int) $request->tahun : $this->tahunTerbaru();
    }

    private function filterScope($query, Request $request)
    {
        return $query
            ->when($request->filled('kategori'), fn ($q) => $q->where('kategori', $request->kategori))
            ->when($request->filled('lokasi'), fn ($q) => $q->where('ruangan', $request->lokasi))
            ->when($request->filled('jenis'), fn ($q) => $q->where('name', $request->jenis));
    }

    private function scoped(int $tahun, Request $request)
    {
        return $this->filterScope(Inventaris::query()->whereYear('waktu_pembelian', $tahun), $request);
    }

    private function rentangBulan(Request $request): ?array
    {
        if ($request->mode_periode === 'bulan' && $request->filled('bulan')) {
            $b = max(1, min(12, (int) $request->bulan));

            return [$b, $b];
        }

        if ($request->mode_periode === 'quartal' && $request->filled('quartal')) {
            $q = max(1, min(4, (int) $request->quartal));

            return [($q - 1) * 3 + 1, $q * 3];
        }

        return null;
    }

    private function withPeriode($query, Request $request)
    {
        $r = $this->rentangBulan($request);

        return $r ? $query->whereRaw('MONTH(waktu_pembelian) between ? and ?', $r) : $query;
    }

    private function filterPeriode(Collection $items, Request $request): Collection
    {
        $r = $this->rentangBulan($request);

        if (!$r) {
            return $items;
        }

        return $items->filter(function ($i) use ($r) {
            $m = $this->bulan($i);

            return $m >= $r[0] && $m <= $r[1];
        })->values();
    }

    private function periodeLabel(Request $request, int $tahun, bool $prefix = true): string
    {
        if ($request->mode_periode === 'bulan' && $request->filled('bulan')) {
            return self::BULAN[max(1, min(12, (int) $request->bulan)) - 1] . ' ' . $tahun;
        }

        if ($request->mode_periode === 'quartal' && $request->filled('quartal')) {
            return 'Quartal ' . max(1, min(4, (int) $request->quartal)) . ' ' . $tahun;
        }

        return $prefix ? 'Tahun ' . $tahun : (string) $tahun;
    }

    private function subJudul(Request $request, int $tahun): string
    {
        $bagian = ['Tahun ' . $tahun];

        if ($request->filled('kategori')) {
            $bagian[] = 'Kategori: ' . $request->kategori;
        }

        if ($request->filled('lokasi')) {
            $bagian[] = 'Lokasi: ' . $request->lokasi;
        }

        if ($request->filled('jenis')) {
            $bagian[] = 'Jenis: ' . $request->jenis;
        }

        return implode(' | ', $bagian);
    }

    private function bulan($item): int
    {
        return (int) Carbon::parse($item->waktu_pembelian)->month;
    }

    private function sumHarga($items): float
    {
        return (float) $items->sum('total_harga');
    }

    private function rupiah($angka): string
    {
        return 'Rp ' . number_format((float) $angka, 0, ',', '.');
    }

    private function angka($angka): string
    {
        return number_format((float) $angka, 0, ',', '.');
    }

    private function persen(float $a, float $b): ?float
    {
        return $b > 0 ? round(($a - $b) / $b * 100, 1) : null;
    }

    private function persenFmt(float $p): string
    {
        return str_replace('.', ',', (string) round($p, 1)) . '%';
    }

    private function persenTeks(float $a, float $b): string
    {
        $p = $this->persen($a, $b);

        if ($p === null) {
            return $a > 0 ? 'Baru' : '-';
        }

        return ($p > 0 ? '+' : '') . str_replace('.', ',', (string) $p) . '%';
    }

    private function perBulan($items): array
    {
        $hasil = [];

        for ($m = 1; $m <= 12; $m++) {
            $hasil[$m] = ['total' => 0.0, 'qty' => 0, 'count' => 0];
        }

        foreach ($items as $i) {
            $m = $this->bulan($i);
            $hasil[$m]['total'] += (float) $i->total_harga;
            $hasil[$m]['qty'] += (int) $i->qty;
            $hasil[$m]['count']++;
        }

        return $hasil;
    }

    private function bulanan(Collection $now, Collection $prev): array
    {
        $a = $this->perBulan($now);
        $b = $this->perBulan($prev);
        $hasil = [];

        for ($m = 1; $m <= 12; $m++) {
            $hasil[] = [
                'label' => self::BULAN_SINGKAT[$m - 1],
                'total' => $a[$m]['total'],
                'prev' => $b[$m]['total'],
                'qty' => $a[$m]['qty'],
                'count' => $a[$m]['count'],
            ];
        }

        return $hasil;
    }

    private function kuartalan(array $months): array
    {
        $hasil = [];

        for ($q = 0; $q < 4; $q++) {
            $slice = array_slice($months, $q * 3, 3);
            $hasil[] = [
                'label' => 'Q' . ($q + 1),
                'total' => (float) array_sum(array_column($slice, 'total')),
                'prev' => (float) array_sum(array_column($slice, 'prev')),
                'qty' => (int) array_sum(array_column($slice, 'qty')),
                'count' => (int) array_sum(array_column($slice, 'count')),
            ];
        }

        return $hasil;
    }

    private function tahunan(Request $request): array
    {
        $rows = $this->filterScope(Inventaris::query()->whereNotNull('waktu_pembelian'), $request)
            ->selectRaw("YEAR(waktu_pembelian) as y, COALESCE(NULLIF(kategori, ''), 'Lainnya') as kat, COUNT(*) as c, SUM(qty) as q, SUM(total_harga) as t")
            ->groupBy('y', 'kat')
            ->orderBy('y')
            ->get();

        $list = $rows->groupBy('y')->map(fn ($g, $y) => [
            'year' => (int) $y,
            'count' => (int) $g->sum('c'),
            'qty' => (int) $g->sum('q'),
            'total' => (float) $g->sum('t'),
        ])->values();

        $labels = $list->pluck('year')->all();

        $series = $rows->pluck('kat')->unique()->values()->map(function ($kat) use ($rows, $labels) {
            return [
                'name' => (string) $kat,
                'data' => array_map(
                    fn ($y) => (float) $rows->filter(fn ($r) => (int) $r->y === (int) $y && $r->kat === $kat)->sum('t'),
                    $labels
                ),
            ];
        })->all();

        return [
            'list' => $list->all(),
            'labels' => $labels,
            'series' => $series,
        ];
    }

    private function rekap(Collection $items, \Closure $kunci, \Closure $nama, float $total, bool $urutKunci = false): array
    {
        $hasil = $items->groupBy($kunci)->map(function ($g, $k) use ($nama, $total) {
            $t = $this->sumHarga($g);

            return [
                'key' => (string) $k,
                'nama' => $nama($k),
                'transaksi' => $g->count(),
                'jumlah_barang' => (int) $g->sum('qty'),
                'total' => $t,
                'persen' => $total > 0 ? round($t / $total * 100, 1) : 0,
            ];
        });

        $hasil = $urutKunci ? $hasil->sortBy(fn ($r) => (int) $r['key']) : $hasil->sortByDesc('total');

        return $hasil->values()->all();
    }

    private function bandingkan(Collection $now, Collection $prev, \Closure $kunci): array
    {
        $a = $now->groupBy($kunci)->map(fn ($g) => $this->sumHarga($g));
        $b = $prev->groupBy($kunci)->map(fn ($g) => $this->sumHarga($g));

        $labels = $a->keys()
            ->merge($b->keys())
            ->unique()
            ->sortByDesc(fn ($l) => $a->get($l, 0) + $b->get($l, 0))
            ->values();

        return [
            'labels' => $labels->map(fn ($l) => (string) $l)->all(),
            'current' => $labels->map(fn ($l) => (float) $a->get($l, 0))->all(),
            'previous' => $labels->map(fn ($l) => (float) $b->get($l, 0))->all(),
        ];
    }

    private function stat(string $label, string $value): array
    {
        return ['label' => $label, 'value' => $value];
    }

    private function paket(string $title, string $subtitle, array $stats, ?array $chart, array $columns, array $rows, array $right = []): array
    {
        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'stats' => $stats,
            'chart' => $chart,
            'columns' => $columns,
            'rows' => $rows,
            'right' => $right,
        ];
    }

    private function barisTransaksi(Collection $items): array
    {
        return $items->sortBy('waktu_pembelian')->values()->map(function ($i, $n) {
            return [
                $n + 1,
                Carbon::parse($i->waktu_pembelian)->format('d-m-Y'),
                $i->idbarang ?: '-',
                $i->idinventaris ?: '-',
                $i->no_kk ?: '-',
                $i->name ?: '-',
                $this->angka($i->qty),
                $i->kategori ?: '-',
                $i->ruangan ?: '-',
                $i->deskripsi ?: '-',
                $this->rupiah($i->total_harga),
            ];
        })->all();
    }

    private function detailTotal(Collection $data, Collection $prev, string $sub): array
    {
        $total = $this->sumHarga($data);
        $bulanan = $this->perBulan($data);
        $akum = 0.0;
        $rows = [];
        $series = [];

        for ($m = 1; $m <= 12; $m++) {
            $t = $bulanan[$m]['total'];
            $akum += $t;
            $series[] = $t;
            $rows[] = [
                self::BULAN[$m - 1],
                $this->angka($bulanan[$m]['count']),
                $this->rupiah($t),
                $total > 0 ? $this->persenFmt($t / $total * 100) : '-',
                $this->rupiah($akum),
                $m > 1 ? $this->persenTeks($t, $bulanan[$m - 1]['total']) : '-',
            ];
        }

        $puncak = max($series);

        return $this->paket(
            'Total pembelian',
            $sub,
            [
                $this->stat('Total pembelian', $this->rupiah($total)),
                $this->stat('Transaksi', $this->angka($data->count())),
                $this->stat('Jumlah barang', $this->angka($data->sum('qty'))),
                $this->stat('Vs tahun lalu', $this->persenTeks($total, $this->sumHarga($prev))),
                $this->stat('Bulan terbesar', $puncak > 0 ? self::BULAN[array_search($puncak, $series) ] : '-'),
            ],
            ['type' => 'bar', 'labels' => self::BULAN_SINGKAT, 'series' => [['name' => 'Total pembelian', 'data' => $series]], 'money' => true],
            ['Bulan', 'Transaksi', 'Total', 'Kontribusi', 'Akumulasi', 'Perubahan dari bulan lalu'],
            $rows,
            [1, 2, 3, 4, 5]
        );
    }

    private function detailQty(Collection $data, string $sub): array
    {
        $qty = (int) $data->sum('qty');

        $kat = $data->groupBy(fn ($i) => $i->kategori ?: 'Lainnya')->map(fn ($g) => [
            'transaksi' => $g->count(),
            'qty' => (int) $g->sum('qty'),
            'total' => $this->sumHarga($g),
        ])->sortByDesc('qty');

        $rows = [];

        foreach ($kat as $nama => $v) {
            $rows[] = [
                (string) $nama,
                $this->angka($v['transaksi']),
                $this->angka($v['qty']),
                $qty > 0 ? $this->persenFmt($v['qty'] / $qty * 100) : '-',
                $this->rupiah($v['total']),
            ];
        }

        $count = $data->count();

        return $this->paket(
            'Jumlah barang',
            $sub,
            [
                $this->stat('Jumlah barang', $this->angka($qty)),
                $this->stat('Transaksi', $this->angka($count)),
                $this->stat('Rata-rata per transaksi', $count ? number_format($qty / $count, 1, ',', '.') . ' unit' : '-'),
                $this->stat('Kategori terbanyak', (string) ($kat->keys()->first() ?? '-')),
            ],
            $kat->isEmpty() ? null : [
                'type' => 'donut',
                'labels' => $kat->keys()->map(fn ($k) => (string) $k)->values()->all(),
                'series' => $kat->pluck('qty')->values()->all(),
                'money' => false,
            ],
            ['Kategori', 'Transaksi', 'Jumlah barang', 'Kontribusi', 'Total pembelian'],
            $rows,
            [1, 2, 3, 4]
        );
    }

    private function detailCount(Collection $data, string $sub): array
    {
        $bulanan = $this->perBulan($data);
        $rows = [];
        $series = [];

        for ($m = 1; $m <= 12; $m++) {
            $series[] = $bulanan[$m]['count'];
            $rows[] = [
                self::BULAN[$m - 1],
                $this->angka($bulanan[$m]['count']),
                $this->angka($bulanan[$m]['qty']),
                $this->rupiah($bulanan[$m]['total']),
            ];
        }

        $puncak = max($series);

        return $this->paket(
            'Jumlah transaksi',
            $sub,
            [
                $this->stat('Transaksi', $this->angka($data->count())),
                $this->stat('Jumlah barang', $this->angka($data->sum('qty'))),
                $this->stat('Bulan tersibuk', $puncak > 0 ? self::BULAN[array_search($puncak, $series)] . ' (' . $puncak . ')' : '-'),
                $this->stat('Rata-rata per bulan', number_format($data->count() / 12, 1, ',', '.')),
            ],
            ['type' => 'bar', 'labels' => self::BULAN_SINGKAT, 'series' => [['name' => 'Transaksi', 'data' => $series]], 'money' => false],
            ['Bulan', 'Transaksi', 'Jumlah barang', 'Total pembelian'],
            $rows,
            [1, 2, 3]
        );
    }

    private function detailAverage(Collection $data, string $sub): array
    {
        $nilai = $data->pluck('total_harga')->map(fn ($v) => (float) $v)->sort()->values();
        $n = $nilai->count();
        $rata = $n ? $nilai->sum() / $n : 0;
        $median = 0;

        if ($n) {
            $tengah = intdiv($n, 2);
            $median = $n % 2 ? $nilai[$tengah] : ($nilai[$tengah - 1] + $nilai[$tengah]) / 2;
        }

        $sd = $n ? sqrt($nilai->map(fn ($v) => ($v - $rata) ** 2)->sum() / $n) : 0;

        $bins = [
            ['< 1 jt', 0, 1000000],
            ['1 - 5 jt', 1000000, 5000000],
            ['5 - 10 jt', 5000000, 10000000],
            ['10 - 50 jt', 10000000, 50000000],
            ['> 50 jt', 50000000, INF],
        ];

        $distribusi = array_map(fn ($b) => $nilai->filter(fn ($v) => $v >= $b[1] && $v < $b[2])->count(), $bins);

        $rows = $data->groupBy(fn ($i) => $i->kategori ?: 'Lainnya')->map(function ($g, $nama) {
            $v = $g->pluck('total_harga')->map(fn ($x) => (float) $x);

            return [
                (string) $nama,
                $this->angka($g->count()),
                $this->rupiah($v->avg()),
                $this->rupiah($v->min()),
                $this->rupiah($v->max()),
            ];
        })->values()->all();

        return $this->paket(
            'Rata-rata per transaksi',
            $sub,
            [
                $this->stat('Rata-rata', $this->rupiah($rata)),
                $this->stat('Median', $this->rupiah($median)),
                $this->stat('Terkecil', $this->rupiah($n ? $nilai->first() : 0)),
                $this->stat('Terbesar', $this->rupiah($n ? $nilai->last() : 0)),
                $this->stat('Simpangan baku', $this->rupiah($sd)),
            ],
            $n ? ['type' => 'bar', 'labels' => array_column($bins, 0), 'series' => [['name' => 'Jumlah transaksi', 'data' => $distribusi]], 'money' => false] : null,
            ['Kategori', 'Transaksi', 'Rata-rata', 'Terkecil', 'Terbesar'],
            $rows,
            [1, 2, 3, 4]
        );
    }

    private function detailMax(Collection $data, string $sub): array
    {
        $total = $this->sumHarga($data);
        $top = $data->sortByDesc('total_harga')->take(10)->values();
        $pertama = $top->first();

        $rows = $top->map(fn ($i, $n) => [
            $n + 1,
            Carbon::parse($i->waktu_pembelian)->format('d-m-Y'),
            $i->no_kk ?: '-',
            $i->name ?: '-',
            $i->kategori ?: '-',
            $i->ruangan ?: '-',
            $this->angka($i->qty),
            $this->rupiah($i->total_harga),
        ])->all();

        return $this->paket(
            'Transaksi terbesar',
            $sub,
            [
                $this->stat('Terbesar', $pertama ? $this->rupiah($pertama->total_harga) : '-'),
                $this->stat('Nama barang', $pertama ? ($pertama->name ?: '-') : '-'),
                $this->stat('Porsi dari total', $pertama && $total > 0 ? $this->persenFmt((float) $pertama->total_harga / $total * 100) : '-'),
                $this->stat('Lokasi', $pertama ? ($pertama->ruangan ?: '-') : '-'),
            ],
            $top->isEmpty() ? null : [
                'type' => 'bar',
                'horizontal' => true,
                'labels' => $top->map(fn ($i) => $i->name ?: '-')->all(),
                'series' => [['name' => 'Total harga', 'data' => $top->map(fn ($i) => (float) $i->total_harga)->all()]],
                'money' => true,
            ],
            ['No', 'Tanggal', 'No. KK', 'Nama Barang', 'Kategori', 'Lokasi', 'Qty', 'Total Harga'],
            $rows,
            [6, 7]
        );
    }

    private function detailTopKategori(Collection $data, Collection $prev, string $sub): array
    {
        $total = $this->sumHarga($data);

        $grp = $data->groupBy(fn ($i) => $i->kategori ?: 'Lainnya')->map(fn ($g) => [
            'transaksi' => $g->count(),
            'qty' => (int) $g->sum('qty'),
            'total' => $this->sumHarga($g),
        ])->sortByDesc('total');

        $prevGrp = $prev->groupBy(fn ($i) => $i->kategori ?: 'Lainnya')->map(fn ($g) => $this->sumHarga($g));

        $rows = [];

        foreach ($grp as $nama => $v) {
            $rows[] = [
                (string) $nama,
                $this->angka($v['transaksi']),
                $this->angka($v['qty']),
                $this->rupiah($v['total']),
                $total > 0 ? $this->persenFmt($v['total'] / $total * 100) : '-',
                $this->persenTeks($v['total'], (float) $prevGrp->get($nama, 0)),
            ];
        }

        $pertama = $grp->first();

        return $this->paket(
            'Kategori terbesar',
            $sub,
            [
                $this->stat('Kategori terbesar', (string) ($grp->keys()->first() ?? '-')),
                $this->stat('Total kategori', $pertama ? $this->rupiah($pertama['total']) : '-'),
                $this->stat('Kontribusi', $pertama && $total > 0 ? $this->persenFmt($pertama['total'] / $total * 100) : '-'),
                $this->stat('Jumlah kategori', $this->angka($grp->count())),
            ],
            $grp->isEmpty() ? null : [
                'type' => 'bar',
                'horizontal' => true,
                'labels' => $grp->keys()->map(fn ($k) => (string) $k)->values()->all(),
                'series' => [['name' => 'Total pembelian', 'data' => $grp->pluck('total')->values()->all()]],
                'money' => true,
            ],
            ['Kategori', 'Transaksi', 'Jumlah barang', 'Total pembelian', 'Kontribusi', 'Vs tahun lalu'],
            $rows,
            [1, 2, 3, 4, 5]
        );
    }

    private function detailGrowth(Collection $data, Collection $prev, int $tahun, string $sub): array
    {
        $a = $this->perBulan($data);
        $b = $this->perBulan($prev);
        $total = $this->sumHarga($data);
        $prevTotal = $this->sumHarga($prev);
        $selisihTotal = $total - $prevTotal;

        $rows = [];
        $now = [];
        $lalu = [];
        $naik = null;
        $naikNilai = 0;

        for ($m = 1; $m <= 12; $m++) {
            $s = $a[$m]['total'] - $b[$m]['total'];
            $now[] = $a[$m]['total'];
            $lalu[] = $b[$m]['total'];

            if ($s > $naikNilai) {
                $naikNilai = $s;
                $naik = $m;
            }

            $rows[] = [
                self::BULAN[$m - 1],
                $this->rupiah($a[$m]['total']),
                $this->rupiah($b[$m]['total']),
                ($s < 0 ? '-' : '+') . $this->rupiah(abs($s)),
                $this->persenTeks($a[$m]['total'], $b[$m]['total']),
            ];
        }

        return $this->paket(
            'Pertumbuhan pembelian',
            $sub,
            [
                $this->stat('Tahun ' . $tahun, $this->rupiah($total)),
                $this->stat('Tahun ' . ($tahun - 1), $this->rupiah($prevTotal)),
                $this->stat('Selisih', ($selisihTotal < 0 ? '-' : '+') . $this->rupiah(abs($selisihTotal))),
                $this->stat('Pertumbuhan', $this->persenTeks($total, $prevTotal)),
                $this->stat('Kenaikan terbesar', $naik ? self::BULAN[$naik - 1] : '-'),
            ],
            [
                'type' => 'bar',
                'labels' => self::BULAN_SINGKAT,
                'series' => [
                    ['name' => 'Tahun ' . $tahun, 'data' => $now],
                    ['name' => 'Tahun ' . ($tahun - 1), 'data' => $lalu],
                ],
                'money' => true,
            ],
            ['Bulan', 'Tahun ' . $tahun, 'Tahun ' . ($tahun - 1), 'Selisih', 'Perubahan'],
            $rows,
            [1, 2, 3, 4]
        );
    }

    private function detailBulan(Collection $data, Collection $prev, int $tahun, int $m, string $sub): array
    {
        $m = max(1, min(12, $m));
        $items = $data->filter(fn ($i) => $this->bulan($i) === $m)->values();
        $prevItems = $prev->filter(fn ($i) => $this->bulan($i) === $m)->values();
        $total = $this->sumHarga($items);
        $prevTotal = $this->sumHarga($prevItems);

        $kat = $items->groupBy(fn ($i) => $i->kategori ?: 'Lainnya')->map(fn ($g) => $this->sumHarga($g))->sortDesc();

        return $this->paket(
            self::BULAN[$m - 1] . ' ' . $tahun,
            $sub,
            [
                $this->stat('Total pembelian', $this->rupiah($total)),
                $this->stat('Transaksi', $this->angka($items->count())),
                $this->stat('Jumlah barang', $this->angka($items->sum('qty'))),
                $this->stat('Bulan sama tahun lalu', $this->rupiah($prevTotal) . ' (' . $this->persenTeks($total, $prevTotal) . ')'),
            ],
            $kat->isEmpty() ? null : [
                'type' => 'donut',
                'labels' => $kat->keys()->map(fn ($k) => (string) $k)->values()->all(),
                'series' => $kat->values()->all(),
                'money' => true,
            ],
            self::KOLOM_TRANSAKSI,
            $this->barisTransaksi($items),
            [6, 10]
        );
    }

    private function detailQuarter(Collection $data, Collection $prev, int $tahun, int $q, string $sub): array
    {
        $q = max(1, min(4, $q));
        $awal = ($q - 1) * 3 + 1;
        $akhir = $q * 3;

        $items = $data->filter(fn ($i) => $this->bulan($i) >= $awal && $this->bulan($i) <= $akhir)->values();
        $prevItems = $prev->filter(fn ($i) => $this->bulan($i) >= $awal && $this->bulan($i) <= $akhir)->values();
        $total = $this->sumHarga($items);
        $prevTotal = $this->sumHarga($prevItems);

        $a = $this->perBulan($items);
        $b = $this->perBulan($prevItems);
        $labels = [];
        $now = [];
        $lalu = [];

        for ($m = $awal; $m <= $akhir; $m++) {
            $labels[] = self::BULAN[$m - 1];
            $now[] = $a[$m]['total'];
            $lalu[] = $b[$m]['total'];
        }

        return $this->paket(
            'Quarter ' . $q . ' ' . $tahun,
            $sub,
            [
                $this->stat('Total pembelian', $this->rupiah($total)),
                $this->stat('Transaksi', $this->angka($items->count())),
                $this->stat('Jumlah barang', $this->angka($items->sum('qty'))),
                $this->stat('Quarter sama tahun lalu', $this->rupiah($prevTotal)),
                $this->stat('Perubahan', $this->persenTeks($total, $prevTotal)),
            ],
            [
                'type' => 'bar',
                'labels' => $labels,
                'series' => [
                    ['name' => 'Tahun ' . $tahun, 'data' => $now],
                    ['name' => 'Tahun ' . ($tahun - 1), 'data' => $lalu],
                ],
                'money' => true,
            ],
            self::KOLOM_TRANSAKSI,
            $this->barisTransaksi($items),
            [6, 10]
        );
    }

    private function detailYear(Collection $data, Collection $prev, int $tahun, string $sub): array
    {
        $total = $this->sumHarga($data);
        $prevTotal = $this->sumHarga($prev);
        $count = $data->count();
        $quarters = $this->kuartalan($this->bulanan($data, $prev));

        $grp = $data->groupBy(fn ($i) => $i->kategori ?: 'Lainnya')->map(fn ($g) => [
            'transaksi' => $g->count(),
            'qty' => (int) $g->sum('qty'),
            'total' => $this->sumHarga($g),
        ])->sortByDesc('total');

        $prevGrp = $prev->groupBy(fn ($i) => $i->kategori ?: 'Lainnya')->map(fn ($g) => $this->sumHarga($g));

        $rows = [];

        foreach ($grp as $nama => $v) {
            $rows[] = [
                (string) $nama,
                $this->angka($v['transaksi']),
                $this->angka($v['qty']),
                $this->rupiah($v['total']),
                $total > 0 ? $this->persenFmt($v['total'] / $total * 100) : '-',
                $this->persenTeks($v['total'], (float) $prevGrp->get($nama, 0)),
            ];
        }

        return $this->paket(
            'Tahun ' . $tahun,
            $sub,
            [
                $this->stat('Total pembelian', $this->rupiah($total)),
                $this->stat('Transaksi', $this->angka($count)),
                $this->stat('Jumlah barang', $this->angka($data->sum('qty'))),
                $this->stat('Vs tahun sebelumnya', $this->persenTeks($total, $prevTotal)),
                $this->stat('Rata-rata per transaksi', $this->rupiah($count ? $total / $count : 0)),
            ],
            [
                'type' => 'bar',
                'labels' => array_column($quarters, 'label'),
                'series' => [
                    ['name' => 'Tahun ' . $tahun, 'data' => array_column($quarters, 'total')],
                    ['name' => 'Tahun ' . ($tahun - 1), 'data' => array_column($quarters, 'prev')],
                ],
                'money' => true,
            ],
            ['Kategori', 'Transaksi', 'Jumlah barang', 'Total pembelian', 'Kontribusi', 'Vs tahun sebelumnya'],
            $rows,
            [1, 2, 3, 4, 5]
        );
    }

    private function detailKelompok(string $tipe, string $nilai, Collection $data, Collection $prev, int $tahun, string $sub): array
    {
        $kunci = match ($tipe) {
            'kategori' => fn ($i) => ($i->kategori ?: 'Lainnya') === $nilai,
            'lokasi' => fn ($i) => ($i->ruangan ?: 'Lainnya') === $nilai,
            default => fn ($i) => ($i->name ?: 'Tanpa nama') === $nilai,
        };

        $items = $data->filter($kunci)->values();
        $prevItems = $prev->filter($kunci)->values();
        $total = $this->sumHarga($items);
        $prevTotal = $this->sumHarga($prevItems);
        $count = $items->count();

        $a = $this->perBulan($items);
        $b = $this->perBulan($prevItems);
        $now = [];
        $lalu = [];

        for ($m = 1; $m <= 12; $m++) {
            $now[] = $a[$m]['total'];
            $lalu[] = $b[$m]['total'];
        }

        $awalan = ['kategori' => 'Kategori ', 'lokasi' => 'Lokasi ', 'jenis' => 'Barang '][$tipe] ?? '';

        return $this->paket(
            $awalan . $nilai,
            $sub,
            [
                $this->stat('Total pembelian', $this->rupiah($total)),
                $this->stat('Transaksi', $this->angka($count)),
                $this->stat('Jumlah barang', $this->angka($items->sum('qty'))),
                $this->stat('Rata-rata per transaksi', $this->rupiah($count ? $total / $count : 0)),
                $this->stat('Tahun lalu', $this->rupiah($prevTotal) . ' (' . $this->persenTeks($total, $prevTotal) . ')'),
            ],
            $items->isEmpty() && $prevItems->isEmpty() ? null : [
                'type' => 'bar',
                'labels' => self::BULAN_SINGKAT,
                'series' => [
                    ['name' => 'Tahun ' . $tahun, 'data' => $now],
                    ['name' => 'Tahun ' . ($tahun - 1), 'data' => $lalu],
                ],
                'money' => true,
            ],
            self::KOLOM_TRANSAKSI,
            $this->barisTransaksi($items),
            [6, 10]
        );
    }
}