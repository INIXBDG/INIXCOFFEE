<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Maintenance;
use App\Models\Inventaris;
use App\Models\Karyawan;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;
use PDF;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Maintenance::query();

        // Filters
        if ($request->filled('jenis')) {
            $query->where('kategori', $request->jenis);
        }
        if ($request->filled('lokasi')) {
            $query->where('divisi', $request->lokasi);
        }
        if ($request->filled('teknisi')) {
            $query->where('teknisi', $request->teknisi);
        }

        // Filter Tahun
        $tahun = $request->input('tahun', date('Y'));
        $query->whereYear('tanggal_mulai', $tahun);

        if ($request->filled('mode_periode')) {
            if ($request->mode_periode == 'bulan' && $request->filled('bulan')) {
                $query->whereMonth('tanggal_mulai', $request->bulan);
            } elseif ($request->mode_periode == 'quartal' && $request->filled('quartal')) {
                $q = $request->quartal;
                $startMonth = ($q - 1) * 3 + 1;
                $endMonth = $startMonth + 2;
                $query->whereMonth('tanggal_mulai', '>=', $startMonth)
                      ->whereMonth('tanggal_mulai', '<=', $endMonth);
            }
        }

        // Get data based on status
        $maintenances = $query->orderBy('tanggal_mulai', 'desc')->get();

        $mendatang = $maintenances->filter(function($item) {
            return $item->status == 'Mendatang' || ($item->status != 'Selesai' && $item->tanggal_mulai > date('Y-m-d'));
        });

        $sedangDikerjakan = $maintenances->filter(function($item) {
            return $item->status == 'On Progress' && $item->tanggal_mulai <= date('Y-m-d');
        });

        $riwayat = $maintenances->filter(function($item) {
            return $item->status == 'Selesai';
        });

        // Statistik Biaya per Kategori
        $statistik = Maintenance::select('kategori', DB::raw('COUNT(id) as total_tiket'), DB::raw('SUM(biaya) as total_biaya'))
            ->whereYear('tanggal_mulai', $tahun)
            ->groupBy('kategori')
            ->get();

        // Statistik per Divisi
        $statistikDivisi = Maintenance::select('divisi', DB::raw('COUNT(id) as total_tiket'))
            ->whereYear('tanggal_mulai', $tahun)
            ->groupBy('divisi')
            ->get();

        // Statistik Biaya per Bulan
        $statistikBulan = Maintenance::select(DB::raw('MONTH(tanggal_mulai) as bulan'), DB::raw('SUM(biaya) as total_biaya'))
            ->whereYear('tanggal_mulai', $tahun)
            ->groupBy(DB::raw('MONTH(tanggal_mulai)'))
            ->orderBy('bulan')
            ->get();

        $kategoris = Maintenance::select('kategori')->distinct()->whereNotNull('kategori')->pluck('kategori');
        $divisis = Maintenance::select('divisi')->distinct()->whereNotNull('divisi')->pluck('divisi');
        
        $inventaris = Inventaris::select('idbarang', 'name', 'kodebarang')->get();
        $teknisis = Karyawan::where('status_aktif', '1')->orderBy('nama_lengkap', 'asc')->get();

        return view('HR.Maintenance.index', compact('mendatang', 'sedangDikerjakan', 'riwayat', 'statistik', 'statistikDivisi', 'statistikBulan', 'inventaris', 'teknisis', 'kategoris', 'divisis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori' => 'required|string',
            'divisi' => 'required|string',
            'nama_barang' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'biaya' => 'required|numeric'
        ]);

        Maintenance::create([
            'kategori' => $request->kategori,
            'divisi' => $request->divisi,
            'teknisi' => $request->teknisi,
            'nama_barang' => $request->nama_barang,
            'tanggal_mulai' => $request->tanggal_mulai,
            'no_voucher' => $request->no_voucher,
            'biaya' => $request->biaya,
            'keterangan' => $request->keterangan,
            'status' => 'On Progress'
        ]);

        return redirect()->back()->with('success', 'Jadwal / Data Maintenance berhasil ditambahkan.');
    }

    public function markAsDone($id)
    {
        $maintenance = Maintenance::findOrFail($id);
        $maintenance->update([
            'status' => 'Selesai',
            'tanggal_selesai' => date('Y-m-d')
        ]);

        return redirect()->back()->with('success', 'Maintenance berhasil ditandai selesai.');
    }

    public function export_pdf(Request $request)
    {
        $maintenances = Maintenance::orderBy('tanggal_mulai', 'desc')->get();
        
        $mendatang = $maintenances->filter(function($item) {
            return $item->status == 'Mendatang' || ($item->status != 'Selesai' && $item->tanggal_mulai > date('Y-m-d'));
        });
        
        $sedangDikerjakan = $maintenances->filter(function($item) {
            return $item->status == 'On Progress' && $item->tanggal_mulai <= date('Y-m-d');
        });
        
        $riwayat = $maintenances->filter(function($item) {
            return $item->status == 'Selesai';
        });

        $pdf = PDF::loadView('HR.Maintenance.export_pdf', compact('mendatang', 'sedangDikerjakan', 'riwayat'));
        return $pdf->download('Data_Maintenance.pdf');
    }

    public function export_excel()
    {
        $maintenances = Maintenance::all();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Maintenance');

        // Header style
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4E79'],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        $sheet->setCellValue('A1', 'NO');
        $sheet->setCellValue('B1', 'ID SERVICE');
        $sheet->setCellValue('C1', 'KATEGORI');
        $sheet->setCellValue('D1', 'DIVISI');
        $sheet->setCellValue('E1', 'TEKNISI');
        $sheet->setCellValue('F1', 'NAMA BARANG');
        $sheet->setCellValue('G1', 'TANGGAL MULAI');
        $sheet->setCellValue('H1', 'TANGGAL SELESAI');
        $sheet->setCellValue('I1', 'NO VOUCHER');
        $sheet->setCellValue('J1', 'BIAYA');
        $sheet->setCellValue('K1', 'KETERANGAN');
        $sheet->setCellValue('L1', 'STATUS');

        $sheet->getStyle('A1:L1')->applyFromArray($headerStyle);

        // Populate Data
        $row = 2;
        foreach ($maintenances as $index => $item) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, 'MNT-' . str_pad($item->id, 4, '0', STR_PAD_LEFT));
            $sheet->setCellValue('C' . $row, $item->kategori);
            $sheet->setCellValue('D' . $row, $item->divisi);
            $sheet->setCellValue('E' . $row, $item->teknisi);
            $sheet->setCellValue('F' . $row, $item->nama_barang);
            $sheet->setCellValue('G' . $row, $item->tanggal_mulai ? Carbon::parse($item->tanggal_mulai)->format('d-m-Y') : '');
            $sheet->setCellValue('H' . $row, $item->tanggal_selesai ? Carbon::parse($item->tanggal_selesai)->format('d-m-Y') : '');
            $sheet->setCellValue('I' . $row, $item->no_voucher);
            $sheet->setCellValue('J' . $row, $item->biaya);
            $sheet->setCellValue('K' . $row, $item->keterangan);
            $sheet->setCellValue('L' . $row, $item->status);

            $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        // Auto size columns
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create Writer
        $writer = new Xlsx($spreadsheet);

        // Return as response
        $fileName = 'Data_Maintenance_' . date('YmdHis') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    public function template_excel()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headerStyle = [
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0E0E0']
            ]
        ];

        // Header template
        $headers = [
            'A1' => 'KATEGORI',
            'B1' => 'DIVISI',
            'C1' => 'TEKNISI',
            'D1' => 'NAMA BARANG',
            'E1' => 'TANGGAL MULAI (DD-MM-YYYY)',
            'F1' => 'TANGGAL SELESAI (DD-MM-YYYY)',
            'G1' => 'NO VOUCHER',
            'H1' => 'BIAYA',
            'I1' => 'KETERANGAN'
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
        
        // Data Contoh
        $sheet->setCellValue('A2', 'Hardware');
        $sheet->setCellValue('B2', 'IT');
        $sheet->setCellValue('C2', 'Budi');
        $sheet->setCellValue('D2', 'Laptop Lenovo');
        $sheet->setCellValue('E2', '10-07-2026');
        $sheet->setCellValue('F2', '');
        $sheet->setCellValue('G2', 'KK-001');
        $sheet->setCellValue('H2', 150000);
        $sheet->setCellValue('I2', 'Ganti RAM');

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Template_Import_Maintenance.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function import(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file'
        ]);

        try {
            $file = $request->file('file_excel');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $row_limit = $sheet->getHighestDataRow();
            
            // Daftar alias untuk pencarian kolom
            $aliases = [
                'id' => ['id_service', 'id', 'idservice', 'no_service', 'nomor_service'],
                'kategori' => ['kategori', 'jenis', 'type', 'tipe'],
                'divisi' => ['divisi', 'lokasi', 'ruangan', 'tempat', 'departemen'],
                'teknisi' => ['teknisi', 'pic', 'penanggung_jawab', 'petugas', 'vendor'],
                'nama_barang' => ['nama_barang', 'barang', 'item', 'deskripsi_barang', 'nama'],
                'tanggal_mulai' => ['tanggal_mulai', 'tgl_mulai', 'waktu_mulai', 'tanggal'],
                'tanggal_selesai' => ['tanggal_selesai', 'tgl_selesai', 'waktu_selesai'],
                'no_voucher' => ['no_voucher', 'voucher', 'nomor_voucher', 'referensi'],
                'biaya' => ['biaya', 'harga', 'total_biaya', 'cost', 'pengeluaran', 'rp'],
                'keterangan' => ['keterangan', 'deskripsi', 'catatan', 'notes'],
                'status' => ['status', 'kondisi', 'progress']
            ];

            // 1. Cari baris header (scan baris 1-15)
            $bestHeaderRow = 1;
            $bestMap = [];
            $maxMatches = 0;

            for ($r = 1; $r <= min(15, $row_limit); $r++) {
                $headers = [];
                foreach ($sheet->getRowIterator($r, $r) as $rowObj) {
                    $cellIterator = $rowObj->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    foreach ($cellIterator as $cell) {
                        $val = $cell->getValue();
                        if ($val) {
                            $headers[$cell->getColumn()] = strtolower(trim((string)$val));
                        }
                    }
                }

                $currentMap = [];
                $matches = 0;
                foreach ($aliases as $key => $aliasList) {
                    $currentMap[$key] = null;
                    foreach ($headers as $colLetter => $headerVal) {
                        $cleanHeader = str_replace(['_', ' '], '', $headerVal);
                        foreach ($aliasList as $alias) {
                            $cleanAlias = str_replace(['_', ' '], '', $alias);
                            if (str_contains($cleanHeader, $cleanAlias)) {
                                $currentMap[$key] = $colLetter;
                                $matches++;
                                break 2;
                            }
                        }
                    }
                }

                if ($matches > $maxMatches) {
                    $maxMatches = $matches;
                    $bestMap = $currentMap;
                    $bestHeaderRow = $r;
                }
            }

            if ($maxMatches == 0) {
                throw new \Exception("Tidak dapat menemukan kolom header yang sesuai (seperti Kategori, Barang, atau Teknisi) di 15 baris pertama.");
            }

            $map = $bestMap;
            $row_range = range($bestHeaderRow + 1, $row_limit);

            $importedCount = 0;

            \Illuminate\Support\Facades\DB::beginTransaction();
            foreach ($row_range as $row) {
                
                // 4. Ambil data secara fleksibel menggunakan pemetaan kolom
                $id_service = $map['id'] ? $sheet->getCell($map['id'] . $row)->getValue() : null;
                $kategori = $map['kategori'] ? $sheet->getCell($map['kategori'] . $row)->getValue() : null;
                $divisi = $map['divisi'] ? $sheet->getCell($map['divisi'] . $row)->getValue() : null;
                $teknisi = $map['teknisi'] ? $sheet->getCell($map['teknisi'] . $row)->getValue() : null;
                $nama_barang = $map['nama_barang'] ? $sheet->getCell($map['nama_barang'] . $row)->getValue() : null;
                
                // Cek jika baris benar-benar kosong (semua key fields kosong)
                if (empty(trim((string)$nama_barang)) && empty(trim((string)$kategori)) && empty(trim((string)$teknisi)) && empty(trim((string)$divisi))) {
                    continue; 
                }

                // Set default value untuk field yang masih kosong
                if (empty(trim((string)$nama_barang))) $nama_barang = 'Tidak Diketahui';
                if (empty(trim((string)$kategori))) $kategori = '-';
                if (empty(trim((string)$divisi))) $divisi = '-';
                if (empty(trim((string)$teknisi))) $teknisi = '-';

                $tgl_mulai = $map['tanggal_mulai'] ? $sheet->getCell($map['tanggal_mulai'] . $row)->getValue() : null;
                $tgl_selesai = $map['tanggal_selesai'] ? $sheet->getCell($map['tanggal_selesai'] . $row)->getValue() : null;
                $no_voucher = $map['no_voucher'] ? $sheet->getCell($map['no_voucher'] . $row)->getValue() : null;
                $biaya = $map['biaya'] ? $sheet->getCell($map['biaya'] . $row)->getValue() : 0;
                $keterangan = $map['keterangan'] ? $sheet->getCell($map['keterangan'] . $row)->getValue() : null;
                $status = $map['status'] ? $sheet->getCell($map['status'] . $row)->getValue() : ($tgl_selesai ? 'Selesai' : 'On Progress');

                // Pembersihan Biaya
                if (is_string($biaya)) {
                    $biaya = (float) preg_replace('/[\,\.Rp\s]+/', '', $biaya);
                } else {
                    $biaya = (float) $biaya;
                }

                // Handle date conversion
                if (is_numeric($tgl_mulai)) {
                    $tgl_mulai = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tgl_mulai)->format('Y-m-d');
                } else if (!empty($tgl_mulai)) {
                    try {
                        $tgl_mulai = \Carbon\Carbon::parse($tgl_mulai)->format('Y-m-d');
                    } catch (\Exception $e) { $tgl_mulai = null; }
                } else {
                    $tgl_mulai = null;
                }

                if (is_numeric($tgl_selesai)) {
                    $tgl_selesai = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tgl_selesai)->format('Y-m-d');
                } else if (!empty($tgl_selesai)) {
                    try {
                        $tgl_selesai = \Carbon\Carbon::parse($tgl_selesai)->format('Y-m-d');
                    } catch (\Exception $e) { $tgl_selesai = null; }
                } else {
                    $tgl_selesai = null;
                }

                $id = !empty($id_service) ? (int) str_replace('MNT-', '', $id_service) : null;

                if (!empty($id)) {
                    $maintenance = \App\Models\Maintenance::find($id);
                    if ($maintenance) {
                        $maintenance->update([
                            'kategori' => $kategori,
                            'divisi' => $divisi,
                            'teknisi' => $teknisi,
                            'nama_barang' => $nama_barang,
                            'tanggal_mulai' => $tgl_mulai,
                            'tanggal_selesai' => $tgl_selesai,
                            'no_voucher' => $no_voucher,
                            'biaya' => $biaya ?? 0,
                            'keterangan' => $keterangan,
                            'status' => $status
                        ]);
                    }
                } else {
                    \App\Models\Maintenance::create([
                        'kategori' => $kategori,
                        'divisi' => $divisi,
                        'teknisi' => $teknisi,
                        'nama_barang' => $nama_barang,
                        'tanggal_mulai' => $tgl_mulai ?? date('Y-m-d'),
                        'tanggal_selesai' => $tgl_selesai,
                        'no_voucher' => $no_voucher,
                        'biaya' => $biaya ?? 0,
                        'keterangan' => $keterangan,
                        'status' => $status
                    ]);
                }
                $importedCount++;
            }

            if ($importedCount == 0) {
                throw new \Exception("Header berhasil dipetakan, namun tidak ada baris data valid yang ditemukan (Pastikan kolom Barang/Kategori/Teknisi terisi).");
            }

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('HR.maintenance.index')->with('success', "Berhasil memigrasikan {$importedCount} data maintenance!");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengimport data: ' . $e->getMessage());
        }
    }
}
