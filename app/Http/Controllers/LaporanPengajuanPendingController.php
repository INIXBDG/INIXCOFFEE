<?php

namespace App\Http\Controllers;

use App\Models\karyawan;
use App\Models\pengajuancuti;
use App\Models\PengajuanBarang;
use App\Models\SuratPerjalanan;
use App\Models\tracking_pengajuan_barang;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LaporanPengajuanPendingController extends Controller
{
    private $allowedRoles = [
        'Koordinator ITSM',
        'SPV Sales',
        'Education Manager',
        'GM',
        'General Manager',
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Helper to authenticate role and resolve division scope.
     */
    private function resolveUserScope(Request $request)
    {
        $user = auth()->user();
        $karyawan = $user->karyawan;
        $jabatan = $karyawan->jabatan ?? $user->jabatan ?? '';

        $isAuthorized = false;
        foreach ($this->allowedRoles as $role) {
            if (strcasecmp($jabatan, $role) === 0) {
                $isAuthorized = true;
                break;
            }
        }

        if (!$isAuthorized) {
            abort(403, 'Akses ditolak. Fitur ini khusus untuk Koordinator ITSM, SPV Sales, Education Manager, dan GM.');
        }

        $isGM = (strcasecmp($jabatan, 'GM') === 0 || strcasecmp($jabatan, 'General Manager') === 0);
        $currentKaryawanId = $karyawan->id ?? $user->karyawan_id ?? null;

        // Map canonical division per role
        $roleDivisionMap = [
            'Koordinator ITSM'   => 'IT Service Management',
            'SPV Sales'          => 'Sales & Marketing',
            'Education Manager'  => 'Education',
        ];

        $defaultDivision = $roleDivisionMap[$jabatan] ?? ($karyawan->divisi ?? 'Office');

        if ($isGM) {
            $requestedDivisi = $request->input('divisi');
            if ($requestedDivisi && $requestedDivisi !== 'all' && $requestedDivisi !== 'Semua Divisi') {
                $activeDivision = $requestedDivisi;
            } else {
                $activeDivision = null; // null means all divisions for GM
            }
        } else {
            // Strictly enforce their own division regardless of input
            $activeDivision = $defaultDivision;
        }

        return [
            'user'                => $user,
            'karyawan'            => $karyawan,
            'current_karyawan_id' => $currentKaryawanId,
            'jabatan'             => $jabatan,
            'default_division'    => $defaultDivision,
            'active_division'     => $activeDivision,
            'is_gm'               => $isGM,
        ];
    }

    /**
     * Check if the authenticated user has authority to approve a given Pengajuan Cuti.
     */
    private function canApproveCuti($item, array $scope): bool
    {
        $currentKaryawanId = $scope['current_karyawan_id'];
        $isGM = $scope['is_gm'];
        $currentJabatan = $scope['jabatan'];

        // User cannot approve their own submission
        if ($currentKaryawanId && (int) $item->id_karyawan === (int) $currentKaryawanId) {
            return false;
        }

        $karyawan = $item->karyawan;
        $requesterJabatan = $karyawan->jabatan ?? '';
        $requesterDivisi = $karyawan->divisi ?? '';

        $atasanJabatans = [
            'Education Manager', 'Office Manager', 'SPV Sales',
            'Koordinator ITSM', 'Koordinator Office', 'GM', 'General Manager'
        ];

        $pengajuanCutiGMIds = [4, 14, 29];
        $isRequesterAtasan = in_array($requesterJabatan, $atasanJabatans)
            || in_array((int) $item->id_karyawan, $pengajuanCutiGMIds)
            || strcasecmp($requesterDivisi, 'Office') === 0;

        if ($isRequesterAtasan) {
            return $isGM;
        }

        if ($isGM) {
            return true;
        }

        if (strcasecmp($currentJabatan, 'Koordinator ITSM') === 0 && strcasecmp($requesterDivisi, 'IT Service Management') === 0) {
            return true;
        }
        if (strcasecmp($currentJabatan, 'SPV Sales') === 0 && strcasecmp($requesterDivisi, 'Sales & Marketing') === 0) {
            return true;
        }
        if (strcasecmp($currentJabatan, 'Education Manager') === 0 && strcasecmp($requesterDivisi, 'Education') === 0) {
            return true;
        }

        return false;
    }

    /**
     * Check if the authenticated user has authority to approve a given Pengajuan Barang.
     */
    private function canApproveBarang($item, array $scope): bool
    {
        $currentKaryawanId = $scope['current_karyawan_id'];
        $isGM = $scope['is_gm'];
        $currentJabatan = $scope['jabatan'];

        // User cannot approve their own submission
        if ($currentKaryawanId && (int) $item->id_karyawan === (int) $currentKaryawanId) {
            return false;
        }

        $trackingStr = $item->tracking->tracking ?? '';

        // If tracking targets GM / Direktur Utama
        if (stripos($trackingStr, 'General Manager') !== false 
            || stripos($trackingStr, 'Direktur Utama') !== false 
            || stripos($trackingStr, 'Review GM') !== false
            || stripos($trackingStr, 'GM') !== false) {
            return $isGM;
        }

        // If tracking targets SPV Sales
        if (stripos($trackingStr, 'SPV Sales') !== false) {
            return $isGM || (strcasecmp($currentJabatan, 'SPV Sales') === 0);
        }

        // If tracking targets Koordinator ITSM
        if (stripos($trackingStr, 'Koordinator IT Service Management') !== false 
            || stripos($trackingStr, 'Koordinator ITSM') !== false) {
            return $isGM || (strcasecmp($currentJabatan, 'Koordinator ITSM') === 0);
        }

        // If tracking targets Education Manager
        if (stripos($trackingStr, 'Education Manager') !== false) {
            return $isGM || (strcasecmp($currentJabatan, 'Education Manager') === 0);
        }

        return $isGM;
    }

    /**
     * Check if the authenticated user has authority to approve a given SPJ.
     */
    private function canApproveSPJ($item, array $scope): bool
    {
        $currentKaryawanId = $scope['current_karyawan_id'];
        $isGM = $scope['is_gm'];
        $currentJabatan = $scope['jabatan'];

        // User cannot approve their own submission
        if ($currentKaryawanId && (int) $item->id_karyawan === (int) $currentKaryawanId) {
            return false;
        }

        $karyawan = $item->karyawan;
        $requesterJabatan = $karyawan->jabatan ?? '';
        $requesterDivisi = $karyawan->divisi ?? '';

        $jabatanAtasan = [
            'Education Manager', 'Office Manager', 'SPV Sales',
            'Koordinator ITSM', 'Koordinator Office', 'GM', 'General Manager'
        ];
        $pengajuAtasan = in_array($requesterJabatan, $jabatanAtasan);

        // SPJ manager/SPV/koordinator hanya boleh diapprove GM
        if ($pengajuAtasan) {
            return $isGM;
        }

        // Internasional yang sudah diapprove manager, butuh approval GM
        if ($item->tipe === 'Internasional' && $item->approval_manager == '1') {
            return $isGM;
        }

        if ($isGM) {
            return true;
        }

        if (strcasecmp($currentJabatan, 'Koordinator ITSM') === 0 && strcasecmp($requesterDivisi, 'IT Service Management') === 0) {
            return true;
        }
        if (strcasecmp($currentJabatan, 'SPV Sales') === 0 && strcasecmp($requesterDivisi, 'Sales & Marketing') === 0) {
            return true;
        }
        if (strcasecmp($currentJabatan, 'Education Manager') === 0 && strcasecmp($requesterDivisi, 'Education') === 0) {
            return true;
        }

        return false;
    }

    /**
     * Main view for the report.
     */
    public function index(Request $request)
    {
        $scope = $this->resolveUserScope($request);

        $divisionsList = [
            'Semua Divisi'           => 'all',
            'IT Service Management'  => 'IT Service Management',
            'Sales & Marketing'      => 'Sales & Marketing',
            'Education'              => 'Education',
            'Office'                 => 'Office',
        ];

        $currentYear = Carbon::now()->year;
        $years = range($currentYear + 1, 2023);

        $months = [
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return view('laporan_pengajuan_pending.index', [
            'jabatan'          => $scope['jabatan'],
            'default_division' => $scope['default_division'],
            'active_division'  => $scope['active_division'],
            'is_gm'            => $scope['is_gm'],
            'divisionsList'    => $divisionsList,
            'years'            => $years,
            'months'           => $months,
            'currentYear'      => $currentYear,
        ]);
    }

    /**
     * Summary KPI counters (total, cuti, barang, spj).
     */
    public function getSummary(Request $request)
    {
        $scope = $this->resolveUserScope($request);
        $divisi = $scope['active_division'];
        $isGM = $scope['is_gm'];

        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan');

        // 1. Cuti Count
        $cutiQuery = pengajuancuti::query()->where('approval_manager', '0');
        if ($divisi) {
            $cutiQuery->whereHas('karyawan', fn($q) => $q->where('divisi', $divisi));
        }
        if ($tahun && $tahun !== 'all' && $tahun !== 'Semua') {
            $cutiQuery->whereYear('tanggal_awal', $tahun);
        }
        if ($bulan && $bulan !== 'all' && $bulan !== 'Semua') {
            $cutiQuery->whereMonth('tanggal_awal', $bulan);
        }
        $cutiCount = $cutiQuery->count();

        // 2. Barang Count
        $barangQuery = PengajuanBarang::query()
            ->whereHas('tracking', function ($q) {
                $q->where('tracking', 'LIKE', '%ditinjau%')
                  ->orWhere('tracking', 'LIKE', '%Ditinjau%');
            });
        if ($divisi) {
            $barangQuery->whereHas('karyawan', fn($q) => $q->where('divisi', $divisi));
        }
        if ($tahun && $tahun !== 'all' && $tahun !== 'Semua') {
            $barangQuery->whereYear('created_at', $tahun);
        }
        if ($bulan && $bulan !== 'all' && $bulan !== 'Semua') {
            $barangQuery->whereMonth('created_at', $bulan);
        }
        $barangCount = $barangQuery->count();

        // 3. SPJ Count
        $spjQuery = SuratPerjalanan::query()
            ->where(function ($q) use ($isGM) {
                if ($isGM) {
                    $q->where('approval_manager', '0')
                        ->orWhere(function ($sub) {
                            $sub->where('tipe', 'Internasional')
                                ->where('approval_manager', '1')
                                ->where('approval_hrd', '1')
                                ->where('approval_gm', '0');
                        });
                } else {
                    $q->where('approval_manager', '0');
                }
            })
            ->where('approval_manager', '!=', '2')
            ->where('approval_hrd', '!=', '2')
            ->where('approval_gm', '!=', '2');
        if ($divisi) {
            $spjQuery->whereHas('karyawan', fn($q) => $q->where('divisi', $divisi));
        }
        if ($tahun && $tahun !== 'all' && $tahun !== 'Semua') {
            $spjQuery->whereYear('tanggal_berangkat', $tahun);
        }
        if ($bulan && $bulan !== 'all' && $bulan !== 'Semua') {
            $spjQuery->whereMonth('tanggal_berangkat', $bulan);
        }
        $spjCount = $spjQuery->count();

        return response()->json([
            'success'       => true,
            'cuti_count'    => $cutiCount,
            'barang_count'  => $barangCount,
            'spj_count'     => $spjCount,
            'total_pending' => $cutiCount + $barangCount + $spjCount,
        ]);
    }

    /**
     * Data Cuti for DataTables.
     */
    public function getDataCuti(Request $request)
    {
        $scope = $this->resolveUserScope($request);
        $divisi = $scope['active_division'];

        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan');

        $query = pengajuancuti::with('karyawan')
            ->where('approval_manager', '0');

        if ($divisi) {
            $query->whereHas('karyawan', fn($q) => $q->where('divisi', $divisi));
        }
        if ($tahun && $tahun !== 'all' && $tahun !== 'Semua') {
            $query->whereYear('tanggal_awal', $tahun);
        }
        if ($bulan && $bulan !== 'all' && $bulan !== 'Semua') {
            $query->whereMonth('tanggal_awal', $bulan);
        }

        $items = $query->latest('created_at')->get();

        $data = $items->map(function ($item, $index) use ($scope) {
            $karyawan = $item->karyawan;
            $nama = $karyawan->nama_lengkap ?? '-';
            $kode = $karyawan->kode_karyawan ?? ($karyawan->nip ?? '-');
            $foto = $karyawan->foto ? asset('storage/' . $karyawan->foto) : asset('css/default-profile.jpg');

            $tglAwal = $item->tanggal_awal ? Carbon::parse($item->tanggal_awal)->locale('id')->isoFormat('D MMM Y') : '-';
            $tglAkhir = $item->tanggal_akhir ? Carbon::parse($item->tanggal_akhir)->locale('id')->isoFormat('D MMM Y') : '-';
            $tglPengajuan = $item->created_at ? Carbon::parse($item->created_at)->locale('id')->isoFormat('D MMM Y, HH:mm') : '-';
            $canApprove = $this->canApproveCuti($item, $scope);

            return [
                'index'           => $index + 1,
                'id'              => $item->id,
                'created_at'      => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '',
                'tgl_pengajuan'   => $tglPengajuan,
                'karyawan_id'     => $item->id_karyawan,
                'nama_lengkap'    => $nama,
                'kode_karyawan'   => $kode,
                'foto'            => $foto,
                'divisi'          => $karyawan->divisi ?? '-',
                'jabatan'         => $karyawan->jabatan ?? '-',
                'kontak'          => $item->kontak ?? '-',
                'tipe'            => $item->tipe ?? '-',
                'alasan'          => $item->alasan ?? '-',
                'durasi'          => ($item->durasi ?? 0) . ' Hari',
                'durasi_num'      => (int) ($item->durasi ?? 0),
                'periode'         => $tglAwal . ' s/d ' . $tglAkhir,
                'tanggal_awal'    => $item->tanggal_awal,
                'tanggal_akhir'   => $item->tanggal_akhir,
                'surat_sakit'     => $item->surat_sakit ? asset('storage/' . $item->surat_sakit) : null,
                'status_raw'      => $item->approval_manager,
                'status_label'    => 'Menunggu Persetujuan Manager Divisi',
                'can_approve'     => $canApprove,
                'action_url'      => url('/pengajuancuti/' . $item->id),
                'pdf_url'         => route('pengajuancuti.show', $item->id),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * Data Pengajuan Barang for DataTables.
     */
    public function getDataBarang(Request $request)
    {
        $scope = $this->resolveUserScope($request);
        $divisi = $scope['active_division'];

        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan');

        $query = PengajuanBarang::with(['karyawan', 'tracking', 'detail'])
            ->whereHas('tracking', function ($q) {
                $q->where('tracking', 'LIKE', '%ditinjau%')
                  ->orWhere('tracking', 'LIKE', '%Ditinjau%');
            });

        if ($divisi) {
            $query->whereHas('karyawan', fn($q) => $q->where('divisi', $divisi));
        }
        if ($tahun && $tahun !== 'all' && $tahun !== 'Semua') {
            $query->whereYear('created_at', $tahun);
        }
        if ($bulan && $bulan !== 'all' && $bulan !== 'Semua') {
            $query->whereMonth('created_at', $bulan);
        }

        $items = $query->latest('created_at')->get();

        $data = $items->map(function ($item, $index) use ($scope) {
            $karyawan = $item->karyawan;
            $nama = $karyawan->nama_lengkap ?? '-';
            $kode = $karyawan->kode_karyawan ?? ($karyawan->nip ?? '-');
            $foto = $karyawan->foto ? asset('storage/' . $karyawan->foto) : asset('css/default-profile.jpg');

            $tglPengajuan = $item->created_at ? Carbon::parse($item->created_at)->locale('id')->isoFormat('D MMM Y, HH:mm') : '-';

            // Calculate total and items detail
            $totalHarga = 0;
            $totalQty = 0;
            $itemNames = [];

            if ($item->detail) {
                foreach ($item->detail as $d) {
                    $rawHarga = explode('.', (string) $d->harga)[0];
                    $cleanHarga = (float) preg_replace('/[^\d.]/', '', $rawHarga);
                    $qty = (int) $d->qty;
                    $totalQty += $qty;
                    $totalHarga += ($qty * $cleanHarga);
                    if (!empty($d->nama_barang)) {
                        $itemNames[] = $d->nama_barang . ' (' . $qty . ')';
                    }
                }
            }

            $trackingStatus = $item->tracking->tracking ?? 'Menunggu Review';
            $canApprove = $this->canApproveBarang($item, $scope);

            return [
                'index'          => $index + 1,
                'id'             => $item->id,
                'created_at'     => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '',
                'tgl_pengajuan'  => $tglPengajuan,
                'karyawan_id'    => $item->id_karyawan,
                'nama_lengkap'   => $nama,
                'kode_karyawan'  => $kode,
                'foto'           => $foto,
                'divisi'         => $karyawan->divisi ?? '-',
                'jabatan'        => $karyawan->jabatan ?? '-',
                'tipe'           => $item->tipe ?? '-',
                'total_qty'      => $totalQty,
                'total_harga'    => $totalHarga,
                'total_harga_rp' => 'Rp ' . number_format($totalHarga, 0, ',', '.'),
                'rincian_barang' => !empty($itemNames) ? implode(', ', $itemNames) : '-',
                'tracking'       => $trackingStatus,
                'can_approve'    => $canApprove,
                'invoice'        => $item->invoice ? asset('storage/' . $item->invoice) : null,
                'detail_url'     => url('/pengajuanbarang/' . $item->id),
                'action_url'     => url('/pengajuanbarang/' . $item->id),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * Data SPJ for DataTables.
     */
    public function getDataSPJ(Request $request)
    {
        $scope = $this->resolveUserScope($request);
        $divisi = $scope['active_division'];
        $isGM = $scope['is_gm'];

        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan');

        $query = SuratPerjalanan::with(['karyawan', 'RKM'])
            ->where(function ($q) use ($isGM) {
                if ($isGM) {
                    $q->where('approval_manager', '0')
                        ->orWhere(function ($sub) {
                            $sub->where('tipe', 'Internasional')
                                ->where('approval_manager', '1')
                                ->where('approval_hrd', '1')
                                ->where('approval_gm', '0');
                        });
                } else {
                    $q->where('approval_manager', '0');
                }
            })
            ->where('approval_manager', '!=', '2')
            ->where('approval_hrd', '!=', '2')
            ->where('approval_gm', '!=', '2');

        if ($divisi) {
            $query->whereHas('karyawan', fn($q) => $q->where('divisi', $divisi));
        }
        if ($tahun && $tahun !== 'all' && $tahun !== 'Semua') {
            $query->whereYear('tanggal_berangkat', $tahun);
        }
        if ($bulan && $bulan !== 'all' && $bulan !== 'Semua') {
            $query->whereMonth('tanggal_berangkat', $bulan);
        }

        $items = $query->latest('created_at')->get();

        $data = $items->map(function ($item, $index) use ($scope) {
            $karyawan = $item->karyawan;
            $nama = $karyawan->nama_lengkap ?? '-';
            $kode = $karyawan->kode_karyawan ?? ($karyawan->nip ?? '-');
            $foto = $karyawan->foto ? asset('storage/' . $karyawan->foto) : asset('css/default-profile.jpg');

            $tglBerangkat = $item->tanggal_berangkat ? Carbon::parse($item->tanggal_berangkat)->locale('id')->isoFormat('D MMM Y') : '-';
            $tglKembali = $item->tanggal_kembali ? Carbon::parse($item->tanggal_kembali)->locale('id')->isoFormat('D MMM Y') : '-';
            $tglPengajuan = $item->created_at ? Carbon::parse($item->created_at)->locale('id')->isoFormat('D MMM Y, HH:mm') : '-';

            $jabatanAtasan = ['Office Manager', 'Education Manager', 'SPV Sales', 'Koordinator ITSM', 'Koordinator Office'];
            $pengajuAtasan = in_array($karyawan->jabatan ?? '', $jabatanAtasan);

            $statusText = 'Menunggu Approval Manager Divisi';
            if ($item->approval_manager == '0' && $pengajuAtasan) {
                $statusText = 'Menunggu Approval GM';
            } elseif ($item->tipe === 'Internasional' && $item->approval_manager == '1' && $item->approval_hrd == '1' && $item->approval_gm == '0') {
                $statusText = 'Menunggu Approval GM & Direksi (Internasional)';
            }

            $canApprove = $this->canApproveSPJ($item, $scope);

            return [
                'index'             => $index + 1,
                'id'                => $item->id,
                'created_at'        => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '',
                'tgl_pengajuan'     => $tglPengajuan,
                'karyawan_id'       => $item->id_karyawan,
                'nama_lengkap'      => $nama,
                'kode_karyawan'     => $kode,
                'foto'              => $foto,
                'divisi'            => $karyawan->divisi ?? '-',
                'jabatan'           => $karyawan->jabatan ?? '-',
                'no_surat'          => $item->no_surat ?? ($item->rkm->no_rkm ?? '-'),
                'tujuan'            => $item->kota_tujuan ?? ($item->tujuan ?? '-'),
                'keperluan'         => $item->keperluan ?? ($item->agenda ?? '-'),
                'tipe'              => $item->tipe ?? 'Domestik',
                'periode'           => $tglBerangkat . ' s/d ' . $tglKembali,
                'tanggal_berangkat' => $item->tanggal_berangkat,
                'tanggal_kembali'   => $item->tanggal_kembali,
                'status_label'      => $statusText,
                'approval_manager'  => $item->approval_manager,
                'approval_gm'       => $item->approval_gm,
                'can_approve'       => $canApprove,
                'pdf_url'           => url('/suratperjalanan/' . $item->id),
                'action_url'        => route('suratperjalanan.approval', $item->id),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * Data Semua (Unified Feed) for DataTables.
     */
    public function getDataSemua(Request $request)
    {
        $cutiRes = $this->getDataCuti($request)->getData(true)['data'] ?? [];
        $barangRes = $this->getDataBarang($request)->getData(true)['data'] ?? [];
        $spjRes = $this->getDataSPJ($request)->getData(true)['data'] ?? [];

        $unified = [];

        foreach ($cutiRes as $c) {
            $unified[] = [
                'id'             => $c['id'],
                'kategori'       => 'Cuti',
                'badge_kategori' => '<span class="badge rounded-pill bg-primary px-2 py-1"><i class="bi bi-calendar-check me-1"></i> Cuti</span>',
                'created_at'     => $c['created_at'],
                'tgl_pengajuan'  => $c['tgl_pengajuan'],
                'nama_lengkap'   => $c['nama_lengkap'],
                'kode_karyawan'  => $c['kode_karyawan'],
                'foto'           => $c['foto'],
                'divisi'         => $c['divisi'],
                'jabatan'        => $c['jabatan'],
                'keterangan'     => '<strong>' . $c['tipe'] . ' (' . $c['durasi'] . ')</strong>: ' . e($c['alasan']),
                'status_label'   => '<span class="badge rounded-pill bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> ' . e($c['status_label']) . '</span>',
                'detail_url'     => $c['pdf_url'],
                'can_approve'    => $c['can_approve'] ?? false,
                'type_key'       => 'cuti',
            ];
        }

        foreach ($barangRes as $b) {
            $unified[] = [
                'id'             => $b['id'],
                'kategori'       => 'Barang',
                'badge_kategori' => '<span class="badge rounded-pill bg-info text-dark px-2 py-1"><i class="bi bi-box-seam me-1"></i> Barang</span>',
                'created_at'     => $b['created_at'],
                'tgl_pengajuan'  => $b['tgl_pengajuan'],
                'nama_lengkap'   => $b['nama_lengkap'],
                'kode_karyawan'  => $b['kode_karyawan'],
                'foto'           => $b['foto'],
                'divisi'         => $b['divisi'],
                'jabatan'        => $b['jabatan'],
                'keterangan'     => '<strong>' . $b['tipe'] . ' (' . $b['total_harga_rp'] . ')</strong>: ' . e($b['rincian_barang']),
                'status_label'   => '<span class="badge rounded-pill bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> ' . e($b['tracking']) . '</span>',
                'detail_url'     => $b['detail_url'],
                'can_approve'    => $b['can_approve'] ?? false,
                'type_key'       => 'barang',
            ];
        }

        foreach ($spjRes as $s) {
            $unified[] = [
                'id'             => $s['id'],
                'kategori'       => 'SPJ',
                'badge_kategori' => '<span class="badge rounded-pill bg-success px-2 py-1"><i class="bi bi-send me-1"></i> SPJ</span>',
                'created_at'     => $s['created_at'],
                'tgl_pengajuan'  => $s['tgl_pengajuan'],
                'nama_lengkap'   => $s['nama_lengkap'],
                'kode_karyawan'  => $s['kode_karyawan'],
                'foto'           => $s['foto'],
                'divisi'         => $s['divisi'],
                'jabatan'        => $s['jabatan'],
                'keterangan'     => '<strong>' . $s['tipe'] . ' ke ' . e($s['tujuan']) . '</strong>: ' . e($s['keperluan']) . ' (' . $s['periode'] . ')',
                'status_label'   => '<span class="badge rounded-pill bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> ' . e($s['status_label']) . '</span>',
                'detail_url'     => $s['pdf_url'],
                'can_approve'    => $s['can_approve'] ?? false,
                'type_key'       => 'spj',
            ];
        }

        // Sort by created_at desc
        usort($unified, function ($a, $b) {
            return strcmp($b['created_at'], $a['created_at']);
        });

        // Re-index
        foreach ($unified as $idx => &$row) {
            $row['index'] = $idx + 1;
        }

        return response()->json([
            'success' => true,
            'data'    => $unified,
        ]);
    }
}
