<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Inventaris; 
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\RekapInventarisExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RekapInventarisImport;

class RekapInventarisController extends Controller
{
    public function index()
    {
        $kategoris = Inventaris::whereNotNull('kategori')->where('kategori', '!=', '')->distinct()->pluck('kategori');
        $latestRecord = Inventaris::orderBy('waktu_pembelian', 'desc')->first();
        $defaultTahun = $latestRecord ? Carbon::parse($latestRecord->waktu_pembelian)->year : date('Y');
        return view('HR.Rekap_Inventaris.index', compact('kategoris', 'defaultTahun'));
    }

    public function getRekapData(Request $request)
    {
        $tahun = $request->tahun;
        if (!$tahun) {
            $latestRecord = Inventaris::orderBy('waktu_pembelian', 'desc')->first();
            $tahun = $latestRecord ? Carbon::parse($latestRecord->waktu_pembelian)->year : date('Y');
        }
        $tahunLalu = $tahun - 1;

        // Current Year Data query
        $queryCurrent = Inventaris::whereYear('waktu_pembelian', $tahun);
        // Previous Year Data query
        $queryPrevious = Inventaris::whereYear('waktu_pembelian', $tahunLalu);

        // Apply general filters
        if ($request->kategori) {
            $queryCurrent->where('kategori', $request->kategori);
            $queryPrevious->where('kategori', $request->kategori);
        }
        if ($request->lokasi) {
            $queryCurrent->where('ruangan', $request->lokasi);
            $queryPrevious->where('ruangan', $request->lokasi);
        }
        if ($request->jenis) {
            $queryCurrent->where('name', $request->jenis);
            $queryPrevious->where('name', $request->jenis);
        }

        // Apply period filters to queryCurrent
        if ($request->mode_periode === 'bulan' && $request->bulan) {
            $queryCurrent->whereMonth('waktu_pembelian', $request->bulan);
            $queryPrevious->whereMonth('waktu_pembelian', $request->bulan);
        } elseif ($request->mode_periode === 'quartal' && $request->quartal) {
            $quartal = $request->quartal;
            if ($quartal == 1) {
                $queryCurrent->whereBetween(DB::raw('MONTH(waktu_pembelian)'), [1, 3]);
                $queryPrevious->whereBetween(DB::raw('MONTH(waktu_pembelian)'), [1, 3]);
            } elseif ($quartal == 2) {
                $queryCurrent->whereBetween(DB::raw('MONTH(waktu_pembelian)'), [4, 6]);
                $queryPrevious->whereBetween(DB::raw('MONTH(waktu_pembelian)'), [4, 6]);
            } elseif ($quartal == 3) {
                $queryCurrent->whereBetween(DB::raw('MONTH(waktu_pembelian)'), [7, 9]);
                $queryPrevious->whereBetween(DB::raw('MONTH(waktu_pembelian)'), [7, 9]);
            } elseif ($quartal == 4) {
                $queryCurrent->whereBetween(DB::raw('MONTH(waktu_pembelian)'), [10, 12]);
                $queryPrevious->whereBetween(DB::raw('MONTH(waktu_pembelian)'), [10, 12]);
            }
        }

        $allData = $queryCurrent->get();
        $prevData = $queryPrevious->get();

        // 1. Tab 1 - Category
        $tab1 = $allData->groupBy('kategori')->map(function ($items, $kategori) {
            return [
                'kategori' => $kategori ?: 'Lainnya',
                'jumlah_barang' => $items->sum('qty'),
                'total' => $items->sum('total_harga')
            ];
        })->values();

        // 2. Tab 2 - Period (Monthly Grouping)
        $tab2 = $allData->groupBy(function($item) {
            return Carbon::parse($item->waktu_pembelian)->month;
        })->map(function ($items, $monthNum) use ($tahun) {
            return [
                'periode' => Carbon::create()->month($monthNum)->translatedFormat('F') . ' ' . $tahun,
                'jumlah_barang' => $items->sum('qty'),
                'total' => $items->sum('total_harga'),
                'filter_mode' => 'bulan',
                'filter_value' => $monthNum
            ];
        })->values();

        // 3. Tab Lokasi
        $tabLokasi = $allData->groupBy('ruangan')->map(function ($items, $lokasi) {
            return [
                'lokasi' => $lokasi ?: 'Lainnya',
                'jumlah_barang' => $items->sum('qty'),
                'total' => $items->sum('total_harga')
            ];
        })->values();

        // 4. Chart Category Comparison
        $currentCat = $allData->groupBy('kategori')->map->sum('total_harga');
        $prevCat = $prevData->groupBy('kategori')->map->sum('total_harga');
        $catLabels = $allData->pluck('kategori')->merge($prevData->pluck('kategori'))->unique()->filter()->values();

        $chartKategoriCurrent = [];
        $chartKategoriPrevious = [];
        foreach ($catLabels as $cat) {
            $chartKategoriCurrent[] = (float)$currentCat->get($cat, 0);
            $chartKategoriPrevious[] = (float)$prevCat->get($cat, 0);
        }

        // 5. Chart Location Comparison
        $currentLoc = $allData->groupBy('ruangan')->map->sum('total_harga');
        $prevLoc = $prevData->groupBy('ruangan')->map->sum('total_harga');
        $locLabels = $allData->pluck('ruangan')->merge($prevData->pluck('ruangan'))->unique()->filter()->values();

        $chartLocationCurrent = [];
        $chartLocationPrevious = [];
        foreach ($locLabels as $loc) {
            $chartLocationCurrent[] = (float)$currentLoc->get($loc, 0);
            $chartLocationPrevious[] = (float)$prevLoc->get($loc, 0);
        }

        return response()->json([
            'success' => true,
            'tab1' => $tab1,
            'tab2' => $tab2,
            'tabLokasi' => $tabLokasi,
            'chart' => [
                'labels' => $locLabels,
                'current' => $chartLocationCurrent,
                'previous' => $chartLocationPrevious
            ],
            'chart_kategori' => [
                'labels' => $catLabels,
                'current' => $chartKategoriCurrent,
                'previous' => $chartKategoriPrevious
            ]
        ]);
    }

