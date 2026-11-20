<?php

namespace App\Http\Controllers\HR;

use App\Exports\RencanaPembelianHrExport;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\PembelianHr;
use App\Models\TrackingPembelianHr;
use App\Models\DetailPembelianHr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Kegiatan;
use App\Models\karyawan;
use App\Models\JurnalAkuntansi;
use App\Models\PengajuanBarang;
use Maatwebsite\Excel\Facades\Excel;

class RencanaPembelianHrController extends Controller
{

    public function index(Request $request)
    {
        $kegiatan = Kegiatan::all();
        $karyawans = karyawan::all();
        $drivers = karyawan::where('jabatan', 'Driver')
            ->where(function ($query) {
                $query->whereDoesntHave('pickupDriver')->orWhereHas('pickupDriver', function ($q) {
                    $q->whereIn('status_driver', ['Selesai, Driver Ready']);
                });
            })
            ->get();

        $filtered = $this->getFilteredData($request, true);
        $rencanas = $filtered['rencanas'];
        $pembelian = $filtered['pembelian'];
        $dibatalkan = $filtered['dibatalkan'];

        $tahunOptions = $this->opsiTahun();
        $filterMode = $this->resolveFilterMode($request);
        $filterBulan = $request->input('bulan');
        $filterTahun = $request->input('tahun');
        $filterTanggalAwal = $request->input('tanggal_awal');
        $filterTanggalAkhir = $request->input('tanggal_akhir');

        $extends = 'layouts.app';
        $section = 'content';

        return view('office.rab.index', compact(
            'kegiatan',
            'drivers',
            'pembelian',
            'rencanas',
            'dibatalkan',
            'extends',
            'section',
            'karyawans',
            'tahunOptions',
            'filterMode',
            'filterBulan',
            'filterTahun',
            'filterTanggalAwal',
            'filterTanggalAkhir'
        ));
    }

    public function exportPdf(Request $request)
    {
        $this->validateExportRequest($request);

        $sections = $this->buildExportSections($request);
        $judul = 'Laporan Rencana Pembelian HR';
        $periodeLabel = $this->labelPeriode($request);
        $file = 'rencana-pembelian-hr-' . now()->format('Ymd-His') . '.pdf';

        return Pdf::loadView('HR.rencana_pembelian.export-pdf', compact('judul', 'periodeLabel', 'sections'))
            ->setPaper('a4', 'landscape')
            ->download($file);
    }

