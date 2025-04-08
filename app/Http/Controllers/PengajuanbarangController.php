<?php

namespace App\Http\Controllers;

use App\Models\detailPengajuanBarang;
use App\Models\jabatan;
use Illuminate\Http\Request;
use App\Models\PengajuanBarang;
use App\Models\karyawan;
use App\Models\tracking_pengajuan_barang;
use App\Models\User;
use App\Notifications\ApprovalbarangNotification;
use App\Notifications\PengajuanbarangNotification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class PengajuanBarangController extends Controller
{
    /**
     * Menampilkan daftar Pengajuan Barang.
     */
    public function index()
    {
        $jabatan = auth()->user()->jabatan;
        if($jabatan == 'Finance & Accounting' || $jabatan == 'GM'){
            $tracking = 'buka';
        }else{
            $karyawan = auth()->user()->karyawan->nama_lengkap;
            $tracking = tracking_pengajuan_barang::with(['pengajuanbarang.karyawan'])
                ->whereHas('pengajuanbarang.karyawan', function ($query) use ($karyawan) {
                    $query->where('nama_lengkap', $karyawan);
                })
                ->latest()
                ->first();
            // dd($tracking->tracking);
            if($tracking == null){
                $tracking = 'buka';
            }else
            if($tracking->tracking == 'Pencairan Sudah Selesai'){
                $tracking = 'tutup';
            }else{
                $tracking = 'buka';
            }
        }
        // return $tracking;
        return view('pengajuanbarang.index', compact('tracking'));
    }

    public function getPengajuanBarang($month, $year) 
    {
        $user = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrfail($user);
        $jabatan = $karyawan->jabatan;
        $divisi = $karyawan->divisi;
        // dd($year);
        if($jabatan == 'Finance & Accounting'){
            $PengajuanBarang = PengajuanBarang::with('karyawan', 'tracking')->whereMonth('created_at', $month)->whereYear('created_at', $year)->get();
        }elseif ($jabatan == 'Office Manager' || $jabatan == 'Education Manager' || $jabatan == 'SPV Sales' || $jabatan == 'Koordinator SO'){
            $PengajuanBarang = PengajuanBarang::with('karyawan', 'tracking')->whereHas('karyawan', function($query) use ($divisi) {
                $query->where('divisi', $divisi);
            })->latest()->get();
        }elseif($jabatan == 'GM'){
            $PengajuanBarang = PengajuanBarang::with('karyawan', 'tracking')->latest()->get();
        }
        else{
            $PengajuanBarang = PengajuanBarang::with('karyawan', 'tracking')->whereHas('karyawan', function($query) use ($user) {
                $query->where('id', $user);
            })->latest()->get();
        }
        return response()->json([
            'success' => true,
            'message' => 'List PengajuanBarang',
            'data' => $PengajuanBarang,
        ]);
    }

    /**
     * Menampilkan form untuk membuat Pengajuan Barang baru.
     */
    public function create()
    {
        $user = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrFail($user);
        return view('pengajuanbarang.create', compact('karyawan'));
    }

    /**
     * Menyimpan Pengajuan Barang baru ke dalam database.
     */
    public function store(Request $request)  
    {  
        // return $request->all();
        
        $request->validate([  
            'id_karyawan' => 'required|string|max:255',  
            'tipe' => 'required|string|max:255',  
            'barang.nama_barang.*' => 'nullable|string|max:255',  
            'barang.qty.*' => 'nullable|string',  
            'barang.harga_barang.*' => 'nullable|string',  
            'barang.keterangan.*' => 'nullable|string',  
        ]);  
    
       $PengajuanBarang = PengajuanBarang::create([  
            'tipe' => $request->tipe,  
            'id_karyawan' => $request->id_karyawan,   
        ]); 
    
        $barangData = [];  
        $namaBarang = $request->input('barang.nama_barang');  
        $qty = $request->input('barang.qty');  
        $hargaBarang = $request->input('barang.harga_barang');  
        $keterangan = $request->input('barang.keterangan');  
    
        for ($i = 0; $i < count($namaBarang); $i++) {  
            $barangData[] = [  
                'id_pengajuan_barang' => $PengajuanBarang->id,  
                'nama_barang' => $namaBarang[$i],  
                'qty' => $qty[$i],  
                'harga' => $hargaBarang[$i],  
                'keterangan' => $keterangan[$i],  
                'created_at' => now(),  
                'updated_at' => now(),  
            ];  
            
        }  
    
        detailPengajuanBarang::insert($barangData);  
        $karyawan = karyawan::findOrFail($request->id_karyawan);
        if($karyawan->divisi == 'Education'){
            $tracking = 'Diajukan dan Sedang Ditinjau oleh Education Manager';
        }elseif($karyawan->divisi == 'Office' && $request->tipe == 'Makanan'){
            $tracking = 'Diajukan dan Sedang Ditinjau oleh Finance';
        }elseif($karyawan->divisi == 'Office' && $request->tipe == 'Operasional'){
            $tracking = 'Diajukan dan Sedang Ditinjau oleh Finance';
        }elseif($karyawan->divisi == 'Office'){
            $tracking = 'Diajukan dan Sedang Ditinjau oleh General Manager';
        }elseif($karyawan->divisi == 'Sales & Marketing'){
            $tracking = 'Diajukan dan Sedang Ditinjau oleh SPV Sales';
        }elseif($karyawan->divisi == 'Service & Operation'){
            $tracking = 'Diajukan dan Sedang Ditinjau oleh Koordinator Service & Operation';
        }

        $tracking_pengajuan_barang = tracking_pengajuan_barang::create([
            'id_pengajuan_barang' => $PengajuanBarang->id,
            'tracking' => $tracking,
            'tanggal' => now(),
            'created_at' => now(),  
            'updated_at' => now(),
        ]);

        $PengajuanBarang->update([
            'id_tracking' => $tracking_pengajuan_barang->id,
        ]);

        // Retrieve users based on the filtered list of kode_karyawan
        $karyawan = karyawan::findOrFail($request->id_karyawan);
        $divisi = $karyawan->divisi;
        $jabatan = $karyawan->jabatan;
    
        $Offman = karyawan::where('jabatan', 'Office Manager')->first();
        $kooroff = karyawan::where('jabatan', 'Koordinator Office')->first();
        $koorSO = karyawan::where('jabatan', 'Koordinator SO')->first();
        $Eduman = karyawan::where('jabatan', 'Education Manager')->first();
        $SPVSales = karyawan::where('jabatan', 'SPV Sales')->first();
        $GM = karyawan::where('jabatan', 'GM')->first();
        $users = []; // Start with the current karyawan's kode_karyawan
        switch ($jabatan) {
            case 'SPV Sales':
            case 'Office Manager':
            case 'Education Manager':
            case 'Koordinator Office':
            case 'Koordinator SO':
                $users[] = $GM->kode_karyawan; // GM
        break;
        
            default:
                switch ($divisi) {
                    case 'Education':
                        $users[] = $Eduman->kode_karyawan; // Eduman
                        break;
        
                    case 'Sales & Marketing':
                        $users[] = $SPVSales->kode_karyawan; // SPVSales
                        break;
        
                    case 'Office':
                        $users[] = $GM->kode_karyawan; // GM
                        break;

                    case 'Service & Operation':
                        $users[] = $koorSO->kode_karyawan;
                        break;
                }
                break;
        }
         // Retrieve users based on the filtered list of kode_karyawan
         $users = User::whereHas('karyawan', function ($query) use ($users) {
            $query->whereIn('kode_karyawan', $users);
        })->get();
        $data = [
            'id_karyawan' => $request->id_karyawan,
            'tipe' => $request->tipe,
            'tanggal_pengajuan' => now()
        ];
        $type = 'Mengajukan Permintaan Barang';
        $path = '/pengajuanbarang';

        foreach ($users as $user) {
            NotificationFacade::send($user, new PengajuanbarangNotification($data, $path, $type));
        }

        return redirect()->route('pengajuanbarang.index')->with('success', 'Pengajuan Barang berhasil dibuat.');  
    }  


    /**
     * Menampilkan detail Pengajuan Barang tertentu.
     */
    public function show($id)
    {
        $data = PengajuanBarang::with('karyawan', 'tracking')->findOrFail($id);
        $detail = detailPengajuanBarang::where('id_pengajuan_barang', $id)->get();
        $tracking = tracking_pengajuan_barang::where('id_pengajuan_barang', $id)->get();
        return view('pengajuanbarang.show', compact('data', 'detail', 'tracking'));
    }

    /**
     * Menampilkan form untuk mengedit Pengajuan Barang.
     */
    public function edit($id)
    {
        $PengajuanBarang = PengajuanBarang::findOrFail($id);
        $user = $PengajuanBarang->id_karyawan;
        $karyawan = karyawan::findOrFail($user);
        return view('pengajuanbarang.edit', compact('PengajuanBarang', 'karyawan'));
    }

    /**
     * Memperbarui Pengajuan Barang di dalam database.
     */
    public function update(Request $request, $id)
    {
        $data = PengajuanBarang::with('karyawan')->findOrFail($id);
        $detail = detailPengajuanBarang::where('id_pengajuan_barang', $id)->get();
        $tracking = tracking_pengajuan_barang::where('id_pengajuan_barang', $id)->latest()->first();  
        $totalHarga = 0;
        $jabatan = auth()->user()->jabatan;

        if($request->approval == '1' && $jabatan == 'Finance & Accounting'){
            $status = $request->status;
            // dd($status);
            $e = tracking_pengajuan_barang::create([
                'id_pengajuan_barang' => $id,
                'tracking' => $status,
                'tanggal' => now()
            ]);
            $data->update([
                'id_tracking' => $e->id
            ]);
            $users = [
                $data->karyawan->kode_karyawan,
            ];
            if($status = "Pencairan Sudah Selesai") {
                $to = $data->karyawan->nama_lengkap;
                $path = '/pengajuanbarang';
                $type = 'Segera Upload Bukti Pembelian/Invoice';
                $data = [
                    'tanggal' => now(),
                    'status' => $status
                ];
                
            }else if($status = "Sedang Dikonfirmasi oleh Bagian Finance kepada General Manager"){
                $to = $data->karyawan->nama_lengkap;
                $path = '/pengajuanbarang';
                $type = 'Menyetujui Pengajuan Barang';
                $data = [
                    'tanggal' => now(),
                    'status' => $status
                ];
                $gm = karyawan::where('jabatan', 'GM')->first();
                $users[] = $gm->kode_karyawan;

            } else if($status = "Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi"){
                $to = $data->karyawan->nama_lengkap;
                $path = '/pengajuanbarang';
                $type = 'Menyetujui Pengajuan Barang';
                $data = [
                    'tanggal' => now(),
                    'status' => $status
                ];
                $direksi = karyawan::where('jabatan', 'Direktur')->first();
                $users[] = $direksi->kode_karyawan;
            } 
            else {
                $to = $data->karyawan->nama_lengkap;
                $path = '/pengajuanbarang';
                $type = 'Menyetujui Pengajuan Barang';
                $data = [
                    'tanggal' => now(),
                    'status' => $status
                ];
                
            }
            $user = User::whereHas('karyawan', function ($query) use ($users) {
                $query->whereIn('kode_karyawan', array_filter($users));
            })->get();
            foreach ($user as $users) {
                NotificationFacade::send($users, new ApprovalbarangNotification($data, $path, $to, $type));
            }
            return redirect()->route('pengajuanbarang.index')->with(['success' => 'Data berhasil diperbarui!']);
        }elseif($request->approval == '2'){
            $status = 'Pengajuan ditolak dikarenakan ' . $request->alasan;
            $e = tracking_pengajuan_barang::create([
                'id_pengajuan_barang' => $id,
                'tracking' => $status,
                'tanggal' => now()
            ]);
            $data->update([
                'id_tracking' => $e->id
            ]);
            $users = [
                $data->karyawan->kode_karyawan,
            ];
            $user = User::whereHas('karyawan', function ($query) use ($users) {
                $query->whereIn('kode_karyawan', array_filter($users));
            })->get();
            
            $to = $data->karyawan->nama_lengkap;
            $path = '/pengajuanbarang';
            $type = 'Menolak Pengajuan Barang';
            $data = [
                'tanggal' => now(),
                'status' => $status
            ];
            foreach ($user as $users) {
                NotificationFacade::send($users, new ApprovalbarangNotification($data, $path, $to, $type));
            }
            return redirect()->route('pengajuanbarang.index')->with(['success' => 'Data berhasil diperbarui!']);

        }

        foreach ($detail as $item){
            $qtyValue = (int)$item->qty; // Konversi ke integer  
            $harga = explode('.', $item->harga);
            $hargaValue = (float) $harga[0]; // Hapus titik dan konversi ke float  
            $totalHarga += $qtyValue * $hargaValue;  
        }
        // dd($tracking);
        // dd($request->all());
        if($data->karyawan->divisi == 'Education'){
            $users = karyawan::where('jabatan', 'Finance & Accounting')->first();
            $status = 'Telah disetujui oleh Education Manager dan sedang diproses oleh Finance';
        }elseif($data->karyawan->divisi == 'Office'){
            $users = karyawan::where('jabatan', 'Finance & Accounting')->first();
            $status = 'Telah disetujui oleh General Manager dan sedang diproses oleh Finance';
        }elseif($data->karyawan->divisi == 'Service & Operation'){
            $users = karyawan::where('jabatan', 'Finance & Accounting')->first();
            $status = 'Telah disetujui oleh Koordinator Service & Operation dan sedang diproses oleh Finance';
        }elseif($data->karyawan->divisi == 'Sales & Marketing'){
            $status = 'Telah disetujui oleh SPV Sales dan sedang diproses oleh Finance';
            $users = karyawan::where('jabatan', 'Finance & Accounting')->first();
            if($totalHarga >= 1000000 && $tracking->tracking == 'Telah disetujui oleh SPV Sales dan sedang ditinjau oleh General Manager'){
                $status = 'Telah disetujui oleh General Manager dan sedang diproses oleh Finance';
                $users = karyawan::where('jabatan', 'Finance & Accounting')->first();
            }elseif($totalHarga >= 1000000){
                $status = 'Telah disetujui oleh SPV Sales dan sedang ditinjau oleh General Manager';
                $users = karyawan::where('jabatan', 'GM')->first();
            }
        }
        // Update the record based on the user's role
        if (in_array($jabatan, ['Office Manager', 'Koordinator Office', 'Education Manager', 'SPV Sales', 'GM', 'Koordinator SO'])){
            $e = tracking_pengajuan_barang::create([
                'id_pengajuan_barang' => $id,
                'tracking' => $status,
                'tanggal' => now()
            ]);
            $data->update([
                'id_tracking' => $e->id
            ]);
        } else {
            return redirect()->route('pengajuanbarang.index')->with(['error' => 'Tidak Bisa mengubah Approval!']);
        }
        $users = [
            $data->karyawan->kode_karyawan,
            $users->kode_karyawan
        ];
        $user = User::whereHas('karyawan', function ($query) use ($users) {
            $query->whereIn('kode_karyawan', array_filter($users));
        })->get();
        
        $to = $data->karyawan->nama_lengkap;
        $path = '/pengajuanbarang';
        $type = 'Menyetujui Pengajuan Barang';
        $data = [
            'tanggal' => now(),
            'status' => $status
        ];
        foreach ($user as $users) {
            NotificationFacade::send($users, new ApprovalbarangNotification($data, $path, $to, $type));
        }

        return redirect()->route('pengajuanbarang.index')->with('success', 'Pengajuan Barang berhasil diperbarui.');
    }

    /**
     * Menghapus Pengajuan Barang dari database.
     */
    public function destroy($id)
    {
        // Temukan data pengajuan barang
        $data = PengajuanBarang::with('karyawan')->findOrFail($id);
        
        // Ambil detail dan tracking yang terkait
        $detail = detailPengajuanBarang::where('id_pengajuan_barang', $id)->get();
        $tracking = tracking_pengajuan_barang::where('id_pengajuan_barang', $id)->get();  

        // Hapus detail dan tracking satu per satu
        foreach ($detail as $item) {
            $item->delete();
        }

        foreach ($tracking as $item) {
            $item->delete();
        }

        // Hapus data pengajuan barang
        $data->delete();

        return redirect()->route('pengajuanbarang.index')->with('success', 'Pengajuan Barang berhasil dihapus!');
    }


    public function uploadInvoice($id)
    {
        $PengajuanBarang = PengajuanBarang::findOrFail($id);
        $user = $PengajuanBarang->id_karyawan;
        $karyawan = karyawan::findOrFail($user);
        return view('pengajuanbarang.uploadinvoice', compact('PengajuanBarang', 'karyawan'));
    }

    public function updateInvoice(Request $request, $id)
    {
        $post = PengajuanBarang::findOrFail($id);
        // dd($request->all());
        if ($request->hasFile('invoice')) {

            Storage::delete('public/storage/pengajuanbarang/'.$post->invoice);

            $file = $request->file('invoice');
            $filename = time() . '.'. $file->getClientOriginalExtension();
            $directory = 'pengajuanbarang';
            $path = $file->storeAs($directory, $filename, 'public');
            $status = 'Selesai';
            
            $e = tracking_pengajuan_barang::create([
                'id_pengajuan_barang' => $id,
                'tracking' => $status,
                'tanggal' => now()
            ]);
            $post->update([
                'id_tracking' => $e->id,
                'invoice' => $path,
            ]);

        } else {
            return redirect()->route('pengajuanbarang.index')->with('error', 'Invoice gagal diupload.');
        }
        return redirect()->route('pengajuanbarang.index')->with('success', 'Invoice berhasil disimpan.');
    }

    public function updateBarang(Request $request, $id)
    {
        // return $request->all();
        // Mengambil data pengajuan barang
        $data = PengajuanBarang::with('karyawan')->findOrFail($id);
        $tracking = tracking_pengajuan_barang::where('id_pengajuan_barang', $id)->latest()->first();  
        $totalHarga = 0;
        $jabatan = auth()->user()->jabatan;

        if ($request->has('deletedatabarang')) {
            foreach ($request->deletedatabarang as $deletedId) {
                // Hapus detail barang dari database
                detailPengajuanBarang::where('id', $deletedId)->delete();
            }
        }
        if($request->has('id_pengajuan_barang')){
            foreach ($request->id_detail_pengajuan as $index => $detailId) {
                // Jika id_detail_pengajuan adalah null, masukkan data baru
                if (is_null($detailId)) {
                    detailPengajuanBarang::create([
                        'id_pengajuan_barang' => $request->id_pengajuan_barang[$index],
                        'nama_barang' => $request->nama_barang[$index],
                        'qty' => $request->qty[$index],
                        'harga' => $request->harga[$index],
                        'keterangan' => $request->keterangan[$index],
                    ]);
                } else {
                    // Jika id_detail_pengajuan tidak null, update data yang ada
                    $detail = detailPengajuanBarang::findOrFail($detailId);
                    $detail->update([
                        'id_pengajuan_barang' => $request->id_pengajuan_barang[$index],
                        'nama_barang' => $request->nama_barang[$index],
                        'qty' => $request->qty[$index],
                        'harga' => $request->harga[$index],
                        'keterangan' => $request->keterangan[$index],
                    ]);
                }
    
                // Hitung total harga
                $totalHarga += $request->qty[$index] * $request->harga[$index];
            }
        }
        $status = "Terjadi perubahan data Barang";
        $e = tracking_pengajuan_barang::create([
            'id_pengajuan_barang' => $id,
            'tracking' => $status,
            'tanggal' => now()
        ]);

        // Redirect setelah pembaruan
        return redirect()->route('pengajuanbarang.show', $id)->with('success', 'Data Berhasil diperbarui.');
    }


    public function exportPDF($id)
    {
        $data = PengajuanBarang::with(['detail', 'tracking', 'karyawan'])->findOrFail($id);
        // return $data->karyawan->divisi;
        if($data->karyawan->divisi == 'Education'){
            $finance = karyawan::where('jabatan','Education Manager')->latest()->first();
        }else if($data->karyawan->divisi == 'Sales & Marketing'){
            $finance = karyawan::where('jabatan','SPV Sales')->latest()->first();
        }else if($data->karyawan->divisi == 'Office'){
            $finance = karyawan::where('jabatan','GM')->latest()->first();
        }else if($data->karyawan->divisi == 'Service & Operation'){
            $finance = karyawan::where('jabatan','Koordinator SO')->latest()->first();
        }
        $gm = karyawan::where('jabatan','GM')->latest()->first();
        // return $finance;

        // Buat file PDF dari tampilan yang berisi data registrasi
        // $pdf = PDF::loadView('exports.pengajuan_barang-pdf', compact('pengajuan_barang'));
        return view ('exports.pengajuan_barang-pdf', compact('data', 'finance', 'gm'));

        // return $pdf->download('Data_pengajuan_barang.pdf');
    }

}
