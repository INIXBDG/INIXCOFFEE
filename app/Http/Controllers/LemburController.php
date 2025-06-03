<?php

namespace App\Http\Controllers;

use App\Models\karyawan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\lembur;
use App\Models\User;
use Carbon\Carbon;
use App\Notifications\LemburNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class LemburController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        return view('lembur.index');
    }

    public function getSuratPerintahLembur()
    {
        $user = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrFail($user);
        $jabatan = $karyawan->jabatan;
        $divisi = $karyawan->divisi;

        // Inisialisasi $lembur sebagai koleksi kosong
        $lembur = collect();

        if ($jabatan == 'Office Manager' || $jabatan == 'Koordinator Office' || $jabatan == 'Education Manager' || $jabatan == 'SPV Sales' || $jabatan == 'Koordinator ITSM') {
            $lembur = lembur::with('karyawan')->whereHas('karyawan', function($query) use ($divisi) {
                $query->where('divisi', $divisi);
            })->latest()->get();
        } elseif ($jabatan == 'HRD' || $jabatan == 'GM') {
            $lembur = lembur::with('karyawan')->latest()->get();
        }

        return response()->json([
            'success' => true,
            'message' => 'List Surat Perintah Lembur',
            'data' => $lembur,
        ]);
    }


    public function getLemburKaryawan()
    {
        $user = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrFail($user);
        $jabatan = $karyawan->jabatan;
        $divisi = $karyawan->divisi;

        $lembur = collect();

        if ($jabatan == 'Office Manager' || $jabatan == 'Koordinator Office' || $jabatan == 'Education Manager' || $jabatan == 'SPV Sales' || $jabatan == 'Koordinator ITSM') {
            $lembur = lembur::with('karyawan')->whereHas('karyawan', function($query) use ($divisi) {
                $query->where('divisi', $divisi);
            })->latest()->get();
        } else{
            $lembur = lembur::with('karyawan')->whereHas('karyawan', function($query) use ($user) {
                $query->where('id', $user);
            })->latest()->get();
        }
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
        return view('lembur.create', compact('karyawanall', 'karyawan'));
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        //validate form
        // return $request->all();
        $this->validate($request, [
            'id_karyawan'     => 'required',
            'tanggal_spl'     => 'required',
            'uraian_tugas'     => 'required',
            'waktu_lembur'     => 'required',
            'tanggal_lembur'     => 'required',
        ]);

        lembur::create([
            'id_karyawan'     => $request->id_karyawan,
            'tanggal_spl'     => $request->tanggal_spl,
            'uraian_tugas'     => $request->uraian_tugas,
            'waktu_lembur'     => $request->waktu_lembur,
            'tanggal_lembur'     => $request->tanggal_lembur,
        ]);

        $karyawan = karyawan::findOrFail($request->id_karyawan);
        $users[] = $karyawan->kode_karyawan;
        // Retrieve users based on the filtered list of kode_karyawan
        $users = User::whereHas('karyawan', function ($query) use ($users) {
            $query->whereIn('kode_karyawan', $users);
        })->get();
        // return $users;

        $data = [
            'id_karyawan' => $request->id_karyawan,
            'tanggal_lembur' => $request->tanggal_lembur,
            'waktu_lembur' => $request->waktu_lembur,
            'uraian_tugas' => $request->uraian_tugas,
        ];
        $type = 'Memerintahkan anda untuk Lembur';
        $path = '/lembur';

        foreach ($users as $user) {
            NotificationFacade::send($user, new LemburNotification($data, $path, $type));
        }


        return redirect()->route('lembur.index')->with(['success' => 'Data Berhasil Disimpan!']);
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
        $gm = karyawan::where('jabatan', 'GM')->latest()->first();
        $hrd = karyawan::where('jabatan', 'HRD')->latest()->first();
        if($data->karyawan->divisi == 'Education'){
            $atasan = karyawan::where('jabatan', 'Education Manager')->latest()->first();
        }elseif($data->karyawan->divisi == 'Sales'){
            $atasan = karyawan::where('jabatan', 'SPV Sales')->latest()->first();
        }else{
            $atasan = karyawan::where('jabatan', 'GM')->latest()->first();
        }
        // return $hrd;
        return view('lembur.pdf', compact('data', 'atasan', 'hrd', 'gm'));
    }

    /**
     * edit
     *
     * @param  mixed $id
     * @return View
     */
    public function edit(string $id)
    {
        $data = lembur::findOrFail($id);
        $user = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrFail($user);
        $karyawanall = karyawan::where('divisi', '!=', 'Direksi')->where('divisi', $karyawan->divisi)->get();

        return view('lembur.edit', compact('data', 'karyawan', 'karyawanall'));
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

        return redirect()->route('lembur.index')->with(['success' => 'Data Berhasil Diubah!']);
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

        return redirect()->route('lembur.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }

    public function editKaryawan(string $id)
    {
        $data = lembur::findOrFail($id);
        $user = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrFail($user);
        $karyawanall = karyawan::where('divisi', '!=', 'Direksi')->where('divisi', $karyawan->divisi)->get();

        return view('lembur.editKaryawan', compact('data', 'karyawan', 'karyawanall'));
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
        // Validasi dasar
        $rules = [
            'tanggal_spl' => 'required',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'keterangan' => 'required',
        ];

        $jabatan = strtolower(auth()->user()->jabatan);
        if (in_array($jabatan, ['office boy', 'driver'])) {
            $rules['foto_mulai'] = 'nullable|image';
            $rules['foto_selesai'] = 'nullable|image';
        }

        $this->validate($request, $rules);

        $post = Lembur::with('karyawan')->findOrFail($id);

        // Update data dasar
        $post->tanggal_spl = $request->tanggal_spl;
        $post->jam_mulai = $request->jam_mulai;
        $post->jam_selesai = $request->jam_selesai;
        $post->keterangan = $request->keterangan;

        if (in_array($jabatan, ['office boy', 'driver'])) {
            // Proses foto_mulai (simpan sebagai foto_masuk)
            if ($request->hasFile('foto_mulai')) {
                $image = $request->file('foto_mulai');
                $imageName = 'mulai_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('lembur', $imageName, 'public');

                // Hapus file lama jika ada
                if ($post->foto_masuk && Storage::disk('public')->exists($post->foto_masuk)) {
                    Storage::disk('public')->delete($post->foto_masuk);
                }

                $post->foto_masuk = $path;
            }

            // Proses foto_selesai (simpan sebagai foto_selesai)
            if ($request->hasFile('foto_selesai')) {
                $image = $request->file('foto_selesai');
                $imageName = 'selesai_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('lembur', $imageName, 'public');

                if ($post->foto_selesai && Storage::disk('public')->exists($post->foto_selesai)) {
                    Storage::disk('public')->delete($post->foto_selesai);
                }

                $post->foto_selesai = $path;
            }
        }

        $post->save();

        // Proses notifikasi (tidak diubah)
        $karyawan = Karyawan::findOrFail($post->id_karyawan);
        $divisi = $karyawan->divisi;
        $jabatan = $karyawan->jabatan;
        $Offman = Karyawan::where('jabatan', 'Office Manager')->first();
        $kooroff = Karyawan::where('jabatan', 'Koordinator Office')->first();
        $koorso = Karyawan::where('jabatan', 'Koordinator ITSM')->first();
        $Eduman = Karyawan::where('jabatan', 'Education Manager')->first();
        $SPVSales = Karyawan::where('jabatan', 'SPV Sales')->first();
        $GM = Karyawan::where('jabatan', 'GM')->first();
        $users = [];

        switch ($jabatan) {
            case 'SPV Sales':
            case 'Office Manager':
            case 'Education Manager':
            case 'Koordinator Office':
            case 'Koordinator ITSM':
                break;

            default:
                switch ($divisi) {
                    case 'Education':
                        $users[] = $Eduman->kode_karyawan;
                        break;

                    case 'Sales & Marketing':
                        $users[] = $SPVSales->kode_karyawan;
                        break;

                    case 'IT Service Management':
                        $users[] = $koorso->kode_karyawan;
                        break;

                    case 'Office':
                        $users[] = $GM->kode_karyawan;
                        $users[] = $kooroff->kode_karyawan;
                        break;
                }
                break;
        }

        $users = User::whereHas('karyawan', function ($query) use ($users) {
            $query->whereIn('kode_karyawan', $users);
        })->get();

        $data = [
            'id_karyawan' => $post->id_karyawan,
            'tanggal_lembur' => $post->tanggal_lembur,
            'waktu_lembur' => $post->waktu_lembur,
            'uraian_tugas' => $post->uraian_tugas,
        ];
        $type = 'Mengisi Jam dan Detail Tugas Lembur';
        $path = '/lembur';

        foreach ($users as $user) {
            NotificationFacade::send($user, new LemburNotification($data, $path, $type));
        }

        return redirect()->route('lembur.index')->with(['success' => 'Data Berhasil Diubah!']);
    }

    public function absenMasuk(Request $request)
    {
        $request->validate([
            'id_karyawan' => 'required',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required',
            'foto_mulai' => 'required',
        ]);

        // Proses foto base64 (memperbaiki prefix format gambar)
        $image = preg_replace('/^data:image\/\w+;base64,/', '', $request->foto_mulai);
        $image = str_replace(' ', '+', $image);
        $imageName = 'mulai_' . Str::random(10) . '.png';

        Storage::disk('public')->put('lembur/' . $imageName, base64_decode($image));

        $lembur = Lembur::where('id_karyawan', $request->id_karyawan)
            ->where('tanggal_lembur', $request->tanggal)
            ->first();

        if (!$lembur) {
            return response()->json(['error' => 'Data lembur tidak ditemukan'], 404);
        }

        if ($lembur->jam_mulai) {
            return response()->json(['error' => 'Anda sudah absen masuk sebelumnya'], 400);
        }

        $lembur->update([
            'jam_mulai' => $request->jam_mulai,
            'foto_masuk' => 'lembur/' . $imageName,
        ]);

        return response()->json(['success' => 'Absensi mulai berhasil']);
    }

    public function absenPulang(Request $request)
    {
        $request->validate([
            'id_karyawan' => 'required',
            'tanggal' => 'required|date',
            'jam_selesai' => 'required',
            'foto_selesai' => 'required',
        ]);

        // Proses foto base64 yang fleksibel
        $image = preg_replace('/^data:image\/\w+;base64,/', '', $request->foto_selesai);
        $image = str_replace(' ', '+', $image);
        $imageName = 'selesai_' . Str::random(10) . '.png';

        Storage::disk('public')->put('lembur/' . $imageName, base64_decode($image));

        // Cari data lembur hari ini
        $lembur = Lembur::where('id_karyawan', $request->id_karyawan)
            ->whereDate('tanggal_lembur', $request->tanggal)
            ->first();

        if (!$lembur) {
            return response()->json(['error' => 'Data lembur tidak ditemukan'], 404);
        }

        if ($lembur->jam_mulai) {
            $selisih = now()->diffInMinutes($lembur->jam_mulai);
            if ($selisih < 60) {
                return response()->json(['error' => 'Absen pulang hanya bisa dilakukan minimal 1 jam setelah absen masuk'], 400);
            }
        } else {
            return response()->json(['error' => 'Anda belum absen masuk'], 400);
        }

        $lembur->update([
            'jam_selesai' => $request->jam_selesai,
            'foto_selesai' => 'lembur/' . $imageName,
        ]);

        return response()->json(['success' => 'Absensi selesai berhasil']);
    }


    public function approvalLemburKaryawan(Request $request, $id)
    {
        // return $request->all();
        $this->validate($request, [
            'approval'     => 'required',
        ]);
        $post = lembur::with('karyawan')->findOrFail($id);
        if($request->approval == '1'){
            $approval = 'Disetujui';
        }else{
            $approval = 'Ditolak';
        }
        $post->update([
            'approval_karyawan'     => $approval,
        ]);

        $karyawan = karyawan::findOrFail($post->id_karyawan);
        $hrd = karyawan::where('jabatan', 'HRD')->latest()->first();
        $users[] = $karyawan->kode_karyawan;
        $users[] = $hrd->kode_karyawan;
        // Retrieve users based on the filtered list of kode_karyawan
        $users = User::whereHas('karyawan', function ($query) use ($users) {
            $query->whereIn('kode_karyawan', $users);
        })->get();
        // return $users;

        $data = [
            'id_karyawan' => $post->karyawan->nama_lengkap,
            'tanggal_lembur' => $post->tanggal_lembur,
            'waktu_lembur' => $post->waktu_lembur,
            'uraian_tugas' => $post->uraian_tugas,
        ];
        $type = 'Telah Menyetujui Perintah Lembur';
        $path = '/lembur';

        foreach ($users as $user) {
            NotificationFacade::send($user, new LemburNotification($data, $path, $type));
        }
        return redirect()->route('lembur.index')->with(['success' => 'Data Berhasil Diubah!']);
    }

}
