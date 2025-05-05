<?php

namespace App\Http\Controllers;

use App\Models\hitunglembur;
use Illuminate\Http\Request;
use App\Models\lembur;
use App\Models\karyawan;
use App\Models\User;
use App\Notifications\ApprovalHitunganLemburNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use App\Exports\LemburExport;
use Maatwebsite\Excel\Facades\Excel;

class OvertimeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        return view('overtime.index');
    }

    public function getOvertimeLembur($month, $year) 
    {
        // Get total lembur for each karyawan
        $lembur = Lembur::with('karyawan')
                        ->select('id_karyawan', DB::raw('COUNT(id) as total_lembur'))
                        ->whereMonth('tanggal_spl', $month)
                        ->whereYear('tanggal_spl', $year)
                        ->groupBy('id_karyawan')
                        ->get();

        // Fetch id_hitung_lembur for each karyawan
        foreach ($lembur as $item) {
            $item->karyawan = Karyawan::find($item->id_karyawan);
            
            // Get the latest id_hitung_lembur for the karyawan
            $latestLembur = Lembur::where('id_karyawan', $item->id_karyawan)
                                ->whereMonth('tanggal_spl', $month)
                                ->whereYear('tanggal_spl', $year)
                                ->orderBy('id', 'desc') // Assuming id is auto-incrementing
                                ->first();

            // Add id_hitung_lembur to the item if it exists
            $item->id_hitung_lembur = $latestLembur ? $latestLembur->id_hitung_lembur : null;
        }

        return response()->json([
            'success' => true,
            'message' => 'Lembur Karyawan Pada ' . $month .' ' . $year,
            'data' => $lembur,
        ]);
    }

    public function getOvertimeLemburByKaryawan($id, $month, $year)  
    {
        $lembur = Lembur::with('karyawan', 'hitunglembur')
        ->where('id_karyawan', $id)
        ->whereMonth('tanggal_spl', $month)
        ->whereYear('tanggal_spl', $year)
        ->get();
        return response()->json([
            'success' => true,
            'message' => 'List Lembur Karyawan',
            'data' => $lembur,
        ]);
    }

     /**
     * create
     *
     * @return View
     */
    public function create()
    {
        $user = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrFail($user);
        $karyawanall = karyawan::where('divisi', '!=', 'Direksi')->where('divisi', $karyawan->divisi)->get();
        return view('overtime.create', compact('karyawanall', 'karyawan'));
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        // return $request->all();

        // Validate form
        $this->validate($request, [
            'id_lembur' => 'required|array',
            'id_lembur.*' => 'required|numeric', // Validate each item in the array
            'nilai_lembur' => 'required|array',
            'nilai_lembur.*' => 'required|numeric', // Validate each item in the array
        ]);

        // Ensure both arrays have the same length
        if (count($request->id_lembur) !== count($request->nilai_lembur)) {
            return redirect()->back()->withErrors(['id_lembur' => 'Jumlah id_lembur dan nilai_lembur harus sama.']);
        }

        try {
            // Use a transaction to ensure data integrity
            DB::beginTransaction();

            // Iterate over the arrays and insert data into the Hitunglembur model
            foreach ($request->id_lembur as $index => $idLembur) {
                $hitung = Hitunglembur::where('id_lembur', $idLembur)->first();

                if ($hitung) {
                    // Update the existing record
                    $hitung->update([
                        'nilai_lembur' => $request->nilai_lembur[$index],
                    ]);
                } else {
                    // Create a new Hitunglembur record
                    $hitung = Hitunglembur::create([
                        'id_lembur' => $idLembur,
                        'nilai_lembur' => $request->nilai_lembur[$index],
                    ]);
                }

                // Find the corresponding lembur record and update it
                $data = Lembur::findOrFail($idLembur); // Use the correct model name
                $data->update([
                    'id_hitung_lembur' => $hitung->id,
                ]);
            }

            // Commit the transaction
            DB::commit();

            // Redirect with success message
            return redirect()->route('overtime.index')->with(['success' => 'Data Berhasil Disimpan!']);
            // return response()->json([
            //     'success' => true,
            //     'message' => 'Data Berhasil Disimpan!',
            // ]);
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollback();

            // Log the error for debugging
            Log::error('Error saving overtime data: ' . $e->getMessage());

            // Redirect with error message
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()]);
            // return response()->json([
            //     'success' => false,
            //     'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage(),
            // ]);
        }
    }

    /**
     * show
     *
     * @param  mixed $id
     * @return View
     */
    public function show(string $id)
    {
        $data = lembur::findOrFail($id);
        $gm = karyawan::where('jabatan', 'GM')->first();
        $hrd = karyawan::where('jabatan', 'HRD')->first();
        if($data->karyawan->divisi == 'Education'){
            $atasan = karyawan::where('jabatan', 'Education Manager')->first();
        }elseif($data->karyawan->divisi == 'Sales'){
            $atasan = karyawan::where('jabatan', 'SPV Sales')->first();
        }else{
            $atasan = karyawan::where('jabatan', 'GM')->first();
        }

        return view('overtime.pdf', compact('data', 'atasan', 'hrd', 'gm'));
    }

    /**
     * edit
     *
     * @param  mixed $id
     * @return View
     */
    public function approvalHitungLemburKaryawan(Request $request)
    {
        // return $request->all();

        try {
            // Use a transaction to ensure data integrity
            DB::beginTransaction();

            // Iterate over the arrays and insert data into the Hitunglembur model
            foreach ($request->id_lembur as $index => $idLembur) {
                $data = hitunglembur::with('lembur')->where('id_lembur', $idLembur)->first(); // Use the correct model name
                // dd($data);
                if($request->approval[$index] === '1'){
                    $data->update([
                        'approval_gm' => $request->approval[$index],
                    ]);
                }else{
                    $data->update([
                        'approval_gm' => $request->approval[$index],
                        'alasan' => $request->alasan[$index],
                    ]);

                    $karyawan = karyawan::findOrFail($data->lembur->id_karyawan);
                    $hrd = karyawan::where('jabatan', 'HRD')->latest()->first();
                    $users = $hrd->kode_karyawan;
                    $users = User::whereHas('karyawan', function ($query) use ($users) {
                        $query->where('kode_karyawan', $users);
                    })->get();
                    
                    $data = [
                        'alasan' => $request->alasan[$index],
                        'nama_karyawan' => $karyawan->nama_lengkap
                    ];
                    $type = 'Menolak Hitungan Lembur';
                    $path = '/overtime';

                    foreach ($users as $user) {
                        NotificationFacade::send($user, new ApprovalHitunganLemburNotification($data, $path, $type));
                    }
                }
                
            }

            // Commit the transaction
            DB::commit();

            // Redirect with success message
            return redirect()->route('overtime.index')->with(['success' => 'Data Berhasil Disimpan!']);
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollback();

            // Log the error for debugging
            Log::error('Error saving overtime data: ' . $e->getMessage());

            // Redirect with error message
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()]);
        }

        // return redirect()->route('overtime.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'tanggal_spl'     => 'required',
            'uraian_tugas'     => 'required',
            'waktu_lembur'     => 'required',
            'tanggal_lembur'     => 'required',
        ]);
        $post = lembur::findOrFail($id);
            $post->update([
                'tanggal_spl'     => $request->tanggal_spl,
                'uraian_tugas'     => $request->uraian_tugas,
                'waktu_lembur'     => $request->waktu_lembur,
                'tanggal_lembur'     => $request->tanggal_lembur,
            ]);

        return redirect()->route('overtime.index')->with(['success' => 'Data Berhasil Diubah!']);
    }

    /**
     * destroy
     *
     * @param  mixed $post
     * @return void
     */
    public function destroy($id)
    {
        $post = lembur::findOrFail($id);

        $post->delete();

        return redirect()->route('overtime.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }

    public function editKaryawan(string $id)
    {
        $data = lembur::findOrFail($id);
        $user = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrFail($user);
        $karyawanall = karyawan::where('divisi', '!=', 'Direksi')->where('divisi', $karyawan->divisi)->get();

        return view('overtime.editKaryawan', compact('data', 'karyawan', 'karyawanall'));
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $id
     * @return RedirectResponse
     */
    public function updateKaryawan(Request $request, $id)
    {
        // return $request->all();
        $this->validate($request, [
            'tanggal_spl'     => 'required',
            'jam_mulai'     => 'required',
            'jam_selesai'     => 'required',
            'keterangan'     => 'required',
        ]);
        $post = lembur::findOrFail($id);
            $post->update([
                'tanggal_spl'     => $request->tanggal_spl,
                'jam_mulai'     => $request->jam_mulai,
                'jam_selesai'     => $request->jam_selesai,
                'keterangan'     => $request->keterangan,
            ]);

        return redirect()->route('overtime.index')->with(['success' => 'Data Berhasil Diubah!']);
    }

    public function approvalLemburKaryawan(Request $request, $id)
    {
        // return $request->all();
        $this->validate($request, [
            'approval'     => 'required',
        ]);
        $post = lembur::findOrFail($id);
        if($request->approval == '1'){
            $approval = 'Disetujui';
        }else{
            $approval = 'Ditolak';
        }
            $post->update([
                'approval_karyawan'     => $approval,
            ]);

        return redirect()->route('overtime.index')->with(['success' => 'Data Berhasil Diubah!']);
    }

    public function exportExcel($year, $month)
    {
        // Validate month and year
        if ($month < 1 || $month > 12 || $year < 2000) {
            return redirect()->back()->withErrors(['error' => 'Invalid month or year.']);
        }
        $filename = 'Data Overtime Bulan ' . $month . ' Tahun ' . $year . '.xlsx';

        return Excel::download(new LemburExport($month, $year), $filename);
    }

    public function exportPDF($id, $year, $month)
    {
        $data = lembur::with(['hitunglembur', 'karyawan'])
        ->where('id_karyawan', $id)
        ->whereYear('tanggal_lembur', $year)
        ->whereMonth('tanggal_lembur', $month)
        ->get();
        // return $data;
        $hrd = karyawan::where('jabatan','HRD')->latest()->first();
        $finance = karyawan::where('jabatan','Finance & Accounting')->first();
        $gm = karyawan::where('jabatan','GM')->latest()->first();
        // return $data;
        // Buat file PDF dari tampilan yang berisi data registrasi
        // $pdf = PDF::loadView('exports.pengajuan_barang-pdf', compact('pengajuan_barang'));
        return view ('exports.lembur-pdf', compact('data', 'hrd', 'finance', 'gm'));

        // return $pdf->download('Data_pengajuan_barang.pdf');
    }
    
}
