<?php

namespace App\Http\Controllers\office;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\CertificateSummary;
use App\Models\eksam as ModelsEksam;
use App\Models\RKM;
use App\Models\karyawan;
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

        $allCertificates = Certificate::whereIn('rkm_id', $rkmIds)
            ->whereIn('id_peserta', $peserta->pluck('id_peserta'))
            ->get();

        $certificates = $allCertificates->keyBy('id_peserta');

        // Hanya hitung sebagai "Sudah Generate" jika nomor_sertifikat dan tanggal_pelatihan sudah terisi
        $certificateIds = $allCertificates
            ->filter(fn ($cert) => !empty($cert->nomor_sertifikat) && !empty($cert->tanggal_pelatihan))
            ->pluck('id_peserta')
            ->unique()
            ->values()
            ->toArray();

        return view('office.certificate.detail', compact(
            'rkm',
            'peserta',
            'certificateIds',
            'certificates',
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

        $lastCert = Certificate::whereNotNull('nomor_sertifikat')
            ->where('nomor_sertifikat', '!=', '')
            ->orderBy('nomor_sertifikat', 'desc')
            ->first();

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

        // Cek apakah sudah ada record sertifikat yang baru berisi kategori_exam (belum di-generate)
        $certificate = Certificate::where('rkm_id', $request->rkm_id)
            ->where('id_peserta', $request->id_peserta)
            ->where(function ($query) {
                $query->whereNull('nomor_sertifikat')
                    ->orWhereNull('tanggal_pelatihan');
            })
            ->first();

        $data = [
            'nomor_sertifikat' => $request->nomor_sertifikat,
            'rkm_id' => $request->rkm_id,
            'id_peserta' => $request->id_peserta,
            'nama_peserta' => $request->nama_peserta,
            'nama_materi' => $request->nama_materi,
            'tanggal_pelatihan' => $request->tanggal_awal . ' - ' . $request->tanggal_akhir,
            'tanggal_pelatihan2' => $request->filled('tanggal_awal2') && $request->filled('tanggal_akhir2')
                ? $request->tanggal_awal2 . ' - ' . $request->tanggal_akhir2
                : null,
        ];

        if ($certificate) {
            $certificate->update($data);
        } else {
            $existingKategori = Certificate::where('rkm_id', $request->rkm_id)
                ->where('id_peserta', $request->id_peserta)
                ->whereNotNull('kategori_exam')
                ->value('kategori_exam');

            if ($existingKategori) {
                $data['kategori_exam'] = $existingKategori;
            }

            $certificate = Certificate::create($data);
        }

        $penandatangan = karyawan::find(4);

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
        $penandatangan = karyawan::find(4);
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
            ->whereNotNull('nomor_sertifikat')
            ->whereNotNull('tanggal_pelatihan')
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
        $penandatangan = karyawan::find(4);

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

        // Resolusi rentang tanggal berdasrkan period
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

        $regDigitalD = CertificateSummary::where('type', 'Reg Digital')
            ->whereBetween($dateColumn, [$startDate, $endDate])
            ->get()
            ->each(fn ($r) => $r->setAttribute('source', 'summary'));

        $webinarD = CertificateSummary::where('type', 'Webinar')
            ->whereBetween($dateColumn, [$startDate, $endDate])
            ->get()
            ->each(fn ($r) => $r->setAttribute('source', 'summary'));

        $summaryByKategori = $regDigitalD
            ->concat($webinarD)
            ->filter(fn ($s) => !empty($s->kategori))
            ->groupBy('kategori');

        $bandungD = Certificate::whereNotNull('nomor_sertifikat')
            ->whereNotNull('tanggal_pelatihan')
            ->whereRaw(
                "STR_TO_DATE(SUBSTRING_INDEX(tanggal_pelatihan, ' - ', 1), '%Y-%m-%d') BETWEEN ? AND ?",
                [$startDate->toDateString(), $endDate->toDateString()]
            )
            ->get();

        $unGeneratedCertWithKategori = Certificate::where(function ($query) {
                $query->whereNull('nomor_sertifikat')
                    ->orWhereNull('tanggal_pelatihan');
            })
            ->whereNotNull('kategori_exam')
            ->whereBetween($dateColumn, [$startDate, $endDate])
            ->get();

        $certByKategori = $bandungD
            ->concat($unGeneratedCertWithKategori)
            ->filter(fn ($c) => !empty($c->kategori_exam))
            ->groupBy('kategori_exam');

        // Bandung = Certificate asli + summary berkategori 'Bandung' (+1)
        $bandungD = $bandungD
            ->concat($summaryByKategori->get('Bandung', collect()))
            ->values();

        $semuaExam = $this->getPesertaExam(
            ['Authorize', 'BNSP', 'Inixcert'],
            $dateColumn,
            $startDate,
            $endDate
        );

        $byKategori = $semuaExam->groupBy('kategori_exam');

        $authorizedD = collect($byKategori->get('Authorize', collect()))
            ->concat($certByKategori->get('Authorize', collect()))
            ->concat($summaryByKategori->get('Authorize', collect()))
            ->values();

        $bnspD = collect($byKategori->get('BNSP', collect()))
            ->concat($certByKategori->get('BNSP', collect()))
            ->concat($summaryByKategori->get('BNSP', collect()))
            ->values();

        $inixcertD = collect($byKategori->get('Inixcert', collect()))
            ->concat($certByKategori->get('Inixcert', collect()))
            ->concat($summaryByKategori->get('Inixcert', collect()))
            ->values();

        $internasionalD = collect($certByKategori->get('Internasional', collect()))->values();

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
                    $peserta->tanggal_awal  = $rkm->tanggal_awal;
                    $peserta->tanggal_akhir = $rkm->tanggal_akhir;
                    $peserta->materi        = $rkm->materi->nama_materi ?? null;
                    return $peserta;
                });
            })
            ->filter()
            ->values();

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
                'data'  => $inixcertD,
            ],

            'internasional' => [
                'count' => $internasionalD->count(),
                'data'  => $internasionalD,
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

   private function getPesertaExam(array $kategori, $dateColumn, $startDate, $endDate)
    {
        return ModelsEksam::with([
                'rkm.materi',
                'registexam.peserta',
                'registexam.dokumentasiExam',
            ])
            // hanya eksam yang punya dokumentasi exam berkategori sesuai
            ->whereHas('registexam.dokumentasiExam', fn ($d) => $d->whereIn('kategori_exam', $kategori))
            ->whereBetween($dateColumn, [$startDate, $endDate])
            ->get()
            ->flatMap(function ($eksam) use ($kategori) {
                $materi = $eksam->materi ?? null;
                return $eksam->registexam->map(function ($reg) use ($materi, $kategori, $eksam) {
                    if (!$reg->peserta) {
                        return null;
                    }

                    // hanya dari dokumentasi exam, tanpa fallback ke materi
                    $kategoriReg = $reg->dokumentasiExam->kategori_exam ?? null;

                    if (!in_array($kategoriReg, $kategori, true)) {
                        return null;
                    }

                    $peserta = clone $reg->peserta;
                    $peserta->tanggal_exam  = $reg->tanggal_exam ?? null;
                    $peserta->materi        = $materi;
                    $peserta->kategori_exam = $kategoriReg;
                    $peserta->eksam_id      = $eksam->id;   // bantu debug
                    $peserta->source        = 'eksam';      // bantu debug

                    return $peserta;
                });
            })
            ->filter()
            ->values();
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
            'kategori' => 'nullable|in:BNSP,Inixcert,Authorize,Bandung'
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
            'kategori' => 'nullable|in:BNSP,Inixcert,Authorize,Bandung'
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

    public function updateKategoriExam(Request $request, $rkm_id, $peserta_id)
    {
        $validated = $request->validate([
            'kategori_exam' => 'required|in:BNSP,Internasional,Authorize,Inixcert',
        ]);

        $rkm = RKM::with('materi')->findOrFail($rkm_id);
        $peserta = Peserta::findOrFail($peserta_id);

        $certificates = Certificate::where('rkm_id', $rkm_id)
            ->where('id_peserta', $peserta_id)
            ->get();

        if ($certificates->isEmpty()) {
            $certificate = Certificate::create([
                'nomor_sertifikat' => null,
                'rkm_id' => $rkm->id,
                'id_peserta' => $peserta->id,
                'nama_peserta' => $peserta->nama,
                'nama_materi' => $rkm->materi->nama_materi ?? '-',
                'tanggal_pelatihan' => null,
                'kategori_exam' => $validated['kategori_exam'],
            ]);
        } else {
            Certificate::where('rkm_id', $rkm_id)
                ->where('id_peserta', $peserta_id)
                ->update([
                    'kategori_exam' => $validated['kategori_exam'],
                ]);

            $certificate = $certificates->first();
            $certificate->kategori_exam = $validated['kategori_exam'];
        }

        return response()->json([
            'success' => true,
            'message' => 'Kategori exam berhasil diperbarui.',
            'kategori_exam' => $certificate->kategori_exam,
        ]);
    }
}
