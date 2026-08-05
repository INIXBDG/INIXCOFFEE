<?php

namespace App\Http\Controllers;

use App\Models\AdministrasiKaryawan;
use App\Models\karyawan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\jabatan;
use App\Models\TunjanganKaryawan;
use Vinkla\Hashids\Facades\Hashids;
use Carbon\Carbon;
use App\Models\EducationalBackground;
use App\Models\LogGaji;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Month;

class KaryawanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware("permission:Managament Gaji", ['only' => ['gajiIndex', 'updateGaji']]);
    }
    public function gantiFoto($id)
    {
        $users = karyawan::findOrFail($id);
        return view('karyawan.gantifoto', compact('users'));
    }

    public function edit($id)
    {
        $decoded = Hashids::decode($id);
        if (empty($decoded))
            abort(404);

        $realId = $decoded[0];
        $users = Karyawan::with('educations')->findOrFail($realId);
        $user = User::where('karyawan_id', $users->id)->firstOrFail();

        // Batasi akses ke user sendiri atau admin
        if (auth()->id() !== $user->id && auth()->user()->role !== 'Admin' && auth()->user()->jabatan !== 'HRD') {
            abort(403);
        }

        $jabatan = Jabatan::all();
        return view('user.edit', compact('users', 'jabatan'));
    }   

    public function updateData(Request $request, $id)
    {
        $decoded = Hashids::decode($id);
        if (empty($decoded[0]))
            abort(404);

        $realId = $decoded[0];

        $karyawan = Karyawan::findOrFail($realId);
        $user = User::where('karyawan_id', $karyawan->id)->firstOrFail();

        // Cek Otorisasi
        if (auth()->id() !== $user->id && auth()->user()->role !== 'Admin' && auth()->user()->jabatan !== 'HRD') {
            abort(403);
        }

        $contactRules = ['nullable'];

        if (in_array($request->jabatan, ['Instruktur', 'Education Manager'])) {
            $contactRules = ['required'];
        } else {
            $contactRules = ['nullable'];
        }

            $data = $request->validate([
                'nama_lengkap' => ['required'],
                'nip' => ['nullable', 'numeric'],
                'kode_karyawan' => ['nullable'],
                'jabatan' => ['nullable'],
                'divisi' => ['nullable'],
                'status_aktif' => ['required'],
                'rekening_maybank' => ['nullable'],
                'rekening_bca' => ['nullable'],
                'telepon' => $contactRules,
                'whatsapp' => $contactRules,
                'email' => array_merge($contactRules, ['email']),

                'awal_probation' => ['nullable', 'date'],
                'akhir_probation' => ['nullable', 'date'],
                'awal_kontrak' => ['nullable', 'date'],
                'akhir_kontrak' => ['nullable', 'date'],
                'awal_tetap' => ['nullable', 'date'],
                'akhir_tetap' => ['nullable', 'date'],

                'keterangan' => ['nullable'],
                'cuti' => ['nullable', 'numeric'],

                'alamat_lengkap' => ['nullable', 'string'],
                'gender' => ['nullable', 'in:Laki-laki,Perempuan'],
                'tempat_lahir' => ['nullable', 'string'],
                'tanggal_lahir' => ['nullable', 'date'],
                'religion' => ['nullable', 'string'],
                'provinsi' => ['nullable', 'string'],
                'kota' => ['nullable', 'string'],

                // --- Educational Background ---
                'educations' => ['nullable', 'array'],
                'educations.*.name' => $contactRules, ['string'],

                'alasan_resign' => ['nullable', 'string', 'max:500'],
                'is_resign' => ['nullable', 'boolean'],
            ]);

        $karyawanData = collect($data)->except(['educations'])->toArray();

        $karyawan->update($karyawanData);

        if ($request->status_aktif === '0') {
            $karyawan->resigned_at = now();
            $karyawan->alasan_resign = $request->is_resign ? $request->alasan_resign : null;
            $karyawan->save();
        } else {
            $karyawan->resigned_at = null;
            $karyawan->alasan_resign = null;
            $karyawan->save();
        }

        $targetKodeKaryawan = $karyawan->kode_karyawan;

        if ($targetKodeKaryawan) {
            EducationalBackground::where('kode_karyawan', $targetKodeKaryawan)->delete();

            if ($request->has('educations') && is_array($request->educations)) {
                foreach ($request->educations as $edu) {
                    // Pastikan nama sekolah tidak kosong
                    if (!empty($edu['name'])) {
                        EducationalBackground::create([
                            'kode_karyawan' => $targetKodeKaryawan, // Ambil otomatis dari user yang diedit
                            'name' => $edu['name'],
                        ]);
                    }
                }
            }
        }

        $id_instruktur = null;
        $id_sales = null;

        if (in_array($request->jabatan, ['Instruktur', 'Technical Support'])) {
            $id_instruktur = $request->kode_karyawan;
        }

        if (in_array($request->jabatan, ['SPV Sales', 'Sales', 'Adm Sales'])) {
            $id_sales = $request->kode_karyawan;
        }

        $user->jabatan = $data['jabatan'] ?? $user->jabatan;
        $user->status_akun = $data['status_aktif'];
        $user->id_instruktur = $id_instruktur;
        $user->id_sales = $id_sales;

        $user->save();

        if ($request->akhir_kontrak) {
            $administrasi = AdministrasiKaryawan::where('id_karyawan', $karyawan->id)
                                ->where('nama_administrasi', 'like', '%Pembuatan Kontrak Kerja%')
                                ->whereNotIn('status', ['selesai', 'terlambat'])
                                ->orderBy('created_at', 'desc')
                                ->first();
            
            if ($administrasi) {
                $administrasi->update([
                    'status' => 'selesai',
                    'tanggal_selesai' => now(),
                    'updated_at' => now(),
                ]);
            }
        } 
        
        if ($request->akhir_tetap) {
            $administrasi = AdministrasiKaryawan::where('id_karyawan', $karyawan->id)
                                ->where('nama_administrasi', 'like', '%Pembuatan Administrasi Karyawan Tetap%')
                                ->whereNotIn('status', ['selesai', 'terlambat'])
                                ->orderBy('created_at', 'desc')
                                ->first();

            if ($administrasi) {
                $administrasi->update([
                    'status' => 'selesai',
                    'tanggal_selesai' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (auth()->user()->jabatan == "HRD") {
            return redirect('/user')->with('success', 'Data Berhasil Diubah');
        }

        return back()->with('success', 'Data Berhasil Diubah');
    }

    public function updateFoto(Request $request, $id): RedirectResponse
    {
        $this->validate($request, [
            'foto' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'ttd' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        $post = Karyawan::findOrFail($id);

        // Proses foto
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $foto->storeAs('public/posts', $foto->hashName());

            // Hapus foto lama jika ada
            if ($post->foto) {
                Storage::delete('public/posts/' . $post->foto);
            }

            $post->foto = $foto->hashName();
        }

        // Proses ttd
        if ($request->hasFile('ttd')) {
            $ttd = $request->file('ttd');
            $ttd->storeAs('public/ttd', $ttd->hashName());

            // Hapus ttd lama jika ada
            if ($post->ttd) {
                Storage::delete('public/ttd/' . $post->ttd);
            }

            $post->ttd = $ttd->hashName();
        }

        $post->save();
        //Encode hashing untuk update foto
        return redirect()->route('user.show', Hashids::encode($post->id))->with([
            'success' => 'Foto dan/atau TTD berhasil diperbarui!'
        ]);
    }

    public function gajiIndex()
    {
        $karyawan = User::with('karyawan', 'karyawan.LogGaji')
            ->where('status_akun', '1')
            ->where('jabatan', '!=', 'Outsource')
            ->get()
            ->groupBy(fn($user) => $user->karyawan->divisi ?? 'Tidak Diketahui');
        // dd($karyawan->toArray());   
        return view('gaji.index', compact('karyawan'));
    }

    public function updateGaji(Request $request, $id)
    {
        $request->validate([
            'jumlah_gaji'       => 'required|numeric|min:0',
            'tunjangan_jabatan' => 'nullable|numeric|min:0',
            'bulan'             => 'required|integer|between:1,12',
            'tahun'             => 'required|integer|min:2000|max:2099',
        ]);

        $bulanInput = (int) $request->bulan;
        $tahunInput = (int) $request->tahun;
        $bulanNow   = (int) Carbon::now()->month;
        $tahunNow   = (int) Carbon::now()->year;

        $isBulanIni = $bulanInput === $bulanNow && $tahunInput === $tahunNow;

        if ($isBulanIni) {
            $karyawan = karyawan::findOrFail($id);
            $karyawan->update([
                'gaji'              => $request->jumlah_gaji,
                'tunjangan_jabatan' => $request->tunjangan_jabatan ?? 0,
            ]);
        }

        LogGaji::updateOrCreate(
            [
                'id_karyawan' => $id,
                'bulan'       => $bulanInput,
                'tahun'       => $tahunInput,
            ],
            [
                'gaji'              => $request->jumlah_gaji,
                'tunjangan_jabatan' => $request->tunjangan_jabatan ?? 0,
            ]
        );

        return redirect()->route('gaji.index')->with('success', 'Gaji berhasil diperbarui.');
    }


    public function slip(Request $request)
    {
        $monthParam = (int) $request->query('bulan', date('n'));
        $yearParam  = (int) $request->query('tahun', date('Y'));

        if ($monthParam == 1) {
            $bulan = 12;
            $tahun = $yearParam - 1;
        } else {
            $bulan = $monthParam - 1;
            $tahun = $yearParam;
        }

        $HRD = User::with('karyawan')->where('jabatan', 'HRD')->where('status_akun', '1')->first();
        $user = User::with('karyawan')->find(Auth::id());

        $tunjangan = TunjanganKaryawan::where('id_karyawan', Auth::id())
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->with('karyawan', 'jenistunjangan')
            ->get();

        $tunjanganItems = $tunjangan->where('keterangan', 'Tunjangan')->values();
        $potonganItems  = $tunjangan->where('keterangan', 'Potongan')->values();

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $isPeriodeBerjalan = ($bulan === (int) date('n')) && ($tahun === (int) date('Y'));

        if ($isPeriodeBerjalan) {
            $gajiPokok        = (float) $user->karyawan->gaji;
            $tunjanganJabatan = (float) ($user->karyawan->tunjangan_jabatan ?? 0);
        } else {
            $logGaji = LogGaji::where('id_karyawan', Auth::id())
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->orderByDesc('created_at')
                ->first();

            $gajiPokok        = $logGaji ? (float) $logGaji->gaji : 0;
            $tunjanganJabatan = $logGaji ? (float) $logGaji->tunjangan_jabatan : 0;
        }

        $tunjanganJabatanFormatted = $tunjanganJabatan > 0
            ? $this->formatRupiah($tunjanganJabatan)
            : '-';

        $totalPendapatan = $gajiPokok + $tunjanganJabatan + $tunjanganItems->sum('total');
        $totalPotongan   = $potonganItems->sum(fn($i) => abs($i->total));
        $totalBersih     = $totalPendapatan - $totalPotongan;

        $rows = [];
        $pendapatanRows = [];
        $pendapatanRows[] = ['nama' => 'Gaji Pokok', 'jumlah' => $this->formatRupiah($gajiPokok)];

        if ($tunjanganJabatan > 0) {
            $pendapatanRows[] = ['nama' => 'Tunjangan Jabatan', 'jumlah' => $this->formatRupiah($tunjanganJabatan)];
        }

        foreach ($tunjanganItems as $item) {
            $pendapatanRows[] = [
                'nama'   => $item->jenistunjangan->nama_tunjangan,
                'jumlah' => $this->formatRupiah($item->total),
            ];
        }

        $potonganRows = [];
        foreach ($potonganItems as $item) {
            $potonganRows[] = [
                'nama'   => $item->jenistunjangan->nama_tunjangan,
                'jumlah' => $this->formatRupiah(abs($item->total)),
            ];
        }

        $maxRows = max(count($pendapatanRows) + 1, count($potonganRows) + 1);

        for ($i = 0; $i < $maxRows; $i++) {
            $row = ['p_no' => '', 'p_nama' => '', 'p_jumlah' => '', 'pot_no' => '', 'pot_nama' => '', 'pot_jumlah' => ''];

            if ($i < count($pendapatanRows)) {
                $row['p_no']     = $i + 1;
                $row['p_nama']   = $pendapatanRows[$i]['nama'];
                $row['p_jumlah'] = $pendapatanRows[$i]['jumlah'];
            } elseif ($i === $maxRows - 1) {
                $row['p_nama']   = 'Total Pendapatan';
                $row['p_jumlah'] = $this->formatRupiah($totalPendapatan);
            }

            if ($i < count($potonganRows)) {
                $row['pot_no']     = $i + 1;
                $row['pot_nama']   = $potonganRows[$i]['nama'];
                $row['pot_jumlah'] = $potonganRows[$i]['jumlah'];
            } elseif ($i === $maxRows - 1) {
                $row['pot_nama']   = 'Total Potongan';
                $row['pot_jumlah'] = $this->formatRupiah($totalPotongan);
            }

            $rows[] = $row;
        }

        $data = [
            'user' => $user,
            'HRD' => $HRD,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'namaBulanText' => $namaBulan[$bulan] ?? '-',
            'tunjanganJabatanFormatted' => $tunjanganJabatanFormatted,
            'rows' => $rows,
            'totalBersih' => $totalBersih,
            'totalBersihFormatted' => $this->formatRupiah($totalBersih),
            'logoBase64' => $this->imgToBase64(public_path('assets/img/inix.png')),
            'signUserBase64' => $this->imgToBase64(storage_path('app/public/ttd/' . $user->karyawan->ttd)),
            'signHrdBase64' => $this->imgToBase64(storage_path('app/public/ttd/' . $HRD->karyawan->ttd)),
        ];

        $pdf = Pdf::loadView('tunjangan.slip_pdf', $data)->setPaper('a4', 'portrait');

        $fileName = 'Slip_Gaji_' . str_replace(' ', '_', $user->karyawan->nama_lengkap)
            . '_' . $namaBulan[$bulan] . '_' . $tahun . '.pdf';

        return $pdf->download($fileName);
    }

    private function formatRupiah($angka)
    {
        return 'Rp ' . number_format((float) $angka, 0, ',', '.');
    }

    private function imgToBase64($path)
    {
        if ($path && file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            return 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($path));
        }
        return null;
    }
}
