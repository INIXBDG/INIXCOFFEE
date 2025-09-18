<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Imports\PerusahaanImport;
use App\Imports\ContactImport;
use Maatwebsite\Excel\Facades\Excel;

class ImportPerusahaanAndContactController extends Controller
{
    public function importPerusahaan(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new PerusahaanImport, $request->file('file'));

        return redirect()->back()->with('success', 'Data perusahaan berhasil diimport!');
    }

    public function importContacts(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);

        Excel::import(new ContactImport, $request->file('file'));

        return redirect()->back()->with('success', 'Data contact berhasil diimport.');
    }

}
