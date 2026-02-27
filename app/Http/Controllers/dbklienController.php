<?php

namespace App\Http\Controllers;

use App\Exports\TemplateKlienExport;
use Illuminate\Http\Request;
use App\Imports\KlienImport;
use Maatwebsite\Excel\Facades\Excel;
class dbklienController extends Controller
{
    public function index()
    {
        return view('dbklien.index');
    }



    public function import(Request $request) 
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new KlienImport, $request->file('file'));
        
        return back()->with('success', 'Data klien berhasil diimpor!');
    }

    public function downloadTemplate()
    {
        return Excel::download(new TemplateKlienExport, 'template_import_klien.xlsx');
    }

}
