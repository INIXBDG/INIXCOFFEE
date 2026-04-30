<?php

namespace App\Http\Controllers;

use App\Models\no_akun;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class NoAkunController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Menyediakan Data untuk DataTables
     */
    public function getData()
    {
        $data = no_akun::latest()->get();
        return response()->json(['data' => $data]);
    }

    /**
     * Simpan Data Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'no' => 'required|string|unique:no_akuns,no',
            'nama_akun' => 'required|string|max:255',
        ]);

        no_akun::create([
            'no' => $request->no,
            'nama_akun' => $request->nama_akun,
        ]);

        return response()->json(['success' => true, 'message' => 'Data No Akun berhasil ditambahkan.']);
    }

    /**
     * Ambil Data Spesifik untuk Edit
     */
    public function edit($id)
    {
        $data = no_akun::findOrFail($id);
        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Perbarui Data
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'no' => 'required|string|unique:no_akuns,no,' . $id,
            'nama_akun' => 'required|string|max:255',
        ]);

        $akun = no_akun::findOrFail($id);
        $akun->update([
            'no' => $request->no,
            'nama_akun' => $request->nama_akun,
        ]);

        return response()->json(['success' => true, 'message' => 'Data No Akun berhasil diperbarui.']);
    }

    /**
     * Hapus Data
     */
    public function destroy($id)
    {
        $akun = no_akun::findOrFail($id);
        $akun->delete();
        
        return response()->json(['success' => true, 'message' => 'Data No Akun berhasil dihapus.']);
    }

    /**
     * Import Excel
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file_no_akun' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $file = $request->file('file_no_akun');
            $dataArray = Excel::toArray([], $file);
            $sheet = $dataArray[0];

            foreach ($sheet as $index => $row) {
                if ($index === 0) continue; 
                if (empty($row[0]) || empty($row[1])) continue;

                // Terapkan trim() dan (string)
                $no_akun_bersih = trim((string) $row[0]);
                $nama_akun_bersih = trim((string) $row[1]);

                no_akun::updateOrCreate(
                    ['no' => $no_akun_bersih],
                    ['nama_akun' => $nama_akun_bersih]
                );
            }

            return response()->json(['success' => true, 'message' => 'Import No Akun berhasil.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal import: ' . $e->getMessage()], 500);
        }
    }
}