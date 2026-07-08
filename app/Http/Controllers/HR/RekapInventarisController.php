<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\RekapInventaris; 
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\RekapInventarisExport;
use Maatwebsite\Excel\Facades\Excel;

class RekapInventarisController extends Controller
{
    public function index()
    {
        $kategoris = RekapInventaris::distinct()->pluck('kategori');
        return view('HR.Rekap_Inventaris.index', compact('kategoris'));
    }

    public function getRekapData(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');
        
        $query = RekapInventaris::whereYear('waktu_pembelian', $tahun);

        if ($request->kategori) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->lokasi) {
            $query->where('ruangan', $request->lokasi);
        }

        if ($request->jenis) {
            $query->where('name', $request->jenis);
        }

        // Apply period filters
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

        $tab1 = $allData->groupBy('kategori')->map(function ($items, $kategori) {
            return [
                'kategori' => $kategori,
                'jumlah_barang' => $items->sum('qty'),
                'total' => $items->sum('total_harga')
            ];
        })->values();

        $tab2 = $allData->groupBy(function($item) {
            return Carbon::parse($item->waktu_pembelian)->format('F');
        })->map(function ($items, $bulan) {
            return [
                'periode' => $bulan,
                'jumlah_barang' => $items->sum('qty'),
                'total' => $items->sum('total_harga'),
                'filter_mode' => 'bulan',
                'filter_value' => $bulan
            ];
        })->values();

        // Tab 3: Rekap Per Lokasi
        $tabLokasi = $allData->groupBy('ruangan')->map(function ($items, $ruangan) {
            return [
                'lokasi' => $ruangan,
                'jumlah_barang' => $items->sum('qty'),
                'total' => $items->sum('total_harga')
            ];
        })->values();

        return response()->json([
            'success' => true,
            'tab1' => $tab1,
            'tab2' => $tab2,
            'tabLokasi' => $tabLokasi,
            'chart' => [
                'labels' => $tab1->pluck('kategori'),
                'current' => $tab1->pluck('total'),
                'previous' => [] 
            ],
            'chart_kategori' => [
                'labels' => $tabLokasi->pluck('lokasi'),
                'current' => $tabLokasi->pluck('total'),
                'previous' => []
            ]
        ]);
    }

    public function getDetailData(Request $request)
    {
        $query = RekapInventaris::query();

        // Apply general filters from request
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
                'tanggal' => Carbon::parse($item->waktu_pembelian)->format('d-m-Y'),
                'no_kk' => $item->no_kk,
                'nama_barang' => $item->name,
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
        $query = RekapInventaris::query();

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
        $lokasi = RekapInventaris::where('kategori', $kategori)->distinct()->pluck('ruangan');
        return response()->json($lokasi);
    }

    public function getJenis($lokasi)
    {
        $jenis = RekapInventaris::where('ruangan', $lokasi)->distinct()->pluck('name', 'name');
        return response()->json($jenis);
    }

    public function export(Request $request)
    {
        return Excel::download(new RekapInventarisExport($request), 'rekap_inventaris.xlsx');
    }
}