    public function getDetailData(Request $request)
    {
        $query = Inventaris::query();

        // Apply general filters from request
        $tahun = $request->tahun;
        if (!$tahun) {
            $latestRecord = Inventaris::orderBy('waktu_pembelian', 'desc')->first();
            $tahun = $latestRecord ? Carbon::parse($latestRecord->waktu_pembelian)->year : date('Y');
        }
        $query->whereYear('waktu_pembelian', $tahun);

        if ($request->kategori) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->lokasi) {
            $query->where('ruangan', $request->lokasi);
        }

        if ($request->jenis) {
            $query->where('name', $request->jenis);
        }

        // Apply specific click filter
        if ($request->tipe === 'kategori') {
            $query->where('kategori', $request->nilai);
        } elseif ($request->tipe === 'lokasi') {
            $query->where('ruangan', $request->nilai);
        } elseif ($request->tipe === 'periode') {
            $monthName = $request->nilai ?? $request->filter_value;
            if ($monthName) {
                try {
                    $monthNumber = Carbon::parse($monthName)->month;
                    $query->whereMonth('waktu_pembelian', $monthNumber);
                } catch (\Exception $e) {
                    // Ignore parsing issues
                }
            }
        }

        $data = $query->get();

        $mappedData = $data->map(function ($item) {
            return [
                'idbarang' => $item->idbarang,
                'idinventaris' => $item->idinventaris,
                'tanggal' => Carbon::parse($item->waktu_pembelian)->format('d-m-Y'),
                'no_kk' => $item->no_kk,
                'nama_barang' => $item->name,
                'qty' => $item->qty,
                'kategori' => $item->kategori,
                'lokasi' => $item->ruangan,
                'keterangan' => $item->deskripsi,
                'total' => $item->total_harga,
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $mappedData
        ]);
    }

    public function exportPdf(Request $request)
    {
        $query = Inventaris::query();

        $tahun = $request->tahun ?? date('Y');
        $query->whereYear('waktu_pembelian', $tahun);

        if ($request->kategori) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->lokasi) {
            $query->where('ruangan', $request->lokasi);
        }

        if ($request->jenis) {
            $query->where('name', $request->jenis);
        }

        if ($request->mode_periode === 'bulan' && $request->bulan) {
            $query->whereMonth('waktu_pembelian', $request->bulan);
        } elseif ($request->mode_periode === 'quartal' && $request->quartal) {
            $quartal = $request->quartal;
            if ($quartal == 1) {
                $query->whereBetween(DB::raw('MONTH(waktu_pembelian)'), [1, 3]);
            } elseif ($quartal == 2) {
                $query->whereBetween(DB::raw('MONTH(waktu_pembelian)'), [4, 6]);
            } elseif ($quartal == 3) {
                $query->whereBetween(DB::raw('MONTH(waktu_pembelian)'), [7, 9]);
            } elseif ($quartal == 4) {
                $query->whereBetween(DB::raw('MONTH(waktu_pembelian)'), [10, 12]);
            }
        }

        $allData = $query->get();

        $mappedData = $allData->map(function ($item) {
            return (object)[
                'idbarang' => $item->idbarang,
                'idinventaris' => $item->idinventaris,
                'tanggal_beli' => $item->waktu_pembelian,
                'no_kk' => $item->no_kk,
                'nama_barang' => $item->name,
                'kategori' => $item->kategori,
                'lokasi' => $item->ruangan,
                'harga' => $item->total_harga
            ];
        });

        $periode = $tahun;
        if ($request->mode_periode === 'bulan' && $request->bulan) {
            $periode = Carbon::create()->month($request->bulan)->translatedFormat('F') . ' ' . $tahun;
        } elseif ($request->mode_periode === 'quartal' && $request->quartal) {
            $periode = 'Quartal ' . $request->quartal . ' ' . $tahun;
        }

        $pdf = Pdf::loadView('HR.Rekap_Inventaris.export_pdf', [
            'data' => $mappedData,
            'periode' => $periode
        ]);

        return $pdf->download('Rekap_Inventaris_' . str_replace(' ', '_', $periode) . '.pdf');
    }

    public function getLokasi($kategori)
    {
        $lokasi = Inventaris::where('kategori', $kategori)->distinct()->pluck('ruangan');
        return response()->json($lokasi);
    }

    public function getJenis($lokasi)
    {
        $jenis = Inventaris::where('ruangan', $lokasi)->distinct()->pluck('name', 'name');
        return response()->json($jenis);
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
            \Illuminate\Support\Facades\Log::error($e);
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
            $randomCode = strtoupper(substr($request->kategori, 0, 3));
            if (strlen($randomCode) < 3) {
                $randomCode = str_pad($randomCode, 3, 'X');
            }
            $randomCode = substr($randomCode, 0, 3);

            Inventaris::create(array_merge($request->all(), [
                'kodebarang' => $randomCode,
                'type' => ($request->kategori === 'ITSM' || $request->kategori === 'Sales/Tim Digital') ? 'E' : 'NE',
                'harga_beli' => $request->total_harga / $request->qty,
                'satuan' => 'unit',
                'kondisi' => 'baik',
            ]));
            return back()->with('success', 'Berhasil menambahkan data rekap inventaris.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error($e);
            return back()->with('error', 'Gagal menambahkan data: ' . $e->getMessage());
        }
    }
}