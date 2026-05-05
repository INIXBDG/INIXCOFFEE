<?php

namespace App\Http\Controllers;

use App\Models\DokumentasiExam;
use App\Models\Peserta;
use App\Models\Registrasi;
use App\Models\RKM;
use App\Models\registexam;
use App\Models\Perusahaan;
use App\Models\eksam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DaftarPesertaExamController extends Controller
{
    public function index()
    {
        return view('daftarPesertaExam.index');
    }

    public function getData()
    {
        try {
            $registExams = registexam::with([
                'peserta:id,nama',
                'exam.materi:id,nama_materi',
                'exam.rkm:id,perusahaan_key'
            ])
            ->whereIn('id', function ($query) {
                $query->select('id_registrasi')
                      ->from('dokumentasi_exams');
            })
            ->latest()
            ->get();

            $data = $registExams->map(function ($item, $index) {
                $dokumen = DokumentasiExam::where('id_registrasi', $item->id)->first();

                return [
                    'no' => $index + 1,
                    'id' => $item->id,
                    'nama_peserta' => $item->peserta?->nama ?? '-',
                    'nama_materi' => $item->exam?->materi?->nama_materi ?? ($item->exam?->rkm?->materi?->nama_materi ?? '-'),
                    'nama_exam' => $dokumen?->nama_exam ?? '-',
                    'tanggal_pelaksanaan' => $dokumen?->tanggal_pelaksanaan ?? '-',
                    'skor' => $dokumen?->skor ?? '-',
                    'dokumentasi' => $dokumen?->dokumentasi,
                    'invoice' => $dokumen?->invoice ?? '-',
                    'keterangan_lulus' => $dokumen?->keterangan_lulus ?? 'Belum Exam',
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

    public function create($id)
    {
        $exam = eksam::findOrFail($id);
        $idPerusahaan = $exam->rkm ? $exam->rkm->perusahaan_key : null;
        if (!$idPerusahaan) {
            return redirect()->back()->with('error', 'Data RKM atau Perusahaan tidak ditemukan pada pengajuan exam ini.');
        }
        $perusahaan = Perusahaan::with('peserta')->find($idPerusahaan);

        return view('daftarPesertaExam.create', compact('exam', 'perusahaan'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_exam' => 'required|exists:eksams,id',
            'peserta_id' => 'required|array|min:1',
            'peserta_id.*' => 'required|integer|exists:pesertas,id',
        ], [
            'peserta_id.required' => 'Pilih minimal 1 peserta',
            'peserta_id.array' => 'Data peserta tidak valid',
            'peserta_id.min' => 'Pilih minimal 1 peserta',
        ]);

        try {
            $exam = eksam::with('rkm.materi')->findOrFail($validated['id_exam']);

            $createdCount = 0;
            $skippedCount = 0;

            foreach ($validated['peserta_id'] as $pesertaId) {
                $exists = registexam::where('id_exam', $exam->id)
                    ->where('id_peserta', $pesertaId)
                    ->exists();

                if (!$exists) {
                    $peserta = Peserta::find($pesertaId);

                    $registExam = registexam::create([
                        'id_exam'      => $exam->id,
                        'id_peserta'   => $pesertaId,
                        'email'        => $peserta->email,
                        'kode_exam'    => $exam->kode_exam,
                        'tanggal_exam' => now()->format('Y-m-d'),
                        'pukul'        => now()->format('H:i'),
                    ]);

                    DokumentasiExam::create([
                        'id_registrasi'       => $registExam->id,
                        'nama_exam'           => $exam->materi?->nama_materi ?? ($exam->rkm?->materi?->nama_materi ?? ''),
                        'tanggal_pelaksanaan' => now()->format('Y-m-d'),
                    ]);

                    $createdCount++;
                } else {
                    $registExam = registexam::where('id_peserta', $pesertaId)->first();
                    $dokumentasi = DokumentasiExam::where('id_registrasi', $registExam->id) ->first();

                    if (!$dokumentasi) {
                        DokumentasiExam::create([
                            'id_registrasi'       => $registExam->id,
                            'nama_exam'           => $exam->materi?->nama_materi ?? ($exam->rkm?->materi?->nama_materi ?? ''),
                            'tanggal_pelaksanaan' => now()->format('Y-m-d'),
                        ]);
                    }

                    $skippedCount++;
                }
            }

            $message = "Berhasil mendaftarkan $createdCount peserta";

            if ($skippedCount > 0) {
                $message .= " (Skipped: $skippedCount peserta sudah terdaftar)";
            }

            return redirect()
                ->route('daftar-peserta-exam.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
    }

    public function edit(string $id)
    {
        $registrasi = registexam::with([
            'peserta',
            'exam.materi',
            'exam.rkm.perusahaan',
            'exam.rkm.instruktur'
        ])->findOrFail($id);

        $perusahaan = $registrasi->exam?->rkm?->perusahaan ?? $registrasi->exam?->perusahaan;
        $instruktur = $registrasi->exam?->rkm?->instruktur ?? $registrasi->exam?->karyawan;

        $dokumentasi = DokumentasiExam::where('id_registrasi', $registrasi->id)->first();

        return view('daftarPesertaExam.edit', compact('registrasi', 'perusahaan', 'instruktur', 'dokumentasi'));
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'nama_exam' => 'required|string|max:255',
            'tanggal_pelaksanaan' => 'required|date',
            'skor' => 'nullable|numeric',
            'dokumentasi' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'invoice' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'keterangan_lulus' => 'nullable|string|max:255',
        ], [
            'nama_exam.required' => 'Nama exam/kelas wajib diisi',
            'tanggal_pelaksanaan.required' => 'Tanggal pelaksanaan wajib diisi',
            'tanggal_pelaksanaan.date' => 'Format tanggal tidak valid',
            'skor.numeric' => 'Skor harus berupa angka',
            'dokumentasi.mimes' => 'Format file dokumentasi tidak didukung',
            'dokumentasi.max' => 'Ukuran file dokumentasi maksimal 10MB',
            'invoice.mimes' => 'Format file invoice tidak didukung',
            'invoice.max' => 'Ukuran file invoice maksimal 10MB',
        ]);

        try {
            $registrasi = registexam::findOrFail($id);
            $dokumentasi = DokumentasiExam::where('id_registrasi', $id)->first() ?? new DokumentasiExam();

            if ($request->hasFile('dokumentasi')) {
                if ($dokumentasi->dokumentasi && \Illuminate\Support\Facades\Storage::exists('public/' . $dokumentasi->dokumentasi)) {
                    \Illuminate\Support\Facades\Storage::delete('public/' . $dokumentasi->dokumentasi);
                }
                $file_dokumentasi = $request->file('dokumentasi');
                $path_dokumentasi = $file_dokumentasi->store('dokumentasi-exam', 'public');
                $validated['dokumentasi'] = $path_dokumentasi;
            } elseif ($dokumentasi->dokumentasi) {
                $validated['dokumentasi'] = $dokumentasi->dokumentasi;
            }

            if ($request->hasFile('invoice')) {
                if ($dokumentasi->invoice && \Illuminate\Support\Facades\Storage::exists('public/' . $dokumentasi->invoice)) {
                    \Illuminate\Support\Facades\Storage::delete('public/' . $dokumentasi->invoice);
                }
                $file_invoice = $request->file('invoice');
                $path_invoice = $file_invoice->store('invoice-exam', 'public');
                $validated['invoice'] = $path_invoice;
            } elseif ($dokumentasi->invoice) {
                $validated['invoice'] = $dokumentasi->invoice;
            }

            if ($dokumentasi->id) {
                $dokumentasi->update(array_merge($validated, [
                    'tanggal_pelaksanaan' => $validated['tanggal_pelaksanaan'],
                ]));
            } else {
                $dokumentasi->fill(array_merge($validated, [
                    'id_registrasi' => $registrasi->id,
                    'tanggal_pelaksanaan' => $validated['tanggal_pelaksanaan'],
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

    public function storePesertaAjax(Request $request)
    {
        $validated = $request->validate([
            'id_perusahaan' => 'required|exists:perusahaans,id',
            'peserta' => 'required|array|min:1',
            'peserta.*.nama' => 'required|string|max:255',
            'peserta.*.jenis_kelamin' => 'required|string|in:L,P',
            'peserta.*.email' => 'required|email|max:255',
            'peserta.*.no_hp' => 'required|string|max:20',
        ]);

        try {
            $newPesertas = [];
            foreach ($validated['peserta'] as $dataPeserta) {
                $peserta = Peserta::create([
                    'nama' => Peserta::formatNama($dataPeserta['nama']),
                    'jenis_kelamin' => $dataPeserta['jenis_kelamin'],
                    'email' => $dataPeserta['email'],
                    'no_hp' => $dataPeserta['no_hp'],
                    'perusahaan_key' => $validated['id_perusahaan'],
                ]);
                $newPesertas[] = $peserta;
            }

            return response()->json([
                'success' => true,
                'data' => $newPesertas,
                'message' => 'Peserta berhasil ditambahkan.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
