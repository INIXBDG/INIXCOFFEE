<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\certificate_summary;
use App\Models\CertificateSummary;
use App\Models\eksam as ModelsEksam;
use App\Models\RKM;
use App\Models\Karyawan;
use App\Models\Materi;
use App\Models\Perusahaan;
use App\Models\Peserta;
use App\Models\Registrasi;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View Inixcert', ['only' => ['index', 'getData', 'detail', 'certificateSummary', 'certificateSummaryJson']]);
        $this->middleware('permission:Store Inixcert', ['only' => ['create', 'store', 'storeSummary', 'updateSummary']]);
        $this->middleware('permission:Delete Inixcert', ['only' => ['delete', 'deleteSummary']]);
    }

    public function index()
    {
        $materis = Materi::select('id', 'nama_materi', 'kode_materi')
            ->orderBy('nama_materi')
            ->get();

        $perusahaans = Perusahaan::select('id', 'nama_perusahaan')
            ->orderBy('nama_perusahaan')
            ->get();

        return view('office.certificate.index', compact('materis', 'perusahaans'));
    }

    public function getData(Request $request)
    {
        $query = RKM::with(['materi:id,nama_materi,kode_materi', 'perusahaan:id,nama_perusahaan'])
            ->whereNotNull('tanggal_awal')
            ->whereNotNull('tanggal_akhir')
            ->where('status', '0');

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas(
                    'materi',
                    fn($m) =>
                    $m->where('nama_materi', 'LIKE', "%{$search}%")
                        ->orWhere('kode_materi', 'LIKE', "%{$search}%")
                )
                    ->orWhereHas(
                        'perusahaan',
                        fn($p) =>
                        $p->where('nama_perusahaan', 'LIKE', "%{$search}%")
                    );
            });
        }

        if ($materi_id = $request->materi_id) {
            $query->where('materi_id', $materi_id);
        }

        if ($start = $request->start_date) {
            $query->whereDate('tanggal_awal', '>=', $start);
        }
        if ($end = $request->end_date) {
            $query->whereDate('tanggal_akhir', '<=', $end);
        }

        $rkm = $query->orderBy('tanggal_awal', 'desc')
            ->paginate($request->per_page ?? 15);

        $grouped = $rkm->getCollection()->groupBy(function ($item) {
            return $item->materi_id . '-' . $item->metode_kelas . '-' . $item->tanggal_awal . '-' . $item->tanggal_akhir;
        });

        $data = $grouped->map(function ($group) {
            $first = $group->first();

            return [
                'id' => $first->id,

                'materi_nama' => $first->materi?->nama_materi ?? '-',
                'materi_kode' => $first->materi?->kode_materi ?? '',
                'perusahaan_nama' => $group->pluck('perusahaan.nama_perusahaan')
                    ->filter()
                    ->unique()
                    ->implode(', '),
                'tanggal_awal' => \Carbon\Carbon::parse($first->tanggal_awal)->format('d M Y'),
                'tanggal_akhir' => \Carbon\Carbon::parse($first->tanggal_akhir)->format('d M Y'),
            ];
        })->values();

        $rkm->setCollection($data);

        return response()->json([
            'data' => $rkm->items(),
            'pagination' => [
                'total'        => $rkm->total(),
                'from'         => $rkm->firstItem(),
                'to'           => $rkm->lastItem(),
                'current_page' => $rkm->currentPage(),
                'last_page'    => $rkm->lastPage(),
                'per_page'     => $rkm->perPage(),
            ]
        ]);
    }

    public function detail($rkm_id)
    {
        $rkm = RKM::with([
            'materi',
            'perusahaan'
        ])->findOrFail($rkm_id);

        $rkmIds = RKM::where('materi_key', $rkm->materi_key)
            ->whereBetween('tanggal_awal', [
                $rkm->tanggal_awal,
                $rkm->tanggal_akhir
            ])
            ->whereDoesntHave('peluang', function ($query) {
                $query->where('tentatif', 1);
            })
            ->pluck('id');
        
        $perusahaan = RKM::with('perusahaan')->where('materi_key', $rkm->materi_key)
            ->whereBetween('tanggal_awal', [
                $rkm->tanggal_awal,
                $rkm->tanggal_akhir
            ])            
            ->whereDoesntHave('peluang', function ($query) {
                $query->where('tentatif', 1);
            })
            ->get()
            ->pluck('perusahaan.nama_perusahaan')
            ->filter()
            ->unique()
            ->values();        

        $peserta = Registrasi::whereIn('id_rkm', $rkmIds)
            ->join('pesertas', 'pesertas.id', '=', 'registrasis.id_peserta')
            ->select(
                'registrasis.id_peserta',
                'registrasis.id_rkm',
                'pesertas.nama',
                'pesertas.email'
            )
            ->distinct()
            ->orderBy('pesertas.nama')
            ->get();

        $certificateIds = Certificate::join(
                'registrasis',
                'registrasis.id_peserta',
                '=',
                'certificates.id_peserta'
            )
            ->whereIn('certificates.rkm_id', $rkmIds)
            ->whereIn('certificates.id_peserta', $peserta->pluck('id_peserta'))
            ->pluck('registrasis.id_peserta')
            ->unique()
            ->toArray();

        return view('office.certificate.detail', compact(
            'rkm',
            'peserta',
            'certificateIds',
            'perusahaan'
        ));
    }


    public function create($rkm_id, $peserta_id)
    {
        $rkm = RKM::with(['materi', 'perusahaan', 'peluang'])->findOrFail($rkm_id);
        $peserta = Peserta::with('perusahaan')->findOrFail($peserta_id);

        $isRegistered = Registrasi::where('id_rkm', $rkm_id)
            ->where('id_peserta', $peserta_id)
            ->exists();

        if (!$isRegistered) {
            return redirect()
                ->route('office.certificate.detail', $rkm_id)
                ->with('error', 'Peserta tidak terdaftar di RKM ini.');
        }

        $existingCert = Certificate::where('rkm_id', $rkm_id)
            ->where('id_peserta', $peserta_id)
            ->first();

        $initialNumber = 26082;

        $lastCert = Certificate::orderBy('nomor_sertifikat', 'desc')->first();

        if ($lastCert && !empty($lastCert->nomor_sertifikat)) {
            $lastNumber = (int) substr($lastCert->nomor_sertifikat, -6);
            $number = $lastNumber + 1;
        } else {
            $number = $initialNumber + 1;
        }

        $nomorSertifikatBaru = sprintf('%06d', $number);

        return view('office.certificate.create', compact('rkm', 'peserta', 'nomorSertifikatBaru'));
    }

    // Proses generate sertifikat dan simpan ke database
    public function store(Request $request)
    {
        $request->validate([
            'rkm_id' => 'required|exists:r_k_m_s,id',
            'id_peserta' => 'required|exists:pesertas,id',
            'nama_peserta' => 'required|string|max:255',
            'nama_materi' => 'required|string|max:255',
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
            'tanggal_awal2' => 'nullable|date',
            'tanggal_akhir2' => 'nullable|date|after_or_equal:tanggal_awal2',
        ]);


        $certificate = Certificate::create([
            'nomor_sertifikat' => $request->nomor_sertifikat,
            'rkm_id' => $request->rkm_id,
            'id_peserta' => $request->id_peserta,
            'nama_peserta' => $request->nama_peserta,
            'nama_materi' => $request->nama_materi,
            'tanggal_pelatihan' => $request->tanggal_awal . ' - ' . $request->tanggal_akhir,
            'tanggal_pelatihan2' => $request->filled('tanggal_awal2') && $request->filled('tanggal_akhir2') ? $request->tanggal_awal2 . ' - ' . $request->tanggal_akhir2 : null,
        ]);

        $penandatangan = Karyawan::find(4);

        // Generate PDF
        $pdf = Pdf::loadView('office.certificate.pdf', compact('certificate', 'penandatangan'))
            ->setPaper('a4', 'landscape');

        if (!Storage::exists('public/certificates')) {
            Storage::makeDirectory('public/certificates');
        }

        $safeFilename = str_replace('/', '-', $request->nomor_sertifikat) . '.pdf';

        $filename = 'certificates/' . $safeFilename;
        Storage::put('public/' . $filename, $pdf->output());

        $certificate->update(['pdf_path' => $filename]);

        return redirect()
            ->route('office.certificate.show', $certificate->id)
            ->with('success', 'Sertifikat berhasil di-generate!');
    }

    public function delete($rkm_id, $peserta_id)
    {
        Log::info('Delete sertifikat dipanggil', [
            'rkm_id' => $rkm_id,
            'peserta_id' => $peserta_id,
        ]);

        $deleted = Certificate::where('rkm_id', $rkm_id)
            ->where('id_peserta', $peserta_id)
            ->delete();

        Log::info('Hasil delete sertifikat', [
            'rkm_id' => $rkm_id,
            'peserta_id' => $peserta_id,
            'deleted_rows' => $deleted,
        ]);

        return back()->with('success', 'Sertifikat berhasil dihapus!');
    }

    // Tampilkan detail sertifikat
    public function show($id)
    {
        $certificate = Certificate::with(['rkm.materi', 'peserta'])->findOrFail($id);
        $penandatangan = Karyawan::find(4);
        // dd($certificate);

        return view('office.certificate.show', compact('certificate', 'penandatangan'));
    }

    // Download PDF sertifikat
    public function download($id)
    {
        $certificate = Certificate::findOrFail($id);

        if ($certificate->pdf_path && Storage::exists('public/' . $certificate->pdf_path)) {
            // Ganti "/" dengan "-" untuk nama file download yang aman
            $downloadName = str_replace('/', '-', $certificate->nomor_sertifikat) . '.pdf';
            return Storage::download('public/' . $certificate->pdf_path, $downloadName);
        }

        return back()->with('error', 'File PDF tidak ditemukan');
    }

    // Download PDF by RKM & Peserta
    public function downloadByPeserta($rkm_id, $peserta_id)
    {
        $certificates = Certificate::where('rkm_id', $rkm_id)
            ->where('id_peserta', $peserta_id)
            ->get();

        if ($certificates->count() === 0) {
            return back()->with('error', 'File PDF tidak ditemukan');
        }

        // Jika hanya satu sertifikat, download langsung
        if ($certificates->count() === 1) {
            $certificate = $certificates->first();
            if ($certificate->pdf_path && Storage::exists('public/' . $certificate->pdf_path)) {
                $downloadName = str_replace('/', '-', $certificate->nomor_sertifikat) . '.pdf';
                return Storage::download('public/' . $certificate->pdf_path, $downloadName);
            }
            return back()->with('error', 'File PDF tidak ditemukan');
        }

        // Jika lebih dari satu, buat ZIP
        $zipFilename = 'sertifikat_' . $rkm_id . '_' . $peserta_id . '_' . date('YmdHis') . '.zip';
        $zipPath = storage_path('app/public/' . $zipFilename);

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== TRUE) {
            return back()->with('error', 'Gagal membuat file ZIP');
        }

        $added = false;
        foreach ($certificates as $certificate) {
            if ($certificate->pdf_path && Storage::exists('public/' . $certificate->pdf_path)) {
                $pdfFullPath = storage_path('app/public/' . $certificate->pdf_path);
                $downloadName = str_replace('/', '-', $certificate->nomor_sertifikat) . '.pdf';
                $zip->addFile($pdfFullPath, $downloadName);
                $added = true;
            }
        }
        $zip->close();

        if (!$added) {
            // Hapus file ZIP jika tidak ada file yang ditambahkan
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }
            return back()->with('error', 'Tidak ada file PDF yang ditemukan');
        }

        // Download ZIP
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    // Preview PDF di browser
    public function preview($id)
    {
        $certificate = Certificate::with(['rkm.materi'])->findOrFail($id);
        $penandatangan = Karyawan::find(4);

        $pdf = Pdf::loadView('office.certificate.pdf', compact('certificate', 'penandatangan'))
            ->setPaper('a4', 'landscape');

        // Ganti "/" dengan "-" untuk nama file stream yang aman
        $streamName = str_replace('/', '-', $certificate->nomor_sertifikat) . '.pdf';
        return $pdf->stream($streamName);
    }

    public function certificateSummary()
    {
        $perusahaans = Perusahaan::orderBy('nama_perusahaan')->get();
        $materis = Materi::orderBy('nama_materi')->get();
        return view('office.certificate.rekap', compact('perusahaans', 'materis'));
    }

    public function certificateSummaryJson(Request $request)
    {
        $period  = $request->input('period', 'month');
        $year    = (int) $request->input('year', now()->year);
        $month   = (int) $request->input('month', now()->month);
        $quarter = (int) $request->input('quarter', ceil(now()->month / 3));

        $dateColumn = 'created_at';

        // Resolusi rentang tanggal berdasarkan period
        if ($period === 'quarter') {
            $startDate = Carbon::create($year, 1, 1)
                ->addMonths(($quarter - 1) * 3)
                ->startOfMonth();

            $endDate = Carbon::create($year, 1, 1)
                ->addMonths(($quarter - 1) * 3 + 2)
                ->endOfMonth();
        } elseif ($period === 'year') {
            $startDate = Carbon::create($year, 1, 1)->startOfYear();
            $endDate   = Carbon::create($year, 12, 31)->endOfYear();
        } else {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate   = Carbon::create($year, $month, 1)->endOfMonth();
        }

        // ==========================================
        // CERTIFICATE SUMMARY
        // ==========================================

        $regDigitalD = CertificateSummary::where('type', 'Reg Digital')
            ->whereBetween($dateColumn, [$startDate, $endDate])
            ->get();

        $webinarD = CertificateSummary::where('type', 'Webinar')
            ->whereBetween($dateColumn, [$startDate, $endDate])
            ->get();

        // ==========================================
        // CERTIFICATE BANDUNG
        // ==========================================

        $bandungD = Certificate::whereBetween($dateColumn, [$startDate, $endDate])
            ->get();

        // ==========================================
        // AUTHORIZED
        // ModelsEksam -> registexam -> peserta
        // Tanggal (tanggal_mulai/tanggal_selesai) ditempel ke tiap peserta SEBELUM
        // di-flatten, supaya info periode-nya tidak hilang.
        // ==========================================

        $authorizedD = ModelsEksam::with('rkm.materi', 'registexam.peserta', 'registexam')
            ->whereHas('rkm.materi', function ($query) {
                $query->where('kategori_exam', 'Authorize');
            })
            ->whereBetween($dateColumn, [$startDate, $endDate])
            ->get()
            ->flatMap(function ($eksam) {
                return collect($eksam->registexam)->map(function ($reg) use ($eksam) {
                    $peserta = $reg->peserta;
                    if (!$peserta) {
                        return null;
                    }
                    $peserta->tanggal_exam = $reg->tanggal_exam ?? null;
                    $peserta->materi = $eksam->materi ?? null;
                    return $peserta;
                });
            })
            ->filter()
            ->values();

        // ==========================================
        // BNSP
        // ModelsEksam -> registexam -> peserta
        // ==========================================

        $bnspD = ModelsEksam::with('rkm.materi', 'registexam.peserta')
            ->whereHas('rkm.materi', function ($query) {
                $query->where('kategori_exam', 'BNSP');
            })
            ->whereBetween($dateColumn, [$startDate, $endDate])
            ->get()
            ->flatMap(function ($eksam) {
                return collect($eksam->registexam)->map(function ($reg) use ($eksam) {
                    $peserta = $reg->peserta;
                    if (!$peserta) {
                        return null;
                    }
                    $peserta->tanggal_exam = $reg->tanggal_exam ?? null;
                    $peserta->materi = $eksam->materi ?? null;
                    return $peserta;
                });
            })
            ->filter()
            ->values();

        // ==========================================
        // INIXCERT
        // ModelsEksam -> registexam -> peserta
        // ==========================================

        $inixcertD = ModelsEksam::with('rkm.materi', 'registexam.peserta')
            ->whereHas('rkm.materi', function ($query) {
                $query->where('kategori_exam', 'Inixcert');
            })
            ->whereBetween($dateColumn, [$startDate, $endDate])
            ->get()
            ->flatMap(function ($eksam) {
                return collect($eksam->registexam)->map(function ($reg) use ($eksam) {
                    $peserta = $reg->peserta;
                    if (!$peserta) {
                        return null;
                    }
                    $peserta->tanggal_exam = $reg->tanggal_exam ?? null;
                    $peserta->materi = $eksam->materi ?? null;
                    return $peserta;
                });
            })
            ->filter()
            ->values();

        // ==========================================
        // WORKSHOP
        // RKM -> registrasi -> peserta
        // Tanggal (tanggal_awal/tanggal_akhir) ditempel ke tiap peserta dari RKM-nya
        // ==========================================

        $workshopD = RKM::with('registrasi.peserta')
            ->where('status', '0')
            ->where('event', 'Workshop')
            ->whereBetween($dateColumn, [$startDate, $endDate])
            ->get()
            ->flatMap(function ($rkm) {
                return collect($rkm->registrasi)->map(function ($reg) use ($rkm) {
                    $peserta = $reg->peserta;
                    if (!$peserta) {
                        return null;
                    }
                    $peserta->tanggal_awal = $rkm->tanggal_awal;
                    $peserta->tanggal_akhir = $rkm->tanggal_akhir;
                    $peserta->materi = $rkm->materi->nama_materi ?? null;
                    return $peserta;
                });
            })
            ->filter()
            ->values();

        // ==========================================
        // RETURN
        // ==========================================

        return response()->json([
            'filter' => [
                'period'     => $period,
                'year'       => $year,
                'month'      => $period === 'month' ? $month : null,
                'quarter'    => $period === 'quarter' ? $quarter : null,
                'start_date' => $startDate->toDateString(),
                'end_date'   => $endDate->toDateString(),
            ],

            'regDigital' => [
                'count' => $regDigitalD->count(),
                'data'  => $regDigitalD,
            ],

            'authorized' => [
                'count' => $authorizedD->count(),
                'data'  => $authorizedD,
            ],

            'bandung' => [
                'count' => $bandungD->count(),
                'data'  => $bandungD,
            ],

            'bnsp' => [
                'count' => $bnspD->count(),
                'data'  => $bnspD,
            ],

            'inixcert' => [
                'count' => $inixcertD->count(),
                'data' => $inixcertD,
            ],

            'workshop' => [
                'count' => $workshopD->count(),
                'data'  => $workshopD,
            ],

            'webinar' => [
                'count' => $webinarD->count(),
                'data'  => $webinarD,
            ],
        ]);
    }

    public function storeSummary(Request $request)
    {
        $validated = $request->validate([
            'type' => 'nullable|in:Reg Digital,Webinar',
            'no_sertifikat' => 'nullable|string|max:255',
            'nama_peserta' => 'required|string|max:255',
            'perusahaan' => 'required|string|max:255',
            'materi' => 'required|string|max:255',
            'awal_training' => 'required|date',
            'akhir_training' => 'required|date|after_or_equal:awal_training',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        CertificateSummary::create($validated);

        return redirect()->back()->with('success', 'Certificate summary created successfully.');
    }


    public function updateSummary(Request $request, $id)
    {
        $validated = $request->validate([
            'type' => 'nullable|in:Reg Digital,Webinar',
            'no_sertifikat' => 'nullable|string|max:255',
            'nama_peserta' => 'required|string|max:255',
            'perusahaan' => 'required|string|max:255',
            'materi' => 'required|string|max:255',
            'awal_training' => 'required|date',
            'akhir_training' => 'required|date|after_or_equal:awal_training',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        $summary = CertificateSummary::findOrFail($id);

        $summary->update($validated);

        return redirect()->back()->with('success', 'Certificate summary updated successfully.');
    }


    public function deleteSummary($id)
    {
        $summary = CertificateSummary::findOrFail($id);

        $summary->delete();

        return redirect()->back()->with('success', 'Certificate summary deleted successfully.');
    }
}
