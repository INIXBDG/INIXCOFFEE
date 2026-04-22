<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\karyawan;
use App\Models\Kwitansi;
use App\Models\outstanding;
use App\Models\Perusahaan;
use App\Models\RKM;
use App\Models\trackingOutstanding;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


// Tambahkan fungsi ini di sini, di luar class atau di dalam class sebagai protected/private
function terbilang($x) {
    $angka = abs($x);
    $baca = array('', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas');
    $temp = '';

    if ($angka < 12) {
        $temp = " " . $baca[$angka];
    } else if ($angka < 20) {
        $temp = terbilang($angka - 10) . " belas";
    } else if ($angka < 100) {
        $temp = terbilang($angka / 10) . " puluh" . terbilang(fmod($angka, 10));
    } else if ($angka < 200) {
        $temp = " seratus" . terbilang($angka - 100);
    } else if ($angka < 1000) {
        $temp = terbilang($angka / 100) . " ratus" . terbilang(fmod($angka, 100));
    } else if ($angka < 2000) {
        $temp = " seribu" . terbilang($angka - 1000);
    } else if ($angka < 1000000) {
        $temp = terbilang($angka / 1000) . " ribu" . terbilang(fmod($angka, 1000));
    } else if ($angka < 1000000000) {
        $temp = terbilang($angka / 1000000) . " juta" . terbilang(fmod($angka, 1000000));
    } else if ($angka < 1000000000000) {
        $temp = terbilang($angka / 1000000000) . " miliar" . terbilang(fmod($angka, 1000000000));
    } else if ($angka < 1000000000000000) {
        $temp = terbilang($angka / 1000000000000) . " triliun" . terbilang(fmod($angka, 1000000000000));
    }

    return $temp;
}

function format_terbilang($angka) {
    if ($angka < 0) {
        return "minus " . trim(ucwords(terbilang($angka))) . " rupiah";
    } else {
        return trim(ucwords(terbilang($angka))) . " rupiah";
    }
}

class InvoiceRKMController extends Controller
{
    /**
     * Menampilkan halaman indeks dengan dua tabel.
     *
     * @return \Illuminate\View\View
     */
public function index(): View
{
    // Ambil semua ID RKM yang sudah ada di tabel invoices
    $existingRKMs = Invoice::pluck('id_rkm')->toArray();

    // Ambil semua ID RKM yang sudah ada di tabel kwitansi
    $receiptedRKMs = Kwitansi::pluck('id_rkm')->toArray();

    // Data untuk tabel 'Belum di-Invoice'
    $notInvoicedRkms = RKM::with(['sales', 'materi', 'instruktur', 'perusahaan'])
        ->whereNotIn('id', $existingRKMs)
        ->orderBy('tanggal_awal', 'desc')
        ->get();

    // Data untuk tabel 'Sudah di-Invoice'
    $invoicedRkms = RKM::with(['sales', 'materi', 'instruktur', 'perusahaan', 'invoice'])
        ->whereIn('id', $existingRKMs)
        ->orderBy('tanggal_awal', 'desc')
        ->get();

    // Data untuk tabel 'Sudah Invoice tapi Belum Kwitansi'
    $notReceiptedRkms = RKM::with(['sales', 'materi', 'instruktur', 'perusahaan', 'invoice'])
        ->whereIn('id', $existingRKMs) // sudah ada invoice
        ->whereNotIn('id', $receiptedRKMs) // tapi belum ada kwitansi
        ->orderBy('tanggal_awal', 'desc')
        ->get();

    // Data untuk tabel 'Sudah ada Kwitansi'
    $receiptedRkms = RKM::with(['sales', 'materi', 'instruktur', 'perusahaan', 'invoice', 'kwitansi'])
        ->whereIn('id', $receiptedRKMs)
        ->orderBy('tanggal_awal', 'desc')
        ->get();

    return view('invoice.index', compact(
        'notInvoicedRkms',
        'invoicedRkms',
        'notReceiptedRkms',
        'receiptedRkms'
    ));
}

        
    /**
     * Menampilkan form untuk membuat invoice baru dari RKM tertentu.
     *
     * @param  string  $id
     * @return \Illuminate\View\View
     */
    public function create(string $id): View
    {
        $rkm = RKM::with('perusahaan', 'materi', 'registrasi.peserta')->findOrFail($id);
        return view('invoice.create', compact('rkm'));
    }

    // public function downloadPDF($id){

    // }
// app/Http/Controllers/InvoiceRKMController.php

public function createKwitansi($invoiceId)
{
    // Ambil data invoice
    $invoice = Invoice::with(['rkm.perusahaan', 'rkm.materi'])->findOrFail($invoiceId);

    // Ambil data karyawan untuk penandatangan
    $karyawan = Karyawan::find(22); // Sesuaikan dengan id karyawan yang benar

    // Tampilkan view form tanpa membuat record kwitansi
    return view('kwitansi.create', compact('invoice', 'karyawan'));
}
    /**
     * Menyimpan invoice baru ke database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'invoice_number' => 'required|string|max:255|unique:invoices',
            'tanggal_invoice' => 'required|date',
            'due_date' => 'nullable|date',
            'purchase_order' => 'nullable|string|max:255',
            'id_rkm' => 'required|exists:r_k_m_s,id',
            'amount' => 'required|numeric',
            'unit_price' => 'nullable|numeric',
            'pax' => 'nullable|integer',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'terbilang' => 'nullable|string',
            'peserta' => 'nullable|array',
            'peserta.*' => 'nullable|string|max:255',
            'is_peserta' => 'required|in:true,false',
            'is_ttd' => 'required|in:true,false',
        ]);

        $pesertaList = $request->input('peserta', []);

        $isPeserta = $request->input('is_peserta') === 'true';
        $isTtd = $request->input('is_ttd') === 'true';
        $nama_perusahaan = $request->input('perusahaan');
        $tanggal_awal = $request->input('tanggal_awal');
        $tanggal_akhir = $request->input('tanggal_akhir');

        $rkm = RKM::where('id', $request->id_rkm)->firstOrFail();
        $duedate = $rkm->tanggal_akhir->addMonths(6)->toDateString();

        $invoice = Invoice::create([
            'invoice_number' => $request->input('invoice_number'),
            'tanggal_invoice' => $request->input('tanggal_invoice'),
            'due_date' => $duedate,
            'purchase_order' => $request->input('purchase_order'),
            'id_rkm' => $request->input('id_rkm'),
            'amount' => $request->input('amount'),
            'unit_price' => $request->input('unit_price'),
            'pax' => $request->input('pax'),
            'bank_name' => $request->input('bank_name'),
            'account_number' => $request->input('account_number'),
            'terbilang' => $request->input('terbilang'),
        ]);

        $outstanding = Outstanding::where('id_rkm', $request->input('id_rkm'))->first();

        if ($outstanding) {
            trackingOutstanding::where('id_outstanding', $outstanding->id)
                ->update(['invoice' => 1]);

            $outstanding->no_invoice = $request->input('invoice_number');
            $outstanding->update();
        }

        return $this->downloadPdf($invoice->id, $pesertaList, $isPeserta, $isTtd, $nama_perusahaan, $tanggal_awal, $tanggal_akhir);

        // return redirect()->route('invoice.index')->with(['success' => 'Invoice berhasil dibuat!']);
    }

public function storeKwitansi(Request $request)
{
    // Validasi data dari form
    $request->validate([
        'invoice_id' => 'required|exists:invoices,id',
        'tanggal_ttd' => 'nullable|date',
        'nama_penandatangan' => 'nullable|string|max:255',
    ]);

    // Ambil invoice untuk mendapatkan id_rkm
    $invoice = Invoice::findOrFail($request->invoice_id);

    // Cek apakah kwitansi untuk invoice ini sudah ada
    $existingKwitansi = Kwitansi::where('invoice_id', $request->invoice_id)->first();

    if ($existingKwitansi) {
        // Kalau sudah ada, bisa pilih update atau balikin pesan error
        return redirect()
            ->route('invoice.index')
            ->with('error', 'Kwitansi untuk invoice ini sudah ada!');
    }

    // Siapkan data yang akan disimpan
    $kwitansiData = [
        'id_rkm'         => $invoice->id_rkm,
        'invoice_id'     => $request->invoice_id,
        'tanggal_cetak'  => $request->tanggal_ttd,
        'dicetak_oleh'   => $request->nama_penandatangan,
    ];

    // Simpan kwitansi baru
    Kwitansi::create($kwitansiData);

    return redirect()
        ->route('invoice.index')
        ->with('success', 'Kwitansi berhasil dibuat!');
}


   /**
 * Menampilkan detail satu invoice.
 *
 * @param  \App\Models\Invoice  $invoice
 * @return \Illuminate\View\View
 */
public function show(string $id): View
{
    // Mengambil data Invoice dan memuat relasi RKM, Perusahaan, dan Materi
    $invoice = Invoice::with(['rkm.perusahaan', 'rkm.materi', 'rkm.registrasi.peserta'])->findOrFail($id);
    
    // Menghitung total terbilang dan mengirimkannya ke view
    $terbilang = format_terbilang($invoice->amount);
    

    $karyawan = karyawan::find(22);
    
    return view('invoice.show', compact('invoice', 'terbilang', 'karyawan'));
}

public function showKwitansi($id)
{
    $kwitansi = Kwitansi::with('invoice.rkm.perusahaan', 'invoice.rkm.materi', 'karyawan')->findOrFail($id);
    
    // Perbaikan: Terbilang diambil dari data kwitansi, bukan invoice
    $terbilang = format_terbilang($kwitansi->invoice->amount);
    
    // Data karyawan untuk penandatangan
    $karyawan = karyawan::find(22); 

    // Menggunakan compact() yang lebih ringkas
    return view('kwitansi.show', compact('kwitansi', 'terbilang', 'karyawan'));
}


    /**
     * Menampilkan form untuk mengedit invoice.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\View\View
     */
    public function edit(Invoice $invoice): View
    {
        $invoice->load('rkm.perusahaan', 'rkm.materi');
        return view('invoice.edit', compact('invoice'));
    }

/**
 * Memperbarui invoice yang sudah ada.
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  \App\Models\Invoice  $invoice
 * @return \Illuminate\Http\RedirectResponse
 */
public function update(Request $request, Invoice $invoice): RedirectResponse
{
    $request->validate([
        'invoice_number' => 'required|string|max:255|unique:invoices,invoice_number,' . $invoice->id,
        'tanggal_invoice' => 'required|date',
        'due_date' => 'nullable|date|after_or_equal:tanggal_invoice', 
        'purchase_order' => 'nullable|string|max:255', 
        'id_rkm' => 'required|exists:r_k_m_s,id',
        'amount' => 'required|numeric',
        'bank_name' => 'nullable|string|max:255', // Tambahan untuk bank_name
        'account_number' => 'nullable|string|max:50', // Tambahan untuk account_number
    ]);

    $invoice->update([
        'invoice_number' => $request->input('invoice_number'),
        'tanggal_invoice' => $request->input('tanggal_invoice'),
        'due_date' => $request->input('due_date'), 
        'purchase_order' => $request->input('purchase_order'),
        'id_rkm' => $request->input('id_rkm'),
        'amount' => $request->input('amount'),
        'bank_name' => $request->input('bank_name'), // Perbarui bank_name
        'account_number' => $request->input('account_number'), // Perbarui account_number
    ]);

    return redirect()->route('invoice.show', $invoice->id)->with('success', 'Invoice berhasil diperbarui!');
}

    /**
     * Menghapus invoice dari database.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Invoice $invoice): RedirectResponse
    {
        $invoice->delete();
        return redirect()->route('invoice.index')->with('success', 'Invoice berhasil dihapus!');
    }
    // Tambahkan di dalam InvoiceRKMController
// private function terbilang($angka)
// {
//     $angka = abs($angka);
//     $baca = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
//     $hasil = "";

//     if ($angka < 12) {
//         $hasil = " " . $baca[$angka];
//     } else if ($angka < 20) {
//         $hasil = $this->terbilang($angka - 10) . " Belas";
//     } else if ($angka < 100) {
//         $hasil = $this->terbilang(intval($angka / 10)) . " Puluh" . $this->terbilang($angka % 10);
//     } else if ($angka < 200) {
//         $hasil = " Seratus" . $this->terbilang($angka - 100);
//     } else if ($angka < 1000) {
//         $hasil = $this->terbilang(intval($angka / 100)) . " Ratus" . $this->terbilang($angka % 100);
//     } else if ($angka < 2000) {
//         $hasil = " Seribu" . $this->terbilang($angka - 1000);
//     } else if ($angka < 1000000) {
//         $hasil = $this->terbilang(intval($angka / 1000)) . " Ribu" . $this->terbilang($angka % 1000);
//     } else if ($angka < 1000000000) {
//         $hasil = $this->terbilang(intval($angka / 1000000)) . " Juta" . $this->terbilang($angka % 1000000);
//     }

//     return trim($hasil);
// }


public function exportExcel($id)
{
    $invoice = Invoice::with('rkm.materi', 'rkm.perusahaan')->findOrFail($id);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Logo (kalau mau tambahin bisa pakai drawing)
    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
    $drawing->setPath(public_path('icon/logoo.png'));
    $drawing->setCoordinates('A1');
    $drawing->setHeight(40);
    $drawing->setWorksheet($sheet);

    // Judul
    $sheet->mergeCells('A1:E1');
    $sheet->setCellValue('A1', 'Detail Invoice #' . $invoice->invoice_number);
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

    // Info invoice
    $sheet->setCellValue('A3', 'Nomor Invoice:');
    $sheet->setCellValue('B3', $invoice->invoice_number);

    $sheet->setCellValue('A4', 'Tanggal Invoice:');
    $sheet->setCellValue('B4', \Carbon\Carbon::parse($invoice->tanggal_invoice)->format('d F Y'));

    $sheet->setCellValue('A5', 'Perusahaan:');
    $sheet->setCellValue('B5', $invoice->rkm->perusahaan->nama_perusahaan ?? '-');

    $sheet->setCellValue('A6', 'Materi:');
    $sheet->setCellValue('B6', $invoice->rkm->materi->nama_materi ?? '-');

    $sheet->setCellValue('A7', 'Tanggal:');
    $sheet->setCellValue('B7', \Carbon\Carbon::parse($invoice->rkm->tanggal_awal)->format('d F Y')
        . ' s/d ' . \Carbon\Carbon::parse($invoice->rkm->tanggal_akhir)->format('d F Y'));

    $sheet->setCellValue('A8', 'Peserta:');
    $sheet->setCellValue('B8', $invoice->rkm->pax . ' orang');

    // Header tabel
    $sheet->setCellValue('A10', 'No');
    $sheet->setCellValue('B10', 'Deskripsi');
    $sheet->setCellValue('C10', 'Pax');
    $sheet->setCellValue('D10', 'Harga Unit');
    $sheet->setCellValue('E10', 'Jumlah');

    $sheet->getStyle('A10:E10')->getFont()->setBold(true);
    $sheet->getStyle('A10:E10')->getAlignment()->setHorizontal('center');
    $sheet->getStyle('A10:E10')->getBorders()->getAllBorders()
        ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    // Data tabel
    $hargaUnit = $invoice->rkm->harga_jual ?? 0;
    $pax = $invoice->rkm->pax ?? 0;
    $jumlah = $hargaUnit * $pax;

    $sheet->setCellValue('A11', '1');
    $sheet->setCellValue('B11', "Materi: {$invoice->rkm->materi->nama_materi}\nTanggal: "
        . \Carbon\Carbon::parse($invoice->rkm->tanggal_awal)->format('d F Y')
        . " s/d "
        . \Carbon\Carbon::parse($invoice->rkm->tanggal_akhir)->format('d F Y')
        . "\nPeserta: {$pax} orang"
    );
    $sheet->getStyle('B11')->getAlignment()->setWrapText(true);

    $sheet->setCellValue('C11', $pax);
    $sheet->setCellValue('D11', $hargaUnit);
    $sheet->setCellValue('E11', $jumlah);

    $sheet->getStyle('A11:E11')->getBorders()->getAllBorders()
        ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    // Subtotal, PPN, Total
    $ppn = $jumlah * 0.11;
    $sheet->setCellValue('D13', 'SubTotal');
    $sheet->setCellValue('E13', $jumlah);

    $sheet->setCellValue('D14', 'PPN 11%');
    $sheet->setCellValue('E14', $ppn);

    $sheet->setCellValue('D15', 'TOTAL');
    $sheet->setCellValue('E15', $jumlah + $ppn);

    $sheet->getStyle('D13:E15')->getFont()->setBold(true);

    // Terbilang
    $sheet->mergeCells('A17:E17');
    $sheet->setCellValue('A17', 'Terbilang: ' . $this->terbilang($jumlah + $ppn));

    // Footer
    $sheet->mergeCells('A20:E20');
    $sheet->setCellValue('A20', 'Bandung, ' . \Carbon\Carbon::now()->format('d F Y'));
    $sheet->mergeCells('A22:E22');
    $sheet->setCellValue('A22', 'Hormat kami, PT. INIXINDO AMIETE MANDIRI');
    $sheet->mergeCells('A26:E26');
    $sheet->setCellValue('A26', 'Nama Penanggung Jawab - Accounting & Finance');

    // Lebar kolom auto
    foreach (range('A','E') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Export langsung
    $writer = new Xlsx($spreadsheet);
    $fileName = 'invoice_'.$invoice->id.'.xlsx';

    return response()->streamDownload(function() use ($writer) {
        $writer->save('php://output');
    }, $fileName);
}

    public function downloadPdf($id, $pesertaList = [], $isPeserta = false, $isTtd = false, $nama_perusahaan = null, $tanggal_awal = null, $tanggal_akhir = null)
    {
        $invoice = Invoice::with(['rkm.perusahaan', 'rkm.materi', 'rkm.registrasi.peserta'])
            ->findOrFail($id);

        $terbilang = $this->formatTerbilang($invoice->amount);
        $karyawan = Karyawan::findOrFail(22);

        $fileName = preg_replace('/[\/\\\\]/', '-', $invoice->invoice_number) . '.pdf';
        $filePath = 'invoice/' . $fileName;

        if (!empty($invoice->file_path) &&
            Storage::disk('local')->exists($invoice->file_path)) {

            return response()->download(
                storage_path('app/' . $invoice->file_path)
            );
        }

        $pdf = Pdf::loadView('invoice.pdf', compact('invoice', 'terbilang', 'karyawan', 'pesertaList', 'isPeserta', 'isTtd', 'nama_perusahaan', 'tanggal_awal', 'tanggal_akhir'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'enable_css_float' => true,
                'enable_html5' => true,
                'debugCss' => false,
                'debugLayout' => false,
                'chroot' => public_path(),
                'dpi' => 96,
            ]);

        Storage::disk('local')->put($filePath, $pdf->output());

        $invoice->file_path = $filePath;
        $invoice->save();

        return response()->download(
            storage_path('app/' . $filePath)
        );
    }


public function downloadPdfKwitansi($id)
{
    $kwitansi = Kwitansi::with('invoice.rkm.perusahaan', 'invoice.rkm.materi', 'karyawan', 'invoice.rkm')->findOrFail($id);
    $terbilang = format_terbilang($kwitansi->invoice->amount);
    $karyawan = karyawan::find(22); 

    $fileName = preg_replace('/[\/\\\\]/', '-', $kwitansi->invoice->invoice_number) . '.pdf';
    $filePath = 'kwitansi/' . $fileName;

    if (!empty($kwitansi->file_path) &&
        Storage::disk('local')->exists($kwitansi->file_path)) {

        return response()->download(
            storage_path('app/' . $kwitansi->file_path)
        );   
    }
    

    $pdf = Pdf::loadView('kwitansi.pdf', compact('kwitansi', 'terbilang', 'karyawan'))
        ->setPaper('a4', 'portrait')
        ->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'enable_css_float' => true,
            'enable_html5' => true,    
            'debugCss' => false,
            'debugLayout' => false,
            'chroot' => public_path(),
            'dpi' => 96,
        ]);

    Storage::disk('local')->put($filePath, $pdf->output());

    $kwitansi->file_path = $filePath;
    $kwitansi->save();

    return response()->download(
        storage_path('app/' . $filePath)
    );
}

private function formatTerbilang($amount)
{
    $bilangan = [
        '', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan',
        'sepuluh', 'sebelas'
    ];
    $satuan = ['', 'ribu', 'juta', 'miliar'];

    $amount = (int)$amount;
    if ($amount < 0) return 'Minus ' . $this->formatTerbilang(abs($amount));
    if ($amount == 0) return 'Nol rupiah';

    $words = '';
    $i = 0;
    while ($amount > 0) {
        $part = $amount % 1000;
        if ($part > 0) {
            $partWords = '';
            if ($part < 12) {
                $partWords = $bilangan[$part];
            } elseif ($part < 20) {
                $partWords = $bilangan[$part - 10] . ' belas';
            } elseif ($part < 100) {
                $puluhan = floor($part / 10);
                $sisaSatuan  = $part % 10;
                $partWords = $bilangan[$puluhan] . ' puluh ' . ($sisaSatuan  > 0 ? $bilangan[$sisaSatuan ] : '');
            } else {
                $ratusan = floor($part / 100);
                $sisa = $part % 100;
                $partWords = $bilangan[$ratusan] . ' ratus ' . ($sisa > 0 ? $this->formatTerbilang($sisa) : '');
            }
            $words = trim($partWords . ' ' . $satuan[$i] . ' ' . $words);
        }
        $amount = floor($amount / 1000);
        $i++;
    }
    return ucwords(trim($words)) . ' rupiah';
}
}