    public function exportExcel(Request $request)
    {
        $this->validateExportRequest($request);

        $sections = $this->buildExportSections($request);
        $judul = 'Laporan Rencana Pembelian HR';
        $periodeLabel = $this->labelPeriode($request);
        $file = 'rencana-pembelian-hr-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(
            new RencanaPembelianHrExport($judul, $periodeLabel, $sections),
            $file
        );
    }

    private function validateExportRequest(Request $request): void
    {
        $request->validate([
            'filter_mode'   => ['nullable', 'in:semua,bulan_tahun,rentang'],
            'bulan'         => ['nullable', 'integer', 'between:1,12'],
            'tahun'         => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'tanggal_awal'  => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date'],
            'status'        => ['nullable', 'in:semua,Rencana,Terlaksana,Dibatalkan'],
        ]);
    }

    private function resolveFilterMode(Request $request): string
    {
        $mode = $request->input('filter_mode');
        if (in_array($mode, ['semua', 'bulan_tahun', 'rentang'], true)) {
            return $mode;
        }
        if ($request->filled('tanggal_awal') || $request->filled('tanggal_akhir')) {
            return 'rentang';
        }
        if ($request->filled('bulan') || $request->filled('tahun')) {
            return 'bulan_tahun';
        }
        return 'semua';
    }

    private function applyDateFilter($query, Request $request, string $column = 'created_at', bool $isKegiatan = false)
    {
        $mode = $this->resolveFilterMode($request);

        if ($mode === 'bulan_tahun') {
            $bulan = (int) $request->input('bulan', 0);
            $tahun = (int) $request->input('tahun', 0);

            if ($isKegiatan) {
                if ($tahun) {
                    $query->whereRaw('YEAR(COALESCE(waktu_kegiatan, created_at)) = ?', [$tahun]);
                }
                if ($bulan) {
                    $query->whereRaw('MONTH(COALESCE(waktu_kegiatan, created_at)) = ?', [$bulan]);
                }
            } else {
                if ($tahun) {
                    $query->whereYear($column, $tahun);
                }
                if ($bulan) {
                    $query->whereMonth($column, $bulan);
                }
            }
        } elseif ($mode === 'rentang') {
            $awal = $request->input('tanggal_awal');
            $akhir = $request->input('tanggal_akhir');

            if ($isKegiatan) {
                if ($awal) {
                    $query->whereRaw('DATE(COALESCE(waktu_kegiatan, created_at)) >= ?', [$awal]);
                }
                if ($akhir) {
                    $query->whereRaw('DATE(COALESCE(waktu_kegiatan, created_at)) <= ?', [$akhir]);
                }
            } else {
                if ($awal) {
                    $query->whereDate($column, '>=', $awal);
                }
                if ($akhir) {
                    $query->whereDate($column, '<=', $akhir);
                }
            }
        }

        return $query;
    }

    private function getFilteredData(Request $request, bool $paginateDibatalkan = true): array
    {
        $user = auth()->user();

        $dibatalkanQuery = PembelianHr::with('tracking', 'tracking.karyawan', 'details')
            ->where('status_pembelian', 'Dibatalkan')
            ->when(!in_array($user->jabatan, ['HRD', 'GM']), function ($query) use ($user) {
                $query->where('id_karyawan', $user->id);
            });
        $this->applyDateFilter($dibatalkanQuery, $request, 'created_at');
        $dibatalkan = $paginateDibatalkan
            ? $dibatalkanQuery->latest()->paginate(10)->withQueryString()
            : $dibatalkanQuery->latest()->get();

        $rencanasQuery = PembelianHr::with([
            'details',
            'tracking.karyawan'
        ])
            ->where('status_pembelian', 'Rencana')
            ->when(!in_array($user->jabatan, ['HRD', 'GM']), function ($query) use ($user) {
                $query->where('id_karyawan', $user->id);
            });
        $this->applyDateFilter($rencanasQuery, $request, 'created_at');

        $rencanasRaw = $rencanasQuery->get()->map(function ($item) {
            $item->source = 'pembelian_hr';
            $item->status = $item->status_pembelian;
            return $item;
        });

        $pembelianQuery = PembelianHr::with([
            'details',
            'tracking.karyawan'
        ])
            ->where('status_pembelian', 'Terlaksana')
            ->when(!in_array($user->jabatan, ['HRD', 'GM']), function ($query) use ($user) {
                $query->where('id_karyawan', $user->id);
            });
        $this->applyDateFilter($pembelianQuery, $request, 'created_at');

        $pembelianRaw = $pembelianQuery->get()->map(function ($item) {
            $item->source = 'pembelian_hr';
            $item->status = $item->status_pembelian;
            return $item;
        });

        $mapKegiatan = function ($item) {
            $details = collect();

            foreach ($item->rincian as $rincian) {
                $details->push((object)[
                    'jenis' => 'rincian',
                    'nama_barang' => $rincian->hal,
                    'qty' => $rincian->qty,
                    'harga' => $rincian->harga_satuan,
                    'url' => null,
                    'keterangan' => $rincian->rincian,
                    'tanggal' => $rincian->tanggal
                ]);
            }

            foreach ($item->pengajuan_barang as $pengajuan) {
                foreach ($pengajuan->detail as $barang) {
                    $details->push((object)[
                        'jenis' => 'barang',
                        'nama_barang' => $barang->nama_barang,
                        'qty' => $barang->qty,
                        'harga' => $barang->harga,
                        'url' => null,
                        'keterangan' => $barang->keterangan,
                        'tanggal' => $pengajuan->tanggal_pencairan,
                    ]);
                }
            }

            return (object)[
                'id' => $item->id,
                'source' => 'kegiatan',
                'periode' => $item->waktu_kegiatan
                    ? Carbon::parse($item->waktu_kegiatan)->translatedFormat('F Y')
                    : '-',
                'details' => $details,
                'tracking' => collect(),
                'kategori' => 'Kegiatan',
                'status_pembelian' => $item->status,
                'created_at' => $item->waktu_kegiatan ?? $item->created_at,
                'menunggu' => $item->menunggu,
                'approved' => $item->approved,
                'pencairan' => $item->pencairan,
                'selesai' => $item->selesai,
                'invoice' => $item->pengajuan_barang->pluck('invoice')->filter()->implode(', '),
                'no_kk' => $item->pengajuan_barang->pluck('no_kk')->filter()->implode(', '),
            ];
        };

        $kegiatanRencanaQuery = Kegiatan::with(['pengajuan_barang.detail', 'rincian'])
            ->where('tipe', 'pembelian')
            ->where('status', '!=', 'selesai');
        $this->applyDateFilter($kegiatanRencanaQuery, $request, 'waktu_kegiatan', true);
        $kegiatanRencanaRaw = $kegiatanRencanaQuery->get()->map($mapKegiatan);

        $kegiatanPembelianQuery = Kegiatan::with(['pengajuan_barang.detail', 'rincian'])
            ->where('tipe', 'pembelian')
            ->where('status', 'selesai');
        $this->applyDateFilter($kegiatanPembelianQuery, $request, 'waktu_kegiatan', true);
        $kegiatanPembelianRaw = $kegiatanPembelianQuery->get()->map($mapKegiatan);

        if (in_array($user->jabatan, ['HRD', 'GM'])) {
            $rencanas = $rencanasRaw
                ->concat($kegiatanRencanaRaw)
                ->sortBy('periode')
                ->values();

            $pembelian = $pembelianRaw
                ->concat($kegiatanPembelianRaw)
                ->sortBy('periode')
                ->values();
        } else {
            $rencanas = $rencanasRaw
                ->sortBy('periode')
                ->values();

            $pembelian = $pembelianRaw
                ->sortBy('periode')
                ->values();
        }

        return compact('rencanas', 'pembelian', 'dibatalkan');
    }

    private function buildExportSections(Request $request): array
    {
        $data = $this->getFilteredData($request, false);
        $statusFilter = $request->input('status', 'semua');
        $sections = [];

        if ($statusFilter === 'semua' || $statusFilter === 'Rencana') {
            $rows = [];
            $no = 1;
            $total = 0;

            foreach ($data['rencanas'] as $row) {
                $tgl = $row->created_at ? Carbon::parse($row->created_at)->format('d-m-Y') : '-';
                if ($row->details->isEmpty()) {
                    $rows[] = [
                        $no++,
                        $tgl,
                        $row->periode ?? '-',
                        $row->kategori ?? '-',
                        '-',
                        0,
                        $this->fmtRupiah(0),
                        $this->fmtRupiah(0),
                        '-',
                        $row->status_pembelian ?? 'Rencana',
                    ];
                } else {
                    foreach ($row->details as $item) {
                        $qty = (int) ($item->qty ?? 0);
                        $harga = (int) ($item->harga ?? 0);
                        $sub = $qty * $harga;
                        $total += $sub;

                        $rows[] = [
                            $no++,
                            $tgl,
                            $row->periode ?? '-',
                            $row->kategori ?? '-',
                            $item->nama_barang ?? '-',
                            $qty,
                            $this->fmtRupiah($harga),
                            $this->fmtRupiah($sub),
                            $item->keterangan ?: '-',
                            $row->status_pembelian ?? 'Rencana',
                        ];
                    }
                }
            }

            $sections[] = [
                'judul'    => 'Rencana Pembelian',
                'headings' => ['No', 'Tanggal', 'Tenggat', 'Kategori', 'Nama Barang', 'Qty', 'Harga Satuan', 'Subtotal', 'Keterangan', 'Status'],
                'rows'     => $rows,
                'footer'   => 'Total Estimasi Rencana: ' . $this->fmtRupiah($total),
            ];
        }

        if ($statusFilter === 'semua' || $statusFilter === 'Terlaksana') {
            $rows = [];
            $no = 1;
            $total = 0;

            foreach ($data['pembelian'] as $row) {
                $tgl = $row->created_at ? Carbon::parse($row->created_at)->format('d-m-Y') : '-';
                if ($row->details->isEmpty()) {
                    $rows[] = [
                        $no++,
                        $tgl,
                        $row->no_kk ?: '-',
                        $row->periode ?? '-',
                        $row->kategori ?? '-',
                        '-',
                        0,
                        $this->fmtRupiah(0),
                        $this->fmtRupiah(0),
                        '-',
                        $row->status_pembelian ?? 'Terlaksana',
                    ];
                } else {
                    foreach ($row->details as $item) {
                        $qty = (int) ($item->qty ?? 0);
                        $harga = (int) ($item->harga ?? 0);
                        $sub = $qty * $harga;
                        $total += $sub;

                        $rows[] = [
                            $no++,
                            $tgl,
                            $row->no_kk ?: '-',
                            $row->periode ?? '-',
                            $row->kategori ?? '-',
                            $item->nama_barang ?? '-',
                            $qty,
                            $this->fmtRupiah($harga),
                            $this->fmtRupiah($sub),
                            $item->keterangan ?: '-',
                            $row->status_pembelian ?? 'Terlaksana',
                        ];
                    }
                }
            }

            $sections[] = [
                'judul'    => 'Pembelian Terlaksana',
                'headings' => ['No', 'Tanggal', 'No. KK', 'Tenggat', 'Kategori', 'Nama Barang', 'Qty', 'Harga Satuan', 'Subtotal', 'Keterangan', 'Status'],
                'rows'     => $rows,
                'footer'   => 'Total Terlaksana: ' . $this->fmtRupiah($total),
            ];
        }

        if ($statusFilter === 'semua' || $statusFilter === 'Dibatalkan') {
            $rows = [];
            $no = 1;
            $total = 0;

            foreach ($data['dibatalkan'] as $row) {
                $tgl = $row->created_at ? Carbon::parse($row->created_at)->format('d-m-Y') : '-';
                if ($row->details->isEmpty()) {
                    $rows[] = [
                        $no++,
                        $tgl,
                        $row->periode ?? '-',
                        $row->kategori ?? '-',
                        '-',
                        0,
                        $this->fmtRupiah(0),
                        $this->fmtRupiah(0),
                        $row->alasan_dibatalkan ?: '-',
                        $row->status_pembelian ?? 'Dibatalkan',
                    ];
                } else {
                    foreach ($row->details as $item) {
                        $qty = (int) ($item->qty ?? 0);
                        $harga = (int) ($item->harga ?? 0);
                        $sub = $qty * $harga;
                        $total += $sub;

                        $rows[] = [
                            $no++,
                            $tgl,
                            $row->periode ?? '-',
                            $row->kategori ?? '-',
                            $item->nama_barang ?? '-',
                            $qty,
                            $this->fmtRupiah($harga),
                            $this->fmtRupiah($sub),
                            $row->alasan_dibatalkan ?: '-',
                            $row->status_pembelian ?? 'Dibatalkan',
                        ];
                    }
                }
            }

            $sections[] = [
                'judul'    => 'Pembelian Dibatalkan',
                'headings' => ['No', 'Tanggal', 'Tenggat', 'Kategori', 'Nama Barang', 'Qty', 'Harga Satuan', 'Subtotal', 'Alasan Dibatalkan', 'Status'],
                'rows'     => $rows,
                'footer'   => 'Total Dibatalkan: ' . $this->fmtRupiah($total),
            ];
        }

        return $sections;
    }

    private function opsiTahun(): array
    {
        $tahunHr = PembelianHr::query()
            ->whereNotNull('created_at')
            ->selectRaw('YEAR(created_at) as y')
            ->distinct()
            ->pluck('y')
            ->all();

        $tahunKeg = Kegiatan::query()
            ->where('tipe', 'pembelian')
            ->selectRaw('YEAR(COALESCE(waktu_kegiatan, created_at)) as y')
            ->distinct()
            ->pluck('y')
            ->all();

        $all = array_unique(array_filter(array_map('intval', array_merge($tahunHr, $tahunKeg, [(int) now()->year]))));
        rsort($all);

        return array_values($all);
    }

    private function labelPeriode(Request $request): string
    {
        $mode = $this->resolveFilterMode($request);
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        if ($mode === 'bulan_tahun') {
            $bulan = (int) $request->input('bulan', 0);
            $tahun = (int) $request->input('tahun', 0);
            $parts = [];
            if ($bulan && isset($namaBulan[$bulan])) {
                $parts[] = $namaBulan[$bulan];
            }
            if ($tahun) {
                $parts[] = (string) $tahun;
            }
            return $parts ? implode(' ', $parts) : 'Semua Periode';
        }

        if ($mode === 'rentang') {
            $awal = $request->input('tanggal_awal');
            $akhir = $request->input('tanggal_akhir');
            if ($awal && $akhir) {
                return Carbon::parse($awal)->format('d-m-Y') . ' s/d ' . Carbon::parse($akhir)->format('d-m-Y');
            }
            if ($awal) {
                return 'Sejak ' . Carbon::parse($awal)->format('d-m-Y');
            }
            if ($akhir) {
                return 'Sampai ' . Carbon::parse($akhir)->format('d-m-Y');
            }
        }

        return 'Semua Periode';
    }

    private function fmtRupiah($nominal): string
    {
        return 'Rp ' . number_format((int) $nominal, 0, ',', '.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'no_kk' => 'nullable', 
            'kategori' => 'required',
            'periode' => 'required|in:Q1,Q2,Q3,Q4',
            'items' => 'required|array|min:1',
            'items.*.nama_barang' => 'required',  
            'items.*.qty' => 'required|integer|min:1', 
            'items.*.harga' => 'required|numeric',
            'items.*.url' => 'nullable',  
            'items.*.keterangan' => 'nullable',
        ]);

        DB::beginTransaction();
        try {
            $pembelian = PembelianHr::create([
                'no_kk' => $validated['no_kk'],
                'status_pembelian' => 'Rencana',
                'periode' => $request->periode,
                'kategori' => $request->kategori,
                'id_karyawan' => auth()->user()->karyawan->id
            ]);

            foreach ($validated['items'] as $item) {
                DetailPembelianHr::create([
                    'id_pembelian' => $pembelian->id,
                    'nama_barang' => $item['nama_barang'],
                    'qty' => $item['qty'],
                    'harga' => (int) $item['harga'],
                    'url' => $item['url'] ?? null,
                    'keterangan' => $item['keterangan'] ?? null,
                ]);
            }

            $auth = auth()->user()->karyawan;
            TrackingPembelianHr::create([
                'id_pembelian' => $pembelian->id,
                'tracking' => $auth->nama_lengkap . ' telah membuat rencana pembelian',
                'id_karyawan' => $auth->id
            ]);

            DB::commit();
            return back()->with('success', 'Rencana pembelian berhasil dibuat');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rencana pembelian hr (store): ' . $e->getMessage());
            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'no_kk' => 'nullable',
            'kategori' => 'required',
            'periode' => 'required|in:Q1,Q2,Q3,Q4',
            'status_pembelian' => 'nullable',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:detail_pembelian_hrs,id',
            'items.*.nama_barang' => 'required',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.harga' => 'required|numeric',
            'items.*.url' => 'nullable',
            'items.*.keterangan' => 'nullable',
            'deleted_ids' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $pembelian = PembelianHr::findOrFail($id);

            $updateData = [
                'no_kk' => $validated['no_kk'],
                'kategori' => $request->kategori,
                'periode' => $request->periode,
            ];

            if (!empty($validated['status_pembelian'])) {
                $updateData['status_pembelian'] = $validated['status_pembelian'];
            }

            $pembelian->update($updateData);

            if ($request->filled('deleted_ids')) {
                $deletedIds = array_filter(explode(',', $request->deleted_ids));
                DetailPembelianHr::whereIn('id', $deletedIds)->delete();
            }

            foreach ($validated['items'] as $item) {
                if (!empty($item['id'])) {
                    $detail = DetailPembelianHr::findOrFail($item['id']);
                    $detail->update([
                        'nama_barang' => $item['nama_barang'],
                        'qty' => $item['qty'],
                        'harga' => (int) $item['harga'],
                        'url' => $item['url'] ?? null,
                        'keterangan' => $item['keterangan'] ?? null,
                    ]);
                } else {
                    DetailPembelianHr::create([
                        'id_pembelian' => $pembelian->id,
                        'nama_barang' => $item['nama_barang'],
                        'qty' => $item['qty'],
                        'harga' => (int) $item['harga'],
                        'url' => $item['url'] ?? null,
                        'keterangan' => $item['keterangan'] ?? null,
                    ]);
                }
            }

            $auth = auth()->user()->karyawan;
            TrackingPembelianHr::create([
                'id_pembelian' => $pembelian->id,
                'tracking' => $auth->nama_lengkap . ' telah merubah rencana pembelian',
                'id_karyawan' => $auth->id
            ]);
            
            DB::commit();
            return back()->with('success', 'Data berhasil diperbaharui');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rencana pembelian hr (update): ' . $e->getMessage());
            return back()->with('error', 'Gagal memperbaharui data: ' . $e->getMessage());
        }
    }

    public function updateInvoice(Request $request, $id) 
    {
        $request->validate([
            'invoice' => 'required|file|mimes:pdf,jpg,jpeg,png' 
        ]);

        try {
            $pembelian = PembelianHr::findOrFail($id);
    
            if ($pembelian->invoice) {
                Storage::disk('public')->delete($pembelian->invoice);
            }
    
            $path = $request->file('invoice')->store('invoice', 'public');
    
            $pembelian->update([
                'invoice' => $path
            ]);
    
            return back()->with('success', 'Berhasil mengupload invoice');
        } catch (\Exception $e) {       
            Log::error('Error upload invoice hr: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengupload file');
        }
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id_pembelian' => 'required|exists:pembelian_hrs,id',
            'status' => 'required',
            'alasan_dibatalkan' => 'required_if:status,Dibatalkan'
        ]);

        try {
            $pembelian = PembelianHr::findOrFail($request->id_pembelian);

            if($request->status === 'Terlaksana' && (!$pembelian->invoice || !$pembelian->no_kk)) {
                return back()->with('error', 'Lengkapi data nomor kk dan invoice untuk update ke terlaksana!');
            }

            $updateData = [
                'status_pembelian' => $request->status,
                'alasan_dibatalkan' => $request->alasan_dibatalkan
            ];

            $pembelian->update($updateData);

            $auth = auth()->user()->karyawan;
            TrackingPembelianHr::create([
                'id_pembelian' => $pembelian->id,
                'tracking' => $auth->nama_lengkap . ' telah mengupdate status rencana pembelian menjadi ' . $request->status,
                'id_karyawan' => $auth->id
            ]);

            return back()->with('success', 'Status pembelian berhasil diubah');
        } catch (\Exception $e) {
            Log::error('Error update status hr: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengubah status');
        }
    }

    public function delete($id)
    {
        try {
            PembelianHr::findOrFail($id)->delete();
            return back()->with('success', 'Data pembelian berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error update status hr: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus data');
        }
    }

     public function getJurnalAkuntansi(Request $request)
    {
        $pembuat = $request->pembuat;

        $jurnalAkuntansi = JurnalAkuntansi::whereNotNull('id_pengajuan_barang')->get();

        $data = $jurnalAkuntansi->map(function ($jurnal) use ($pembuat) {

            $idsPengajuan = is_array($jurnal->id_pengajuan_barang)
                ? $jurnal->id_pengajuan_barang
                : json_decode($jurnal->id_pengajuan_barang, true);

            $pengajuanBarang = PengajuanBarang::whereIn('id', $idsPengajuan ?? [])
                ->where('id_karyawan', $pembuat)
                ->with(['detail','karyawan'])
                ->get();

            if ($pengajuanBarang->isEmpty()) {
                return null;
            }

            $namaBarang = $pengajuanBarang
                ->flatMap(fn($p) => $p->detail->pluck('nama_barang'))
                ->implode(', ');

            return [
                'id_jurnal'            => $jurnal->id,
                'id_pengajuan'  => $idsPengajuan,
                'nama_barang'          => $namaBarang,
                'total_harga'          => $jurnal->kredit,
                'tanggal'              => $jurnal->tanggal_transaksi,
                'diajukan_oleh'        => optional($pengajuanBarang->first()->karyawan)->nama_lengkap,
            ];

        })->filter()->values();

        return response()->json([
            'data' => $data
        ]);
    }

    public function storeRekap(Request $request) 
    {
        $request->validate([
            'items.*.id_pengajuan' => 'required|exists:pengajuanbarangs,id',
            'items.*.id_jurnal' => 'required|exists:jurnal_akuntansis,id',
            'kategori' => 'required',
            'periode' => 'required|in:Q1,Q2,Q3,Q4',
        ]);

        DB::beginTransaction();
        try{

            foreach($request->items as $item){
                $jurnal = JurnalAkuntansi::findOrFail($item['id_jurnal']);

                $pengajuan = PengajuanBarang::with([
                    'detail',
                    'karyawan'
                ])->findOrFail($item['id_pengajuan']);

                $exists = PembelianHr::where('id_pengajuan', $pengajuan->id)->exists();

                if ($exists) {
                    continue;
                }

                $pembelian = PembelianHr::create([
                    'no_kk'=>$jurnal->nomor_kk,
                    'id_karyawan'=>$pengajuan->id_karyawan,
                    'status_pembelian'=>'Terlaksana',
                    'periode'=>$request->periode,
                    'kategori'=>$request->kategori,
                    'invoice'=>$pengajuan->invoice,
                    'id_pengajuan'=>$pengajuan->id
                ]);

                foreach($pengajuan->detail as $detail){
                    DetailPembelianHr::create([
                        'id_pembelian'=>$pembelian->id,
                        'nama_barang'=>$detail->nama_barang,
                        'qty'=>$detail->qty,
                        'harga'=>$detail->harga,
                        'keterangan'=>$detail->keterangan,
                    ]);
                }

                TrackingPembelianHr::create([
                    'id_pembelian'=>$pembelian->id,
                    'tracking'=>$pengajuan->karyawan->nama_lengkap.' telah membuat rencana pembelian',
                    'id_karyawan'=>$pengajuan->id_karyawan,
                    'created_at'=>$pengajuan->created_at,
                    'updated_at'=>$pengajuan->updated_at,
                ]);
            }

            DB::commit();
            return back()->with('success','Rencana pembelian berhasil dibuat.');
        } catch(\Exception $e){

            DB::rollBack();
            Log::error($e);
            return back()->with('error','Gagal membuat rekap.');
        }
    }
}