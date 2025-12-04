<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuratPerjalanan;
use App\Models\User;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use App\Models\karyawan;
use App\Models\Materi;
use App\Models\Perusahaan;
use App\Models\RKM;
use App\Notifications\ApprovalSPJNotification;
use App\Notifications\PengajuanSPJNotification;
use Carbon\Carbon;
use App\Exports\SuratPerjalananExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class SuratPerjalananController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * 
     * Menampilkan daftar surat perjalanan.
     */
    public function index()
    {
        return view('suratperjalanan.index');
    }

    public function getSuratPerjalanan()
    {
        $user = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrFail($user);
        $jabatan = $karyawan->jabatan;
        $divisi = $karyawan->divisi;

        if (in_array($jabatan, ['Office Manager', 'Education Manager', 'SPV Sales', 'Koordinator ITSM'])) {

            $SuratPerjalanan = SuratPerjalanan::with('karyawan', 'RKM')
                ->whereHas('karyawan', function ($query) use ($divisi) {
                    $query->where('divisi', $divisi);
                })->latest()->get();
        } elseif (in_array($jabatan, ['HRD', 'Koordinator Office', 'Direktur Utama', 'Direktur', 'GM'])) {

            $SuratPerjalanan = SuratPerjalanan::with('karyawan', 'RKM')->latest()->get();
        } else {

            $SuratPerjalanan = SuratPerjalanan::with('karyawan', 'RKM')
                ->whereHas('karyawan', function ($query) use ($user) {
                    $query->where('id', $user);
                })->latest()->get();
        }

        return response()->json([
            'success' => true,
            'message' => 'List SuratPerjalanan',
            'data' => $SuratPerjalanan,
        ]);
    }



    public function createPrint()
    {
        return view('suratperjalanan.print');
    }

    public function getToPrint()
    {
        $user = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrfail($user);
        // return $karyawan;
        $jabatan = $karyawan->jabatan;
        $divisi = $karyawan->divisi;
        if ($jabatan == 'Office Manager' || $jabatan == 'Education Manager' || $jabatan == 'SPV Sales' || $jabatan == 'Koordinator ITSM') {
            $SuratPerjalanan = SuratPerjalanan::with('karyawan')->whereHas('karyawan', function ($query) use ($divisi) {
                $query->where('divisi', $divisi);
            })->latest()->get();
        } elseif ($jabatan == 'HRD' || $jabatan == "Koordinator Office" || $jabatan == 'GM' || $jabatan == 'Direktur Utama' || $jabatan == 'Direktur') {
            $SuratPerjalanan = SuratPerjalanan::with('karyawan')->latest()->get();
        } else {
            $SuratPerjalanan = SuratPerjalanan::with('karyawan')->whereHas('karyawan', function ($query) use ($user) {
                $query->where('id', $user);
            })->latest()->get();
        }
        return response()->json([
            'success' => true,
            'message' => 'List SuratPerjalanan',
            'data' => $SuratPerjalanan,
        ]);
    }
    public function getToExcelMonth(Request $request)
    {
        $month = $request->input('bulan');

        if (!is_numeric($month) || $month < 1 || $month > 12) {
            return redirect()->back()->with('error', 'Bulan tidak valid.');
        }

        $userId = auth()->user()->karyawan_id;
        $karyawan = Karyawan::findOrFail($userId);
        $divisi = $karyawan->divisi;

        $data = SuratPerjalanan::with('karyawan', 'RKM')
            ->whereMonth('tanggal_berangkat', $month)
            ->get();

        if ($data->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data untuk bulan yang dipilih.');
        }

        $monthName = Carbon::create()->month($month)->locale('id')->isoFormat('MMMM');
        $fileName = 'SuratPerjalanan_' . $monthName . '_' . now()->format('Y') . '.xlsx';

        return Excel::download(new SuratPerjalananExport($data), $fileName);
    }

    public function getToExcelYear(Request $request)
    {
        $year = $request->input('tahun');

        if (!is_numeric($year) || $year < 2024) {
            return redirect()->back()->with('error', 'Tahun tidak Tesedia.');
        }

        $userId = auth()->user()->karyawan_id;
        $karyawan = Karyawan::findOrFail($userId);
        $divisi = $karyawan->divisi;

        $data = SuratPerjalanan::with('karyawan', 'RKM')
            ->whereYear('tanggal_berangkat', $year)
            ->get();

        if ($data->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data untuk tahun yang dipilih.');
        }

        $fileName = 'data_SPJ_Tahunan' . '_' . now()->format('Y') . '.xlsx';

        return Excel::download(new SuratPerjalananExport($data), $fileName);
    }

    public function getToPdfMonth(Request $request)
    {
        $month = $request->input('bulan');
        $user = auth()->user()->karyawan_id;
        $karyawan = Karyawan::findOrFail($user);
        $divisi = $karyawan->divisi;

        if (!is_numeric($month) || $month < 1 || $month > 12) {
            return redirect()->back()->with('error', 'Bulan tidak valid.');
        }

        $data = SuratPerjalanan::with(['karyawan', 'RKM'])
            ->whereMonth('tanggal_berangkat', $month)
            ->get();

        if ($data->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data untuk bulan yang dipilih.');
        }

        $pdf = PDF::loadView('exports.surat_perjalanan_pdf', ['data' => $data]);

        return $pdf->download('SuratPerjalanan_' . now()->format('F_Y') . '.pdf');
    }

    public function getToPdfYear(Request $request)
    {
        $Year = $request->input('tahun');
        $user = auth()->user()->karyawan_id;
        $karyawan = Karyawan::findOrFail($user);
        $divisi = $karyawan->divisi;

        if (!is_numeric($Year) || $Year < 2024 || $Year > now('Y')) {
            return redirect()->back()->with('error', 'Tahun tidak tersedia.');
        }

        $data = SuratPerjalanan::with(['karyawan', 'RKM'])
            ->whereYear('tanggal_berangkat', $Year)
            ->get();

        if ($data->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data untuk tahun yang dipilih.');
        }

        $pdf = PDF::loadView('exports.surat_perjalanan_pdf_tahunan', ['data' => $data]);

        return $pdf->download('SuratPerjalanan_Tahunan' . now()->format('Y') . '.pdf');
    }
    /**
     * Menampilkan form untuk membuat surat perjalanan baru.
     */
    public function create()
    {
        $user = auth()->user()->karyawan_id;
        $karyawan = Karyawan::findOrFail($user);

        $today = now()->startOfWeek();
        $twoWeeksFromNow = $today->copy()->addDays(14)->endOfDay();

        $data_rkm = RKM::with(['materi', 'perusahaan'])
            ->whereBetween('tanggal_awal', [$today, $twoWeeksFromNow])
            ->orderBy('tanggal_awal', 'asc')
            ->get()
            ->groupBy(function ($item) {
                return \Carbon\Carbon::parse($item->tanggal_awal)->translatedFormat('d F Y');
            });

        return view('suratperjalanan.create', compact('karyawan', 'data_rkm'));
    }

    /**
     * Menyimpan surat perjalanan baru ke dalam database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_karyawan' => 'required|string|max:255',
            'tipe' => ['required', 'string', 'max:255', 'not_in:-,null'],
            'tujuan' => 'required|string|max:255',
            'tanggal_berangkat' => 'required|date',
            'tanggal_pulang' => 'required|date|after_or_equal:tanggal_berangkat',
            'alasan' => 'required|string',
            'jenis_dinas' => 'required'
        ], [
            'tipe.not_in' => 'Anda harus memilih jenis travel yang valid.',
            'tanggal_pulang.after_or_equal' => 'Tanggal Pulang tidak boleh kurang dari Tanggal Berangkat.',
        ]);

        $data = $request->all();
        $data['jadwal_RKM'] = $request->input('jadwal_RKM') !== '-' ? $request->input('jadwal_RKM') : null;

        $suratPerjalanan = SuratPerjalanan::create($data);

        $karyawan = karyawan::findOrFail($request->id_karyawan);
        $divisi = $karyawan->divisi;
        $jabatan = $karyawan->jabatan;

        $Offman = karyawan::where('jabatan', 'Office Manager')->first();
        $kooroff = karyawan::where('jabatan', 'Koordinator Office')->first();
        $koorso = karyawan::where('jabatan', 'Koordinator ITSM')->first();
        $Eduman = karyawan::where('jabatan', 'Education Manager')->first();
        $SPVSales = karyawan::where('jabatan', 'SPV Sales')->first();
        $GM = karyawan::where('jabatan', 'GM')->first();

        $users = [];

        switch ($jabatan) {
            case 'SPV Sales':
            case 'Office Manager':
            case 'Education Manager':
            case 'Koordinator Office':
            case 'Koordinator ITSM':
                $users[] = $GM->kode_karyawan;
                break;

            default:
                switch ($divisi) {
                    case 'Education':
                        $users[] = $Eduman->kode_karyawan;
                        break;
                    case 'Sales & Marketing':
                        $users[] = $SPVSales->kode_karyawan;
                        break;
                    case 'Office':
                        $users[] = $kooroff->kode_karyawan;
                        break;
                    case 'IT Service Management':
                        $users[] = $koorso->kode_karyawan;
                        break;
                }
                break;
        }

        $users = User::whereHas('karyawan', function ($query) use ($users) {
            $query->whereIn('kode_karyawan', $users);
        })->get();

        $type = 'Mengajukan Surat Perjalanan';
        $path = '/suratperjalanan';

        foreach ($users as $user) {
            NotificationFacade::send($user, new PengajuanSPJNotification($suratPerjalanan, $path, $type));
        }

        return redirect()->route('suratperjalanan.index')->with('success', 'Surat perjalanan berhasil dibuat.');
    }

    /**
     * Menampilkan detail surat perjalanan tertentu.
     */
    public function show($id)
    {
        $suratperjalanan = SuratPerjalanan::with('karyawan', 'RKM')->findOrFail($id);
        // return $suratperjalanan;
        $divisi = $suratperjalanan->karyawan->divisi;
        $jabatan = $suratperjalanan->karyawan->jabatan;
        if ($jabatan === 'SPV Sales' || $jabatan === 'Office Manager' || $jabatan === 'Education Manager' || $jabatan = 'Koordinator Office') {
            $manager = karyawan::where('jabatan', 'GM')->first();
        } elseif ($divisi == 'Office') {
            // $manager = karyawan::where('jabatan', 'Office Manager')->first();
            $manager = karyawan::where('jabatan', 'Koordinator Office')->first();
        } elseif ($divisi == 'Sales & Marketing') {
            $manager = karyawan::where('jabatan', 'SPV Sales')->first();
        } elseif ($divisi == 'Education') {
            $manager = karyawan::where('jabatan', 'Education Manager')->first();
        } elseif ($divisi == 'Direksi') {
            $manager = karyawan::where('id', $suratperjalanan->id_karyawan)->first();
        } else {
            $manager = karyawan::where('jabatan', 'GM')->first();
        }

        // $hrd = karyawan::where('jabatan', 'HRD')->first();
        // $office_manager = karyawan::where('jabatan', 'Office Manager')->first();
        $office_manager = karyawan::where('jabatan', 'Finance & Accounting')->first();
        $hrd = karyawan::where('jabatan', 'Koordinator Office')->first();


        return view('suratperjalanan.form', compact('suratperjalanan', 'manager', 'hrd', 'office_manager'));
    }

    /**
     * Menampilkan form untuk mengedit surat perjalanan.
     */
    public function edit($id)
    {
        $suratperjalanan = SuratPerjalanan::findOrFail($id);
        $user = $suratperjalanan->id_karyawan;
        $karyawan = karyawan::findOrFail($user);
        return view('suratperjalanan.edit', compact('suratperjalanan', 'karyawan'));
    }

    public function editspj($id)
    {
        $suratperjalanan = SuratPerjalanan::findOrFail($id);
        $user = $suratperjalanan->id_karyawan;
        $karyawan = karyawan::findOrFail($user);
        return view('suratperjalanan.editspj', compact('suratperjalanan', 'karyawan'));
    }

    /**
     * Memperbarui surat perjalanan di dalam database.
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());
        $request->validate([
            'id_karyawan' => 'required|string|max:255',
            'approval_hrd' => 'required|string|max:255',
            'durasi' => 'required|string|max:255',
            'ratemakan' => 'nullable',
            'ratespj' => 'nullable',
            'ratetaksi' => 'nullable',
            'total' => 'required',
        ]);
        $suratPerjalanan = SuratPerjalanan::findOrFail($id);
        $suratPerjalanan->update($request->all());
        $karyawan = karyawan::findOrFail($suratPerjalanan->id_karyawan);
        $Offman = karyawan::where('jabatan', 'Office Manager')->first();
        $kooroff = karyawan::where('jabatan', 'Koordinator Office')->first();

        $users = [
            $karyawan->kode_karyawan,
            $Offman->kode_karyawan,
            $kooroff->kode_karyawan,
        ];

        // Retrieve the first matching user based on the 'kode_karyawan'
        $users = User::whereHas('karyawan', function ($query) use ($users) {
            $query->whereIn('kode_karyawan', array_filter($users));
        })->get();

        $data = $suratPerjalanan;

        $to = $karyawan->nama_lengkap;

        $path = '/suratperjalanan';

        foreach ($users as $user) {
            NotificationFacade::send($user, new ApprovalSPJNotification($data, $path, $to));
        }
        return redirect()->route('suratperjalanan.index')->with('success', 'Surat perjalanan berhasil diperbarui.');
    }

    /**
     * Menghapus surat perjalanan dari database.
     */
    public function destroy($id)
    {
        $suratPerjalanan = SuratPerjalanan::findOrFail($id);
        $suratPerjalanan->delete();

        return redirect()->route('suratperjalanan.index')->with('success', 'Surat perjalanan berhasil dihapus.');
    }

    public function approval(Request $request, $id)
    {
        $suratPerjalanan = SuratPerjalanan::findOrFail($id);
        $suratPerjalanan->update($request->all());
        $karyawan = karyawan::findOrFail($suratPerjalanan->id_karyawan);
        $HRD = karyawan::where('jabatan', 'HRD')->first();
        // $Offman = karyawan::where('jabatan' , 'Office Manager')->first();
        $kooroff = karyawan::where('jabatan', 'Koordinator Office')->first();


        $users = [
            $karyawan->kode_karyawan,
            $HRD->kode_karyawan,
            $kooroff->kode_karyawan,
        ];

        // Retrieve the first matching user based on the 'kode_karyawan'
        $users = User::whereHas('karyawan', function ($query) use ($users) {
            $query->whereIn('kode_karyawan', array_filter($users));
        })->get();

        $data = $suratPerjalanan;

        $to = $karyawan->nama_lengkap;

        $path = '/suratperjalanan';

        foreach ($users as $user) {
            NotificationFacade::send($user, new ApprovalSPJNotification($data, $path, $to));
        }

        return redirect()->route('suratperjalanan.index')->with('success', 'Surat perjalanan berhasil disetujui.');
    }
}
