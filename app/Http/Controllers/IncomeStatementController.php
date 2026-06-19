<?php

namespace App\Http\Controllers;

use App\Models\ApprovalPendapatan;
use Illuminate\Http\Request;
use App\Models\Cost;
use App\Models\IncomeTransaction;

class IncomeStatementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index(Request $request)
    {
        $year = $request->input('year', date('Y'));

        $variableCosts = Cost::where('status', 'variable')->get()->groupBy('type');
        $fixedCosts = Cost::where('status', 'fixed')->get()->groupBy('type');

        $transactions = IncomeTransaction::where('year', $year)->get();

        $transactionData = [];
        foreach ($transactions as $trx) {
            $transactionData[$trx->item_code][$trx->month] = $trx->amount;
        }

        return view('income_statement.index', compact('variableCosts', 'fixedCosts', 'transactionData', 'year'));
    }

    public function store(Request $request)
    {
        // Menambahkan nilai default berupa array kosong jika request tidak memiliki data 'transactions'
        $data = $request->input('transactions', []);
        $year = date('Y'); 

        // Memvalidasi tipe data
        if (!is_array($data) || empty($data)) {
            return response()->json(['status' => 'error', 'message' => 'Tidak ada data transaksi yang valid untuk disimpan.']);
        }

        foreach ($data as $item) {
            // Memeriksa eksistensi item_code dan month sebelum melakukan operasi database
            if (isset($item['item_code']) && isset($item['month'])) {
                IncomeTransaction::updateOrCreate(
                    [
                        'item_code' => $item['item_code'],
                        'month' => $item['month'],
                        'year' => $year
                    ],
                    [
                        // Menggunakan operator ?? untuk memberikan nilai 0 jika 'amount' tidak ada (undefined)
                        'amount' => $item['amount'] ?? 0
                    ]
                );
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Data berhasil disimpan.']);
    }

    public function laporan(Request $request)
    {
        $year = $request->input('year', date('Y'));

        $variableCosts = Cost::where('status', 'variable')->get()->groupBy('type');
        $fixedCosts = Cost::where('status', 'fixed')->get()->groupBy('type');

        $transactions = IncomeTransaction::where('year', $year)->get();

        $transactionData = [];
        foreach ($transactions as $trx) {
            $transactionData[$trx->item_code][$trx->month] = $trx->amount;
        }

        // ==========================================
        // 1. PENDAPATAN
        // ==========================================
        $total_penjualan_training = $transactions->where('item_code', 'sales_training')->sum('amount');
        $total_exam = $transactions->where('item_code', 'exam')->sum('amount');
        $total_payment_advance = $transactions->where('item_code', 'payment_advance')->sum('amount');
        $total_discount = $transactions->where('item_code', 'discount')->sum('amount');
        
        $netSales_training = $total_penjualan_training - $total_exam - $total_payment_advance - $total_discount;

        // Persentase Pendapatan
        $pct_payment_advance = $total_penjualan_training > 0 ? ($total_payment_advance / $total_penjualan_training) * 100 : 0;
        $pct_exam = $total_penjualan_training > 0 ? ($total_exam / $total_penjualan_training) * 100 : 0;
        $pct_discount = $total_penjualan_training > 0 ? ($total_discount / $total_penjualan_training) * 100 : 0;
        $pct_netSales_training = $total_penjualan_training > 0 ? ($netSales_training / $total_penjualan_training) * 100 : 0;


        // ==========================================
        // 2. BEBAN BIAYA PENJUALAN (VARIABLE COST)
        // ==========================================
        $vc_training_keys = collect(range(1, 25))->map(fn($i) => 'vc_' . $i)->toArray();
        $biaya_biaya_training = $transactions->whereIn('item_code', $vc_training_keys)->sum('amount');
        $tunjangan_sales = $transactions->where('item_code', 'vc_26')->sum('amount');
        $tunjangan_instruktur = $transactions->where('item_code', 'vc_27')->sum('amount');
        $bonus_tahunan_sales = $transactions->where('item_code', 'vc_28')->sum('amount');
        $fee_proyek = $transactions->where('item_code', 'vc_29')->sum('amount');

        $total_beban_biaya_penjualan = $biaya_biaya_training + $tunjangan_sales + $tunjangan_instruktur + $bonus_tahunan_sales + $fee_proyek;

        // Persentase Biaya Penjualan (terhadap Net Sales Training / Total Pendapatan Bruto)
        $pct_biaya_training = $netSales_training > 0 ? ($biaya_biaya_training / $netSales_training) * 100 : 0;
        $pct_tunjangan_sales = $netSales_training > 0 ? ($tunjangan_sales / $netSales_training) * 100 : 0;
        $pct_tunjangan_instruktur = $netSales_training > 0 ? ($tunjangan_instruktur / $netSales_training) * 100 : 0;
        $pct_bonus_sales = $netSales_training > 0 ? ($bonus_tahunan_sales / $netSales_training) * 100 : 0;
        $pct_fee_proyek = $netSales_training > 0 ? ($fee_proyek / $netSales_training) * 100 : 0;
        $pct_total_beban_penjualan = $netSales_training > 0 ? ($total_beban_biaya_penjualan / $netSales_training) * 100 : 0;


        // ==========================================
        // 3. BEBAN BIAYA OPERASIONAL (FIXED COST)
        // ==========================================
        $fc_inventaris_keys = collect(range(1, 5))->map(fn($i) => 'fc_' . $i)->toArray();
        $biaya_inventaris = $transactions->whereIn('item_code', $fc_inventaris_keys)->sum('amount');

        $fc_gaji_keys = collect(range(6, 18))->map(fn($i) => 'fc_' . $i)->toArray();
        $biaya_tunjangan_karyawan = $transactions->whereIn('item_code', $fc_gaji_keys)->sum('amount');

        $fc_spj_keys = collect(range(19, 31))->map(fn($i) => 'fc_' . $i)->toArray();
        $biaya_spj = $transactions->whereIn('item_code', $fc_spj_keys)->sum('amount');

        $fc_operasional_keys = collect(range(32, 78))->map(fn($i) => 'fc_' . $i)->toArray();
        $biaya_operasional = $transactions->whereIn('item_code', $fc_operasional_keys)->sum('amount');

        $total_biaya_operasional = $biaya_inventaris + $biaya_tunjangan_karyawan + $biaya_spj + $biaya_operasional;

        // Persentase Biaya Operasional (terhadap Net Sales Training / Total Pendapatan Bruto)
        $pct_biaya_inventaris = $netSales_training > 0 ? ($biaya_inventaris / $netSales_training) * 100 : 0;
        $pct_tunjangan_karyawan = $netSales_training > 0 ? ($biaya_tunjangan_karyawan / $netSales_training) * 100 : 0;
        $pct_spj = $netSales_training > 0 ? ($biaya_spj / $netSales_training) * 100 : 0;
        $pct_operasional = $netSales_training > 0 ? ($biaya_operasional / $netSales_training) * 100 : 0;
        $pct_total_biaya_operasional = $netSales_training > 0 ? ($total_biaya_operasional / $netSales_training) * 100 : 0;


        // ==========================================
        // 4. KESIMPULAN
        // ==========================================
        $total_beban_biaya = $total_beban_biaya_penjualan + $total_biaya_operasional;
        $laba_bersih = $netSales_training - $total_beban_biaya;

        $pct_total_beban_biaya = $netSales_training > 0 ? ($total_beban_biaya / $netSales_training) * 100 : 0;


        return view('income_statement.laporan', compact(
            'variableCosts', 'fixedCosts', 'transactionData', 'year',
            'total_penjualan_training', 'total_exam', 'total_payment_advance', 'total_discount', 'netSales_training',
            'pct_payment_advance', 'pct_exam', 'pct_discount', 'pct_netSales_training',
            
            'biaya_biaya_training', 'tunjangan_sales', 'tunjangan_instruktur', 'bonus_tahunan_sales', 'fee_proyek', 'total_beban_biaya_penjualan',
            'pct_biaya_training', 'pct_tunjangan_sales', 'pct_tunjangan_instruktur', 'pct_bonus_sales', 'pct_fee_proyek', 'pct_total_beban_penjualan',
            
            'biaya_inventaris', 'biaya_tunjangan_karyawan', 'biaya_spj', 'biaya_operasional', 'total_biaya_operasional',
            'pct_biaya_inventaris', 'pct_tunjangan_karyawan', 'pct_spj', 'pct_operasional', 'pct_total_biaya_operasional',
            
            'total_beban_biaya', 'laba_bersih', 'pct_total_beban_biaya'
        ));
    }
}