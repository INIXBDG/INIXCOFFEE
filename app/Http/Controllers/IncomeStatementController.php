<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cost;
use App\Models\IncomeTransaction;

class IncomeStatementController extends Controller
{
    public function index(Request $request)
    {
        // Mendapatkan tahun berjalan atau tahun dari parameter request
        $year = $request->input('year', date('Y'));

        $variableCosts = Cost::where('status', 'variable')->get()->groupBy('type');
        $fixedCosts = Cost::where('status', 'fixed')->get()->groupBy('type');

        // Mengambil transaksi untuk tahun yang dipilih
        $transactions = IncomeTransaction::where('year', $year)->get();

        // Menyusun ulang struktur data transaksi agar mudah diakses di Blade
        // Format: $transactionData['item_code']['month'] = amount
        $transactionData = [];
        foreach ($transactions as $trx) {
            $transactionData[$trx->item_code][$trx->month] = $trx->amount;
        }

        return view('income_statement.index', compact('variableCosts', 'fixedCosts', 'transactionData', 'year'));
    }

    public function store(Request $request)
    {
        $data = $request->input('transactions');
        $year = date('Y'); // Menggunakan tahun berjalan

        foreach ($data as $item) {
            IncomeTransaction::updateOrCreate(
                [
                    'item_code' => $item['item_code'],
                    'month' => $item['month'],
                    'year' => $year
                ],
                [
                    'amount' => $item['amount']
                ]
            );
        }

        return response()->json(['status' => 'success', 'message' => 'Data berhasil disimpan.']);
    }

    public function laporan(Request $request)
    {
        // Mendapatkan tahun berjalan atau tahun dari parameter request
        $year = $request->input('year', date('Y'));

        $variableCosts = Cost::where('status', 'variable')->get()->groupBy('type');
        $fixedCosts = Cost::where('status', 'fixed')->get()->groupBy('type');

        // Mengambil transaksi untuk tahun yang dipilih
        $transactions = IncomeTransaction::where('year', $year)->get();

        // Menyusun ulang struktur data transaksi agar mudah diakses di Blade
        // Format: $transactionData['item_code']['month'] = amount
        $transactionData = [];
        foreach ($transactions as $trx) {
            $transactionData[$trx->item_code][$trx->month] = $trx->amount;
        }

        return view('income_statement.laporan', compact('variableCosts', 'fixedCosts', 'transactionData', 'year'));
    }
}