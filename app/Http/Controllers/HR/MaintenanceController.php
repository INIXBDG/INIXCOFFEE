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

        // Statistik Biaya
        $statistik = Maintenance::select('kategori', DB::raw('COUNT(id) as total_tiket'), DB::raw('SUM(biaya) as total_biaya'))
            ->whereYear('tanggal_mulai', $tahun)
            ->groupBy('kategori')
            ->get();

        $inventaris = Inventaris::select('idbarang', 'name', 'kodebarang')->get();
        $teknisis = Karyawan::where('status_aktif', '1')->orderBy('nama_lengkap', 'asc')->get();

        return view('HR.Maintenance.index', compact('mendatang', 'sedangDikerjakan', 'riwayat', 'statistik', 'inventaris', 'teknisis'));
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
        $sheet->setCellValue('B1', 'KATEGORI');
        $sheet->setCellValue('C1', 'DIVISI');
        $sheet->setCellValue('D1', 'TEKNISI');
        $sheet->setCellValue('E1', 'NAMA BARANG');
        $sheet->setCellValue('F1', 'TANGGAL MULAI');
        $sheet->setCellValue('G1', 'TANGGAL SELESAI');
        $sheet->setCellValue('H1', 'NO VOUCHER');
        $sheet->setCellValue('I1', 'BIAYA');
        $sheet->setCellValue('J1', 'KETERANGAN');
        $sheet->setCellValue('K1', 'STATUS');

        $sheet->getStyle('A1:K1')->applyFromArray($headerStyle);

        // Populate Data
        $row = 2;
        foreach ($maintenances as $index => $item) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $item->kategori);
            $sheet->setCellValue('C' . $row, $item->divisi);
            $sheet->setCellValue('D' . $row, $item->teknisi);
            $sheet->setCellValue('E' . $row, $item->nama_barang);
            $sheet->setCellValue('F' . $row, $item->tanggal_mulai ? Carbon::parse($item->tanggal_mulai)->format('d-m-Y') : '');
            $sheet->setCellValue('G' . $row, $item->tanggal_selesai ? Carbon::parse($item->tanggal_selesai)->format('d-m-Y') : '');
            $sheet->setCellValue('H' . $row, $item->no_voucher);
            $sheet->setCellValue('I' . $row, $item->biaya);
            $sheet->setCellValue('J' . $row, $item->keterangan);
            $sheet->setCellValue('K' . $row, $item->status);

            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        // Auto size columns
        foreach (range('A', 'K') as $col) {
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
}
