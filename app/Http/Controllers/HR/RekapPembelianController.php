<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RekapPembelianController extends Controller
{
    public function index() {
        return view('HR.Rekap_Penjualan.index');
    }
}
