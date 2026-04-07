<?php

namespace App\Http\Controllers;

use App\Models\DokumentasiExam;
use App\Models\Peserta;
use App\Models\Registrasi;
use App\Models\RKM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DaftarPesertaExamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('daftarPesertaExam.index');
    }

    /**
     * Get data for AJAX - Return JSON
     */
    public function getData()
    {
        try {
            $registrasis = Registrasi::with([
                'peserta:id,nama',
                'materi:id,nama_materi',
                'rkm:id,tanggal_akhir',
                'dokumentasiExam'
            ])
                ->select(
                    'registrasis.id',
                    'registrasis.id_peserta',
                    'registrasis.id_materi',
                    'registrasis.id_rkm'
                )
                ->orderBy('registrasis.created_at', 'desc')
                ->get();

            $data = $registrasis->map(function ($item, $index) {
                $dokumen = $item->dokumentasiExam;
                return [
                    'no' => $index + 1,
                    'id' => $item->id,
                    'nama_peserta' => $item->peserta?->nama ?? '-',
                    'nama_materi' => $item->materi?->nama_materi ?? '-',
                    'nama_exam' => $dokumen?->nama_exam ?? '-',
                    'tanggal_perusahaan' => $dokumen?->tanggal_perusahaan ?? '-',
                    'skor' => $dokumen?->skor,
                    'dokumentasi' => $dokumen?->dokumentasi,
                    'invoice' => $dokumen?->invoice ?? '-',
                    'keterangan_lulus' => $dokumen?->keterangan_lulus ?? '-',
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($id)
    {
        $rkm = RKM::with('perusahaan')->findOrFail($id);

        return view('daftarPesertaExam.create', compact('rkm'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'id_rkm' => 'required|exists:r_k_m_s,id',
            'peserta_id' => 'required|array|min:1',
            'peserta_id.*' => 'required|integer|exists:pesertas,id',
        ], [
            'peserta_id.required' => 'Pilih minimal 1 peserta',
            'peserta_id.array' => 'Data peserta tidak valid',
            'peserta_id.min' => 'Pilih minimal 1 peserta',
        ]);

        try {
            // Ambil data RKM
            $rkm = RKM::findOrFail($validated['id_rkm']);

            // Siapkan data untuk registrasi
            $registrasiData = [
                'id_rkm' => $rkm->id,
                'id_materi'     => $rkm->materi_key,
                'id_instruktur' => $rkm->instruktur_key,
                'id_sales'      => $rkm->sales_key,
            ];

            // dd($registrasiData);
            // Loop melalui peserta yang dipilih dan buat record Registrasi
            $createdCount = 0;
            $skippedCount = 0;
            $skippedPeserta = [];

            foreach ($validated['peserta_id'] as $pesertaId) {
                // Cek apakah sudah terdaftar
                $exists = Registrasi::where('id_rkm', $rkm->id)
                    ->where('id_peserta', $pesertaId)
                    ->exists();

                if (!$exists) {
                    // Create registrasi
                    $registrasi = Registrasi::create(array_merge($registrasiData, [
                        'id_peserta' => $pesertaId,
                    ]));
                    
                    // Get peserta name
                    $peserta = Peserta::find($pesertaId);
                    
                    // Create dokumentasi exam automatically
                    DokumentasiExam::create([
                        'id_registrasi' => $registrasi->id,
                        'nama_exam' => $rkm->materi?->nama_materi ?? '',
                        'tanggal_perusahaan' => $rkm->tanggal_akhir,
                    ]);
                    
                    $createdCount++;
                } else {
                    $skippedCount++;
                    $skippedPeserta[] = $pesertaId;
                }
            }

            // Return response dengan pesan
            $message = "Berhasil mendaftarkan $createdCount peserta";
            
            if ($skippedCount > 0) {
                $message .= " (Skipped: $skippedCount peserta sudah terdaftar)";
            }

            return redirect()
                ->route('daftar-peserta-exam.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $registrasi = Registrasi::with([
            'peserta:id,nama',
            'materi:id,nama_materi',
            'rkm:id,tanggal_akhir,tanggal_awal,instruktur_key,perusahaan_key',
            'karyawan:kode_karyawan,nama_lengkap',
            'dokumentasiExam'
        ])->findOrFail($id);
        
        // Get perusahaan from rkm relationship
        $perusahaan = $registrasi->rkm?->perusahaan;
        $instruktur = $registrasi->karyawan;
        $dokumentasi = $registrasi->dokumentasiExam;
        
        return view('daftarPesertaExam.edit', compact('registrasi', 'perusahaan', 'instruktur', 'dokumentasi'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validasi input
        $validated = $request->validate([
            'nama_exam' => 'required|string|max:255',
            'tanggal_pelaksanaan' => 'required|date',
            'skor' => 'nullable|numeric|min:0|max:100',
            'dokumentasi' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'invoice' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'keterangan_lulus' => 'nullable|string|max:255',
        ], [
            'nama_exam.required' => 'Nama exam/kelas wajib diisi',
            'tanggal_pelaksanaan.required' => 'Tanggal pelaksanaan wajib diisi',
            'tanggal_pelaksanaan.date' => 'Format tanggal tidak valid',
            'skor.numeric' => 'Skor harus berupa angka',
            'skor.max' => 'Skor maksimal 100',
            'dokumentasi.mimes' => 'Format file dokumentasi tidak didukung',
            'dokumentasi.max' => 'Ukuran file dokumentasi maksimal 10MB',
            'invoice.mimes' => 'Format file invoice tidak didukung',
            'invoice.max' => 'Ukuran file invoice maksimal 10MB',
        ]);

        try {
            // Get registrasi
            $registrasi = Registrasi::findOrFail($id);
            $dokumentasi = $registrasi->dokumentasiExam ?? new DokumentasiExam();

            // Handle file uploads
            if ($request->hasFile('dokumentasi')) {
                // Delete old file if exists
                if ($dokumentasi->dokumentasi && Storage::exists('public/' . $dokumentasi->dokumentasi)) {
                    Storage::delete('public/' . $dokumentasi->dokumentasi);
                }
                $file_dokumentasi = $request->file('dokumentasi');
                $path_dokumentasi = $file_dokumentasi->store('dokumentasi-exam', 'public');
                $validated['dokumentasi'] = $path_dokumentasi;
            } elseif ($dokumentasi->dokumentasi) {
                // Keep existing file
                $validated['dokumentasi'] = $dokumentasi->dokumentasi;
            }

            if ($request->hasFile('invoice')) {
                // Delete old file if exists
                if ($dokumentasi->invoice && Storage::exists('public/' . $dokumentasi->invoice)) {
                    Storage::delete('public/' . $dokumentasi->invoice);
                }
                $file_invoice = $request->file('invoice');
                $path_invoice = $file_invoice->store('invoice-exam', 'public');
                $validated['invoice'] = $path_invoice;
            } elseif ($dokumentasi->invoice) {
                // Keep existing file
                $validated['invoice'] = $dokumentasi->invoice;
            }

            // Update atau create dokumentasi exam
            if ($dokumentasi->id) {
                $dokumentasi->update(array_merge($validated, [
                    'tanggal_perusahaan' => $validated['tanggal_pelaksanaan'],
                ]));
            } else {
                $dokumentasi->fill(array_merge($validated, [
                    'id_registrasi' => $registrasi->id,
                    'tanggal_perusahaan' => $validated['tanggal_pelaksanaan'],
                ]));
                $dokumentasi->save();
            }

            return redirect()
                ->route('daftar-peserta-exam.index')
                ->with('success', 'Data berhasil diperbarui');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
