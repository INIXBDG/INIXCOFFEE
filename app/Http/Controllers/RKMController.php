<?php

namespace App\Http\Controllers;

use App\Models\AbsensiPDF;
use App\Models\comment;
use App\Models\eksam;
use App\Models\exam;
use App\Models\karyawan;
use App\Models\Materi;
use App\Models\Nilaifeedback;
use App\Models\Perusahaan;
use App\Models\Peserta;
use App\Models\souvenirinhouse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use App\Models\RKM;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Registrasi;
use App\Models\SertifikatPDF;
use App\Notifications\AssignkelasNotification;
use App\Notifications\rkmnewNotification;
use App\Notifications\RKMUpdateNotification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RKMExport;

// use Carbon\CarbonImmutable;
use Carbon\Carbon;

class RKMController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View RKM', ['only' => ['index']]);
        $this->middleware('permission:Create RKM', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit RKM', ['only' => ['update', 'edit', 'editRKM']]);
        $this->middleware('permission:RegistrasiForm RKM', ['only' => ['createRegistForm', 'uploadRegistForm']]);
        $this->middleware('permission:Delete RKM', ['only' => ['destroy']]);
    }
    public function index()
    {

        return view('rkm.index');
    }


    public function excelDownload(Request $request)
    {
        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan');

        $filename = 'rkm-' . $bulan . '-' . $tahun . '.xlsx';

        return Excel::download(new RKMExport($tahun, $bulan), $filename);
    }

    public function create(): View
    {
        // $sales = karyawan::where('jabatan', 'sales')->get();
        $sales = Karyawan::whereIn('jabatan', ['Sales', 'SPV Sales', 'Adm Sales', 'Tim Digital'])
            ->where('status_aktif', '1')
            ->get();

        $instruktur = Karyawan::whereIn('jabatan', ['Instruktur', 'Education Manager'])
            ->where('status_aktif', '1')
            ->get();

        $materi = Materi::where('status', 'Aktif')->get();
        $perusahaan = Perusahaan::get();
        $date = Carbon::now(); // atau bisa menggunakan Carbon::parse('2024-10-17')
        // $quarter = 'Q' . $date->quarter;
        $year = $date->year;
        // dd($materi);
        return view('rkm.tambahrkm', compact('sales', 'materi', 'perusahaan', 'instruktur', 'year'));
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $hargaJualBersih = preg_replace('/[^\d]/', '', $request->harga_jual);
        $request->harga_jual = $hargaJualBersih;
        $request->merge([
            'harga_jual' => $hargaJualBersih,
        ]);
        $this->validate($request, [
            'sales_key' => 'required',
            'materi_key' => 'nullable',
            'perusahaan_key' => 'nullable',
            'harga_jual' => 'nullable',
            'pax' => 'nullable',
            'tanggal_awal' => 'nullable',
            'tanggal_akhir' => 'nullable',
            'metode_kelas' => 'nullable',
            'event' => 'nullable',
            'exam' => 'nullable',
            'authorize' => 'nullable',
            'status' => 'nullable',
            'registrasi_form' => 'nullable',
        ]);
        $tanggal_rkm = explode('-', $request->tanggal_awal);
        $tahun = $tanggal_rkm[0];
        $bulan = str_pad($tanggal_rkm[1], 2, '0', STR_PAD_LEFT); // Menambahkan 0 di depan jika perlu
        $hari = str_pad($tanggal_rkm[2], 2, '0', STR_PAD_LEFT); // Menambahkan 0 di depan jika perlu
        $perusahaan = Perusahaan::where('id', $request->perusahaan_key)->first();
        $materi = Materi::where('id', $request->materi_key)->first();
        $kelas = $request->metode_kelas;
        if ($kelas == 'Inhouse Bandung') {
            $kelas = 'inhb';
        } else if ($kelas == 'Inhouse Luar Bandung') {
            $kelas = 'inhlb';
        } else if ($kelas == 'Offline') {
            $kelas = 'off';
        } else {
            $kelas = 'vir';
        }
        $data = [
            'nama_materi' => $materi->nama_materi,
            'nama_perusahaan' => $perusahaan->nama_perusahaan,
        ];
        $file = $request->file('registrasi_form');
        $path = null;
        if ($file) {
            $extension = $file->getClientOriginalExtension();

            // Set path dan nama file berdasarkan jenis file
            $filename = 'registrasiform_' . $materi->nama_materi . '_' . $perusahaan->nama_perusahaan . '_' . $request->tanggal_awal . '_' . $request->tanggal_akhir . '.' . $extension;

            // Tentukan direktori penyimpanan dan lakukan pengecekan jenis file
            $directory = 'registrasiform';
            if (in_array(strtolower($extension), ['pdf', 'jpg', 'jpeg', 'png'])) {
                $path = $file->storeAs($directory, $filename, 'public');
            }
            // Jika perlu melanjutkan proses lebih lanjut, letakkan di sini.
        }

        // Proses selanjutnya jika ada


        $exam = $request->exam ? '1' : '0';
        $authorize = $request->authorize ? '1' : '0';

        function countDaysPerMonth($tanggalAwal, $tanggalAkhir)
        {
            $startDate = Carbon::parse($tanggalAwal);
            $endDate = Carbon::parse($tanggalAkhir);

            $monthDays = []; // Initialize an array to store day counts per month

            // Iterate through each day in the range
            while ($startDate->lte($endDate)) {
                $month = $startDate->format('Y-m'); // Get year and month (e.g., '2024-10')

                // Initialize the month key if not set
                if (!isset($monthDays[$month])) {
                    $monthDays[$month] = 0;
                }

                // Increment the day count for the current month
                $monthDays[$month]++;

                // Move to the next day
                $startDate->addDay();
            }

            return $monthDays;
        }
        $monthQuarter = countDaysPerMonth($request->tanggal_awal, $request->tanggal_akhir);
        $months = array_keys($monthQuarter);
        $days = array_values($monthQuarter);

        // Determine which month to use based on the day count
        if ($days[0] >= ($days[1] ?? 0)) {
            $bulanBaru = $months[0] ?? null; // Use the first month if it's the only one or has more/equal days
        } else {
            $bulanBaru = $months[1] ?? null; // Use the second month if it has more days
        }
        $carbonBulan = $bulanBaru ? Carbon::parse($bulanBaru . '-01') : null;
        $bulanIndo = $carbonBulan ? $carbonBulan->translatedFormat('F') : null;

        // Determine the quarter based on the month
        if ($carbonBulan) {
            $monthNumber = $carbonBulan->month; // Get the month as a number

            // Map the month to its respective quarter
            if ($monthNumber >= 1 && $monthNumber <= 3) {
                $quartal = 'Q1';
            } elseif ($monthNumber >= 4 && $monthNumber <= 6) {
                $quartal = 'Q2';
            } elseif ($monthNumber >= 7 && $monthNumber <= 9) {
                $quartal = 'Q3';
            } else {
                $quartal = 'Q4';
            }
        } else {
            $quartal = null; // Set to null if no month is available
        }
        $date = Carbon::now();
        $year = $date->year;
        RKM::create([
            'sales_key' => $request->sales_key,
            'materi_key' => $request->materi_key,
            'perusahaan_key' => $request->perusahaan_key,
            'harga_jual' => $request->harga_jual,
            'pax' => $request->pax,
            'tanggal_awal' => $request->tanggal_awal,
            'tanggal_akhir' => $request->tanggal_akhir,
            'metode_kelas' => $request->metode_kelas,
            'event' => $request->event,
            'exam' => $exam,
            'authorize' => $authorize,
            'status' => $request->status,
            'quartal' => $quartal,
            'tahun' => $request->tahun,
            'bulan' => $bulanIndo,
            'isi_pax' => $request->pax,
            'registrasi_form' => $path,
        ]);
        $Offman = karyawan::where('jabatan', 'Office Manager')->first();
        $kooroff = karyawan::where('jabatan', 'Koordinator Office')->first();
        $Eduman = karyawan::where('jabatan', 'Education Manager')->first();
        $SPVSales = karyawan::where('jabatan', 'SPV Sales')->first();
        $GM = karyawan::where('jabatan', 'GM')->first();
        // Mengambil pengguna yang terlibat
        $users = array_map(function ($user) {
            return $user === '-' ? null : $user;
        }, [
            $request->sales_key,
            $Eduman->kode_karyawan,
            $Offman->kode_karyawan,
            $kooroff->kode_karyawan,
            $SPVSales->kode_karyawan,
            $GM->kode_karyawan,
        ]);

        $users = User::whereHas('karyawan', function ($query) use ($users) {
            $query->whereIn('kode_karyawan', array_filter($users));
        })->get();


        $path = '/rkm/' . $request->materi_key . 'ixb' . $hari . 'ie' . $tahun . 'ie' . $bulan . 'ixb' . $kelas;
        foreach ($users as $user) {
            NotificationFacade::send($user, new rkmnewNotification($data, $path));
        }


        return redirect()->route('rkm.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }
    /**
     * show
     *
     * @param  mixed $id
     * @return View
     */
    public function show(string $id)
    {
        $array = explode('ixb', $id);
        $materi_key = $array[0];
        $bulans = $array[1];
        $kelas = $array[2];
        $tanggal_rkm = explode('ie', $bulans);
        $tahun = $tanggal_rkm[1];
        $bulan = str_pad($tanggal_rkm[2], 2, '0', STR_PAD_LEFT); // Menambahkan 0 di depan jika perlu
        $hari = str_pad($tanggal_rkm[0], 2, '0', STR_PAD_LEFT); // Menambahkan 0 di depan jika perlu
        $tanggal_awal = $tahun . '-' . $bulan . '-' . $hari;
        $tanggal_mulai = Carbon::parse($tanggal_awal);
        $endOfWeek = $tanggal_mulai->copy()->addDays(2);
        $tanggal_akhir = $endOfWeek->format('Y-m-d');
        $params = $id;

        // Menyesuaikan nilai $kelas berdasarkan kode
        if ($kelas == 'inhb') {
            $kelas = 'Inhouse Bandung';
        } else if ($kelas == 'inhlb') {
            $kelas = 'Inhouse Luar Bandung';
        } else if ($kelas == 'off') {
            $kelas = 'Offline';
        } else if ($kelas == 'vir') {
            $kelas = 'Virtual';
        } else {
            return 404;
        }

        // Query RKM
        $rkm = RKM::with(['sales', 'materi', 'instruktur', 'perusahaan', 'instruktur2', 'asisten'])
            ->where('materi_key', $materi_key)
            ->where('metode_kelas', $kelas)
            ->whereBetween('tanggal_awal', [$tanggal_awal, $tanggal_akhir])
            ->get();

        if ($rkm->isEmpty()) {
            return back()->with('error', 'RKM ini telah dipindahkan atau dihapus.');
        }

        // Ambil data pertama dari RKM
        $ids = $rkm->firstOrFail();

        // Inisialisasi comments dan souvenir
        $comments = collect();
        $souvenir = null;

        foreach ($rkm as $data) {
            $comments = $comments->merge($data->comments);
            $souvenir = souvenirinhouse::where('id_rkm', $data->id)->first();
        }

        return view('rkm.show', compact('rkm', 'comments', 'ids', 'params', 'materi_key', 'souvenir'));
    }
    public function edit(string $id)
    {
        $post = RKM::with(['sales', 'materi', 'instruktur', 'perusahaan'])->findOrFail($id);
        $sales = Karyawan::whereIn('jabatan', ['Sales', 'SPV Sales', 'Adm Sales', 'Tim Digital'])
            ->where('status_aktif', '1')
            ->get();

        $instruktur = Karyawan::whereIn('jabatan', ['Instruktur', 'Education Manager'])
            ->where('status_aktif', '1')
            ->get();
        $materi = Materi::get();
        $perusahaan = Perusahaan::get();
        return view('rkm.edit', compact('post', 'sales', 'materi', 'perusahaan', 'instruktur'));
    }
    /**
     * edit
     *
     * @param  mixed $id
     * @return View
     */
    public function editRKM(): View
    {
        //get post by ID
        $rkm = RKM::with('materi')->get();
        $sales = karyawan::where('jabatan', 'sales')->get();
        $instruktur = Karyawan::whereIn('jabatan', ['Instruktur', 'Education Manager'])->get();
        $materi = Materi::get();
        $perusahaan = Perusahaan::get();

        //render view with post

        return view('rkm.edit', compact('rkm', 'sales', 'materi', 'perusahaan', 'instruktur'));
    }
    public function editInstruktur($id)
    {
        // return $id;
        $karyawan = Karyawan::whereIn('jabatan', ['Instruktur', 'Education Manager', 'Technical Support'])
            ->where('status_aktif', '1')
            ->get();
        $array = explode('ixb', $id);
        $materi_key = $array[0];
        $bulans = $array[1];
        $tanggal_rkm = explode('ie', $bulans);
        $tahun = $tanggal_rkm[1];
        $bulan = str_pad($tanggal_rkm[2], 2, '0', STR_PAD_LEFT); // Menambahkan 0 di depan jika perlu
        $hari = str_pad($tanggal_rkm[0], 2, '0', STR_PAD_LEFT); // Menambahkan 0 di depan jika perlu
        $tanggal_awal = $tahun . '-' . $bulan . '-' . $hari;
        $metode_kelas = $array[2];
        if ($metode_kelas == 'inhb') {
            $metode_kelas = 'Inhouse Bandung';
        } else if ($metode_kelas == 'inhlb') {
            $metode_kelas = 'Inhouse Luar Bandung';
        } else if ($metode_kelas == 'off') {
            $metode_kelas = 'Offline';
        } else {
            $metode_kelas = 'Virtual';
        }
        // return $metode_kelas;
        $params = $id;
        $rkm = RKM::with(['sales', 'materi', 'instruktur', 'perusahaan', 'instruktur2', 'asisten'])
            ->where('metode_kelas', $metode_kelas)
            ->where('materi_key', $materi_key)
            ->where('tanggal_awal', $tanggal_awal)
            ->firstOrFail();
        // return $rkm;

        return view('rkm.editinstruktur', compact('rkm', 'karyawan'));
    }
    public function updateInstruktur(Request $request)
    {
        // dd($request->all());
        $validatedData = $this->validate($request, [
            'instruktur_key' => 'nullable',
            'instruktur_key2' => 'nullable',
            'asisten_key' => 'nullable',
            'ruang' => 'nullable',
        ]);
        $test = $rkm = RKM::findOrFail($request->id_rkm);
        $metode_kelas = $test->metode_kelas;
        $materiKey = $request->materi_key;
        $tanggalAwal = $request->tanggal_awal;
        $tanggal_mulai = Carbon::parse($tanggalAwal);
        $endOfWeek = $tanggal_mulai->copy()->addWeek();
        $ids = RKM::with(['sales', 'materi', 'instruktur', 'perusahaan'])
            ->where('materi_key', $materiKey)
            ->whereBetween('tanggal_awal', [$tanggalAwal, $endOfWeek])
            // ->where('tanggal_awal', $tanggalAwal)
            ->where('metode_kelas', $metode_kelas)
            ->get();
        // return $ids;
        // $ids = $request->ids;

        foreach ($ids as $rkms) {

            $rkm = RKM::findOrFail($rkms->id);
            $rkm->update($validatedData);
            $tanggal_rkm = explode('-', $rkm->tanggal_awal);
            $tahun = $tanggal_rkm[0];
            $bulan = str_pad($tanggal_rkm[1], 2, '0', STR_PAD_LEFT); // Menambahkan 0 di depan jika perlu
            $hari = str_pad($tanggal_rkm[2], 2, '0', STR_PAD_LEFT); // Menambahkan 0 di depan jika perlu
            $perusahaan = Perusahaan::where('id', $rkm->perusahaan_key)->first();
            $materi = Materi::where('id', $rkm->materi_key)->first();
            $kelas = $rkm->metode_kelas;
            if ($kelas == 'Inhouse Bandung') {
                $kelas = 'inhb';
            } else if ($kelas == 'Inhouse Luar Bandung') {
                $kelas = 'inhlb';
            } else if ($kelas == 'Offline') {
                $kelas = 'off';
            } else {
                $kelas = 'vir';
            }
            $data = [
                'nama_materi' => $materi->nama_materi,
                'nama_perusahaan' => $perusahaan->nama_perusahaan,
            ];
            // Menentukan peran berdasarkan pengguna
            $roles = [
                $rkm->instruktur_key => 'Instruktur',
                $rkm->instruktur_key2 => 'Instruktur #2',
                $rkm->asisten_key => 'Asisten',
            ];

            // Mengambil pengguna yang terlibat dan peran mereka
            $users = [];
            foreach ($roles as $key => $role) {
                if ($key !== '-') {
                    $user = User::whereHas('karyawan', function ($query) use ($key) {
                        $query->where('kode_karyawan', $key);
                    })->first();

                    if ($user) {
                        $users[] = ['user' => $user, 'role' => $role];
                    }
                }
            }

            $path = '/rkm/' . $rkm->materi_key . 'ixb' . $hari . 'ie' . $tahun . 'ie' . $bulan . 'ixb' . $kelas;

            foreach ($users as $userInfo) {
                $user = $userInfo['user'];
                $role = $userInfo['role'];

                // Menambahkan informasi role ke dalam data
                $data['role'] = $role;

                // Mengirimkan notifikasi
                NotificationFacade::send($user, new AssignkelasNotification($data, $path));
            }
        }
        return redirect()->route('rkm.index')->with(['success' => 'Data Berhasil Diubah!']);
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
        // Validate form
        $hargaJualBersih = preg_replace('/[^\d]/', '', $request->harga_jual);
        $request->harga_jual = $hargaJualBersih;
        $this->validate($request, [
            'sales_key' => 'required',
            'materi_key' => 'nullable',
            'harga_jual' => 'nullable',
            'pax' => 'nullable',
            'tanggal_awal' => 'nullable',
            'tanggal_akhir' => 'nullable',
            'metode_kelas' => 'nullable',
            'event' => 'nullable',
            'exam' => 'nullable',
            'authorize' => 'nullable',
            'status' => 'nullable',
            'registrasi_form' => 'nullable',
        ]);

        $post = RKM::findOrFail($id);
        if ($request->status == '0') {
            if ($request->hasFile('registrasi_form')) {
                $file = $request->file('registrasi_form');

                $extension = $file->getClientOriginalExtension();

                $filename = 'registrasiform_' .
                    $post->materi->nama_materi . '_' .
                    $post->perusahaan->nama_perusahaan . '_' .
                    $post->tanggal_awal . '_' .
                    $post->tanggal_akhir . '.' .
                    $extension;

                // Direktori penyimpanan
                $directory = 'registrasiform';

                if (in_array(strtolower($extension), ['pdf', 'jpg', 'jpeg', 'png'])) {
                    $path = $file->storeAs($directory, $filename, 'public');

                    $post->registrasi_form = $path;
                    $post->save();
                } else {
                    return back()->with('error', 'Format file tidak didukung. Harap unggah file dengan format PDF, JPG, JPEG, atau PNG.');
                }
            }
            // Jika tidak ada file dan belum pernah upload sebelumnya

        } else if (!$post->registrasi_form && $request->status == '0') {
            return back()->with('error', 'Harap isi terlebih dahulu registrasi form sebelum kelas dimerahkan.');
        }

        $exam = $request->exam ? '1' : '0';
        $authorize = $request->authorize ? '1' : '0';

        function countDaysPerMonthUpdate($tanggalAwal, $tanggalAkhir)
        {
            $startDate = Carbon::parse($tanggalAwal);
            $endDate = Carbon::parse($tanggalAkhir);

            $monthDays = []; // Initialize an array to store day counts per month

            // Iterate through each day in the range
            while ($startDate->lte($endDate)) {
                $month = $startDate->format('Y-m'); // Get year and month (e.g., '2024-10')

                // Initialize the month key if not set
                if (!isset($monthDays[$month])) {
                    $monthDays[$month] = 0;
                }

                // Increment the day count for the current month
                $monthDays[$month]++;

                $startDate->addDay();
            }

            return $monthDays;
        }

        if (!$request->quarter) {
            $monthQuarter = countDaysPerMonthUpdate($request->tanggal_awal, $request->tanggal_akhir);
            $months = array_keys($monthQuarter);
            $days = array_values($monthQuarter);

            // Determine which month to use based on the day count
            $bulanBaru = ($days[0] >= ($days[1] ?? 0)) ? ($months[0] ?? null) : ($months[1] ?? null);
            $carbonBulan = $bulanBaru ? Carbon::parse($bulanBaru . '-01') : null;
            $bulanIndo = $carbonBulan ? $carbonBulan->translatedFormat('F') : null;

            // Determine the quarter based on the month
            $quarter = null;
            if ($carbonBulan) {
                $monthNumber = $carbonBulan->month;
                if ($monthNumber >= 1 && $monthNumber <= 3) {
                    $quarter = 'Q1';
                } elseif ($monthNumber >= 4 && $monthNumber <= 6) {
                    $quarter = 'Q2';
                } elseif ($monthNumber >= 7 && $monthNumber <= 9) {
                    $quarter = 'Q3';
                } else {
                    $quarter = 'Q4';
                }
            }
        } else {
            $monthQuarter = countDaysPerMonthUpdate($request->tanggal_awal, $request->tanggal_akhir);
            $months = array_keys($monthQuarter);
            $days = array_values($monthQuarter);

            // Determine which month to use based on the day count
            $bulanBaru = ($days[0] >= ($days[1] ?? 0)) ? ($months[0] ?? null) : ($months[1] ?? null);
            $carbonBulan = $bulanBaru ? Carbon::parse($bulanBaru . '-01') : null;
            $bulanIndo = $carbonBulan ? $carbonBulan->translatedFormat('F') : null;
            $quarter = $request->quarter; // Use the request's quarter if provided
        }
        $date = Carbon::now();
        $year = $date->year;
        $post->update([
            'sales_key' => $request->sales_key,
            'materi_key' => $request->materi_key,
            'harga_jual' => $request->harga_jual,
            'pax' => $request->pax,
            'isi_pax' => $request->pax,
            'tanggal_awal' => $request->tanggal_awal,
            'tanggal_akhir' => $request->tanggal_akhir,
            'metode_kelas' => $request->metode_kelas,
            'event' => $request->event,
            'quartal' => $quarter,
            'tahun' => $year,
            'bulan' => $bulanIndo,
            'exam' => $exam,
            'authorize' => $authorize,
            'status' => $request->status,
        ]);
        // $HRD = karyawan::where('jabatan', 'HRD')->first() ?? '-';
        $kooroff = karyawan::where('jabatan', 'Koordinator Office')->first() ?? '-';
        $users = array_map(function ($user) {
            return $user === '-' ? null : $user;
        }, [
            // $HRD->kode_karyawan,
            $kooroff->kode_karyawan,
        ]);

        $users = User::whereHas('karyawan', function ($query) use ($users) {
            $query->whereIn('kode_karyawan', array_filter($users));
        })->get();

        $tanggal_rkm = explode('-', $request->tanggal_awal);
        $tahun = $tanggal_rkm[0];
        $bulan = str_pad($tanggal_rkm[1], 2, '0', STR_PAD_LEFT); // Menambahkan 0 di depan jika perlu
        $hari = str_pad($tanggal_rkm[2], 2, '0', STR_PAD_LEFT); // Menambahkan 0 di depan jika perlu
        $perusahaan = Perusahaan::where('id', $request->perusahaan_key)->first();
        $materi = Materi::where('id', $request->materi_key)->first();
        $kelas = $request->metode_kelas;
        if ($kelas == 'Inhouse Bandung') {
            $kelas = 'inhb';
        } else if ($kelas == 'Inhouse Luar Bandung') {
            $kelas = 'inhlb';
        } else if ($kelas == 'Offline') {
            $kelas = 'off';
        } else {
            $kelas = 'vir';
        }

        $data = [
            'nama_materi' => $materi->nama_materi,
            'nama_perusahaan' => $perusahaan->nama_perusahaan,
        ];
        $path = '/rkm/' . $request->materi_key . 'ixb' . $hari . 'ie' . $tahun . 'ie' . $bulan . 'ixb' . $kelas;
        foreach ($users as $user) {
            NotificationFacade::send($user, new RKMUpdateNotification($data, $path));
        }
        return redirect()->route('rkm.index')->with(['success' => 'Data Berhasil Diubah!']);
    }
    /**
     * destroy
     *
     * @param  mixed $post
     * @return void
     */
    public function destroy($id): RedirectResponse
    {
        // dd($id);
        $post = RKM::findOrFail($id);
        $registrasi = Registrasi::where('id_rkm', $id);
        $feedback = Nilaifeedback::where('id_rkm', $id);
        $exam = eksam::where('id_rkm', $id);
        $comment = comment::where('rkm_key', $id);


        // Storage::delete('public/npwp/'. $post->foto_npwp);

        $post->delete();
        $registrasi->delete();
        $feedback->delete();
        // $exam->delete();
        $comment->delete();

        return redirect()->route('rkm.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }
    public function absensiPeserta(string $id)
    {
        $rkm = RKM::with('perusahaan', 'materi', 'registrasi.peserta', 'instruktur')->findOrFail($id);

        // Calculate the number of registrations
        $jumlahRegistrasi = $rkm->registrasi->count();

        // Check if the number of registrations is less than the pax value
        if ($jumlahRegistrasi < $rkm->pax) {
            return back()->withErrors($jumlahRegistrasi . ' dari ' . $rkm->pax . ' peserta belum mendaftar');
        }

        // If the number of registrations matches the pax value, return the view
        return view('rkm.absensi', compact('rkm'));
    }
    public function createRegistForm($id)
    {
        $rkm = RKM::findOrFail($id);
        return view('rkm.registform', compact('rkm'));
    }

    public function uploadRegistForm(Request $request, $id)
    {
        $post = RKM::with('materi', 'perusahaan')->findOrFail($id);

        if ($request->hasFile('registrasi_form')) {
            $file = $request->file('registrasi_form');
            $extension = strtolower($file->getClientOriginalExtension());

            // Sanitize all dynamic parts of the filename!
            function sanitizeFileName($str)
            {
                // Remove invisible characters and replace invalid ones with underscores.
                return preg_replace('/[^\w\d\-_.]+/u', '_', trim($str));
            }

            $nama_materi     = sanitizeFileName($post->materi->nama_materi ?? 'materi');
            $nama_perusahaan = sanitizeFileName($post->perusahaan->nama_perusahaan ?? 'perusahaan');
            $tanggal_awal    = sanitizeFileName($post->tanggal_awal ?? date('Y-m-d'));
            $tanggal_akhir   = sanitizeFileName($post->tanggal_akhir ?? date('Y-m-d'));

            // Compose safe filename.
            $filename =
                "registrasiform_" .
                "{$nama_materi}_" .
                "{$nama_perusahaan}_" .
                "{$tanggal_awal}_" .
                "{$tanggal_akhir}.{$extension}";

            if ($extension === 'pdf') {
                // Store the sanitized filename.
                $path = $file->storeAs('registrasiform', $filename, 'public');

                // Update DB record.
                $post->update([
                    'registrasi_form' => $path,
                ]);

                return redirect()->route('rkm.index')->with(['success' => 'Data Berhasil Disimpan']);
            } else {
                return redirect()->back()->withErrors(['registrasi_form' => 'Hanya file PDF yang diperbolehkan.']);
            }
        }

        return redirect()->back()->withErrors(['registrasi_form' => 'Tidak ada file yang diunggah.']);
    }


    public function cekregisform($id)
    {
        $path = storage_path('app/public/registrasiform/' . $id);
        return response()->file($path);
    }

    public function uploadAbsensi(string $id)
    {
        $array = explode('ixb', $id);
        $materi_key = $array[0];
        $bulans = $array[1];
        $kelas = $array[2];
        $tanggal_rkm = explode('ie', $bulans);
        $tahun = $tanggal_rkm[1];
        $bulan = str_pad($tanggal_rkm[2], 2, '0', STR_PAD_LEFT); // Menambahkan 0 di depan jika perlu
        $hari = str_pad($tanggal_rkm[0], 2, '0', STR_PAD_LEFT); // Menambahkan 0 di depan jika perlu
        $tanggal_awal = $tahun . '-' . $bulan . '-' . $hari;
        $tanggal_mulai = Carbon::parse($tanggal_awal);
        $endOfWeek = $tanggal_mulai->copy()->addDays(2);
        $tanggal_akhir = $endOfWeek->format('Y-m-d');
        $params = $id;



        // Menyesuaikan nilai $kelas berdasarkan kode
        if ($kelas == 'inhb') {
            $kelas = 'Inhouse Bandung';
        } else if ($kelas == 'inhlb') {
            $kelas = 'Inhouse Luar Bandung';
        } else if ($kelas == 'off') {
            $kelas = 'Offline';
        } else if ($kelas == 'vir') {
            $kelas = 'Virtual';
        } else {
            return 404;
        }

        // Query RKM
        $rkm = RKM::with(['sales', 'materi', 'instruktur', 'perusahaan', 'instruktur2', 'asisten'])
            ->where('materi_key', $materi_key)
            ->where('metode_kelas', $kelas)
            ->whereBetween('tanggal_awal', [$tanggal_awal, $tanggal_akhir])
            ->firstOrFail(); // ✅ sudah jaminan dapat data

        $comments = $rkm->comments;
        $souvenir = souvenirinhouse::where('id_rkm', $rkm->id)->first();
        $pdf = AbsensiPDF::where('id_rkm', $rkm->id)->first();

        return view("rkm.uploadabsensi", compact('rkm', 'comments', 'params', 'materi_key', 'souvenir', 'pdf'));
    }

    public function storeAbsensi(Request $request)
    {
        $request->validate([
            'absensi' => 'required|file|mimes:pdf|max:10240',
        ]);

        $file = $request->file('absensi');
        $fileName = 'absensi_rkm_' . $request->id_rkm . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('public/absensi', $fileName);

        $existingPdf = AbsensiPDF::where('id_rkm', $request->id_rkm)->first();

        if ($existingPdf) {
            if (Storage::exists($existingPdf->pdf_path)) {
                Storage::delete($existingPdf->pdf_path);
            }

            $existingPdf->pdf_path = $path;
            $existingPdf->pdf_name = $fileName;
            $existingPdf->save();
        } else {
            $pdf = new AbsensiPDF();
            $pdf->id_rkm = $request->id_rkm;
            $pdf->pdf_path = $path;
            $pdf->pdf_name = $fileName;
            $pdf->save();
        }

        return back()->with('success', 'Absensi berhasil diupload.');
    }

    public function deleteAbsensi(Request $request)
    {
        $pdfId = $request->input('pdf_id');
        $pdf = AbsensiPDF::where('id', $pdfId)->first();

        if ($pdf) {
            if (Storage::exists($pdf->pdf_path)) {
                Storage::delete($pdf->pdf_path);
            }

            $pdf->delete();

            return back()->with('success', 'PDF absensi berhasil dihapus.');
        }

        return back()->with('error', 'PDF absensi tidak ditemukan.');
    }


    public function uploadSertifikat(string $id)
    {
        $rkm = RKM::with('perusahaan', 'materi', 'registrasi.peserta.perusahaan', 'instruktur', 'sertifikatPDF')->findOrFail($id);
        // dd($rkm->toArray());

        $jumlahRegistrasi = $rkm->registrasi->count();

        if ($jumlahRegistrasi < $rkm->pax) {
            return back()->withErrors($jumlahRegistrasi . ' dari ' . $rkm->pax . ' peserta belum mendaftar');
        }

        return view("rkm.uploadSertifikat", compact('rkm'));
    }

    public function storeSertifikat(Request $request)
    {
        $request->validate([
            'sertifikat.*' => 'required|file|mimes:pdf|max:10240',
        ]);

        if ($request->hasFile('sertifikat')) {
            foreach ($request->file('sertifikat') as $file) {
                $fileName = 'sertifikat_peserta_rkm' . '_' . $request->id_rkm . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('public/sertifikat', $fileName);

                // Simpan ke database sebagai entri baru
                $pdf = new SertifikatPDF();
                $pdf->id_rkm = $request->id_rkm;
                $pdf->pdf_path = $path;
                $pdf->pdf_name = $fileName;
                $pdf->save();
            }

            return back()->with('success', 'Semua sertifikat berhasil diupload.');
        }

        return back()->with('error', 'Tidak ada file yang diupload.');
    }


    public function deleteSertifikat(Request $request)
    {
        $pdfId = $request->input('pdf_id');
        // dd($pdfId);

        $pdf = SertifikatPDF::where('id', $pdfId)->first();

        if ($pdf) {
            if (Storage::exists($pdf->pdf_path)) {
                Storage::delete($pdf->pdf_path);
            }

            $pdf->delete();

            return back()->with('success', 'PDF sertifikat berhasil dihapus.');
        }

        return back()->with('error', 'PDF sertifikat tidak ditemukan.');
    }

    public function uploadPage()
    {
        return view('rkm.uploadPage');
    }

    public function dataPage(string $id)
    {
        $array = explode('ixb', $id);
        $materi_key = $array[0];
        $bulans = $array[1];
        $kelas = $array[2];
        $tanggal_rkm = explode('ie', $bulans);
        $tahun = $tanggal_rkm[1];
        $bulan = str_pad($tanggal_rkm[2], 2, '0', STR_PAD_LEFT); // Menambahkan 0 di depan jika perlu
        $hari = str_pad($tanggal_rkm[0], 2, '0', STR_PAD_LEFT); // Menambahkan 0 di depan jika perlu
        $tanggal_awal = $tahun . '-' . $bulan . '-' . $hari;
        $tanggal_mulai = Carbon::parse($tanggal_awal);
        $endOfWeek = $tanggal_mulai->copy()->addDays(2);
        $tanggal_akhir = $endOfWeek->format('Y-m-d');
        $params = $id;

        // Menyesuaikan nilai $kelas berdasarkan kode
        if ($kelas == 'inhb') {
            $kelas = 'Inhouse Bandung';
        } else if ($kelas == 'inhlb') {
            $kelas = 'Inhouse Luar Bandung';
        } else if ($kelas == 'off') {
            $kelas = 'Offline';
        } else if ($kelas == 'vir') {
            $kelas = 'Virtual';
        } else {
            return 404;
        }

        // Query RKM
        $rkm = RKM::with(['sales', 'materi', 'instruktur', 'perusahaan', 'instruktur2', 'asisten'])
            ->where('materi_key', $materi_key)
            ->where('metode_kelas', $kelas)
            ->whereBetween('tanggal_awal', [$tanggal_awal, $tanggal_akhir])
            ->get();

        if ($rkm->isEmpty()) {
            return back()->with('error', 'RKM ini telah dipindahkan atau dihapus.');
        }

        // Ambil data pertama dari RKM
        $ids = $rkm->firstOrFail();

        // Inisialisasi comments dan souvenir
        $comments = collect();
        $souvenir = null;

        foreach ($rkm as $data) {
            $comments = $comments->merge($data->comments);
            $souvenir = souvenirinhouse::where('id_rkm', $data->id)->first();
        }

        return view('rkm.show', compact('rkm', 'comments', 'ids', 'params', 'materi_key', 'souvenir'));
    }

    public function updateMakanan(Request $request, $id)
{
    $rkm = RKM::find($id); // jangan langsung OrFail biar bisa handle error sendiri
    if (!$rkm) {
        return response()->json([
            'status' => false,
            'message' => 'RKM tidak ditemukan'
        ], 404);
    }

    $rkm->makanan = $request->makanan;
    $rkm->save();

    return response()->json([
        'status' => true,
        'message' => 'Makanan berhasil diperbarui'
    ]);
}


}