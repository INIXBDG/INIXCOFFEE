<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\RKM;
use App\Models\Karyawan;
use App\Models\Peserta;
use App\Models\Registrasi;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    // Halaman list RKM untuk generate sertifikat
    public function index()
    {
        $rkm = RKM::with(['materi', 'perusahaan', 'peluang'])
            ->select('r_k_m_s.*')
            ->whereNotNull('tanggal_awal')
            ->whereNotNull('tanggal_akhir')
            ->orderBy('tanggal_awal', 'desc')
            ->paginate(10);

        return view('office.certificate.index', compact('rkm'));
    }

    // Detail RKM - List Peserta
    public function detail($rkm_id)
    {
        $rkm = RKM::with(['materi', 'perusahaan'])->findOrFail($rkm_id);

        // Ambil id_peserta yang terdaftar di RKM ini
        $pesertaIds = Registrasi::where('id_rkm', $rkm_id)
            ->pluck('id_peserta')
            ->toArray();

        // Ambil data peserta yang hanya ikut RKM ini
        // 1. Ambil data registrasi peserta untuk RKM ini + join ambil nama peserta
        $peserta = Registrasi::where('id_rkm', $rkm_id)
            ->join('pesertas', 'pesertas.id', '=', 'registrasis.id_peserta')
            ->select('registrasis.id_peserta', 'pesertas.nama')
            ->orderBy('pesertas.nama')
            ->get();

        // 2. Ambil peserta yang sudah punya sertifikat untuk RKM ini
        $certificateIds = Certificate::join('registrasis', 'registrasis.id_peserta', '=', 'certificates.id_peserta')
            ->where('registrasis.id_rkm', $rkm_id)
            ->pluck('registrasis.id_peserta')
            ->toArray();

        return view('office.certificate.detail', compact('rkm', 'peserta', 'certificateIds'));
    }

    public function create($rkm_id, $peserta_id)
    {
        $rkm = RKM::with(['materi', 'perusahaan', 'peluang'])->findOrFail($rkm_id);
        $peserta = Peserta::findOrFail($peserta_id);

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

        if ($existingCert) {
            return redirect()
                ->route('office.certificate.show', $existingCert->id)
                ->with('info', 'Sertifikat sudah ada.');
        }

        return view('office.certificate.create', compact('rkm', 'peserta'));
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
        ]);

        $nomorSertifikat = Certificate::generateNomorSertifikat();

        $certificate = Certificate::create([
            'nomor_sertifikat' => $nomorSertifikat,
            'rkm_id' => $request->rkm_id,
            'id_peserta' => $request->id_peserta,
            'nama_peserta' => $request->nama_peserta,
            'nama_materi' => $request->nama_materi,
            'tanggal_pelatihan' => $request->tanggal_awal . ' - ' . $request->tanggal_akhir,
        ]);

        $penandatangan = Karyawan::find(4);

        // Generate PDF
        $pdf = Pdf::loadView('office.certificate.pdf', compact('certificate', 'penandatangan'))
            ->setPaper('a4', 'landscape');

        // Pastikan folder certificates ada
        if (!Storage::exists('public/certificates')) {
            Storage::makeDirectory('public/certificates');
        }

        // Ganti "/" dengan "-" untuk nama file yang aman
        $safeFilename = str_replace('/', '-', $nomorSertifikat) . '.pdf';
        
        // Simpan file dengan path yang benar
        $filename = 'certificates/' . $safeFilename;
        Storage::put('public/' . $filename, $pdf->output());
        
        // Update database dengan path relatif (tanpa 'public/')
        $certificate->update(['pdf_path' => $filename]);

        return redirect()
            ->route('office.certificate.show', $certificate->id)
            ->with('success', 'Sertifikat berhasil di-generate!');
    }

    // Tampilkan detail sertifikat
    public function show($id)
    {
        $certificate = Certificate::with(['rkm.materi', 'peserta'])->findOrFail($id);
        $penandatangan = Karyawan::find(4);

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
        $certificate = Certificate::where('rkm_id', $rkm_id)
            ->where('id_peserta', $peserta_id)
            ->firstOrFail();

        if ($certificate->pdf_path && Storage::exists('public/' . $certificate->pdf_path)) {
            // Ganti "/" dengan "-" untuk nama file download yang aman
            $downloadName = str_replace('/', '-', $certificate->nomor_sertifikat) . '.pdf';
            return Storage::download('public/' . $certificate->pdf_path, $downloadName);
        }

        return back()->with('error', 'File PDF tidak ditemukan');
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
}