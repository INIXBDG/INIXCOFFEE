<?php

namespace App\Http\Controllers;

use App\Models\JurnalAkuntansi;
use App\Models\karyawan;
use App\Models\no_akun;
use Illuminate\Http\Request;
use App\Models\PengajuanBarang;
use App\Models\perhitunganNetSales;
use Barryvdh\DomPDF\Facade\Pdf;

class JurnalAkuntansiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Menampilkan halaman indeks jurnal akuntansi.
     */
    public function index()
    {
        $no_akun = no_akun::get();
        return view('jurnalakuntansi.index', compact('no_akun'));
    }

    private function generateNomorKK($tanggal_transaksi)
    {
        $tahun = \Carbon\Carbon::parse($tanggal_transaksi)->format('Y');

        $lastData = JurnalAkuntansi::whereYear('tanggal_transaksi', $tahun)
            ->whereNotNull('nomor_kk')
            ->where('nomor_kk', 'like', 'KK-%')
            ->orderByRaw("CAST(SUBSTRING(nomor_kk, 4) AS UNSIGNED) DESC")
            ->first();

        if ($lastData) {
            $lastNumber = (int) substr($lastData->nomor_kk, 3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'KK-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Mengembalikan data JSON untuk DataTables.
     */
    public function getData(Request $request)
    {
        $query = JurnalAkuntansi::with('no_accounting', 'netSales');

        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_transaksi', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_transaksi', '<=', $request->end_date);
        }

        $data = $query->latest()->get();

        $data->transform(function ($jurnal) {
            // Ambil data pengajuan barang berdasarkan array ID di JSON
            if (is_array($jurnal->id_pengajuan_barang) && count($jurnal->id_pengajuan_barang) > 0) {
                $jurnal->list_pengajuan = PengajuanBarang::with('karyawan')->whereIn('id', $jurnal->id_pengajuan_barang)
                                            ->with('detail') // Muat detail barangnya juga
                                            ->get();
            } else {
                $jurnal->list_pengajuan = [];
            }
            return $jurnal;
        });

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * Mengembalikan data JSON pengajuan barang yang belum memiliki jurnal.
     */
    public function getBelumJurnal()
    {
        // 1. Ambil semua ID yang sudah terdaftar di Jurnal Akuntansi
        $alreadyJurnaledIds = JurnalAkuntansi::whereNotNull('id_pengajuan_barang')
            ->pluck('id_pengajuan_barang')
            ->flatten()
            ->unique()
            ->toArray();

        // 2. Ambil data pengajuan yang ID-nya TIDAK ADA di array di atas
        $data = PengajuanBarang::with(['karyawan', 'detail', 'tracking'])
            ->whereNotIn('id', $alreadyJurnaledIds)
            ->whereHas('tracking', function($query) {
                $query->whereIn('tracking', ['Selesai', 'Pencairan Sudah Selesai']);
            })
            ->get();
        
        $formattedData = $data->map(function($item) {
            $totalHarga = 0;
            foreach($item->detail as $det) {
                $qtyValue = (int) $det->qty;
                $hargaClean = str_replace('.', '', $det->harga);
                $totalHarga += ($qtyValue * (float)$hargaClean);
            }

            return [
                'id' => $item->id,
                'nama_karyawan' => $item->karyawan->nama_lengkap ?? '-',
                'tipe' => $item->tipe,
                'tanggal' => $item->created_at->format('Y-m-d'),
                'total' => $totalHarga,
                'tracking' => $item->tracking,
                'detail_pengajuan_barang' => $item->detail,
            ];
        });

        return response()->json(['data' => $formattedData]);
    }

    /**
     * Mengembalikan data JSON Perhitungan Net Sales yang belum memiliki jurnal.
     */
    public function getBelumJurnalNetSales()
    {
        $data = perhitunganNetSales::with(['karyawan', 'trackingNetSales', 'rkm.materi', 'rkm.perusahaan'])
            ->whereDoesntHave('jurnalAkuntansi')
            ->get();
        // return $data;
        $formattedData = $data->map(function($item) {
            // Kalkulasi total pengeluaran Net Sales
            $totalHarga = $item->transportasi + $item->akomodasi_peserta + $item->akomodasi_tim + 
                          $item->fresh_money + $item->entertaint + $item->souvenir + 
                          $item->cashback + $item->sewa_laptop;

            return [
                'id' => $item->id,
                'nama_materi' => $item->rkm->materi->nama_materi ?? '-',
                'nama_perusahaan' => $item->rkm->perusahaan->nama_perusahaan ?? '-',
                'tipe' => 'Payment Advanced',
                'tanggal' => $item->created_at->format('Y-m-d'),
                'total' => $totalHarga,
                'detail_biaya' => [
                    'Transportasi' => $item->transportasi,
                    'Akomodasi Peserta' => $item->akomodasi_peserta,
                    'Akomodasi Tim' => $item->akomodasi_tim,
                    'Fresh Money' => $item->fresh_money,
                    'Entertaint' => $item->entertaint,
                    'Souvenir' => $item->souvenir,
                    'Cashback' => $item->cashback,
                    'Sewa Laptop' => $item->sewa_laptop
                ]
            ];
        });

        return response()->json(['data' => $formattedData]);
    }

    /**
     * Menyimpan jurnal akuntansi secara manual dari Perhitungan Net Sales.
     */
    public function storeManualNetSales($id)
    {
        $netSales = \App\Models\perhitunganNetSales::with('rkm.materi', 'rkm.perusahaan')->findOrFail($id);
        
        $jurnalExist = JurnalAkuntansi::where('id_perhitungan_net_sales', $id)->first();
        if ($jurnalExist) {
            return response()->json(['success' => false, 'message' => 'Jurnal untuk Net Sales ini sudah ada!']);
        }

        $totalPengeluaran = $netSales->transportasi + $netSales->akomodasi_peserta + $netSales->akomodasi_tim + 
                            $netSales->fresh_money + $netSales->entertaint + $netSales->souvenir + 
                            $netSales->cashback + $netSales->sewa_laptop;

        $tanggal_transaksi = now();
        $materi = $netSales->rkm->materi->nama_materi ?? '-';
        $perusahaan = $netSales->rkm->perusahaan->nama_perusahaan ?? '-';
        $bulan = $netSales->rkm->bulan ?? '-';

        $keterangan = "Pengeluaran Payment Advanced - {$materi} | {$perusahaan} | {$bulan}";
        // return $netSales;

        JurnalAkuntansi::create([
            'nomor_kk' => $this->generateNomorKK(now()),
            'id_perhitungan_net_sales' => $id, 
            'id_pengajuan_barang' => [], 
            'tanggal_transaksi' => now(),
            'keterangan' => $keterangan,
            'debit' => $totalPengeluaran,
            'kredit' => 0,
        ]);

        return response()->json(['success' => true, 'message' => 'Jurnal Net Sales berhasil dibuat!']);
    }

    /**
     * Menyimpan jurnal akuntansi secara manual dari pengajuan barang.
     */
    public function storeManual($id)
    {
        $pengajuan = PengajuanBarang::with('detail', 'karyawan')->findOrFail($id);
        
        $jurnalExist = JurnalAkuntansi::whereJsonContains('id_pengajuan_barang', (int)$id)->first();
        
        if ($jurnalExist) {
            return response()->json(['success' => false, 'message' => 'Jurnal untuk pengajuan ini sudah ada!']);
        }

        $totalPengeluaran = 0;
        foreach ($pengajuan->detail as $item) {
            $qtyValue = (int) $item->qty;
            $hargaClean = str_replace('.', '', $item->harga);
            $totalPengeluaran += ($qtyValue * (float)$hargaClean);
        }

        $tanggal_transaksi = now();

        JurnalAkuntansi::create([
            'nomor_kk' => $this->generateNomorKK($tanggal_transaksi),
            'id_pengajuan_barang' => [(int)$id], 
            'tanggal_transaksi' => $tanggal_transaksi,
            'keterangan' => 'Pengeluaran untuk Pengajuan Barang dari : ' . $pengajuan->karyawan->nama_lengkap . ' (' . $pengajuan->tipe . ')',
            'kredit' => $totalPengeluaran, 
            'debit' => 0,
        ]);

        return response()->json(['success' => true, 'message' => 'Jurnal berhasil dibuat!']);
    }
    /**
     * Mengambil data spesifik jurnal akuntansi untuk form edit.
     */
    /**
     * Mengambil data spesifik jurnal akuntansi untuk form edit beserta identifikasi jenis.
     */
    public function edit($id)
    {
        $jurnal = JurnalAkuntansi::findOrFail($id);
        $is_petty_cash = is_null($jurnal->id_pengajuan_barang);
        
        return response()->json([
            'success' => true,
            'data' => $jurnal,
            'is_petty_cash' => $is_petty_cash
        ]);
    }
    public function update(Request $request, $id)
    {
        $jurnal = JurnalAkuntansi::findOrFail($id);

        $request->validate([
            'tanggal_transaksi' => 'required|date',
            'keterangan' => 'required|string',
            'no_akun' => 'nullable',
            'debit' => 'required|numeric|min:0',
            'kredit' => 'required|numeric|min:0',
        ]);

        $jurnal->update([
            'tanggal_transaksi' => $request->tanggal_transaksi,
            'keterangan' => $request->keterangan,
            'no_akun' => $request->no_akun,
            'debit' => $request->debit,
            'kredit' => $request->kredit,
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Data Jurnal Akuntansi berhasil diperbarui.'
        ]);
    }

    /**
     * Menyimpan data jurnal akuntansi manual (Kas Kecil).
     */
    public function storePettyCash(Request $request)
    {
        $request->validate([
            'tanggal_transaksi' => 'required|date',
            'keterangan' => 'required|string',
            'tipe_transaksi' => 'required|in:debit,kredit',
            'nominal' => 'required|numeric|min:1',
        ]);

        $debit = $request->tipe_transaksi === 'debit' ? $request->nominal : 0;
        $kredit = $request->tipe_transaksi === 'kredit' ? $request->nominal : 0;

        JurnalAkuntansi::create([
            'nomor_kk' => $this->generateNomorKK($request->tanggal_transaksi), // GENERATE DISINI
            'id_pengajuan_barang' => null, 
            'tanggal_transaksi' => $request->tanggal_transaksi,
            'keterangan' => '[Kas Kecil] ' . $request->keterangan,
            'debit' => $debit,
            'kredit' => $kredit,
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Data Kas Kecil berhasil ditambahkan.'
        ]);
    }

    /**
     * Memproses dan menyimpan data dari file Excel.
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $file = $request->file('file');
            $data = \Maatwebsite\Excel\Facades\Excel::toArray([], $file);
            $sheet = $data[0];

            foreach ($sheet as $key => $row) {
                // Melewati baris pertama (index 0) yang berisi Header Kolom
                if ($key === 0) continue; 

                // Mengabaikan baris jika Tanggal atau Keterangan kosong
                if (empty($row[1]) || empty($row[2])) continue;

                // Konversi format tanggal (mendukung Serial Date Excel maupun String Date biasa)
                $tanggal = $row[1];
                if (is_numeric($tanggal)) {
                    $tanggal = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggal)->format('Y-m-d');
                } else {
                    $tanggal = \Carbon\Carbon::parse($tanggal)->format('Y-m-d');
                }

                // Menentukan Nomor KK: Gunakan dari file Excel, jika kosong jalankan method generator
                $nomor_kk = !empty($row[0]) ? $row[0] : $this->generateNomorKK($tanggal);
                $no_akun_bersih = isset($row[3]) && $row[3] !== '' ? trim((string) $row[3]) : null;
                // Normalisasi string mata uang menjadi float
                $debit = isset($row[4]) ? (float) preg_replace('/[^0-9.]/', '', $row[4]) : 0;
                $kredit = isset($row[5]) ? (float) preg_replace('/[^0-9.]/', '', $row[5]) : 0;

                JurnalAkuntansi::create([
                    'nomor_kk' => $nomor_kk,
                    'id_pengajuan_barang' => null,
                    'tanggal_transaksi' => $tanggal,
                    'keterangan' => $row[2],
                    'no_akun' => $no_akun_bersih,
                    'debit' => $debit,
                    'kredit' => $kredit,
                ]);
            }

            return response()->json([
                'success' => true, 
                'message' => 'Data Jurnal Akuntansi dari Excel berhasil diimpor.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Gagal mengimpor data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Memproses export data berdasarkan periode yang dipilih.
     */
    /**
     * Memproses export data berdasarkan periode yang dipilih.
     */
    public function export(Request $request)
    {
        $tipe_periode = $request->tipe_periode;
        $tanggal_acuan = \Carbon\Carbon::parse($request->tanggal_acuan);
        $format = $request->format_export; // Menerima: preview, excel, pdf

        $query = JurnalAkuntansi::query();

        // Logika Filter Periode
        switch ($tipe_periode) {
            case 'harian':
                $query->whereDate('tanggal_transaksi', $tanggal_acuan->format('Y-m-d'));
                $labelPeriode = "Harian (" . $tanggal_acuan->format('d M Y') . ")";
                break;
            case 'mingguan':
                $query->whereBetween('tanggal_transaksi', [
                    $tanggal_acuan->copy()->startOfWeek()->format('Y-m-d'),
                    $tanggal_acuan->copy()->endOfWeek()->format('Y-m-d')
                ]);
                $labelPeriode = "Mingguan (" . $tanggal_acuan->copy()->startOfWeek()->format('d M Y') . " - " . $tanggal_acuan->copy()->endOfWeek()->format('d M Y') . ")";
                break;
            case 'bulanan':
                $query->whereMonth('tanggal_transaksi', $tanggal_acuan->month)
                      ->whereYear('tanggal_transaksi', $tanggal_acuan->year);
                $labelPeriode = "Bulanan (" . $tanggal_acuan->format('F Y') . ")";
                break;
            case 'triwulan':
                $query->whereBetween('tanggal_transaksi', [
                    $tanggal_acuan->copy()->firstOfQuarter()->format('Y-m-d'),
                    $tanggal_acuan->copy()->lastOfQuarter()->format('Y-m-d')
                ]);
                $labelPeriode = "Triwulan (" . $tanggal_acuan->copy()->firstOfQuarter()->format('d M Y') . " - " . $tanggal_acuan->copy()->lastOfQuarter()->format('d M Y') . ")";
                break;
            case 'tahunan':
                $query->whereYear('tanggal_transaksi', $tanggal_acuan->year);
                $labelPeriode = "Tahunan (" . $tanggal_acuan->format('Y') . ")";
                break;
            default:
                $labelPeriode = "Semua Data";
                break;
        }

        $data = $query->orderBy('tanggal_transaksi', 'asc')->get();

        // Mode Preview: Mengembalikan tampilan interaktif
        if ($format === 'preview') {
            $totalDebit = $data->sum('debit');
            $totalKredit = $data->sum('kredit');
            return view('jurnalakuntansi.preview_export', compact('data', 'labelPeriode', 'tipe_periode', 'tanggal_acuan', 'totalDebit', 'totalKredit'));
        }

        // Mode Export: Menangkap nilai hasil kalkulasi manual dari UI Preview
        $saldo_awal = (float) $request->input('saldo_awal', 0);
        $kas_masuk = (float) $request->input('kas_masuk', 0);
        $kas_keluar = (float) $request->input('kas_keluar', 0);
        $saldo_akhir = (float) $request->input('saldo_akhir', 0);

        if ($format === 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\JurnalAkuntansiExport($data, $labelPeriode, $format, $saldo_awal, $kas_masuk, $kas_keluar, $saldo_akhir), 'Jurnal_Akuntansi_' . time() . '.xlsx');
        } elseif ($format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('jurnalakuntansi.export_template', [
                'data' => $data,
                'periode' => $labelPeriode,
                'format' => $format,
                'saldo_awal' => $saldo_awal,
                'kas_masuk' => $kas_masuk,
                'kas_keluar' => $kas_keluar,
                'saldo_akhir' => $saldo_akhir
            ])->setPaper('a4', 'landscape');
            return $pdf->download('Jurnal_Akuntansi_' . time() . '.pdf');
        }

        return redirect()->back()->with('error', 'Format eksport tidak valid.');
    }

    public function eksportPdf($id) 
    {
        $jurnalAkuntansi = JurnalAkuntansi::findOrFail($id);

        $listPengajuan = $jurnalAkuntansi->ListPengajuan(); 
        // dd($listPengajuan);
    
        $firstPengajuan = $listPengajuan->first();
        $finance = null;

        if ($firstPengajuan && $firstPengajuan->karyawan) {
            $divisi = $firstPengajuan->karyawan->divisi;
            
            if ($divisi == 'Education') {
                $finance = karyawan::where('jabatan', 'Education Manager')->latest()->first();
            } elseif ($divisi == 'Sales & Marketing') {
                $finance = karyawan::where('jabatan', 'SPV Sales')->latest()->first();
            } elseif ($divisi == 'Office') {
                $finance = karyawan::where('jabatan', 'GM')->latest()->first();
            } elseif ($divisi == 'IT Service Management') { 
                $finance = karyawan::where('jabatan', 'Koordinator ITSM')->latest()->first();
            }
        }
        
        $gm = karyawan::where('jabatan', 'GM')->latest()->first();
        $pdf = Pdf::loadView('jurnalakuntansi.eksportPdf', compact('jurnalAkuntansi', 'gm', 'finance', 'listPengajuan'))
            ->setPaper('A4', 'portrait');

        return $pdf->download('laporan-jurnal-' . $jurnalAkuntansi->nomor_kk . '.pdf');
    }
}