<?php

namespace App\Http\Controllers\KPI;

use App\Exports\KpiTargetTemplateExport;
use App\Http\Controllers\Controller;
use App\Models\AbsensiKaryawan;
use App\Models\ActivityInstruktur;
use App\Models\activityLog;
use App\Models\Aktivitas;
use App\Models\AnalysisQuarterDescription;
use App\Models\AnalysisReport;
use App\Models\BiayaTransportasiDriver;
use App\Models\ChecklistKeperluan;
use App\Models\checklistRKM;
use App\Models\colaborator;
use App\Models\ContentSchedule;
use App\Models\detailPersonKPI;
use App\Models\DetailTargetKPI;
use App\Models\DokumentasiExam;
use App\Models\formPenilaian;
use App\Models\JenisTunjangan;
use App\Models\IdeInovasi;
use App\Models\karyawan;
use App\Models\KategoriDaftarTugas;
use App\Models\User;
use App\Models\Kegiatan;
use App\Models\KomplainPeserta;
use App\Models\KondisiKendaraan;
use App\Models\KontrolTugas;
use App\Models\LaporanHarianSales;
use App\Models\Materi;
use App\Models\Nilaifeedback;
use App\Models\nilaiKPI;
use App\Models\NomorModul;
use App\Models\outstanding;
use App\Models\Pelatihan;
use App\Models\Peluang;
use App\Models\PengajuanBarang;
use App\Models\PenilaianExam;
use App\Models\PerbaikanKendaraan;
use App\Models\perhitunganNetSales;
use App\Models\pickupDriver;
use App\Models\Registrasi;
use App\Models\RekomendasiLanjutan;
use App\Models\RKM;
use App\Models\Sertifikasi;
use App\Models\SurveyKepuasan;
use App\Models\target as ModelsTarget;
use App\Models\targetKPI;
use App\Models\Tickets;
use App\Models\TodoAdministrasi;
use App\Models\trackingTagihanPerusahaan;
use App\Models\TunjanganKaryawan;
use App\Models\kategoriKPI;
use App\Models\shareForm;
use App\Models\tipeKategoriTabel;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Google\Service\CloudDeploy\Target;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Imports\KpiTargetImport;
use App\Models\AdministrasiKaryawan;
use App\Models\ApprovalPendapatan;
use App\Models\DataTarget;
use App\Models\HariLibur;
use Maatwebsite\Excel\Validators\ValidationException;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use Illuminate\Validation\Validator;
use App\Models\LeadProject;
use App\Models\pengajuancuti;

class TargetKPIController extends Controller
{
    public function kpiIndex()
    {
        $daftarKaryawan = karyawan::where('status_aktif', '1')->whereNot('jabatan', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')->whereNot('jabatan', 'Pilih Jabatan')->where('nip', '!=', null)->whereNot('divisi', 'Direksi')->get();

        return view('KPIdata.TargetDivisi.index', compact('daftarKaryawan'));
    }

    public function getKaryawanByJabatan(Request $request)
    {
        $jabatanList = $request->input('jabatan', []);

        if (!is_array($jabatanList)) {
            $jabatanList = [$jabatanList];
        }

        $karyawan = karyawan::whereIn('jabatan', $jabatanList)
            ->where('status_aktif', '1')->whereNot('jabatan', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')->whereNot('jabatan', 'Pilih Jabatan')->where('nip', '!=', null)->whereNot('divisi', 'Direksi')
            ->select('id', 'nama_lengkap', 'jabatan')
            ->get()
            ->map(function ($k) {
                return [
                    'id' => $k->id,
                    'text' => $k->nama_lengkap . ' (' . $k->jabatan . ')',
                ];
            });

        return response()->json($karyawan);
    }

    public function getAssistantRoutesByJabatan(Request $request)
    {
        $jabatanList = $request->input('jabatan', []);

        if (!is_array($jabatanList)) {
            $jabatanList = [$jabatanList];
        }

        if (empty($jabatanList)) {
            return response()->json([]);
        }

        $jabatanList = array_map('strtolower', $jabatanList);

        $routeMapping = [
            'gm' => [
                'pemasukan kotor',
                'pemasukan bersih',
                'target penjualan project tahunan',
                'kepuasan pelanggan',
                'rasio biaya operasional terhadap revenue',
                'performa kpi departemen'
            ],

            'customer care' => [
                'peserta puas dengan pelayanan dan fasilitas training',
                'dorong inovasi pelayanan',
                'penanganan komplain perseta',
                'report persiapan kelas'
            ],

            'finance & accounting' => [
                'outstanding',
                'inisiatif efisiensi keuangan',
                'mengurangi manual work dan error',
                'laporan analisis keuangan',
                'pencairan biaya operasional',
                'penyelesaian tagihan perusahaan',
                'akurasi pencatatan masuk'
            ],

            'hrd' => [
                'pelaksanaan kegiatan karyawan',
                'pengeluaran biaya karyawan',
                'administrasi karyawan'
            ],

            'driver' => [
                'perbaikan kendaraan',
                'report kondisi kendaraan',
                'kontrol pengeluaran transportasi',
                'feedback kenyamanan berkendaran'
            ],

            'office boy' => [
                'feedback kebersihan dan kenyamanan',
                'penyelesaian tugas harian'
            ],

            'koordinator itsm' => [
                'meningkatkan kepuasan dan loyalitas peserta/client',
                'availability sistem internal kritis',
                'persentase gap kompetensi tim terhadap standar skill'
            ],

            'programmer' => [
                'ketepatan waktu penyelesaian fitur',
                'mengukur kualitas aplikasi agar minim bug'
            ],

            'tim digital' => [
                'konsistensi campaign digital',
                'efektifitas digital marketing'
            ],

            'project administrator & business support' => [
                'pendapatan penjualan project',
                'leads project'
            ],

            'technical support' => [
                'keberhasilan support memenuhi sla',
                'kualitas layanan exam'
            ],

            'instruktur' => [
                'presentase kinerja instruktur',
                'kepuasan peserta pelatihan',
                'upseling lanjutan materi',
                'sertifikasi kompetensi internal',
                'pelatihan kompetensi eksternal'
            ],

            'education manager' => [
                'pengembangan kurikulum pelatihan',
                'peningkatan knowledge sharing',
                'peningkatan kontribusi pelatihan',
                'evaluasi kinerja instruktur',
                'pembuatan artikel'
            ],

            'sales' => [
                'target penjualan tahunan',
                'biaya akuisisi perclient',
                'peningkatan kemampuan kompetensi sales'
            ],

            'spv sales' => [
                'meningkatkan revenue perusahaan',
                'customer acquisition cost',
                'evaluasi kinerja sales'
            ],

            'adm sales' => [
                'laporan mom',
                'akurasi kelengkapan data penjualan',
                'todo administrasi'
            ],

            'admin holding' => [
                'ketepatan waktu po',
                'kualitas dokumentasi support dan proctor'
            ],
        ];

        $kombinasiIT = ['programmer', 'tim digital', 'technical support'];
        $kombinasiSales = ['sales', 'spv sales', 'adm sales'];

        $availableRoutes = [];

        if (count(array_intersect($jabatanList, $kombinasiIT)) === 3) {
            $availableRoutes = [
                'kepuasan client itsm',
                'inovation adaption rate',
                'persentase gap kompetensi tim terhadap standar skill'
            ];
        } elseif (count(array_intersect($jabatanList, $kombinasiSales)) === 3) {
            $availableRoutes = [
                'peningkatan kemampuan kompetensi sales'
            ];
        } else {
            foreach ($jabatanList as $jabatan) {
                if (isset($routeMapping[$jabatan])) {
                    $availableRoutes = array_merge(
                        $availableRoutes,
                        $routeMapping[$jabatan]
                    );
                }
            }

            $availableRoutes = array_unique($availableRoutes);
        }

        // Query database tanpa peduli huruf besar/kecil
        $dataTargets = DataTarget::whereIn(
            DB::raw('LOWER(asistant_route)'),
            $availableRoutes
        )->get([
            'asistant_route',
            'jangka_target',
            'tipe_target',
            'nilai_target'
        ]);

        return response()->json($dataTargets);
    }

    public function cleaningDatabase()
    {
        try {
            DB::beginTransaction();

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            DB::table('target_k_p_i_s')->truncate();
            DB::table('detail_target_k_p_i_s')->truncate();
            DB::table('detail_person_k_p_i_s')->truncate();
            DB::table('data_targets')->truncate();

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            DB::commit();

            return redirect()->back()->with('success', 'Seluruh data database berhasil dibersihkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal membersihkan database: ' . $e->getMessage());
        }
    }

    public function getDataTargetByRoute(Request $request)
    {
        $route = $request->query('route');

        if (!$route) {
            return response()->json(['error' => 'Parameter route diperlukan'], 400);
        }

        $dataTarget = DataTarget::where('asistant_route', $route)->first();

        if (!$dataTarget) {
            return response()->json(['error' => 'Data target tidak ditemukan'], 404);
        }

        return response()->json($dataTarget);
    }

    public function createTarget(Request $request)
    {
        $validated = $request->validate([
            'id_pembuat'       => 'required',
            'judul_kpi'        => 'required|string',
            'deskripsi_kpi'    => 'nullable|string',
            'jabatan'          => 'required|array',
            'jabatan.*'        => 'required|string|distinct',
            'karyawan'         => 'nullable|array',
            'karyawan.*'       => 'required|integer|distinct',
            'asistant_route'   => 'required|string',
            'detail_jangka'    => 'required',
        ]);

        $dataTarget = DataTarget::where('asistant_route', $validated['asistant_route'])->first();

        if (!$dataTarget) {
            return response()->json(['message' => 'Konfigurasi target tidak ditemukan'], 404);
        }

        $jabatans = array_unique($validated['jabatan']);

        return DB::transaction(function () use ($validated, $dataTarget, $jabatans) {
            $createTarget = targetKPI::create([
                'id_pembuat'     => $validated['id_pembuat'],
                'id_data_target' => $dataTarget->id,
                'judul'          => $validated['judul_kpi'],
                'deskripsi'      => $validated['deskripsi_kpi'],
                'status'         => '0',
            ]);

            foreach ($jabatans as $jabatan) {
                $dataDivisi = karyawan::where('jabatan', $jabatan)
                    ->where('divisi', '!=', 'Direksi')
                    ->value('divisi');

                $detail_jangka_value = null;
                if ($dataTarget->jangka_target === 'Tahunan' && !empty($validated['detail_jangka'])) {
                    $detail_jangka_value = $validated['detail_jangka'];
                }

                $detailStore = DetailTargetKPI::create([
                    'id_targetKPI'   => $createTarget->id,
                    'jabatan'        => $jabatan,
                    'divisi'         => $dataDivisi,
                    'id_data_target' => $dataTarget->id,
                    'jangka_target'  => $dataTarget->jangka_target,
                    'detail_jangka'  => $detail_jangka_value,
                    'tipe_target'    => $dataTarget->tipe_target,
                    'nilai_target'   => $dataTarget->nilai_target,
                ]);

                $karyawanIds = [];

                // PERBAIKAN: Tambahkan ->distinct() sebelum pluck untuk memastikan ID unik dari database
                if (!empty($validated['karyawan'])) {
                    $karyawanIds = karyawan::whereIn('id', $validated['karyawan'])
                        ->where('jabatan', $jabatan)
                        ->where('status_aktif', '1')
                        ->where('jabatan', '!=', 'Outsource') // Disarankan pakai != daripada whereNot untuk kompatibilitas
                        ->where('kode_karyawan', 'NOT LIKE', 'OL%')
                        ->where('jabatan', '!=', 'Pilih Jabatan')
                        ->whereNotNull('nip') // Disarankan pakai whereNotNull
                        ->where('divisi', '!=', 'Direksi')
                        ->distinct() // PENTING: Mencegah ID ganda dari database
                        ->pluck('id')
                        ->toArray();
                } else {
                    $karyawanIds = karyawan::where('jabatan', $jabatan)
                        ->where('divisi', '!=', 'Direksi')
                        ->where('status_aktif', '1')
                        ->where('jabatan', '!=', 'Outsource')
                        ->where('kode_karyawan', 'NOT LIKE', 'OL%')
                        ->where('jabatan', '!=', 'Pilih Jabatan')
                        ->whereNotNull('nip')
                        ->where('divisi', '!=', 'Direksi')
                        ->distinct() // PENTING: Mencegah ID ganda dari database
                        ->pluck('id')
                        ->toArray();
                }

                foreach ($karyawanIds as $karyawanId) {
                    detailPersonKPI::create([
                        'id_target'       => $createTarget->id,
                        'detailTargetKey' => $detailStore->id,
                        'id_karyawan'     => $karyawanId,
                    ]);
                }
            }

            return response()->json([
                'message' => 'Target berhasil dibuat',
                'data'    => ['id_target' => $createTarget->id]
            ], 201);
        });
    }

    public function importTarget(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
                'skip_duplicate' => 'nullable|boolean',
                'dry_run' => 'nullable|boolean',
            ], [
                'file.mimes' => 'File harus berformat Excel (.xlsx/.xls) atau CSV',
                'file.max' => 'Ukuran file maksimal 10MB',
            ]);

            $file = $request->file('file');

            $options = [
                'skip_duplicate' => $request->boolean('skip_duplicate'),
                'dry_run' => $request->boolean('dry_run'),
            ];

            if ($options['dry_run']) {
                return $this->previewImport($file, $options);
            }

            DB::beginTransaction();

            $import = new KpiTargetImport($options);

            Excel::import($import, $file);

            $summary = $import->getSummary();

            if (!empty($summary['errors'])) {
                DB::rollBack();

                Log::warning('Import KPI dengan error', [
                    'user_id' => auth()->id(),
                    'error_count' => count($summary['errors']),
                    'sample_errors' => array_slice($summary['errors'], 0, 10)
                ]);

                return response()->json([
                    'errors' => [
                        'file' => array_slice($summary['errors'], 0, 50)
                    ]
                ], 422);
            }

            DB::commit();

            $messages = [];

            if ($summary['imported'] > 0) {
                $messages[] = "✅ {$summary['imported']} data berhasil diimport.";
            }

            if ($summary['skipped'] > 0) {
                $messages[] = "⏭️ {$summary['skipped']} data dilewati (duplikat).";
            }

            return response()->json([
                'success' => implode(' ', $messages) ?: 'Import selesai',
                'summary' => $summary
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();

            $errorList = collect($e->failures())
                ->map(function ($failure) {
                    return "Baris #{$failure->row()} [{$failure->attribute()}]: " . implode(', ', $failure->errors());
                })
                ->take(20);

            return response()->json([
                'errors' => [
                    'file' => $errorList->toArray()
                ]
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Import KPI failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'errors' => [
                    'file' => [
                        'Terjadi kesalahan sistem: ' . $e->getMessage()
                    ]
                ]
            ], 500);
        }
    }

    private function previewImport($file, array $options)
    {
        try {
            $import = new KpiTargetImport($options);

            Excel::toCollection($import, $file);

            $summary = $import->getSummary();

            if (!empty($summary['errors'])) {
                return response()->json([
                    'errors' => [
                        'preview' => array_slice($summary['errors'], 0, 20)
                    ]
                ], 422);
            }

            return response()->json([
                'success' => '✅ Preview: Semua data valid!',
                'summary' => $summary,
                'message' => 'Siap untuk diimport. Klik "Import Sekarang" untuk menyimpan ke database.'
            ]);
        } catch (ValidationException $e) {
            $errors = collect($e->failures())
                ->map(function ($failure) {
                    return "Baris #{$failure->row()} [{$failure->attribute()}]: " . implode(', ', $failure->errors());
                })
                ->take(20);

            return response()->json([
                'errors' => [
                    'preview' => $errors->toArray()
                ]
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => [
                    'preview' => [
                        'Error preview: ' . $e->getMessage()
                    ]
                ]
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        $allRoutes = DataTarget::pluck('asistant_route')->unique()->sort()->values();

        $routeMapping = $this->getRouteMapping();

        return Excel::download(
            new KpiTargetTemplateExport($allRoutes, $routeMapping),
            'template_import_kpi_' . date('Y-m-d') . '.xlsx'
        );
    }

    private function getRouteMapping(): array
    {
        return [
            'gm' => [
                'pemasukan kotor',
                'pemasukan bersih',
                'kepuasan pelanggan',
                'target penjualan project tahunan',
                'rasio biaya operasional terhadap revenue',
                'performa kpi departemen'
            ],
            'customer care' => [
                'peserta puas dengan pelayanan dan fasilitas training',
                'dorong inovasi pelayanan',
                'penanganan komplain perseta',
                'report persiapan kelas'
            ],
            'finance & accounting' => [
                'outstanding',
                'inisiatif efisiensi keuangan',
                'mengurangi manual work dan error',
                'laporan analisis keuangan',
                'pencairan biaya operasional',
                'penyelesaian tagihan perusahaan',
                'akurasi pencatatan masuk'
            ],
            'hrd' => [
                'pelaksanaan kegiatan karyawan',
                'pengeluaran biaya karyawan',
                'administrasi karyawan'
            ],
            'driver' => [
                'perbaikan kendaraan',
                'report kondisi kendaraan',
                'kontrol pengeluaran transportasi',
                'feedback kenyamanan berkendaran'
            ],
            'office boy' => [
                'feedback kebersihan dan kenyamanan',
                'penyelesaian tugas harian'
            ],
            'koordinator itsm' => [
                'meningkatkan kepuasan dan loyalitas peserta/client',
                'availability sistem internal kritis',
                'persentase gap kompetensi tim terhadap standar skill'
            ],
            'programmer' => [
                'ketepatan waktu penyelesaian fitur',
                'mengukur kualitas aplikasi agar minim bug'
            ],
            'tim digital' => [
                'konsistensi campaign digital',
                'efektifitas digital marketing'
            ],
            'project administrator & business support' => [
                'pendapatan penjualan project',
                'leads project'
            ],
            'technical support' => [
                'keberhasilan support memenuhi sla',
                'kualitas layanan exam'
            ],
            'instruktur' => [
                'presentase kinerja instruktur',
                'kepuasan peserta pelatihan',
                'upseling lanjutan materi',
                'sertifikasi kompetensi internal',
                'pelatihan kompetensi eksternal'
            ],
            'education manager' => [
                'pengembangan kurikulum pelatihan',
                'peningkatan knowledge sharing',
                'peningkatan kontribusi pelatihan',
                'evaluasi kinerja instruktur',
                'pembuatan artikel'
            ],
            'sales' => [
                'target penjualan tahunan',
                'biaya akuisisi perclient',
                'peningkatan kemampuan kompetensi sales'
            ],
            'spv sales' => [
                'meningkatkan revenue perusahaan',
                'customer acquisition cost',
                'evaluasi kinerja sales'
            ],
            'adm sales' => [
                'laporan mom',
                'akurasi kelengkapan data penjualan',
                'todo administrasi'
            ],
            'admin holding' => [
                'ketepatan waktu po',
                'kualitas dokumentasi support dan proctor'
            ],
        ];
    }

    public function updateGapKompetensi(Request $request)
    {
        $data = $request->input('data');

        if (empty($data)) {
            return response()->json([
                'status' => false,
                'message' => 'Data kosong'
            ], 400);
        }

        foreach ($data as $item) {
            $id = $item['id'] ?? null;
            $kemampuan = $item['kemampuan'] ?? 0;
            $standar = $item['standar'] ?? 0;

            if (!$id) {
                continue;
            }

            $detail = detailPersonKPI::find($id);

            if (!$detail) {
                continue;
            }

            $detail->presentase_kemampuan = $kemampuan;
            $detail->presentase_standar = $standar;
            $detail->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diupdate'
        ]);
    }

    public function hapusTarget($id)
    {
        $target = targetKPI::with('detailTargetKPI.detailPersonKPI')->find($id);

        if (!$target) {
            return response()->json(
                [
                    'message' => 'Target tidak ditemukan',
                ],
                404,
            );
        }

        foreach ($target->detailTargetKPI as $detail) {
            $detail->detailPersonKPI()->delete();
        }

        $target->detailTargetKPI()->delete();

        $target->delete();

        return response()->json([
            'message' => 'Berhasil menghapus target',
        ]);
    }

    public function manualValue(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'manual_value' => 'nullable|string',
            'biaya_gaji_tahunan' => 'nullable|numeric',
            'biaya_bpjs_tahunan' => 'nullable|numeric',
            'biaya_rekrutmen_tahunan' => 'nullable|numeric',
            'manual_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $detail = DetailTargetKPI::where('id_targetKPI', $request->id)->first();

        if (!$detail) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Detail Target KPI tidak ditemukan',
                ],
                404,
            );
        }

        $existingDocument = $detail->manual_document;
        $manualValue = $request->manual_value;

        if (!is_null($request->biaya_gaji_tahunan) || !is_null($request->biaya_bpjs_tahunan) || !is_null($request->biaya_rekrutmen_tahunan)) {
            $gaji = $request->biaya_gaji_tahunan ?? 0;
            $bpjs = $request->biaya_bpjs_tahunan ?? 0;
            $rekrutmen = $request->biaya_rekrutmen_tahunan ?? 0;

            $manualValue = (int) $gaji . ',' . (int) $bpjs . ',' . (int) $rekrutmen;
        }

        $updateData = [
            'manual_value' => $manualValue,
        ];

        if ($request->hasFile('manual_document')) {
            $file = $request->file('manual_document');

            if ($existingDocument && Storage::disk('public')->exists($existingDocument)) {
                Storage::disk('public')->delete($existingDocument);
            }

            $filePath = $file->store('manual_documents', 'public');
            $updateData['manual_document'] = $filePath;
        }

        $detail->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil memasukkan data manual',
            'data' => [
                'id' => $detail->id,
                'manual_value' => $detail->manual_value,
                'manual_document' => $detail->manual_document,
            ],
        ]);
    }

    public function getProgressDashboard(Request $request)
    {
        $user = auth()->user();
        $id_pembuat = $user->id;
        $jabatan_pembuat = $user->jabatan;
        $idUser = $request->idUser;
        $typeGet = $request->typeGet;
        $currentYear = now()->year;

        $targetEmployeeId = filled($idUser) && filled($typeGet) ? $idUser : $id_pembuat;
        $karyawan = karyawan::find($targetEmployeeId);

        if (!$karyawan) {
            return response()->json(['average_progress' => 0, 'message' => 'Karyawan tidak ditemukan'], 404);
        }

        $persentaseJenis = [
            'General Manager' => 35,
            'Manager/SPV/Team Leader (Atasan Langsung)' => 30,
            'Rekan Kerja (Satu Divisi)' => 20,
            'Pekerja (Beda Divisi)' => 10,
            'Self Apprisial' => 5,
        ];

        $calculatePenilaianScore = function ($collectionNilaiKPI) use ($persentaseJenis) {
            $jenisTotalRaw = [];

            foreach ($persentaseJenis as $jenis => $bobot) {
                $nilaiForJenis = $collectionNilaiKPI->where('jenis_penilaian', $jenis)
                    ->pluck('nilai')
                    ->filter(fn($n) => is_numeric($n));

                if ($nilaiForJenis->isNotEmpty()) {
                    $avgNilai = $nilaiForJenis->avg();
                    $jenisTotalRaw[$jenis] = ($avgNilai * $bobot) / 100;
                }
            }

            return empty($jenisTotalRaw) ? 0 : round(array_sum($jenisTotalRaw), 2);
        };

        $calculateEmployeeAverageKPI = function ($empId, $yr) {
            $listKPI = targetKPI::with(['detailTargetKPI'])
                ->whereYear('created_at', $yr)
                ->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($empId) {
                    $q->where('id_karyawan', $empId);
                })
                ->get();

            $allProgressValues = [];

            foreach ($listKPI as $item) {
                $detail = $item->detailTargetKPI->first();
                if (!$detail) continue;

                $result = $this->getCalculationByRoute($item, $empId);
                if (!isset($result['progress'])) continue;

                $progress = (float)$result['progress'];

                if ($detail->tipe_target === 'rupiah' && $detail->nilai_target > 0) {
                    $progress = ($progress / $detail->nilai_target) * 100;
                } elseif ($detail->tipe_target === 'angka' && $detail->nilai_target > 0) {
                    $progress = ($progress / $detail->nilai_target) * 100;
                }

                $progress = min($progress, 100);
                $allProgressValues[] = round($progress, 2);
            }

            return count($allProgressValues) > 0
                ? round(array_sum($allProgressValues) / count($allProgressValues), 2)
                : 0;
        };

        $listKPI = targetKPI::with(['detailTargetKPI'])
            ->whereYear('created_at', $currentYear)
            ->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($targetEmployeeId) {
                $q->where('id_karyawan', $targetEmployeeId);
            })
            ->get();

        $allNilaiKPI = nilaiKPI::where('id_evaluated', $targetEmployeeId)
            ->whereYear('created_at', $currentYear)
            ->get();

        $allProgressValues = [];
        $monthly_progress = [];
        $daily_progress_per_month = [];

        foreach ($listKPI as $item) {
            $detail = $item->detailTargetKPI->first();
            if (!$detail) continue;

            $result = $this->getCalculationByRoute($item, $targetEmployeeId);
            if (!isset($result['progress'])) continue;

            $progress = (float)$result['progress'];

            if ($detail->tipe_target === 'rupiah' && $detail->nilai_target > 0) {
                $progress = ($progress / $detail->nilai_target) * 100;
            } elseif ($detail->tipe_target === 'angka' && $detail->nilai_target > 0) {
                $progress = ($progress / $detail->nilai_target) * 100;
            }

            $progress = min($progress, 100);
            $progressRounded = round($progress, 2);

            $allProgressValues[] = $progressRounded;

            $monthKey = $item->created_at->format('Y-m');
            $dayKey = $item->created_at->format('Y-m-d');

            if (!isset($monthly_progress[$monthKey])) {
                $monthly_progress[$monthKey] = [];
            }
            $monthly_progress[$monthKey][] = $progressRounded;

            if (!isset($daily_progress_per_month[$monthKey])) {
                $daily_progress_per_month[$monthKey] = [];
            }
            if (!isset($daily_progress_per_month[$monthKey][$dayKey])) {
                $daily_progress_per_month[$monthKey][$dayKey] = [];
            }
            $daily_progress_per_month[$monthKey][$dayKey][] = $progressRounded;
        }

        $avgTargetYearly = count($allProgressValues) > 0
            ? round(array_sum($allProgressValues) / count($allProgressValues), 2)
            : 0;

        $avgPenilaianYearly = $calculatePenilaianScore($allNilaiKPI);

        if ($avgTargetYearly == 0 && $avgPenilaianYearly == 0) {
            $nilaiKpiAnda = 0;
            $titleGetData = 'Tidak ada data';
        } elseif ($avgTargetYearly == 0) {
            $nilaiKpiAnda = round($avgPenilaianYearly * 0.4, 2);
            $titleGetData = 'Dari Penilaian';
        } elseif ($avgPenilaianYearly == 0) {
            $nilaiKpiAnda = $avgTargetYearly;
            $titleGetData = 'Dari Target KPI';
        } else {
            $nilaiKpiAnda = round($avgTargetYearly * 0.6 + $avgPenilaianYearly * 0.4, 2);
            $titleGetData = 'Gabungan Target KPI & Penilaian';
        }

        $kpiPerbulan = [];
        $now = now();

        for ($i = 3; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $key = $date->format('Y-m');

            $nilai = isset($monthly_progress[$key]) && count($monthly_progress[$key]) > 0
                ? round(array_sum($monthly_progress[$key]) / count($monthly_progress[$key]), 2)
                : 0;

            $kpiPerbulan[] = [
                'bulan' => $date->locale('id')->isoFormat('MMMM YYYY'),
                'nilai' => $nilai
            ];
        }

        $personalDashboard = [
            'nilai_kpi_anda' => $nilaiKpiAnda,
            'progress_kpi_perbulan' => $kpiPerbulan,
            'performance' => 0,
            'performance_title' => 'Stabil',
            'deadline' => "{$currentYear}-12-31 23:59:59",
            'countdown' => '',
            'titleGet_data' => $titleGetData,
            'daily_progress_per_month' => $daily_progress_per_month,
            'monthly_progress' => $monthly_progress,
        ];

        $divisionTeamData = [];
        $currentUser = karyawan::find($id_pembuat);

        if ($currentUser && !empty($currentUser->divisi)) {
            $teamMembers = karyawan::where('divisi', $currentUser->divisi)->where('status_aktif', '1')->whereNot('jabatan', 'Outsource')
                ->where('kode_karyawan', 'NOT LIKE', 'OL%')->whereNot('jabatan', 'Pilih Jabatan')->where('nip', '!=', null)->whereNot('divisi', 'Direksi')->get();

            foreach ($teamMembers as $member) {
                $avgTargetTeam = $calculateEmployeeAverageKPI($member->id, $currentYear);

                $allNilaiKPITeam = nilaiKPI::where('id_evaluated', $member->id)
                    ->whereYear('created_at', $currentYear)
                    ->get();

                $avgPenilaianTeam = $calculatePenilaianScore($allNilaiKPITeam);

                if ($avgTargetTeam == 0 && $avgPenilaianTeam == 0) {
                    $nilaiKpiTeam = 0;
                } elseif ($avgTargetTeam == 0) {
                    $nilaiKpiTeam = round($avgPenilaianTeam * 0.4, 2);
                } elseif ($avgPenilaianTeam == 0) {
                    $nilaiKpiTeam = $avgTargetTeam;
                } else {
                    $nilaiKpiTeam = round($avgTargetTeam * 0.6 + $avgPenilaianTeam * 0.4, 2);
                }

                $divisionTeamData[] = [
                    'nama_karyawan' => $member->nama_lengkap,
                    'jabatan' => $member->jabatan,
                    'nilaitargetkpi' => $nilaiKpiTeam,
                    'performance' => 0,
                    'performance_title' => 'Stabil',
                    'nilai_performance' => 0,
                ];
            }
        }

        $divisionKpiData = [];
        $divisions = karyawan::whereNotNull('divisi')
            ->whereNotIn('divisi', ['', 'Pilih Divisi', 'Direksi'])
            ->distinct()
            ->pluck('divisi');

        foreach ($divisions as $divisi) {
            $employees = karyawan::where('divisi', $divisi)
                ->where('status_aktif', '1')
                ->whereNot('jabatan', 'Outsource')
                ->where('kode_karyawan', 'NOT LIKE', 'OL%')
                ->whereNot('jabatan', 'Pilih Jabatan')
                ->whereNotNull('nip')
                ->whereNot('divisi', 'Direksi')
                ->pluck('id');

            if ($employees->isEmpty()) continue;

            $allDivisionProgress = [];

            foreach ($employees as $empId) {
                $listKPIDiv = targetKPI::with(['detailTargetKPI'])
                    ->whereYear('created_at', $currentYear)
                    ->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($empId) {
                        $q->where('id_karyawan', $empId);
                    })
                    ->get();

                foreach ($listKPIDiv as $item) {
                    $detail = $item->detailTargetKPI->first();
                    if (!$detail) continue;

                    $result = $this->getCalculationByRoute($item, $empId);
                    if (!isset($result['progress'])) continue;

                    $progress = (float)$result['progress'];

                    if ($detail->tipe_target === 'rupiah' && $detail->nilai_target > 0) {
                        $progress = ($progress / $detail->nilai_target) * 100;
                    } elseif ($detail->tipe_target === 'angka' && $detail->nilai_target > 0) {
                        $progress = ($progress / $detail->nilai_target) * 100;
                    }

                    $progress = min($progress, 100);
                    $allDivisionProgress[] = round($progress, 2);
                }
            }

            $avgKpiValue = count($allDivisionProgress) > 0
                ? round(array_sum($allDivisionProgress) / count($allDivisionProgress), 2)
                : 0;

            $divisionKpiData[] = [
                'divisi' => $divisi,
                'nilai_kpi' => $avgKpiValue,
                'performance' => 0,
                'performance_title' => 'Stabil',
                'tahun' => $currentYear,
            ];
        }

        return response()->json([
            'output_1' => $personalDashboard,
            'output_2' => $divisionTeamData,
            'output_3' => $divisionKpiData,
        ]);
    }

    public function getDataTarget(Request $request)
    {
        $user = auth()->user();
        $id_pembuat = $user->id;
        $jabatan_pembuat = $user->jabatan;
        $idUser = $request->idUser;
        $typeGet = $request->typeGet;

        // Ambil data karyawan yang dituju atau karyawan login
        if (filled($idUser) && filled($typeGet)) {
            $karyawan = karyawan::find($idUser);
            if (!$karyawan) {
                return response()->json(['message' => 'Karyawan tidak ditemukan'], 404);
            }
            $divisiUser = $karyawan->divisi;
        } else {
            $karyawan = karyawan::find($id_pembuat);
            if (!$karyawan) {
                return response()->json(['message' => 'Karyawan tidak ditemukan'], 404);
            }
            $divisiUser = $karyawan->divisi;
        }

        $superRoles = ['GM', 'HRD', 'Direktur Utama'];

        // === JABATAN LIST (untuk filter) ===
        if (in_array($user->jabatan, $superRoles)) {
            $dataJabatan = karyawan::whereNotIn('jabatan', ['Direktur Utama', 'Direktur'])
                ->distinct()
                ->pluck('jabatan');
        } else {
            $dataJabatan = karyawan::where('divisi', $divisiUser)
                ->whereNotIn('jabatan', ['Direktur Utama', 'Direktur'])
                ->distinct()
                ->pluck('jabatan');
        }

        // === QUERY UTAMA ===
        $query = targetKPI::with([
            'karyawan',
            'detailTargetKPI.dataTarget',
            'detailTargetKPI.detailPersonKPI'
        ])->whereYear('created_at', now()->year);

        // Kasus khusus: melihat target orang lain (misalnya HRD melihat target karyawan tertentu)
        if (filled($idUser) && filled($typeGet)) {
            $query->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($idUser) {
                $q->where('id_karyawan', $idUser);
            });
        } 
        // Non-super user → filter berdasarkan divisi
        elseif (!in_array($jabatan_pembuat, $superRoles)) {
            $query->whereHas('detailTargetKPI', function ($q) use ($divisiUser) {
                $q->where('divisi', $divisiUser);
            });
        }
        // Super user → tidak ada filter tambahan (bisa lihat semua divisi)

        $detailList = $query->get();

        $data = [
            'detail' => $detailList
                ->map(function ($item) use ($idUser) {
                    $detail = $item->detailTargetKPI->first();
                    if (!$detail) {
                        return null;
                    }

                    $tenggat_waktu = null;
                    $jangka = strtolower($detail->dataTarget?->jangka_target ?? '');
                    $detailJangka = $detail->detail_jangka;

                    switch ($jangka) {
                        case 'tahunan':
                            $year = (int) $detailJangka;
                            $tenggat_waktu = date('Y-m-d', strtotime("last day of December $year"));
                            break;
                    }

                    $personId = !empty($idUser) ? (int) $idUser : null;
                    $progress = $this->resolveProgress($item, $personId);

                    return [
                        'id' => $item->id,
                        'pembuat' => $item->karyawan->nama_lengkap ?? null,
                        'id_pembuat' => $item->id_pembuat,
                        'judul' => $item->judul,
                        'deskripsi' => $item->deskripsi,
                        'jabatan' => $item->detailTargetKPI->pluck('jabatan')->unique()->values(),
                        'divisi' => $item->detailTargetKPI->pluck('divisi')->unique()->values(),
                        'asistant_route' => $detail->dataTarget?->asistant_route,
                        'jangka_target' => $detail->dataTarget?->jangka_target,
                        'detail_jangka' => $detail->detail_jangka,
                        'tipe_target' => $detail->dataTarget?->tipe_target,
                        'nilai_target' => $detail->dataTarget?->nilai_target,
                        'manual_value' => $detail->manual_value,
                        'status' => $item->status,
                        'created_at' => $item->created_at,
                        'tenggat_waktu' => $tenggat_waktu,
                        'progress' => $progress,
                    ];
                })
                ->filter()
                ->values(),
                            
            'jabatan_list' => $dataJabatan,
            'routes' => DataTarget::select('asistant_route', 'jangka_target', 'tipe_target', 'nilai_target')->get(),
        ];

        return response()->json($data);
    }

    private function resolveProgress($item, $personId)
    {
        $progress = 0;

        $detail = $item->detailTargetKPI->first();
        $asistantRoute = strtolower($detail->dataTarget->asistant_route);

        if ($asistantRoute === 'kepuasan pelanggan') {
            $progress = $this->calculateProgressKepuasanPelanggan($item, $personId);
        } elseif ($asistantRoute === 'pemasukan kotor') {
            $progress = $this->calculatePemasukanKotor($item, $personId);
        } elseif ($asistantRoute === 'pemasukan bersih') {
            $progress = $this->calculatePemasukanBersih($item, $personId);
        } elseif ($asistantRoute === 'target penjualan project tahunan') {
            $progress = $this->calculateTargetPenjualanProjectTahunan($item, $personId);
        } elseif ($asistantRoute === 'rasio biaya operasional terhadap revenue') {
            $progress = $this->calculateRasioBiayaOperasionalTerhadapRevenue($item, $personId);
        } elseif ($asistantRoute === 'performa kpi departemen') {
            $progress = $this->calculatePerformaKPIDepartemen($item, $personId);
        } elseif ($asistantRoute === 'peserta puas dengan pelayanan dan fasilitas training') {
            $progress = $this->calculatePesertaPuasDenganPelayananDanFasilitasTraining($item, $personId);
        } elseif ($asistantRoute === 'dorong inovasi pelayanan') {
            $progress = $this->calculateDorongInovasiPelayanan($item, $personId);
        } elseif ($asistantRoute === 'penanganan komplain peserta') {
            $progress = $this->calculatePenangananKomplainPerseta($item, $personId);
        } elseif ($asistantRoute === 'report persiapan kelas') {
            $progress = $this->calculateReportPersiapanKelas($item, $personId);
        } elseif ($asistantRoute === 'outstanding') {
            $progress = $this->calculateOutstanding($item, $personId);
        } elseif ($asistantRoute === 'inisiatif efisiensi keuangan') {
            $progress = $this->calculateInisiatifEfisiensiKeuangan($item, $personId);
        } elseif ($asistantRoute === 'mengurangi manual work dan error') {
            $progress = $this->calculateMengurangiManualWorkDanError($item, $personId);
        } elseif ($asistantRoute === 'laporan analisis keuangan') {
            $progress = $this->calculateLaporanAnalisisKeuangan($item, $personId);
        } elseif ($asistantRoute === 'pencairan biaya operasional') {
            $progress = $this->calculatePencairanBiayaOperasional($item, $personId);
        } elseif ($asistantRoute === 'penyelesaian tagihan perusahaan') {
            $progress = $this->calculatePenyelesaianTagihanPerusahaan($item, $personId);
        } elseif ($asistantRoute === 'akurasi pencatatan masuk') {
            $progress = $this->calculateAkurasiPencatatanMasuk($item, $personId);
        } elseif ($asistantRoute === 'pelaksanaan kegiatan karyawan') {
            $progress = $this->calculatePelaksanaanKegiatanKaryawan($item, $personId);
        } elseif ($asistantRoute === 'pengeluaran biaya karyawan') {
            $progress = $this->calculatePengeluaranBiayaKaryawan($item, $personId);
        } elseif ($asistantRoute === 'administrasi karyawan') {
            $progress = $this->calculateAdministrasiKaryawan($item, $personId);
        } elseif ($asistantRoute === 'perbaikan kendaraan') {
            $progress = $this->calculatePerbaikanKendaraan($item, $personId);
        } elseif ($asistantRoute === 'kontrol pengeluaran transportasi') {
            $progress = $this->calculateKontrolPengeluaranTransportasi($item, $personId);
        } elseif ($asistantRoute === 'report kondisi kendaraan') {
            $progress = $this->calculateReportKondisiKendaraan($item, $personId);
        } elseif ($asistantRoute === 'feedback kenyamanan berkendaran') {
            $progress = $this->calculateFeedbackKenyamananBerkendara($item, $personId);
        } elseif ($asistantRoute === 'ketepatan waktu po') {
            $progress = $this->calculateKetepatanWaktuPo($item, $personId);
        } elseif ($asistantRoute === 'kualitas dokumentasi support dan proctor') {
            $progress = $this->calculatekualitasDokumentasiSupportDanProctor($item, $personId);
        } elseif ($asistantRoute === 'feedback kebersihan dan kenyamanan') {
            $progress = $this->calculateFeedbackKebersihanDanKenyamanan($item, $personId);
        } elseif ($asistantRoute === 'penyelesaian tugas harian') {
            $progress = $this->calculatePenyelesaianTugasHarian($item, $personId);
        } elseif ($asistantRoute === 'kepuasan client itsm') {
            $progress = $this->calculateProgressKepuasanClientITSM($item, $personId);
        } elseif ($asistantRoute === 'inovation adaption rate') {
            $progress = $this->calculateInovationAdaptionRate($item, $personId);
        } elseif ($asistantRoute === 'availability sistem internal kritis') {
            $progress = $this->calculateAvailabilitySistemInternalKritis($item, $personId);
        } elseif ($asistantRoute === 'meningkatkan kepuasan dan loyalitas peserta/client') {
            $progress = $this->calculateMeningkatkanKepuasanDanLoyalitasPeserta($item, $personId);
        } elseif ($asistantRoute === 'persentase gap kompetensi tim terhadap standar skill') {
            $progress = $this->calculatePersentaseGapKompetensi($item, $personId);
        } elseif ($asistantRoute === 'ketepatan waktu penyelesaian fitur') {
            $progress = $this->calculateProgressKetepatanWaktuPenyelesaianFitur($item, $personId);
        } elseif ($asistantRoute === 'mengukur kualitas aplikasi agar minim bug') {
            $progress = $this->calculateMengukurKualitasAplikasiAgarMinimBug($item, $personId);
        } elseif ($asistantRoute === 'konsistensi campaign digital') {
            $progress = $this->calculateKonsistensiCampaignDigital($item, $personId);
        } elseif ($asistantRoute === 'efektifitas digital marketing') {
            $progress = $this->calculateEfektifitasDiitalMarketing($item, $personId);
        } elseif ($asistantRoute === 'pendapatan penjualan project') {
            $progress = $this->calculatePendapatanPenjualanProject($item, $personId);
        } elseif ($asistantRoute === 'leads project') {
            $progress = $this->calculateLeadsProject($item, $personId);
        } elseif ($asistantRoute === 'keberhasilan support memenuhi sla') {
            $progress = $this->calculateTingkatKeberhasilanSupportMemenuhiSLA($item, $personId);
        } elseif ($asistantRoute === 'kualitas layanan exam') {
            $progress = $this->calculateKualitasLayananExam($item, $personId);
        } elseif ($asistantRoute === 'kepuasan peserta pelatihan') {
            $progress = $this->calculateKepuasanPesertaPelatihan($item, $personId);
        } elseif ($asistantRoute === 'upseling lanjutan materi') {
            $progress = $this->calculateUpselingLanjutanMateri($item, $personId);
        } elseif ($asistantRoute === 'sertifikasi kompetensi internal') {
            $progress = $this->calculateSertifikasiKompetensiInternal($item, $personId);
        } elseif ($asistantRoute === 'pelatihan kompetensi eksternal') {
            $progress = $this->calculatePelatihanKompetensiEksternal($item, $personId);
        } elseif ($asistantRoute === 'presentase kinerja instruktur') {
            $progress = $this->calculatePresentaseKinerjaInstruktur($item, $personId);
        } elseif ($asistantRoute === 'pengembangan kurikulum pelatihan') {
            $progress = $this->calculatePengembanganKurikulumPelatihan($item, $personId);
        } elseif ($asistantRoute === 'peningkatan knowledge sharing') {
            $progress = $this->calculatePeningkatanKnowledgeSharing($item, $personId);
        } elseif ($asistantRoute === 'peningkatan kontribusi pelatihan') {
            $progress = $this->calculatePeningkatanKontribusiPelatihan($item, $personId);
        } elseif ($asistantRoute === 'evaluasi kinerja instruktur') {
            $progress = $this->calculateEvaluasiKinerjaInstruktur($item, $personId);
        } elseif ($asistantRoute === 'pembuatan artikel') {
            $progress = $this->calculatePembuatanArtikel($item, $personId);
        } elseif ($asistantRoute === 'target penjualan tahunan') {
            $progress = $this->calculateTargetPenjualanTahunan($item, $personId);
        } elseif ($asistantRoute === 'peningkatan kemampuan kompetensi sales') {
            $progress = $this->calculatePeningkatanKemampuanKompetensiSales($item, $personId);
        } elseif ($asistantRoute === 'customer acquisition cost') {
            $progress = $this->calculateCustomerAcquisitionCost($item, $personId);
        } elseif ($asistantRoute === 'meningkatkan revenue perusahaan') {
            $progress = $this->calculateMeningkatkanRevenuePerusahaan($item, $personId);
        } elseif ($asistantRoute === 'evaluasi kinerja sales') {
            $progress = $this->calculateEvaluasiKinerjaSales($item, $personId);
        } elseif ($asistantRoute === 'biaya akuisisi perclient') {
            $progress = $this->calculateBiayaAkuisisiClient($item, $personId);
        } elseif ($asistantRoute === 'laporan mom') {
            $progress = $this->calculateLaporanMOM($item, $personId);
        } elseif ($asistantRoute === 'akurasi kelengkapan data penjualan') {
            $progress = $this->calculateAkurasiKelengkapanDataPenjualan($item, $personId);
        } elseif ($asistantRoute === 'todo administrasi') {
            $progress = $this->calculateTodoAdministrasi($item);
        }

        $detail = $item->detailTargetKPI->first();
        $nilaiTarget = (float) ($detail->dataTarget->nilai_target ?? $detail->nilai_target ?? 0);
        $progress = $nilaiTarget > 0 ? min($progress, $nilaiTarget) : $progress;

        return $progress;
    }

    //Target office
    //target GM
    private function calculateProgressKepuasanPelanggan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $start = "$tahun-01-01";
        $end = "$tahun-12-31";

        $feedbacks = Nilaifeedback::with('rkm.materi')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $groupedFeedbacks = $feedbacks
            ->groupBy(function ($feedback) {
                return optional($feedback->rkm)->materi?->nama_materi . '/' . optional($feedback->rkm)->tanggal_awal;
            })
            ->filter();

        $averageFeedbacks = [];

        foreach ($groupedFeedbacks as $group) {
            $totalFeedbacks = $group->count();
            if ($totalFeedbacks === 0) {
                continue;
            }

            $averageM = round(($group->sum('M1') + $group->sum('M2') + $group->sum('M3') + $group->sum('M4')) / ($totalFeedbacks * 4), 1);
            $averageP = round(($group->sum('P1') + $group->sum('P2') + $group->sum('P3') + $group->sum('P4') + $group->sum('P5') + $group->sum('P6') + $group->sum('P7')) / ($totalFeedbacks * 7), 1);
            $averageF = round(($group->sum('F1') + $group->sum('F2') + $group->sum('F3') + $group->sum('F4') + $group->sum('F5')) / ($totalFeedbacks * 5), 1);
            $averageI = round(($group->sum('I1') + $group->sum('I2') + $group->sum('I3') + $group->sum('I4') + $group->sum('I5') + $group->sum('I6') + $group->sum('I7') + $group->sum('I8')) / ($totalFeedbacks * 8), 1);

            $averageIb = round(($group->sum('I1b') + $group->sum('I2b') + $group->sum('I3b') + $group->sum('I4b') + $group->sum('I5b') + $group->sum('I6b') + $group->sum('I7b') + $group->sum('I8b')) / ($totalFeedbacks * 8), 1);
            $averageIas = round(($group->sum('I1as') + $group->sum('I2as') + $group->sum('I3as') + $group->sum('I4as') + $group->sum('I5as') + $group->sum('I6as') + $group->sum('I7as') + $group->sum('I8as')) / ($totalFeedbacks * 8), 1);

            $averageValues = [$averageM, $averageP, $averageF, $averageI];
            if ($averageIb > 0) {
                $averageValues[] = $averageIb;
            }
            if ($averageIas > 0) {
                $averageValues[] = $averageIas;
            }

            $averageTotal = round(array_sum($averageValues) / count($averageValues), 1);
            $averageFeedbacks[] = $averageTotal;
        }

        $total = count($averageFeedbacks);
        if ($total > 0) {
            $above = count(array_filter($averageFeedbacks, fn($v) => $v >= 3.5));
            return round(($above / $total) * 100, 1);
        }

        return 0;
    }

    private function calculatePemasukanKotor($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        if ($nilaiTarget <= 0) {
            return 0;
        }

        $totalSales = ApprovalPendapatan::whereYear('tanggal_mulai', $tahun)->select(DB::raw('SUM(CAST(harga_net AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total_sales'))->value('total_sales');

        $totalSales = (float) ($totalSales ?? 0);

        if ($totalSales <= 0) {
            return 0;
        }

        $progress = $totalSales;

        return round($progress);
    }

    private function calculatePemasukanBersih($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $labaKotor = $this->calculatePemasukanKotor($item, $personId);

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        if ($labaKotor == 0) {
            return 0;
        }

        $dataAnalisis = AnalysisReport::where('year', $tahun)->get();

        $nominal = $dataAnalisis->sum('nilai');

        if ($nominal === 0) {
            return 0;
        }

        $progress = 0;

        if ($labaKotor < $nominal) {
            return 0;
        }

        if ($nominal > 0) {
            $progress = ($nominal / $labaKotor) * 100;
        }

        return round($progress, 2);
    }

    private function calculateTargetPenjualanProjectTahunan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $target = (float) $detail->nilai_target;

        $query = LeadProject::where('status', 'won')
            ->whereYear('updated_at', $tahun);

        if ($personId !== null) {
            $kodeKaryawan = Karyawan::where('id', $personId)->value('kode_karyawan');
            if (!$kodeKaryawan) {
                return 0;
            }
            $query->where('lead_projects.sales_id', $kodeKaryawan);
        }

        $totalSales = (float) ($query
            ->select(DB::raw('SUM(lead_projects.estimasi_nilai) as total_sales'))
            ->value('total_sales') ?? 0);

        return round($totalSales);
    }

    private function calculateRasioBiayaOperasionalTerhadapRevenue($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        $labaKotor = $this->calculatePemasukanKotor($item, $personId);

        if ($labaKotor == 0) {
            return 0;
        }

        if (is_null($detail) || is_null($detail->manual_value)) {
            return 0;
        }

        $progress = 0;
        $manualValue = (float) $detail->manual_value;

        if ($manualValue > 0) {
            $rasio = ($manualValue / $labaKotor) * 100;

            $batas = $nilaiTarget;

            $progress = ($batas / $rasio) * 100;
        }

        return round($progress, 1);
    }

    private function calculatePerformaKPIDepartemen($item, $personId)
    {
        $allTargets = targetKPI::with(['detailTargetKPI.dataTarget'])
            ->whereYear('created_at', now()->year)
            ->get();

        $targetsByDivisi = [];

        foreach ($allTargets as $target) {
            $details = $target->detailTargetKPI;
            if (!$details || $details->isEmpty()) continue;

            $divisions = $details->pluck('divisi')->unique()->filter();

            foreach ($divisions as $divisi) {
                $targetsByDivisi[$divisi][] = $target;
            }
        }

        $divisionAverages = [];

        foreach ($targetsByDivisi as $divisi => $items) {
            $progresses = [];

            foreach ($items as $itemTarget) {
                $detail = $itemTarget->detailTargetKPI->first();
                if (!$detail) continue;

                $route = strtolower($detail->dataTarget?->asistant_route ?? '');

                if ($route === 'performa kpi departemen') continue;

                $progress = $this->resolveProgress($itemTarget, $personId);

                if ($detail->tipe_target === 'rupiah') {
                    $targetVal = $detail->nilai_target;

                    if ($route === 'pemasukan kotor') {
                        $data = $this->calculatePemasukanKotor($itemTarget, $personId);
                        $progress = $targetVal > 0 ? max(0, min(100, round(($data / $targetVal) * 100, 2))) : 0;
                    } elseif ($route === 'meningkatkan revenue perusahaan') {
                        $data = $this->calculateMeningkatkanRevenuePerusahaan($itemTarget, $personId);
                        $progress = $targetVal > 0 ? max(0, min(100, round(($data / $targetVal) * 100, 2))) : 0;
                    } elseif ($route === 'pendapatan penjualan project') {
                        $data = $this->calculatePendapatanPenjualanProject($itemTarget, $personId);
                        $progress = $targetVal > 0 ? max(0, min(100, round(($data / $targetVal) * 100, 2))) : 0;
                    } elseif ($route === 'target penjualan project tahunan') {
                        $data = $this->calculateTargetPenjualanProjectTahunan($itemTarget, $personId);
                        $progress = $targetVal > 0 ? max(0, min(100, round(($data / $targetVal) * 100, 2))) : 0;
                    } elseif ($route === 'laporan mom') {
                        $progress = $this->calculateLaporanMOM($itemTarget);
                    }
                }

                if (is_numeric($progress)) {
                    $progresses[] = $progress;
                }
            }

            if (!empty($progresses)) {
                $avg = array_sum($progresses) / count($progresses);
                $divisionAverages[] = round($avg, 1);
            }
        }

        if (!empty($divisionAverages)) {
            $progress = array_sum($divisionAverages) / count($divisionAverages);
            return round($progress, 1);
        }

        return 0;
    }

    //CS
    private function calculatePesertaPuasDenganPelayananDanFasilitasTraining($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }


        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return 0;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $allScores = [];

        $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])->get();
        foreach ($feedbacks as $fb) {
            $f1 = is_numeric($fb->F1) ? (float) $fb->F1 : 0;
            $f2 = is_numeric($fb->F2) ? (float) $fb->F2 : 0;
            $f3 = is_numeric($fb->F3) ? (float) $fb->F3 : 0;
            $f4 = is_numeric($fb->F4) ? (float) $fb->F4 : 0;
            $f5 = is_numeric($fb->F5) ? (float) $fb->F5 : 0;
            $p1 = is_numeric($fb->P1) ? (float) $fb->P1 : 0;
            $p2 = is_numeric($fb->P2) ? (float) $fb->P2 : 0;
            $p3 = is_numeric($fb->P3) ? (float) $fb->P3 : 0;
            $p4 = is_numeric($fb->P4) ? (float) $fb->P4 : 0;
            $p5 = is_numeric($fb->P5) ? (float) $fb->P5 : 0;
            $p6 = is_numeric($fb->P6) ? (float) $fb->P6 : 0;
            $p7 = is_numeric($fb->P7) ? (float) $fb->P7 : 0;

            $avg = ($f1 + $f2 + $f3 + $f4 + $f5 + $p1 + $p2 + $p3 + $p4 + $p5 + $p1 + $p2) / 12;
            $avg = min(4, max(1, $avg));
            $allScores[] = $avg;
        }

        if (empty($allScores)) {
            return 0;
        }

        $totalResponden = count($allScores);
        $respondenPuas = 0;

        foreach ($allScores as $skor) {
            if ($skor >= 3.5) {
                $respondenPuas++;
            }
        }

        $progress = ($respondenPuas / $totalResponden) * 100;
        return round($progress, 1);
    }

    private function calculateDorongInovasiPelayanan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $progress = 0;

        if (!is_null($detail->manual_value)) {
            $manualValue = (float) $detail->manual_value;

            if ($manualValue > 0) {
                $progress = $manualValue;
            }
        }

        return round($progress);
    }

    private function calculatePenangananKomplainPerseta($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        if (!$detail) {
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return 0;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $komplainData = KomplainPeserta::whereBetween('created_at', [$start, $end])->get();

        $totalData = $komplainData->count();

        if ($totalData === 0) {
            return 0;
        }

        $dataTepatWaktu = 0;

        foreach ($komplainData as $data) {
            if ($data->tanggal_selesai) {
                $createdDate = Carbon::parse($data->created_at);
                $finishedDate = Carbon::parse($data->tanggal_selesai);

                if ($createdDate->format('Y-m-d') === $finishedDate->format('Y-m-d')) {
                    $dataTepatWaktu++;
                }
            }
        }

        $presentase = ($dataTepatWaktu / $totalData) * 100;

        return round($presentase, 1);
    }

    private function calculateReportPersiapanKelas($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        $totalRkm = RKM::with('materi', 'instruktur', 'instruktur2', 'asisten', 'nilaifeedback')
                ->where('status', '0')
                ->whereYear('tanggal_awal', $tahun)->count();

        $totalTuntas = ChecklistKeperluan::whereHas('rkm', function ($query) use ($tahun) {
            $query->whereYear('tanggal_awal', $tahun);
        })
            ->whereNotNull('tanggal_keperluan')
            ->where('materi', '1')
            ->where('kelas', '1')
            ->where('cb', '1')
            ->where('maksi', '1')
            ->where('keperluan_kelas', '1')
            ->whereHas('subChecklistKeperluans', function ($subQuery) {
                $subQuery->where('materi_module', '1')
                    ->where('materi_elearning', '1')
                    ->where('cb_instruktur', '1')
                    ->where('cb_peserta', '1')
                    ->where('maksi_instruktur', '1')
                    ->where('maksi_peserta', '1')
                    ->where('kelas_ac', '1')
                    ->where('kelas_jam', '1')
                    ->where('kelas_buku', '1')
                    ->where('kelas_pulpen', '1')
                    ->where('kelas_permen', '1')
                    ->where('kelas_camilan', '1')
                    ->where('kelas_minuman', '1')
                    ->where('kelas_lampu', '1')
                    ->where('kelas_kondisi_kebersihan', '1');
            })
            ->count();

        if ($totalRkm > 0) {
            $progress = ($totalTuntas / $totalRkm) * 100;
        } else {
            $progress = 0;
        }

        return round($progress, 1);
    }

    //finance
    private function calculateOutstanding($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return 0;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $outstandings = Outstanding::whereBetween('created_at', [$start, $end])->get();

        if ($outstandings->isEmpty()) {
            return 0;
        }

        $totalData = $outstandings->count();

        $tepatTenggat = $outstandings->filter(function ($data) {
            return $data->status_pembayaran == 1
                && $data->tanggal_bayar
                && $data->due_date
                && Carbon::parse($data->tanggal_bayar)->lt(Carbon::parse($data->due_date));
        })->count();

        $presentase = ($tepatTenggat / $totalData) * 100;

        return round($presentase, 1);
    }

    private function calculateInisiatifEfisiensiKeuangan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $progress = 0;
        $manualValue = (float) $detail->manual_value;
        $targetValue = (float) $detail->nilai_target;

        if ($targetValue == null) {
            return 0;
        }

        if ($manualValue > 0) {
            $progress = $manualValue;
        }

        return round($progress);
    }

    private function calculateMengurangiManualWorkDanError($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $progress = 0;
        $manualValue = (float) $detail->manual_value;
        $targetValue = (float) $detail->nilai_target;

        if ($targetValue == null) {
            return 0;
        }

        if ($manualValue > 0) {
            $progress = $manualValue;
        }

        return round($progress);
    }

    private function calculateLaporanAnalisisKeuangan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        $analisisData = AnalysisReport::where('year', $tahun)->count();

        $progress = 0;

        if ($analisisData == 0) {
            return 0;
        }

        if ($analisisData > 0) {
            $progress = $analisisData;
        }

        return round($progress);
    }

    private function calculatePencairanBiayaOperasional($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $dataPengajuan = PengajuanBarang::with('tracking', 'detail')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $totalPengajuan = 0;
        $jumlahSesuai = 0;

        $completedStatuses = ['Selesai', 'Pencairan Sudah Selesai'];
        $excludedStatuses = [
            'Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi',
            'Finance Menunggu Approve Direksi',
            'Membuat Permintaan Ke Direktur Utama',
            'Diajukan dan Sedang Ditinjau oleh Education Manager',
            'Diajukan dan Sedang Ditinjau oleh Koordinator IT Service Management',
            'Diajukan dan Sedang Ditinjau oleh SPV Sales',
            'Diajukan dan Sedang Ditinjau oleh General Manager'
        ];

        foreach ($dataPengajuan as $pengajuan) {
            $trackingStatus = optional($pengajuan->tracking)->tracking;

            if (in_array($trackingStatus, $excludedStatuses)) {
                continue;
            }

            $totalPengajuan++;

            $isCompleted = in_array($trackingStatus, $completedStatuses);
            $score = 0;

            if ($isCompleted) {
                $tanggalTerimaFinance = Carbon::parse($pengajuan->tanggal_terima_finance ?? null);
                $tanggalSelesai = Carbon::parse($pengajuan->tanggal_selesai ?? null);

                if ($tanggalTerimaFinance && $tanggalSelesai && $tanggalTerimaFinance->addDays(7)->isBefore($tanggalSelesai)) {
                    $score = 0;
                } else {
                    $score = 1;
                }
            } else {
                $ageInDays = now()->diffInDays($pengajuan->created_at, false);
                if ($ageInDays <= 2) {
                    $score = 1;
                } elseif ($ageInDays <= 21) {
                    $decayDays = $ageInDays - 2;
                    $score = exp(-0.05 * $decayDays);
                    $score = max(0, min(1, $score));
                } else {
                    $score = 0;
                }
            }

            $jumlahSesuai += $score;
        }

        if ($totalPengajuan == 0) {
            return 0;
        }

        $progress = ($jumlahSesuai / $totalPengajuan) * 100;

        return round($progress, 1);
    }

    private function calculatePenyelesaianTagihanPerusahaan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $foreignKey = 'id_tagihan_perusahaan';

        $latestIds = trackingTagihanPerusahaan::whereBetween(
                'tanggal_perkiraan_mulai',
                [$start, $end]
            )
            ->selectRaw("{$foreignKey}, MAX(id) as latest_id")
            ->groupBy($foreignKey)
            ->pluck('latest_id')
            ->filter()
            ->unique()
            ->values();

        if ($latestIds->isEmpty()) {
            return 0;
        }

        $totalTagihan = $latestIds->count();

        if ($totalTagihan <= 0) {
            return 0;
        }

        $tagihanSelesai = trackingTagihanPerusahaan::whereIn('id', $latestIds)
            ->where('status', 'Selesai')
            ->where('tracking', 'Selesai')
            ->count();

        $progress = ($tagihanSelesai / max($totalTagihan, 1)) * 100;

        return round($progress, 1);
    }

    private function calculateAkurasiPencatatanMasuk($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $data = outstanding::whereBetween('created_at', [$start, $end])->get();

        $totalTagihan = $data->count();
        $totalAkurat = 0;

        foreach ($data as $row) {

            $netSales = (float) $row->net_sales;
            $jumlahBayar = (float) $row->jumlah_pembayaran;

            $totalPotongan = 0;

            if (!empty($row->jumlah_potongan)) {
                $potongan = is_array($row->jumlah_potongan)
                    ? $row->jumlah_potongan
                    : json_decode($row->jumlah_potongan, true);

                if (is_array($potongan)) {
                    foreach ($potongan as $p) {
                        $totalPotongan += (float) ($p['jumlah'] ?? 0);
                    }
                }
            }

            if ($netSales == $jumlahBayar || $netSales == ($jumlahBayar + $totalPotongan)) {
                $totalAkurat++;
            }
        }

        $progress = $totalTagihan > 0 ? ($totalAkurat / $totalTagihan) * 100 : 0;

        return round($progress, 1);
    }

    //HRD
    private function calculatePelaksanaanKegiatanKaryawan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }
        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $kegiatans = Kegiatan::whereYear('created_at', $tahun)->get();

        $totalKegiatan = $kegiatans->count();
        if ($totalKegiatan == 0) {
            return 0;
        }
        $totalKehadiranValid = 0;

        foreach ($kegiatans as $kegiatan) {
            $pesertaIds = is_array($kegiatan->id_peserta) ? $kegiatan->id_peserta : json_decode($kegiatan->id_peserta, true);

            if (empty($pesertaIds)) {
                continue;
            }

            $jumlahPeserta = count($pesertaIds);

            $tanggalKegiatan = Carbon::parse($kegiatan->waktu_kegiatan);
            $startOfDay = $tanggalKegiatan->copy()->startOfDay();
            $endOfDay = $tanggalKegiatan->copy()->endOfDay();

            $jumlahHadir = AbsensiKaryawan::whereIn('id_karyawan', $pesertaIds)
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->where('keterangan', 'Masuk')
                ->count();

            $persentase = ($jumlahHadir / $jumlahPeserta) * 100;

            if ($persentase >= 80) {
                $totalKehadiranValid++;
            }
        }

        $progress = ($totalKehadiranValid / $totalKegiatan) * 100;

        return round($progress, 1);
    }

    private function calculatePengeluaranBiayaKaryawan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $startOfYear = Carbon::create($tahun, 1, 1)->startOfDay();
        $endOfYear = Carbon::create($tahun, 12, 31)->endOfDay();

        $parts = explode(',', $detail->manual_value ?? '');

        $gaji = (float) ($parts[0] ?? 0);
        $bpjsManual = (float) ($parts[1] ?? 0);
        $rekrutmenManual = (float) ($parts[2] ?? 0);

        $bpjsIds = JenisTunjangan::whereIn('nama_tunjangan', [
            'BPJS Tenaga Kerja',
            'BPJS Kesehatan'
        ])->pluck('id');

        $bpjsBudget = TunjanganKaryawan::whereBetween('created_at', [$startOfYear, $endOfYear])
            ->whereIn('jenis_tunjangan', $bpjsIds)
            ->sum('total');

        $rekrutmenBudget = Kegiatan::whereBetween('created_at', [$startOfYear, $endOfYear])
            ->where('tipe', 'rekrutment')
            ->sum('realisasi');

        $kegiatanBudget = 0;
        $kegiatanRealisasi = 0;

        $kegiatans = Kegiatan::with('pengajuan_barang.detail')
            ->whereBetween('created_at', [$startOfYear, $endOfYear])
            ->where('tipe', 'kegiatan')
            ->get();

        foreach ($kegiatans as $kegiatan) {

            $kegiatanRealisasi += (float) $kegiatan->realisasi;

            if ($kegiatan->pengajuan_barang) {
                foreach ($kegiatan->pengajuan_barang as $pengajuan) {

                    if ($pengajuan->detail) {
                        foreach ($pengajuan->detail as $d) {

                            $qty = (float) ($d->qty ?? 0);
                            $harga = (float) ($d->harga ?? 0);

                            $kegiatanBudget += $qty * $harga;
                        }
                    }
                }
            }
        }

        $score = 0;

        if ($gaji > 0) {
            $score++;
        }

        if ($bpjsManual > 0 && $bpjsManual <= $bpjsBudget) {
            $score++;
        }

        if ($rekrutmenManual > 0 && $rekrutmenManual <= $rekrutmenBudget) {
            $score++;
        }

        if ($kegiatanRealisasi > 0 && $kegiatanRealisasi <= $kegiatanBudget) {
            $score++;
        }

        $progress = ($score / 4) * 100;

        return round($progress, 1);
    }

    private function calculateAdministrasiKaryawan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $dataAdministrasi = AdministrasiKaryawan::whereYear('created_at', $tahun)->get();
        $totalData = $dataAdministrasi->count();

        if ($totalData == 0) {
            return 0;
        }

        $totalSkor = 0;
        $penaltyPerDay = 0.1;
        $maxLateDays = 7;

        foreach ($dataAdministrasi as $data) {
            if ($data->status === 'selesai') {
                if ($data->dateline && $data->tanggal_selesai) {
                    $dateline = Carbon::parse($data->dateline);
                    $selesai  = Carbon::parse($data->tanggal_selesai);

                    $daysLate = $selesai->greaterThan($dateline) ? $selesai->diffInDays($dateline) : 0;

                    if ($daysLate >= $maxLateDays) {
                        $skor = 0;
                    } else {
                        $skor = max(0, 1 - ($daysLate * $penaltyPerDay));
                    }
                } else {
                    $skor = 0;
                }
            } else {
                $skor = 0;
            }

            $totalSkor += $skor;
        }

        $progress = ($totalSkor / $totalData) * 100;

        return round($progress, 2);
    }

    //Driver
    private function calculatePerbaikanKendaraan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $totalQuery = PerbaikanKendaraan::whereBetween('created_at', [$start, $end]);
        $selesaiQuery = PerbaikanKendaraan::whereBetween('created_at', [$start, $end])
            ->where('status', 'Selesai');

        if ($personId !== null) {
            $totalQuery->where('id_user', $personId);
            $selesaiQuery->where('id_user', $personId);
        }

        $totalData = $totalQuery->count();
        $dataDiperbaiki = $selesaiQuery->count();

        if ($totalData <= 0) {
            return 0;
        }

        $presentase = ($dataDiperbaiki / $totalData) * 100;

        return round($presentase, 1);
    }

    private function calculateKontrolPengeluaranTransportasi($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $query = pickupDriver::whereBetween('created_at', [$start, $end])
            ->whereNotNull('budget');

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $DataPickup = $query->with(['biayaTransportasi'])->get();

        $totalData = $DataPickup->count();
        if ($totalData === 0) {
            return 0;
        }

        $countAman = 0;

        foreach ($DataPickup as $data) {
            $totalBiaya = $data->biayaTransportasi->sum('harga') ?? 0;

            if ($totalBiaya <= $data->budget) {
                $countAman++;
            }
        }

        $presentase = ($countAman / $totalData) * 100;

        return round($presentase, 1);
    }

    private function calculateReportKondisiKendaraan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        $response = Http::get("https://libur.deno.dev/api", ['year' => $tahun]);
        
        if ($response->successful()) {
            foreach ($response->json() as $libur) {
                HariLibur::updateOrCreate(
                    ['tanggal' => $libur['date']],
                    ['nama' => $libur['name'], 'year' => $tahun]
                );
            }
        }

        $startPeriode = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $endPeriode = Carbon::createFromDate($tahun, 12, 31)->endOfDay();
        $hariIni = now()->startOfDay();

        if ($hariIni > $endPeriode) {
            $hariIni = $endPeriode;
        }

        $hariLibur = HariLibur::where('year', $tahun)
            ->pluck('tanggal')
            ->map(function ($d) {
                return Carbon::parse($d)->toDateString();
            })
            ->toArray();

        if ($personId !== null) {
            $firstReport = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$startPeriode, $hariIni])
                ->where('user_id', $personId)
                ->whereNotNull('tanggal_pemeriksaan')
                ->get()
                ->filter(function ($item) use ($hariLibur) {
                    return !in_array(Carbon::parse($item->tanggal_pemeriksaan)->toDateString(), $hariLibur);
                })
                ->sortBy('tanggal_pemeriksaan')
                ->first();
        } else {
            $firstReport = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$startPeriode, $hariIni])
                ->whereNotNull('tanggal_pemeriksaan')
                ->get()
                ->filter(function ($item) use ($hariLibur) {
                    return !in_array(Carbon::parse($item->tanggal_pemeriksaan)->toDateString(), $hariLibur);
                })
                ->sortBy('tanggal_pemeriksaan')
                ->first();
        }

        if (!$firstReport) {
            return 0;
        }

        $startMinggu = Carbon::parse($firstReport->tanggal_pemeriksaan)->startOfWeek(Carbon::MONDAY);

        $today = Carbon::now();
        $dayOfWeek = $today->dayOfWeek;

        if ($dayOfWeek < 6) {
            $checkUntil = $today->copy()->subWeek()->endOfWeek(Carbon::SUNDAY);
        } else {
            $checkUntil = $today->endOfDay();
        }

        if ($checkUntil > $endPeriode) {
            $checkUntil = $endPeriode;
        }

        $totalMinggu = ceil($startMinggu->diffInDays($checkUntil) / 7);
        if ($totalMinggu < 1) {
            $totalMinggu = 1;
        }

        $jumlahReportTepat = 0;

        for ($i = 0; $i < $totalMinggu; $i++) {
            $weekStart = $startMinggu->copy()->addWeeks($i)->startOfWeek(Carbon::MONDAY);
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

            if ($weekEnd > $checkUntil) {
                $weekEnd = $checkUntil;
            }

            // === CHECK REPORT EXISTENCE (EXCLUDE HOLIDAYS) ===
            if ($personId !== null) {
                $hasReport = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$weekStart, $weekEnd])
                    ->where('user_id', $personId)
                    ->whereNotNull('tanggal_pemeriksaan')
                    ->get()
                    ->filter(function ($item) use ($hariLibur) {
                        return !in_array(Carbon::parse($item->tanggal_pemeriksaan)->toDateString(), $hariLibur);
                    })
                    ->isNotEmpty();
            } else {
                $hasReport = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$weekStart, $weekEnd])
                    ->whereNotNull('tanggal_pemeriksaan')
                    ->get()
                    ->filter(function ($item) use ($hariLibur) {
                        return !in_array(Carbon::parse($item->tanggal_pemeriksaan)->toDateString(), $hariLibur);
                    })
                    ->isNotEmpty();
            }

            if ($hasReport) {
                $jumlahReportTepat++;
            }
        }

        $presentase = ($jumlahReportTepat / $totalMinggu) * 100;
        return round($presentase, 1);
    }

    private function calculateFeedbackKenyamananBerkendara($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $allScores = [];

        $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])->get();

        foreach ($feedbacks as $fb) {
            $p8 = is_numeric($fb->p8) ? (float) $fb->p8 : 0;

            $avg = ($p8) / 1;
            $avg = min(4, max(1, $avg));
            $allScores[] = $avg;
        }

        if (empty($allScores)) {
            return 0;
        }

        $totalResponden = count($allScores);
        $respondenPuas = 0;

        foreach ($allScores as $skor) {
            if ($skor >= 3.5) {
                $respondenPuas++;
            }
        }

        $progress = ($respondenPuas / $totalResponden) * 100;
        return round($progress, 1);
    }

    //OB
    private function calculateFeedbackKebersihanDanKenyamanan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $allScores = [];

        $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])->get();

        foreach ($feedbacks as $fb) {
            $f1 = is_numeric($fb->F1) ? (float) $fb->F1 : 0;
            $f2 = is_numeric($fb->F2) ? (float) $fb->F2 : 0;
            $f3 = is_numeric($fb->F3) ? (float) $fb->F3 : 0;
            $f4 = is_numeric($fb->F4) ? (float) $fb->F4 : 0;
            $f5 = is_numeric($fb->F5) ? (float) $fb->F5 : 0;

            $avg = ($f1 + $f2 + $f3 + $f4 + $f5) / 5;
            $avg = min(4, max(1, $avg));
            $allScores[] = $avg;
        }

        if (empty($allScores)) {
            return 0;
        }

        $totalResponden = count($allScores);
        $respondenPuas = 0;

        foreach ($allScores as $skor) {
            if ($skor >= 3.5) {
                $respondenPuas++;
            }
        }

        $progress = ($respondenPuas / $totalResponden) * 100;
        return round($progress, 1);
    }

    private function calculatePenyelesaianTugasHarian($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        if ($personId !== null) {
            $daftarTugas = KontrolTugas::whereYear('created_at', $tahun)
                ->where('id_karyawan', $personId);
        } else {
            $daftarTugas = KontrolTugas::whereYear('created_at', $tahun);
        }

        $jumlahTugas = $daftarTugas->count();

        if ($jumlahTugas === 0) {
            return 0;
        }

        $jumlahTugasSelesai = $daftarTugas->where('status', '1')->count();

        $presentase = ($jumlahTugasSelesai / $jumlahTugas) * 100;

        return round($presentase, 1);
    }

    //target ITSM
    //Koordinator ITSM
    private function calculateMeningkatkanKepuasanDanLoyalitasPeserta($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $allScores = [];

        $dataSurvey = SurveyKepuasan::whereBetween('created_at', [$start, $end])->get();

        foreach ($dataSurvey as $survey) {
            $nilaiQ1 = match ($survey->q1) {
                1 => 10,
                2 => 20,
                3 => 30,
                4 => 40,
                default => 0,
            };

            $nilaiQ4 = match ($survey->q4) {
                1 => 10,
                2 => 20,
                3 => 30,
                4 => 40,
                default => 0,
            };

            $nilaiQ2 = match ($survey->q2) {
                'Ya' => 20,
                'Tidak' => 10,
                default => 0,
            };

            $totalBaris = min(100, max(0, $nilaiQ1 + $nilaiQ2 + $nilaiQ4));

            $skor = 1 + ($totalBaris * 3) / 100;

            $allScores[] = $skor;
        }

        if (empty($allScores)) {
            return 0;
        }

        $totalResponden = count($allScores);
        $respondenPuas = 0;

        foreach ($allScores as $skor) {
            if ($skor >= 3.0) {
                $respondenPuas++;
            }
        }

        $progress = ($respondenPuas / $totalResponden) * 100;

        return round($progress, 1);
    }

    private function calculateAvailabilitySistemInternalKritis($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $logs = activityLog::whereBetween('status', ['100', '599'])
            ->whereBetween('checked_at', [$start, $end])
            ->get();

        if ($logs->isEmpty()) {
            return 0;
        }

        $totalChecks = $logs->count();
        $upChecks = $logs->where('is_up', 1)->count();

        $availability = ($upChecks / $totalChecks) * 100;

        return round($availability, 1);
    }

    private function calculatePersentaseGapKompetensi($item, $personId = null)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        $detailIds = $detail->pluck('id');

        $query = detailPersonKPI::whereIn('detailTargetKey', $detailIds);

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $detailPersons = $query->get();

        if ($detailPersons->isEmpty()) {
            return 0;
        }

        $totalKemampuan = 0;
        $totalStandar = 0;

        foreach ($detailPersons as $detailPerson) {
            $kemampuan = (float) $detailPerson->presentase_kemampuan;
            $standar = (float) $detailPerson->presentase_standar;

            if ($standar <= 0) {
                continue;
            }

            $totalKemampuan += $kemampuan;
            $totalStandar += $standar;
        }

        if ($totalStandar <= 0) {
            return 0;
        }

        $progress = ($totalKemampuan / $totalStandar) * 100;

        return round(min($progress, 100), 1);
    }

    //Tim Digital
    private function calculateKonsistensiCampaignDigital($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail || !$detail->detail_jangka) {
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return 0;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $contentSchedules = ContentSchedule::whereBetween('upload_date', [$start, $end])
            ->whereNotNull('upload_date')
            ->get();

        if ($contentSchedules->isEmpty()) {
            return 0;
        }

        $weeklyCounts = [];

        foreach ($contentSchedules as $schedule) {
            $date = Carbon::parse($schedule->upload_date);

            $weekStart = $date->copy()->startOfWeek(Carbon::MONDAY);
            $weekEnd = $date->copy()->endOfWeek(Carbon::SUNDAY);

            $weekKey = $weekStart->format('Y-m-d') . '_' . $weekEnd->format('Y-m-d');

            $weeklyCounts[$weekKey] = ($weeklyCounts[$weekKey] ?? 0) + 1;
        }

        $targetMingguan = 3;

        $compliantWeeks = 0;
        $totalWeeksWithData = 0;

        foreach ($weeklyCounts as $count) {
            if ($count >= 1) {
                $totalWeeksWithData++;

                if ($count >= $targetMingguan) {
                    $compliantWeeks++;
                }
            }
        }

        $CS = $totalWeeksWithData === 0 ? 0 : $compliantWeeks / $totalWeeksWithData;

        $totalKonten = $contentSchedules->count();

        $jumlahMinggu = 0;

        $current = $start->copy()->startOfWeek(Carbon::MONDAY);
        $endOfYearWeek = $end->copy()->endOfWeek(Carbon::SUNDAY);

        while ($current <= $endOfYearWeek) {
            $jumlahMinggu++;
            $current->addWeek();
        }

        $PS = $totalKonten / ($targetMingguan * $jumlahMinggu);
        $PS = min($PS, 1);

        $finalScore = ($CS * 0.6) + ($PS * 0.4);

        return round($finalScore * 100, 1);
    }

    private function calculateEfektifitasDiitalMarketing($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $dataColaborator = Colaborator::whereBetween('created_at', [$start, $end])->get();

        $totalQuarters = 4;

        $quartersWith = [];

        foreach ($dataColaborator as $colab) {
            $month = $colab->created_at->month;
            $quarter = (int) ceil($month / 3);
            $quartersWith[$quarter] = true;
        }

        $filledQuartersCount = count($quartersWith);

        $konsistensiPersen = $filledQuartersCount;

        return (string) round($konsistensiPersen);
    }

    //project administrator & usiness support
    private function calculatePendapatanPenjualanProject($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $target = (float) $detail->nilai_target;

        $query = LeadProject::where('status', 'won')
            ->whereYear('updated_at', $tahun);

        $totalSales = (float) ($query
            ->select(DB::raw('SUM(lead_projects.estimasi_nilai) as total_sales'))
            ->value('total_sales') ?? 0);

        return round($totalSales);
    }

    private function calculateLeadsProject($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        $target = (int) $detail->nilai_target;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $totalLead = LeadProject::whereYear('created_at', $tahun)->count();

        $progress = ($totalLead / $target) * 100;

        return round($totalLead);
    }

    //Programmer
    private function calculateMengukurKualitasAplikasiAgarMinimBug($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        $start = Carbon::create($tahun, 1, 1, 0, 0, 0, 'Asia/Jakarta');
        $end = Carbon::create($tahun, 12, 31, 23, 59, 59, 'Asia/Jakarta');

        $idKaryawans = detailPersonKPI::where('detailTargetKey', $detail->id)
            ->pluck('id_karyawan')
            ->unique()
            ->toArray();

        if (empty($idKaryawans)) {
            return 0;
        }

        $picNames = karyawan::whereIn('id', $idKaryawans)
            ->pluck('nama_lengkap')
            ->map(fn($nama) => explode(' ', trim($nama))[0] ?? '')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($picNames)) {
            return 0;
        }

        $normalizedPicNames = array_map(function ($name) {
            return match ($name) {
                'Stepanus' => 'Stefan',
                'Jonathan' => 'Valen',
                default => $name,
            };
        }, $picNames);

        $errorQuery = Tickets::whereBetween('created_at', [$start, $end])
            ->where('kategori', 'Error (Aplikasi)')
            ->where('keperluan', 'Programming')
            ->whereNotNull('tanggal_selesai');

        $requestQuery = Tickets::whereBetween('created_at', [$start, $end])
            ->where('kategori', 'Request');

        if ($personId !== null) {
            $karyawanData = karyawan::find($personId);
            if (!$karyawanData) {
                return 0;
            }

            $firstName = explode(' ', trim($karyawanData->nama_lengkap))[0] ?? '';
            if (!$firstName) {
                return 0;
            }

            $firstName = match ($firstName) {
                'Stepanus' => 'Stefan',
                'Jonathan' => 'Valen',
                default => $firstName,
            };

            $errorQuery->where('pic', $firstName);
            $requestQuery->where('pic', $firstName);
        } else {
            $errorQuery->whereIn('pic', $normalizedPicNames);
            $requestQuery->whereIn('pic', $normalizedPicNames);
        }

        $ticketsError = $errorQuery->get();
        $ticketsRequest = $requestQuery->get();

        $jumlahError = $ticketsError->count();
        $jumlahRequest = $ticketsRequest->count();
        $totalTicket = $jumlahError + $jumlahRequest;

        if ($totalTicket === 0) {
            return 0;
        }

        $skorRasio = ($jumlahRequest / $totalTicket) * 100;

        if ($jumlahError === 0) {
            $rataSkorError = 100;
        } else {
            $totalSkorError = 0;

            foreach ($ticketsError as $ticket) {
                try {
                    $startAt = Carbon::parse($ticket->created_at, 'Asia/Jakarta');

                    $endAt = strlen($ticket->tanggal_selesai) > 10
                        ? Carbon::parse($ticket->tanggal_selesai, 'Asia/Jakarta')
                        : Carbon::parse($ticket->tanggal_selesai . ' ' . ($ticket->jam_selesai ?? '23:59:59'), 'Asia/Jakarta');

                    $durasiJam = $this->hitungJamKerja($startAt, $endAt);

                    $skorDurasi = match (true) {
                        $durasiJam <= 4 => 100,
                        $durasiJam <= 8 => 80,
                        $durasiJam <= 24 => 60,
                        default => 30,
                    };

                    $bobot = match ($ticket->tingkat_kesulitan) {
                        'Major' => 1.5,
                        'Moderate' => 1.2,
                        default => 1.0,
                    };

                    $totalSkorError += min(100, $skorDurasi * $bobot);
                } catch (\Exception $e) {
                    continue;
                }
            }

            $rataSkorError = $jumlahError > 0 ? $totalSkorError / $jumlahError : 0;
        }

        $skorKualitas = ($skorRasio * 0.5) + ($rataSkorError * 0.5);

        $progress = $nilaiTarget > 0 ? ($skorKualitas / $nilaiTarget) * 100 : 0;

        return min(100, round($progress, 1));
    }

    private function calculateProgressKetepatanWaktuPenyelesaianFitur($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        $start = Carbon::create($tahun, 1, 1, 0, 0, 0, 'Asia/Jakarta');
        $end = Carbon::create($tahun, 12, 31, 23, 59, 59, 'Asia/Jakarta');

        $idKaryawans = detailPersonKPI::where('detailTargetKey', $detail->id)
            ->pluck('id_karyawan')
            ->unique()
            ->toArray();

        if (empty($idKaryawans)) {
            return 0;
        }

        $picNames = karyawan::whereIn('id', $idKaryawans)
            ->pluck('nama_lengkap')
            ->map(fn($nama) => explode(' ', trim($nama))[0] ?? '')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($picNames)) {
            return 0;
        }

        $picJabatan = karyawan::whereIn('id', $idKaryawans)
            ->pluck('jabatan')
            ->filter()
            ->unique()
            ->map(fn($n) => ucwords(strtolower($n)))
            ->values()
            ->toArray();

        $jabatanFilter = array_map(function ($jabatan) {
            return match (strtolower($jabatan)) {
                'programmer', 'koordinator itsm' => 'Programming',
                'technical support' => 'Technical Support',
                'tim digital' => 'Tim Digital',
                default => $jabatan,
            };
        }, $picJabatan);

        $jabatanFilter = array_unique(array_filter($jabatanFilter));

        if (empty($jabatanFilter)) {
            return 0;
        }

        $ticketQuery = Tickets::whereIn('keperluan', $jabatanFilter)
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('tanggal_selesai');

        if ($personId !== null) {
            $karyawanData = karyawan::find($personId);
            if (!$karyawanData) {
                return 0;
            }

            $firstName = explode(' ', trim($karyawanData->nama_lengkap))[0] ?? '';

            $firstName = match ($firstName) {
                'Stepanus' => 'Stefan',
                'Jonathan' => 'Valen',
                default => $firstName,
            };

            $ticketQuery->where('pic', $firstName);
        } else {
            $ticketQuery->whereIn('pic', $picNames);
        }

        $tickets = $ticketQuery->get();

        if ($tickets->isEmpty()) {
            return 0;
        }

        $metCount = 0;
        $total = $tickets->count();

        foreach ($tickets as $ticket) {
            try {
                $priority = 'Low';

                if (in_array(strtolower($ticket->tingkat_kesulitan), ['major', 'moderate'])) {
                    $priority = 'High';
                } elseif ($ticket->kategori === 'Error (Aplikasi)') {
                    $priority = 'Medium';
                }

                $startAt = Carbon::parse($ticket->created_at, 'Asia/Jakarta');

                $endAt = strlen($ticket->tanggal_selesai) > 10
                    ? Carbon::parse($ticket->tanggal_selesai, 'Asia/Jakarta')
                    : Carbon::parse($ticket->tanggal_selesai . ' ' . ($ticket->jam_selesai ?? '23:59:59'), 'Asia/Jakarta');

                $actualHours = $this->hitungJamKerja($startAt, $endAt);

                $slaMet = match ($priority) {
                    'High' => $actualHours <= 24,
                    'Medium' => $actualHours <= 40,
                    default => true,
                };

                if ($slaMet) {
                    $metCount++;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        $realisasiPersen = ($metCount / $total) * 100;
        $progress = $nilaiTarget > 0 ? ($realisasiPersen / $nilaiTarget) * 100 : 0;

        return min(100, round($progress, 1));
    }
    //TS
    private function calculateTingkatKeberhasilanSupportMemenuhiSLA($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $start = Carbon::create($tahun, 1, 1, 0, 0, 0, 'Asia/Jakarta');
        $end = Carbon::create($tahun, 12, 31, 23, 59, 59, 'Asia/Jakarta');

        $idKaryawans = detailPersonKPI::where('detailTargetKey', $detail->id)
            ->pluck('id_karyawan')
            ->unique()
            ->toArray();

        if (empty($idKaryawans)) {
            return 0;
        }

        $picNames = karyawan::whereIn('id', $idKaryawans)
            ->pluck('nama_lengkap')
            ->map(fn($nama) => explode(' ', trim($nama))[0] ?? '')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($picNames)) {
            return 0;
        }

        $picJabatan = karyawan::whereIn('id', $idKaryawans)
            ->pluck('jabatan')
            ->filter()
            ->unique()
            ->map(fn($n) => strtolower($n))
            ->values()
            ->toArray();

        $keperluanPatterns = [];

        foreach ($picJabatan as $jabatan) {
            if (str_contains($jabatan, 'programmer') || str_contains($jabatan, 'koordinator itsm')) {
                $keperluanPatterns[] = 'Programming';
            } elseif (str_contains($jabatan, 'technical support')) {
                $keperluanPatterns[] = 'Technical Support';
            }
        }

        $keperluanPatterns = array_unique($keperluanPatterns);

        if (empty($keperluanPatterns)) {
            return 0;
        }

        $ticketQuery = DB::table('tickets')
            ->whereIn('keperluan', $keperluanPatterns)
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('tanggal_selesai');

        if ($personId !== null) {
            $karyawanData = karyawan::find($personId);
            if (!$karyawanData) {
                return 0;
            }

            $firstName = explode(' ', trim($karyawanData->nama_lengkap))[0] ?? '';
            $ticketQuery->where('pic', $firstName);
        } else {
            $ticketQuery->whereIn('pic', $picNames);
        }

        $tickets = $ticketQuery->get();

        if ($tickets->isEmpty()) {
            return 0;
        }

        $total = 0;
        $met = 0;

        foreach ($tickets as $ticket) {
            try {
                $createdAt = Carbon::parse($ticket->created_at, 'Asia/Jakarta');

                if (empty($ticket->tanggal_selesai)) {
                    continue;
                }

                $resolvedAt = Carbon::parse(
                    strlen($ticket->tanggal_selesai) > 10
                        ? $ticket->tanggal_selesai
                        : $ticket->tanggal_selesai . ' ' . ($ticket->jam_selesai ?? '23:59:59'),
                    'Asia/Jakarta'
                );

                $startAt = $createdAt;

                if (!empty($ticket->tanggal_response) && !empty($ticket->jam_response)) {
                    $startAt = Carbon::createFromFormat(
                        'Y-m-d H:i:s',
                        $ticket->tanggal_response . ' ' . $ticket->jam_response,
                        'Asia/Jakarta'
                    );
                }

                $hours = $this->hitungJamKerja($startAt, $resolvedAt);

                $total++;

                if ($hours <= 8) {
                    $met++;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        if ($total === 0) {
            return 0;
        }

        return round(($met / $total) * 100, 1);
    }

    private function calculateKualitasLayananExam($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $query = PenilaianExam::selectRaw('id_rkm, AVG(nilai_emote) as nilai')
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('id_rkm')
            ->groupBy('id_rkm');

        $data = $query->get();

        $totalPenilaian = $data->count();

        if ($totalPenilaian == 0) {
            return 0.0;
        }

        $qualifiedPenilaian = $data
            ->filter(function ($item) {
                return $item->nilai >= 3.5;
            })
            ->count();

        $progress = ($qualifiedPenilaian / $totalPenilaian) * 100;

        return round($progress, 1);
    }

    //all jabatan kecuali Kooordinator ITSM
    private function calculateProgressKepuasanClientITSM($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $allScores = [];

        $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])->get();

        foreach ($feedbacks as $fb) {
            $f1 = is_numeric($fb->F1) ? (float) $fb->F1 : 0;
            $f2 = is_numeric($fb->F2) ? (float) $fb->F2 : 0;
            $f3 = is_numeric($fb->F3) ? (float) $fb->F3 : 0;
            $f4 = is_numeric($fb->F4) ? (float) $fb->F4 : 0;
            $f5 = is_numeric($fb->F5) ? (float) $fb->F5 : 0;

            $avg = ($f1 + $f2 + $f3 + $f4 + $f5) / 5;

            // Pastikan tetap di skala 1 - 4
            $avg = min(4, max(1, $avg));

            $allScores[] = $avg;
        }

        if (empty($allScores)) {
            return 0;
        }

        $totalResponden = count($allScores);
        $respondenPuas = 0;

        foreach ($allScores as $skor) {
            if ($skor >= 3.0) {
                $respondenPuas++;
            }
        }

        $progress = ($respondenPuas / $totalResponden) * 100;

        return round($progress, 1);
    }

    private function calculateInovationAdaptionRate($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        $totalIde = IdeInovasi::whereYear('created_at', $tahun)->count();

        if ($totalIde <= 0) {
            return 0;
        }

        $progress = ($totalIde / $totalIde) * 100;

        return round($progress, 1);
    }

    //Education
    //Instruktur
    private function calculateKepuasanPesertaPelatihan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $allScores = [];

        if ($personId !== null) {
            $kodeKaryawan = karyawan::where('id', $personId)->first();

            if ($kodeKaryawan) {
                $rkmList = RKM::where('instruktur_key', $kodeKaryawan->kode_karyawan)->orWhere('instruktur_key2', $kodeKaryawan->kode_karyawan)->orWhere('asisten_key', $kodeKaryawan->kode_karyawan)->get();

                if (!$rkmList->isEmpty()) {
                    $rkmIds = $rkmList->pluck('id_rkm')->filter()->toArray();

                    $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])
                        ->whereIn('id_rkm', $rkmIds)
                        ->get();

                    foreach ($feedbacks as $fb) {
                        $rkm = $rkmList->firstWhere('id_rkm', $fb->id_rkm);

                        if (!$rkm) {
                            continue;
                        }

                        $avg = 0;

                        if ($rkm->instruktur_key == $kodeKaryawan->kode_karyawan) {
                            $scores = [(float) ($fb->I1 ?? 0), (float) ($fb->I2 ?? 0), (float) ($fb->I3 ?? 0), (float) ($fb->I4 ?? 0), (float) ($fb->I5 ?? 0), (float) ($fb->I6 ?? 0), (float) ($fb->I7 ?? 0), (float) ($fb->I8 ?? 0)];
                            $avg = array_sum($scores) / 8;
                        } elseif ($rkm->instruktur_key2 == $kodeKaryawan->kode_karyawan) {
                            $scores = [(float) ($fb->I1b ?? 0), (float) ($fb->I2b ?? 0), (float) ($fb->I3b ?? 0), (float) ($fb->I4b ?? 0), (float) ($fb->I5b ?? 0), (float) ($fb->I6b ?? 0), (float) ($fb->I7b ?? 0), (float) ($fb->I8b ?? 0)];
                            $avg = array_sum($scores) / 8;
                        } elseif ($rkm->asisten_key == $kodeKaryawan->kode_karyawan) {
                            $scores = [(float) ($fb->I1as ?? 0), (float) ($fb->I2as ?? 0), (float) ($fb->I3as ?? 0), (float) ($fb->I4as ?? 0), (float) ($fb->I5as ?? 0), (float) ($fb->I6as ?? 0), (float) ($fb->I7as ?? 0), (float) ($fb->I8as ?? 0)];
                            $avg = array_sum($scores) / 8;
                        }

                        $avg = min(4, max(1, $avg));
                        $allScores[] = $avg;
                    }
                }
            }
        } else {
            $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])->get();

            foreach ($feedbacks as $fb) {
                $i1 = (float) ($fb->I1 ?? 0);
                $i2 = (float) ($fb->I2 ?? 0);
                $i3 = (float) ($fb->I3 ?? 0);
                $i4 = (float) ($fb->I4 ?? 0);
                $i5 = (float) ($fb->I5 ?? 0);
                $i6 = (float) ($fb->I6 ?? 0);
                $i7 = (float) ($fb->I7 ?? 0);
                $i8 = (float) ($fb->I8 ?? 0);
                $sumBase = $i1 + $i2 + $i3 + $i4 + $i5 + $i6 + $i7 + $i8;

                $i1b = (float) ($fb->I1b ?? 0);
                $i2b = (float) ($fb->I2b ?? 0);
                $i3b = (float) ($fb->I3b ?? 0);
                $i4b = (float) ($fb->I4b ?? 0);
                $i5b = (float) ($fb->I5b ?? 0);
                $i6b = (float) ($fb->I6b ?? 0);
                $i7b = (float) ($fb->I7b ?? 0);
                $i8b = (float) ($fb->I8b ?? 0);
                $sumB = $i1b + $i2b + $i3b + $i4b + $i5b + $i6b + $i7b + $i8b;

                if ($sumB > 0) {
                    $totalSum = $sumBase + $sumB;
                    $totalItem = 16;
                } else {
                    $totalSum = $sumBase;
                    $totalItem = 8;
                }

                if ($totalItem > 0) {
                    $avg = $totalSum / $totalItem;
                    $avg = min(4, max(1, $avg));
                    $allScores[] = $avg;
                }
            }
        }

        if (empty($allScores)) {
            return 0;
        }

        $totalResponden = count($allScores);
        $respondenPuas = 0;

        foreach ($allScores as $skor) {
            if ($skor >= 3.5) {
                $respondenPuas++;
            }
        }

        $progress = ($respondenPuas / $totalResponden) * 100;

        return round($progress, 1);
    }

    private function calculateUpselingLanjutanMateri($item, $personId): float
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();
        if ($personId !== null) {
            $kodeKaryawan = karyawan::where('id', $personId)->first();

            $rkmQuery = RKM::whereBetween('created_at', [$start, $end])
                ->where('instruktur_key', $kodeKaryawan->kode_karyawan)
                ->where('tanggal_akhir', '<', now());
        } else {
            $rkmQuery = RKM::whereBetween('created_at', [$start, $end])->where('tanggal_akhir', '<', now());
        }

        $totalData = $rkmQuery->count();

        if ($totalData === 0) {
            return 0.0;
        }

        $rkmIds = $rkmQuery->pluck('id');

        $totalRekomendasi = RekomendasiLanjutan::whereIn('id_rkm', $rkmIds)->count();

        $presentase = ($totalRekomendasi / $totalData) * 100;

        return round($presentase, 1);
    }

    private function calculateSertifikasiKompetensiInternal($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $startYear = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $endYear = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $query = detailPersonKPI::where('detailTargetKey', $detail->id);

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $detailPersons = $query->get();
        $totalData = $detailPersons->count();

        if ($totalData === 0) {
            return 0.0;
        }

        $countAchieved = 0;

        foreach ($detailPersons as $personItem) {
            $validSertifikasi = Sertifikasi::where('user_id', $personItem->id_karyawan)
                ->where('tanggal_berlaku_dari', '<=', $endYear)
                ->where(function ($q) use ($startYear) {
                    $q->where('tanggal_berlaku_sampai', '>=', $startYear)->orWhereNull('tanggal_berlaku_sampai');
                })
                ->count();

            if ($personId !== null) {
                $countAchieved += $validSertifikasi;
            } else {
                if ($validSertifikasi > 0) {
                    $countAchieved += 1;
                }
            }
        }

        if ($personId !== null) {
            $progress = max(100, $countAchieved);
        } else {
            $progress = $countAchieved;
        }

        return round($progress);
    }

    private function calculatePelatihanKompetensiEksternal($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $startYear = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $endYear = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $query = detailPersonKPI::where('detailTargetKey', $detail->id);

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $detailPersons = $query->get();
        $totalData = $detailPersons->count();

        if ($totalData === 0) {
            return 0.0;
        }

        $countAchieved = 0;

        foreach ($detailPersons as $personItem) {
            $validSertifikasi = Pelatihan::where('user_id', $personItem->id_karyawan)
                ->whereBetween('tanggal_selesai', [$startYear, $endYear])
                ->count();

            if ($personId !== null) {
                $countAchieved += $validSertifikasi;
            } else {
                if ($validSertifikasi > 0) {
                    $countAchieved += 1;
                }
            }
        }

        if ($personId !== null) {
            $progress = max(100, $countAchieved);
        } else {
            $progress = $countAchieved;
        }

        return round($progress);
    }

    private function calculatePresentaseKinerjaInstruktur($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0.0;
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0.0;
        }

        $isMonthly = true;
        $jamKerjaPerHari = 9;

        $now = Carbon::now();

        $liburNasional = HariLibur::pluck('tanggal')
            ->map(fn($t) => Carbon::parse($t)->toDateString())
            ->toArray();

        $startMonth = $now->copy()->startOfMonth();
        $endMonth   = $now->copy()->endOfMonth();
        $hariKerjaPerbulan = 0;

        for ($date = $startMonth->copy(); $date->lte($endMonth); $date->addDay()) {
            if (!$date->isWeekend() && !in_array($date->toDateString(), $liburNasional)) {
                $hariKerjaPerbulan++;
            }
        }

        $startYear = $now->copy()->startOfYear();
        $endYear   = $now->copy()->endOfYear();
        $hariKerjaPertahun = 0;

        for ($date = $startYear->copy(); $date->lte($endYear); $date->addDay()) {
            if (!$date->isWeekend() && !in_array($date->toDateString(), $liburNasional)) {
                $hariKerjaPertahun++;
            }
        }

        if ($isMonthly) {
            $startDate = Carbon::create($tahun, now()->month, 1)->startOfMonth();
            $endDate   = $startDate->copy()->endOfMonth();
            $targetJamPerOrang = $jamKerjaPerHari * $hariKerjaPerbulan;
        } else {
            $startDate = Carbon::create($tahun, 1, 1)->startOfYear();
            $endDate   = Carbon::create($tahun, 12, 31)->endOfYear();
            $targetJamPerOrang = $jamKerjaPerHari * $hariKerjaPertahun;
        }

        $totalJamMengajar = 0;

        if ($personId !== null) {
            $instrukturList = karyawan::where('id', $personId)->get();
        } else {
            $instrukturList = karyawan::where('status_aktif', '1')
                ->whereNot('jabatan', 'Outsource')
                ->where('kode_karyawan', 'NOT LIKE', 'OL%')
                ->whereNot('jabatan', 'Pilih Jabatan')
                ->whereNotNull('nip')
                ->whereNot('divisi', 'Direksi')
                ->where('jabatan', 'instruktur')
                ->get();
        }

        foreach ($instrukturList as $instruktur) {
            $kode = $instruktur->kode_karyawan;
            $idInstruktur = $instruktur->id;

            $activityDates = ActivityInstruktur::whereNull('id_rkm')
                ->whereBetween('activity_date', [$startDate, $endDate])
                ->whereHas('user', function ($q) use ($idInstruktur) {
                    $q->where('user_id', $idInstruktur);
                })
                ->pluck('activity_date')
                ->map(fn($date) => Carbon::parse($date)->toDateString())
                ->unique()
                ->toArray();

            $rkms = RKM::where('tanggal_awal', '<=', $endDate)
                ->where('tanggal_akhir', '>=', $startDate)
                ->where(function ($q) use ($kode) {
                    $q->where('instruktur_key', $kode)
                        ->orWhere('instruktur_key2', $kode)
                        ->orWhere('asisten_key', $kode);
                })->get();

            $rkmDates = [];
            foreach ($rkms as $rkm) {
                $rkmStart = Carbon::parse($rkm->tanggal_awal);
                $rkmEnd   = Carbon::parse($rkm->tanggal_akhir);

                $effectiveStart = $rkmStart->greaterThan($startDate) ? $rkmStart : $startDate;
                $effectiveEnd   = $rkmEnd->lessThan($endDate) ? $rkmEnd : $endDate;

                for ($date = $effectiveStart->copy(); $date->lte($effectiveEnd); $date->addDay()) {
                    $rkmDates[] = $date->toDateString();
                }
            }

            $allWorkingDays = array_unique(array_merge($activityDates, $rkmDates));

            $cutiDates = [];

            $cutis = pengajuancuti::where('id_karyawan', $instruktur->id)
                ->where('tanggal_awal', '<=', $endDate)
                ->where('tanggal_akhir', '>=', $startDate)
                ->get();

            foreach ($cutis as $cuti) {
                $cutiStart = Carbon::parse($cuti->tanggal_awal);
                $cutiEnd   = Carbon::parse($cuti->tanggal_akhir);

                $effectiveStart = $cutiStart->greaterThan($startDate) ? $cutiStart : $startDate;
                $effectiveEnd   = $cutiEnd->lessThan($endDate) ? $cutiEnd : $endDate;

                for ($date = $effectiveStart->copy(); $date->lte($effectiveEnd); $date->addDay()) {
                    $cutiDates[] = $date->toDateString();
                }
            }

            $cutiDates = array_unique($cutiDates);

            $allWorkingDays = array_diff($allWorkingDays, $cutiDates);

            $totalJamMengajar += count($allWorkingDays) * $jamKerjaPerHari;
        }

        $jumlahInstruktur = $instrukturList->count();

        $avgFactor = ($personId !== null || $jumlahInstruktur == 0) ? 1 : $jumlahInstruktur;

        $totalJamMengajarRataRata = $totalJamMengajar / $avgFactor;

        $targetJam = $targetJamPerOrang;

        if ($targetJam <= 0) {
            return 0.0;
        }

        $persentase = ($totalJamMengajarRataRata / $targetJam) * 100;

        return round($persentase, 2);
    }

    //Manager Education
    private function calculatePengembanganKurikulumPelatihan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        $dataMateri = Materi::whereYear('created_at', $tahun)->get();

        $totalBulanDalamTahun = 12;

        $bulanYangAdaMateri = $dataMateri
            ->pluck('created_at')
            ->map(function ($date) {
                return Carbon::parse($date)->month;
            })
            ->unique()
            ->count();

        if ($totalBulanDalamTahun == 0) {
            return 0;
        }

        $progress = $bulanYangAdaMateri;

        return round($progress);
    }

    private function calculatePeningkatanKnowledgeSharing($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        $dataMateri = ActivityInstruktur::whereYear('activity_date', $tahun)->where('activity_type', 'Sharing Knowledge')->get();

        $totalMingguDalamTahun = Carbon::create($tahun, 1, 1)->weeksInYear;

        $mingguYangSudahJalan = [];

        foreach ($dataMateri as $activity) {
            $nomorMinggu = Carbon::parse($activity->activity_date)->week;

            $mingguYangSudahJalan[$nomorMinggu] = true;
        }

        $jumlahMingguTerisi = count($mingguYangSudahJalan);

        if ($totalMingguDalamTahun == 0) {
            $progress = 0;
        } else {
            $progress = $jumlahMingguTerisi;
        }

        if ($progress > 100) {
            $progress = 100;
        }

        return round($progress);
    }

    private function calculatePeningkatanKontribusiPelatihan($item)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        $startDate = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $endDate = Carbon::createFromDate($tahun, 12, 31)->endOfDay();
        $targetKelas = 357;

        $totalKelas = 0;
        $totalKelasOL = 0;

        $rkmQuery = RKM::where('tanggal_awal', '<=', $endDate)
            ->where('tanggal_akhir', '>=', $startDate)
            ->whereNotNull('instruktur_key')
            ->where('instruktur_key', '!=', '-');

        $rkms = $rkmQuery->get();
        $processedRkmIds = [];

        foreach ($rkms as $rkm) {
            if (in_array($rkm->id, $processedRkmIds)) {
                continue;
            }
            $processedRkmIds[] = $rkm->id;

            $isOLClass = (
                $rkm->instruktur_key === 'OL' ||
                $rkm->instruktur_key2 === 'OL' ||
                $rkm->asisten_key === 'OL'
            );

            if ($isOLClass) {
                $totalKelasOL += 1;
            } else {
                $totalKelas += 1;
            }
        }

        $totalKelasValid = $totalKelas;

        if ($targetKelas <= 0) {
            return 0.0;
        }

        $persentase = ($totalKelasValid / $targetKelas) * 100;
        $progress = round($persentase, 2);

        return $progress;
    }

    private function calculateEvaluasiKinerjaInstruktur($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        $instrukturs = Karyawan::where('Divisi', '!=', 'Direksi')
            ->where('status_aktif', '1')
            ->where('jabatan', 'Instruktur')
            ->get();

        if ($instrukturs->isEmpty()) {
            return 0;
        }

        $startDate = Carbon::create($tahun, 1, 1);
        $endDate = min(Carbon::create($tahun, 12, 31), now());

        if ($startDate > $endDate) {
            return 0;
        }

        $period = CarbonPeriod::create($startDate, $endDate);

        $activities = ActivityInstruktur::whereYear('created_at', $tahun)
            ->whereIn('user_id', $instrukturs->pluck('id'))
            ->get()
            ->groupBy(function ($item) {
                return $item->user_id . '_' . Carbon::parse($item->created_at)->format('Y-m-d');
            });

        $totalHariKerja = 0;
        $totalAktif = 0;

        foreach ($period as $date) {
            if ($date->isWeekend()) {
                continue;
            }

            $totalHariKerja++;
            $dateKey = $date->format('Y-m-d');

            foreach ($instrukturs as $instruktur) {
                $key = $instruktur->id . '_' . $dateKey;

                if (isset($activities[$key])) {
                    $totalAktif++;
                }
            }
        }

        $totalKemungkinan = $totalHariKerja * $instrukturs->count();

        if ($totalKemungkinan == 0) {
            return 0;
        }

        $progress = ($totalAktif / $totalKemungkinan) * 100;

        return round($progress, 2);
    }

    private function calculatePembuatanArtikel($item, $personId) {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $startDate = carbon::create($tahun, '01', '01');
        $endDate = carbon::create($tahun, '12', '31');
        $response = Http::get('http://202.138.248.36:8003/api/filtered-articles')->json();

        $apiArtikel = collect($response['data'] ?? []);

        $getData = $apiArtikel->filter(function ($item) use ($startDate, $endDate) {
            $tanggal = Carbon::parse($item['tanggal']);

            return $tanggal->between($startDate, $endDate);
        });

        $totalData = $getData->count();

        if ($totalData == 0) {
            return 0;
        }

        $progress = ($totalData / 24) * 100;

        return round($progress, 2);
    }

    //Sales & Marketing
    //Sales
    private function calculateTargetPenjualanTahunan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        $totalSales = RKM::where('status', '0')
            ->whereYear('tanggal_awal', $tahun);

        if ($personId !== null) {

            $personId = detailPersonKPI::where('detailTargetKey', $detail->id)->first()?->id_karyawan;

            $kodeKaryawan = Karyawan::where('id', $personId)->value('kode_karyawan');
            if (!$kodeKaryawan) {
                return 0;
            }

            $totalSales = $totalSales->where('sales_key', $kodeKaryawan)
                ->select(DB::raw('SUM(CAST(harga_jual AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total_sales'))
                ->value('total_sales');
        } else {

            $totalSales = $totalSales
                ->select(DB::raw('SUM(CAST(harga_jual AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total_sales'))
                ->value('total_sales');
        }

        $dataTarget = targetKPI::with('detailTargetKPI')
            ->whereHas(
                'dataTarget',
                fn($q) =>
                $q->where('asistant_route', 'pemasukan kotor')
            )
            ->first();
        $targetGM   = ModelsTarget::where('quartal', 'All')->first() ?? null;

        $target = $dataTarget->detailTargetKPI->first()->nilai_target
            ?? $targetGM->target
            ?? 0;

        $progressRupiah = (float) ($totalSales ?? 0);

        $progress = $target > 0 ? ($progressRupiah / $target) * 100 : 0;

        return round($progress, 1);
    }

    // SPV Saless
    private function calculateMeningkatkanRevenuePerusahaan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $progress = 0;

        $peluang = ApprovalPendapatan::whereYear('created_at', $tahun)
            ->get();

        foreach ($peluang as $p) {
            $bersih = $p->total_penjualan_bersih;

            $progress += $bersih;
        }

        return round($progress);
    }

    private function calculateCustomerAcquisitionCost($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $labaKotor = $this->calculatePemasukanKotor($item, $personId);

        if ($labaKotor == 0) {
            return 0;
        }

        $karyawanIds = detailPersonKPI::where('detailTargetKey', $detail->id)->pluck('id_karyawan');
        $kodeKaryawanList = karyawan::whereIn('id', $karyawanIds)->pluck('kode_karyawan')->filter();

        if ($kodeKaryawanList->isEmpty()) {
            return 0;
        }

        $totalBiayaAkuisisi = perhitunganNetSales::whereHas('rkm', function ($query) use ($kodeKaryawanList, $tahun) {
            $query->whereIn('sales_key', $kodeKaryawanList)
                ->whereYear('tanggal_awal', $tahun);
        })
            ->whereBetween('tgl_pa', [$start, $end])
            ->get()
            ->sum(function ($record) {
                return ($record->transportasi ?? 0) +
                    ($record->akomodasi_peserta ?? 0) +
                    ($record->akomodasi_tim ?? 0) +
                    ($record->fresh_money ?? 0) +
                    ($record->entertaint ?? 0) +
                    ($record->souvenir ?? 0) +
                    ($record->cashback ?? 0) +
                    ($record->sewa_laptop ?? 0);
            });

        if ($totalBiayaAkuisisi > ($labaKotor * ($nilaiTarget / 100))) {
            $totalBiayaAkuisisi = $labaKotor * ($nilaiTarget / 100);
        }

        $progress = 0;

        if ($totalBiayaAkuisisi > 0) {
            $rasio = ($totalBiayaAkuisisi / $labaKotor) * 100;
            $batas = $nilaiTarget;
            $progress = ($batas / $rasio) * 100;
        }

        return round($progress, 1);
    }

    private function calculateBiayaAkuisisiClient($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        $peluang = Peluang::with('rkm.perhitunganNetSales')
            ->whereYear('created_at', $tahun)
            ->get();

        $actualCAC = 0;
        $targetTahunan = $this->calculatePemasukanKotor($item, $personId);

        foreach ($peluang as $p) {
            if ($p->tahap === 'merah') {
                if ($p->rkm && $p->rkm->perhitunganNetSales) {
                    foreach ($p->rkm->perhitunganNetSales as $perhitungan) {
                        $actualCAC +=
                            ($perhitungan->transportasi ?? 0) +
                            ($perhitungan->akomodasi_peserta ?? 0) +
                            ($perhitungan->akomodasi_tim ?? 0) +
                            ($perhitungan->fresh_money ?? 0) +
                            ($perhitungan->entertaint ?? 0) +
                            ($perhitungan->souvenir ?? 0) +
                            ($perhitungan->cashback ?? 0) +
                            ($perhitungan->sewa_laptop ?? 0);
                    }
                }
            }
        }

        if ($actualCAC <= 0) {
            return 0.0;
        }

        $maxCAC = ($nilaiTarget / 100) * $targetTahunan;

        if ($maxCAC <= 0) {
            return 0.0;
        }

        $progress = min(($maxCAC / $actualCAC) * 100, 100);

        return round($progress, 2);
    }

    //Adm Sales
    private function calculateLaporanMOM($item)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        $momCount = LaporanHarianSales::whereYear('created_at', $tahun)->count();

        $PACount = checklistRKM::whereYear('created_at', $tahun)->where('PA', '1')->count();
        $SuratKontrakCount = checklistRKM::whereYear('created_at', $tahun)->where('surat_kontrak', '1')->count();

        $rkmBase = RKM::whereYear('tanggal_awal', $tahun);

        $totalDataERegist = (clone $rkmBase)->count();

        $totalDataAboveERegist = (clone $rkmBase)
            ->whereNotNull('registrasi_form')
            ->count();

        $persenCalculationMom = $momCount == 0 ? 100 : 25;
        $persenCalculationERegist = $totalDataERegist == 0 ? 0 : 25;

        $progressMoM = $momCount > 0 ? ($momCount / $momCount) * $persenCalculationERegist : 0;
        $progressPA = $PACount > 0 ? ($PACount / $PACount) * $persenCalculationERegist : 0;
        $progressSuratKontrak = $SuratKontrakCount > 0 ? ($SuratKontrakCount / $SuratKontrakCount) * $persenCalculationERegist : 0;

        if ($progressMoM == 0) {
            $progressMoM = 0;
        }

        $progressERegist = $totalDataERegist > 0
            ? ($totalDataAboveERegist / $totalDataERegist) * $persenCalculationMom
            : 0;

        if ($progressERegist == 0) {
            $progressERegist = 0;
        }

        $progress = $progressMoM + $progressERegist + $progressPA + $progressSuratKontrak;

        if ($progress == 0) {
            return 0;
        }

        return round($progress, 1);
    }

    private function calculateAkurasiKelengkapanDataPenjualan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $rkms = RKM::with(['perhitunganNetSales', 'outstanding']) 
            ->whereYear('created_at', $tahun)->get();

        $totalRkmDenganPerhitungan = 0;
        $totalRkmAkurat = 0;

        foreach ($rkms as $rkm) {
            $listPerhitungan = $rkm->perhitunganNetSales;

            if (empty($listPerhitungan)) {
                continue;
            }

            $totalRkmDenganPerhitungan++;

            $listOutstanding = $rkm->outstanding;

            if (blank($listOutstanding)) {
                continue;
            }

            $sumKomponen = 0;

            $itemsPerhitungan = $listPerhitungan instanceof \Illuminate\Database\Eloquent\Collection
                ? $listPerhitungan
                : [$listPerhitungan];

            foreach ($itemsPerhitungan as $p) {
                $sumKomponen +=
                    (int)($p->transportasi ?? 0) +
                    (int)($p->akomodasi_peserta ?? 0) +
                    (int)($p->akomodasi_tim ?? 0) +
                    (int)($p->fresh_money ?? 0) +
                    (int)($p->entertaint ?? 0) +
                    (int)($p->souvenir ?? 0) +
                    (int)($p->cashback ?? 0) +
                    (int)($p->sewa_laptop ?? 0);
            }

            $sumOutstanding = 0;
            $itemsOutstanding = $listOutstanding instanceof \Illuminate\Database\Eloquent\Collection
                ? $listOutstanding
                : [$listOutstanding];

            foreach ($itemsOutstanding as $o) {
                $sumOutstanding += (int)($o->net_sales ?? 0);
            }

            if ($sumKomponen === $sumOutstanding) {
                $totalRkmAkurat++;
            }
        }

        if ($totalRkmDenganPerhitungan == 0) {
            return 0.0;
        }

        $persentase = ($totalRkmAkurat / $totalRkmDenganPerhitungan) * 100;

        return round($persentase, 1);
    }

    private function calculateEvaluasiKinerjaSales($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;

        $Saless = Karyawan::where('status_aktif', '1')->whereNot('jabatan', 'Outsource')->where('kode_karyawan', 'NOT LIKE', 'OL%')->whereNot('jabatan', 'Pilih Jabatan')->where('nip', '!=', null)->whereNot('divisi', 'Direksi')
            ->where('jabatan', 'Sales')
            ->get();

        if ($Saless->isEmpty()) {
            return 0;
        }

        // ✅ PERBAIKAN 1: Hanya hitung dari awal tahun sampai hari ini
        $startDate = Carbon::create($tahun, 1, 1);
        $endDate = min(Carbon::create($tahun, 12, 31), now());

        // Jika tanggal mulai lebih besar dari tanggal akhir, return 0
        if ($startDate > $endDate) {
            return 0;
        }

        $period = CarbonPeriod::create($startDate, $endDate);

        // ✅ OPTIMASI 2: Load semua aktivitas sekali saja (hindari query di dalam loop)
        $activities = Aktivitas::whereYear('created_at', $tahun)
            ->whereIn('id_sales', $Saless->pluck('kode_karyawan'))
            ->get()
            ->groupBy(function ($item) {
                return $item->id_sales . '_' . Carbon::parse($item->created_at)->format('Y-m-d');
            });

        $totalHariKerja = 0;
        $totalAktif = 0;

        foreach ($period as $date) {
            if ($date->isWeekend()) {
                continue;
            }

            $totalHariKerja++;
            $dateKey = $date->format('Y-m-d');

            foreach ($Saless as $sales) {
                // Cek di array yang sudah di-load, bukan query database
                $key = $sales->kode_karyawan . '_' . $dateKey;

                if (isset($activities[$key])) {
                    $totalAktif++;
                }
            }
        }

        $totalKemungkinan = $totalHariKerja * $Saless->count();

        if ($totalKemungkinan == 0) {
            return 0;
        }

        $progress = ($totalAktif / $totalKemungkinan) * 100;

        return round($progress, 2);
    }

    //All Sales
    private function calculatePeningkatanKemampuanKompetensiSales($item, $personId)
    {
        $nilaiUkur = 90;
        $totalPenilaian = 0;
        $totalMelebihiNilaiUkur = 0;

        $karyawanJabatan = Karyawan::where('divisi', 'Sales & Marketing')
            ->where('status_aktif', '1')
            ->where('jabatan', '!=', 'Tim Digital')
            ->where('jabatan', '!=', 'GM')
            ->pluck('jabatan')
            ->map(fn($jabatan) => strtolower(trim($jabatan)))
            ->unique()
            ->toArray();

        $userQuery = User::whereHas('karyawan', function ($query) use ($personId, $karyawanJabatan) {
            $query->where('divisi', 'Sales & Marketing')
                ->whereIn('jabatan', $karyawanJabatan);

            if ($personId !== null) {
                $query->where('id', $personId);
            }
        });

        $salesUsernames = $userQuery->pluck('username')
            ->filter()
            ->map(fn($username) => strtolower(trim($username)))
            ->toArray();

        if (empty($salesUsernames)) {
            return 0;
        }

        try {
            $apiUrl = env('MOODLE_API_URL');
            $apiUsername = env('MOODLE_API_USERNAME');
            $apiPassword = env('MOODLE_API_PASSWORD');

            $response = Http::withBasicAuth($apiUsername, $apiPassword)
                ->timeout(15)
                ->get($apiUrl);

            if (!$response->successful()) {
                return 0;
            }

            $moodleData = $response->json();
        } catch (Exception $e) {
            return 0;
        }

        if (empty($moodleData) || !is_array($moodleData)) {
            return 0;
        }

        $moodleDataValid = array_values($moodleData['data']);
        $moodleDataCount = count($moodleDataValid);

        for ($i = 0; $i < $moodleDataCount; $i++) {
            if (!isset($moodleDataValid[$i]) || !is_array($moodleDataValid[$i])) {
                continue;
            }

            $data = $moodleDataValid[$i];

            $moodleUsername = strtolower(trim($data['username']));

            if (in_array($moodleUsername, $salesUsernames)) {
                $totalPenilaian++;
                $score = (float) ($data['score'] ?? 0);

                if ($score > $nilaiUkur) {
                    $totalMelebihiNilaiUkur++;
                }
            }
        }

        if ($totalPenilaian === 0) {
            return 0;
        }

        $progress = ($totalMelebihiNilaiUkur / $totalPenilaian) * 100;

        return round($progress, 1);
    }

    //Admin Holding
    private function calculateKetepatanWaktuPo($item)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $pos = NomorModul::with('moduls')
            ->whereYear('created_at', $tahun)
            ->get();



        if ($pos->isEmpty()) {
            return 0.0;
        }

        $totalPercent = 0;
        $count = 0;

        foreach ($pos as $po) {

            if (!$po->uploaded) {
                continue;
            }

            $uploaded = Carbon::parse($po->uploaded)->startOfDay();

            foreach ($po->moduls as $modul) {

                if (!$modul->awal_training) {
                    continue;
                }

                $awalTraining = Carbon::parse($modul->awal_training)->startOfDay();

                $daysBefore = $awalTraining->diffInDays($uploaded);

                if ($daysBefore >= 7) {
                    $percent = 100;
                } elseif ($daysBefore > 0) {
                    $percent = ($daysBefore * 100) / 7;
                } else {
                    $percent = 0;
                }

                $totalPercent += $percent;
                $count++;
            }
        }

        if ($count === 0) {
            return 0.0;
        }

        $progress = $totalPercent / $count;

        return round($progress, 1);
    }

    private function calculatekualitasDokumentasiSupportDanProctor($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $registrasi = Registrasi::whereYear('created_at', $tahun)
            ->count();

        if ($registrasi === 0) {
            return 0.0;
        }

        $dataTerdokumentasi = DokumentasiExam::whereYear('created_at', $tahun)
            ->where(function ($q) {
                $q->whereNotNull('skor')
                    ->orWhereNotNull('dokumentasi');
            })
            ->count();

        $progress = ($dataTerdokumentasi / $registrasi) * 100;

        return round($progress, 2);
    }

    private function defaultResult()
    {
        return [
            'progress' => 0,
            'gap' => 0,
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
        ];
    }

    //detail_target
    public function detailData(Request $request)
    {
        $idTarget = $request->id;
        $personId = $request->idUser ?? null;

        $query = targetKPI::with(['karyawan', 'detailTargetKPI.detailPersonKPI.karyawan', 'detailTargetKPI.dataTarget'])
            ->where('id', $idTarget);

        if ($personId !== null) {
            $query->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($personId) {
                $q->where('id_karyawan', $personId);
            });
        }

        $detailList = $query->get();

        $data = [
            'detail' => $detailList
                ->map(function ($itemDetail) use ($personId) {
                    $detail = $itemDetail->detailTargetKPI->first();
                    if (!$detail) {
                        return null;
                    }

                    $tenggat_waktu = $this->resolveTenggatWaktu($detail);
                    $dataCalculation = $this->getCalculationByRoute($itemDetail, $personId);

                    $dataOutput = [
                        'pembuat' => $itemDetail->karyawan->nama_lengkap ?? null,
                        'judul' => $itemDetail->judul,
                        'condition' => $detail->dataTarget?->asistant_route,
                        'deskripsi' => $itemDetail->deskripsi,
                        'jabatan_kpi' => $detail->jabatan,
                        'divisi_kpi' => $detail->divisi,
                        'karyawan' => $itemDetail->detailTargetKPI
                            ->flatMap(function ($detailItem) {
                                return $detailItem->detailPersonKPI->map(function ($person) {
                                    return [
                                        'id' => $person->id,
                                        'nama_lengkap' => $person->karyawan->nama_lengkap ?? null,
                                        'jabatan' => $person->karyawan->jabatan ?? null,
                                        'presentase_kemampuan' => $person->presentase_kemampuan ?? 0,
                                        'presentase_standar' => $person->presentase_standar ?? 100,
                                    ];
                                });
                            })
                            ->values(),
                        'jangka_target' => $detail->jangka_target,
                        'detail_jangka' => $detail->detail_jangka,
                        'tipe_target' => $detail->tipe_target,
                        'nilai_target' => $detail->nilai_target,
                        'tenggat_waktu' => $tenggat_waktu,
                        'data_detail' => $dataCalculation,
                    ];

                    return ['data' => $dataOutput];
                })
                ->filter()
                ->values(),
        ];

        return response()->json($data);
    }

    private function resolveTenggatWaktu($detail)
    {
        $tenggat_waktu = null;
        $jangka = strtolower($detail->jangka_target ?? '');
        $detailJangka = $detail->detail_jangka;

        switch ($jangka) {
            case 'tahunan':
                $year = (int) $detailJangka;
                $tenggat_waktu = date('Y-m-d', strtotime("last day of December $year"));
                break;
            case 'bulanan':
                if ($detailJangka && preg_match('/(\d{4})-(\d{2})/', $detailJangka, $m)) {
                    $lastDay = date('t', strtotime("$detailJangka-01"));
                    $tenggat_waktu = "$detailJangka-$lastDay";
                }
                break;
            case 'kuartalan':
                if ($detailJangka && preg_match('/(\d{4})-Q([1-4])/', $detailJangka, $m)) {
                    $monthEnd = ((int)$m[2]) * 3;
                    $lastDay = date('t', strtotime("{$m[1]}-$monthEnd-01"));
                    $tenggat_waktu = "{$m[1]}-$monthEnd-$lastDay";
                }
                break;
            case 'mingguan':
                if ($detailJangka && preg_match('/(\d{4})-W(\d{1,2})/', $detailJangka, $m)) {
                    $firstDay = strtotime("{$m[1]}-01-01");
                    $weekStart = strtotime("+" . (($m[2] - 1) * 7) . " days", $firstDay);
                    $tenggat_waktu = date('Y-m-d', strtotime("+6 days", $weekStart));
                }
                break;
            default:
                $tenggat_waktu = $detailJangka;
        }

        return $tenggat_waktu;
    }

    private function getCalculationByRoute($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();
        $route  = strtolower($detail->dataTarget?->asistant_route ?? '');

        // --- Target Detail Office - GM ---
        if ($route === 'kepuasan pelanggan') {
            return $this->calculateProgressKepuasanPelangganDetail($itemDetail);
        } elseif ($route === 'pemasukan kotor') {
            return $this->calculatePemasukanKotorDetail($itemDetail);
        } elseif ($route === 'pemasukan bersih') {
            return $this->calculatePemasukanBersihDetail($itemDetail);
        } elseif ($route === 'target penjualan project tahunan') {
            return $this->calculateTargetPenjualanProjectTahunanDetail($itemDetail, $personId);
        } elseif ($route === 'rasio biaya operasional terhadap revenue') {
            return $this->calculateRasioBiayaOperasionalTerhadapRevenueDetail($itemDetail);
        } elseif ($route === 'performa kpi departemen') {
            return $this->calculatePerformaKPIDepartemenDetail($itemDetail, $personId);
        }

        // --- CS ---
        elseif ($route === 'peserta puas dengan pelayanan dan fasilitas training') {
            return $this->calculatePesertaPuasDenganPelayananDanFasilitasTrainingDetail($itemDetail);
        } elseif ($route === 'dorong inovasi pelayanan') {
            return $this->calculateDorongInovasiPelayananDetail($itemDetail);
        } elseif ($route === 'penanganan komplain peserta') {
            return $this->calculatePenangananKomplainPersetaDetail($itemDetail);
        } elseif ($route === 'report persiapan kelas') {
            return $this->calculateReportPersiapanKelasDetail($itemDetail, $personId);
        }

        // --- Finance ---
        elseif ($route === 'outstanding') {
            return $this->calculateOutstandingDetail($itemDetail);
        } elseif ($route === 'inisiatif efisiensi keuangan') {
            return $this->calculateInisiatifEfisiensiKeuanganDetail($itemDetail);
        } elseif ($route === 'mengurangi manual work dan error') {
            return $this->calculateMengurangiManualWorkDanErrorDetail($itemDetail);
        } elseif ($route === 'laporan analisis keuangan') {
            return $this->calculateLaporanAnalisisKeuanganDetail($itemDetail);
        } elseif ($route === 'penyelesaian tagihan perusahaan') {
            return $this->calculatePenyelesaianTagihanPerusahaanDetail($itemDetail, $personId);
        } elseif ($route === 'pencairan biaya operasional') {
            return $this->calculatePencairanBiayaOperasionalDetail($itemDetail, $personId);
        } elseif ($route === 'akurasi pencatatan masuk') {
            return $this->calculateAkurasiPencatatanMasukDetail($itemDetail);
        }

        // --- HRD ---
        elseif ($route === 'pelaksanaan kegiatan karyawan') {
            return $this->calculatePelaksanaanKegiatanKaryawanDetail($itemDetail);
        } elseif ($route === 'pengeluaran biaya karyawan') {
            return $this->calculatePengeluaranBiayaKaryawanDetail($itemDetail);
        } elseif ($route === 'administrasi karyawan') {
            return $this->calculateAdministrasiKaryawanDetail($itemDetail, $personId);
        }

        // --- Driver ---
        elseif ($route === 'perbaikan kendaraan') {
            return $this->calculatePerbaikanKendaraanDetail($itemDetail, $personId);
        } elseif ($route === 'kontrol pengeluaran transportasi') {
            return $this->calculateKontrolPengeluaranTransportasiDetail($itemDetail, $personId);
        } elseif ($route === 'report kondisi kendaraan') {
            return $this->calculateReportKondisiKendaraanDetail($itemDetail, $personId);
        } elseif ($route === 'feedback kenyamanan berkendara') {
            return $this->calculateFeedbackKenyamananBerkendaraDetail($itemDetail, $personId);
        }

        // ADM Holding
        elseif ($route === 'ketepatan waktu po') {
            return $this->calculateKetepatanWaktuPoDetail($itemDetail);
        } elseif ($route === 'kualitas dokumentasi support dan proctor') {
            return $this->calculatekualitasDokumentasiSupportDanProctorDetail($itemDetail);
        }

        // --- OB ---
        elseif ($route === 'feedback kebersihan dan kenyamanan') {
            return $this->calculateFeedbackKebersihanDanKenyamananDetail($itemDetail);
        } elseif ($route === 'penyelesaian tugas harian') {
            return $this->calculatePenyelesaianTugasHarianDetail($itemDetail, $personId);
        }

        // --- ITSM ---
        elseif ($route === 'kepuasan client itsm') {
            return $this->calculateProgressKepuasanClientITSMDetail($itemDetail);
        } elseif ($route === 'inovation adaption rate') {
            return $this->calculateInovationAdaptionRateDetail($itemDetail, $personId);
        }

        //Koordinator ITSM
        elseif ($route === 'availability sistem internal kritis') {
            return $this->calculateAvailabilitySistemInternalKritisDetail($itemDetail);
        } elseif ($route === 'meningkatkan kepuasan dan loyalitas peserta/client') {
            return $this->calculateMeningkatkanKepuasanDanLoyalitasPesertaDetail($itemDetail);
        } elseif ($route === 'persentase gap kompetensi tim terhadap standar skill') {
            return $this->calculatePersentaseGapKompetensiDetail($itemDetail, $personId);
        }

        // --- Programmer ---
        elseif ($route === 'ketepatan waktu penyelesaian fitur') {
            return $this->calculateProgressKetepatanWaktuPenyelesaianFiturDetail($itemDetail, $personId);
        } elseif ($route === 'mengukur kualitas aplikasi agar minim bug') {
            return $this->calculateMengukurKualitasAplikasiAgarMinimBugDetail($itemDetail, $personId);
        }

        // --- Tim Digital ---
        elseif ($route === 'konsistensi campaign digital') {
            return $this->calculateKonsistensiCampaignDigitalDetail($itemDetail);
        } elseif ($route === 'efektifitas digital marketing') {
            return $this->calculateEfektifitasDiitalMarketingDetail($itemDetail, $personId);
        }

        //project administrator & usiness support
        elseif ($route === 'pendapatan penjualan project') {
            return $this->calculatePendapatanPenjualanProjectDetail($itemDetail);
        } elseif ($route === 'leads project') {
            return $this->calculateLeadsProjectDetail($itemDetail);
        }

        // --- TS ---
        elseif ($route === 'keberhasilan support memenuhi sla') {
            return $this->calculateTingkatKeberhasilanSupportMemenuhiSLADetail($itemDetail, $personId);
        } elseif ($route === 'kualitas layanan exam') {
            return $this->calculateKualitasLayananExamDetail($itemDetail, $personId);
        }

        // --- Education (Instruktur) ---
        elseif ($route === 'kepuasan peserta pelatihan') {
            return $this->calculateKepuasanPesertaPelatihanDetail($itemDetail, $personId);
        } elseif ($route === 'upseling lanjutan materi') {
            return $this->calculateUpselingLanjutanMateriDetail($itemDetail, $personId);
        } elseif ($route === 'sertifikasi kompetensi internal') {
            return $this->calculateSertifikasiKompetensiInternalDetail($itemDetail, $personId);
        } elseif ($route === 'pelatihan kompetensi eksternal') {
            return $this->calculatePelatihanKompetensiEksternalDetail($itemDetail, $personId);
        } elseif ($route === 'presentase kinerja instruktur') {
            return $this->calculatePresentaseKinerjaInstrukturDetail($itemDetail, $personId);
        }

        // --- Education Manager ---
        elseif ($route === 'pengembangan kurikulum pelatihan') {
            return $this->calculatePengembanganKurikulumPelatihanDetail($itemDetail);
        } elseif ($route === 'peningkatan knowledge sharing') {
            return $this->calculatePeningkatanKnowledgeSharingDetail($itemDetail);
        } elseif ($route === 'peningkatan kontribusi pelatihan') {
            return $this->calculatePeningkatanKontribusiPelatihanDetail($itemDetail);
        } elseif ($route === 'evaluasi kinerja instruktur') {
            return $this->calculateEvaluasiKinerjaInstrukturDetail($itemDetail);
        } elseif ($route === 'pembuatan artikel') {
            return $this->calculatePembuatanArtikelDetail($itemDetail);
        }

        //Sales & Marketing
        // Sales
        elseif ($route === 'target penjualan tahunan') {
            return $this->calculateTargetPenjualanTahunanDetail($itemDetail, $personId);
        } elseif ($route === 'peningkatan kemampuan kompetensi sales') {
            return $this->calculatePeningkatanKemampuanKompetensiSalesDetail($itemDetail, $personId);
        } elseif ($route === 'customer acquisition cost') {
            return $this->calculateCustomerAcquisitionCostDetail($itemDetail, $personId);
        }

        // SPV Sales
        elseif ($route === 'meningkatkan revenue perusahaan') {
            return $this->calculateMeningkatkanRevenuePerusahaanDetail($itemDetail);
        } elseif ($route === 'evaluasi kinerja sales') {
            return $this->calculateEvaluasiKinerjaSalesDetail($itemDetail);
        } elseif ($route === 'biaya akuisisi perclient') {
            return $this->calculateBiayaAkuisisiClientDetail($itemDetail);
        }

        // ADM Sales
        elseif ($route === 'laporan mom') {
            return $this->calculateLaporanMOMDetail($itemDetail);
        } elseif ($route === 'akurasi kelengkapan data penjualan') {
            return $this->calculateAkurasiKelengkapanDataPenjualanDetail($itemDetail, $personId);
        } elseif ($route === 'todo administrasi') {
            return $this->calculateTodoAdministrasiDetail($itemDetail);
        }

        return null;
    }

    public function getChartStatistics(Request $request)
    {
        $user = auth()->user();
        $userJabatan = $user ? trim($user->jabatan) : null;

        $allowedJabatans = null;

        if ($userJabatan) {
            $jLower = strtolower($userJabatan);

            if (in_array($jLower, ['gm', 'hrd', 'direktur utama', 'direktur'])) {
                $allowedJabatans = null;
            } elseif ($jLower === 'koordinator itsm') {
                $allowedJabatans = ['Programmer', 'Tim Digital', 'Technical Support', 'Koordinator ITSM'];
            } elseif ($jLower === 'education manager') {
                $allowedJabatans = ['Instruktur', 'Education Manager'];
            } elseif ($jLower === 'spv sales') {
                $allowedJabatans = ['SPV Sales', 'Sales'];
            } else {
                $allowedJabatans = [$userJabatan];
            }
        }

        $requestJabatan = $request->jabatan ? trim($request->jabatan) : null;
        $finalJabatanFilter = null;

        if ($allowedJabatans === null) {
            $finalJabatanFilter = $requestJabatan ? [$requestJabatan] : null;
        } else {
            if ($requestJabatan) {
                $isPermitted = false;
                foreach ($allowedJabatans as $allowed) {
                    if (strtolower($allowed) === strtolower($requestJabatan)) {
                        $isPermitted = true;
                        break;
                    }
                }

                if ($isPermitted) {
                    $finalJabatanFilter = [$requestJabatan];
                } else {
                    $finalJabatanFilter = $allowedJabatans;
                }
            } else {
                $finalJabatanFilter = $allowedJabatans;
            }
        }

        $tahunFilter = $request->tahun ?? date('Y');
        $idTargetFilter = $request->id_target ?? null;

        $query = targetKPI::with(['karyawan', 'detailTargetKPI.detailPersonKPI.karyawan']);

        if ($idTargetFilter) {
            $query->where('id', $idTargetFilter);
        }

        $query->whereYear('created_at', $tahunFilter);

        if ($finalJabatanFilter !== null && !empty($finalJabatanFilter)) {
            $query->whereHas('detailTargetKPI', function ($q) use ($finalJabatanFilter) {
                if (count($finalJabatanFilter) > 1) {
                    $q->whereIn('jabatan', $finalJabatanFilter);
                } else {
                    $q->where('jabatan', $finalJabatanFilter[0]);
                }
            });
        }

        $targets = $query->get();

        $allTargetData = [];
        $monthlyAggregates = [];
        $jabatanAggregates = [];
        $jabatanMonthlyAggregates = [];

        $stats = [
            'total_targets' => 0,
            'completed_targets' => 0,
            'achieved_targets' => 0,
            'in_progress_targets' => 0,
        ];

        foreach ($targets as $target) {
            $detail = $target->detailTargetKPI->first();

            if (!$detail || !$detail->nilai_target || (float) $detail->nilai_target <= 0) {
                continue;
            }

            if ($finalJabatanFilter !== null) {
                $isDetailAllowed = false;
                foreach ($finalJabatanFilter as $allowed) {
                    if (strtolower($detail->jabatan) === strtolower($allowed)) {
                        $isDetailAllowed = true;
                        break;
                    }
                }
                if (!$isDetailAllowed) {
                    continue;
                }
            }

            $calculationData = $this->getCalculationByRoute($target, null);
            if (!$calculationData || !isset($calculationData['progress'])) {
                continue;
            }

            $progress = (float) $calculationData['progress'];
            $nilaiTarget = (float) $detail->nilai_target;
            $jabatan = $detail->jabatan ?? 'Unknown';
            $monthlyData = $calculationData['monthly_data'] ?? [];

            $stats['total_targets']++;

            if ($progress >= 100) {
                $stats['completed_targets']++;
            }

            if ($progress >= $nilaiTarget && $nilaiTarget > 0) {
                $stats['achieved_targets']++;
            } else {
                $stats['in_progress_targets']++;
            }

            $allTargetData[] = [
                'id' => $target->id,
                'judul' => $target->judul,
                'jabatan' => $jabatan,
                'progress' => $progress,
                'target' => $nilaiTarget,
                'gap' => $calculationData['gap'] ?? 0,
                'asistant_route' => $target->asistant_route,
            ];

            if (!isset($jabatanAggregates[$jabatan])) {
                $jabatanAggregates[$jabatan] = [];
            }
            $jabatanAggregates[$jabatan][] = $progress;

            foreach ($monthlyData as $monthKey => $avgScore) {
                // Filter bulan jika ada request bulan
                if ($request->bulan) {
                    $monthPart = (int) explode('-', $monthKey)[1];
                    if ($monthPart !== (int) $request->bulan) {
                        continue;
                    }
                }

                if (!isset($monthlyAggregates[$monthKey])) {
                    $monthlyAggregates[$monthKey] = [];
                }
                $monthlyAggregates[$monthKey][] = $avgScore;

                if (!isset($jabatanMonthlyAggregates[$jabatan])) {
                    $jabatanMonthlyAggregates[$jabatan] = [];
                }
                if (!isset($jabatanMonthlyAggregates[$jabatan][$monthKey])) {
                    $jabatanMonthlyAggregates[$jabatan][$monthKey] = [];
                }
                $jabatanMonthlyAggregates[$jabatan][$monthKey][] = $avgScore;
            }
        }

        $monthlyChart = [];
        foreach ($monthlyAggregates as $month => $scores) {
            if (!empty($scores)) {
                $monthlyChart[$month] = round(array_sum($scores) / count($scores), 1);
            }
        }
        ksort($monthlyChart);

        $jabatanChart = [];
        foreach ($jabatanAggregates as $jabatan => $scores) {
            if (!empty($scores)) {
                $jabatanChart[$jabatan] = round(array_sum($scores) / count($scores), 1);
            }
        }

        $jabatanMonthlyChart = [];
        foreach ($jabatanMonthlyAggregates as $jabatan => $months) {
            foreach ($months as $month => $scores) {
                if (!empty($scores)) {
                    if (!isset($jabatanMonthlyChart[$jabatan])) {
                        $jabatanMonthlyChart[$jabatan] = [];
                    }
                    $jabatanMonthlyChart[$jabatan][$month] = round(array_sum($scores) / count($scores), 1);
                }
            }
        }

        $allProgressValues = [];
        foreach ($jabatanAggregates as $scores) {
            $allProgressValues = array_merge($allProgressValues, $scores);
        }
        $overallAverage = !empty($allProgressValues) ? round(array_sum($allProgressValues) / count($allProgressValues), 1) : 0;

        $yearlyMonthlyAverage = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthKey = "{$tahunFilter}-" . str_pad($m, 2, '0', STR_PAD_LEFT);
            $yearlyMonthlyAverage[$monthKey] = $monthlyChart[$monthKey] ?? 0;
        }

        $responseData = [
            'filters' => [
                'jabatan' => $requestJabatan,
                'bulan' => $request->bulan,
                'tahun' => (int) $tahunFilter,
                'user_scope' => $userJabatan,
            ],
            'summary' => [
                'overall_average' => $overallAverage,
                'total_targets' => $stats['total_targets'],
                'completed_targets' => $stats['completed_targets'],
                'achieved_targets' => $stats['achieved_targets'],
                'in_progress_targets' => $stats['in_progress_targets'],
                'completion_rate' => $stats['total_targets'] > 0 ? round(($stats['completed_targets'] / $stats['total_targets']) * 100, 1) : 0,
                'achievement_rate' => $stats['total_targets'] > 0 ? round(($stats['achieved_targets'] / $stats['total_targets']) * 100, 1) : 0,
            ],
            'charts' => [
                'monthly_trend' => $yearlyMonthlyAverage,
                'by_jabatan' => $jabatanChart,
                'jabatan_monthly' => $jabatanMonthlyChart,
            ],
            'targets_detail' => $allTargetData,
        ];

        return response()->json($responseData);
    }

    //Target Detail Office
    //gm
    private function calculateProgressKepuasanPelangganDetail($itemDetail)
    {
        $empty = [
            'progress' => 0,
            'gap' => 0,
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
            'category_scores' => [],
            'top_performer' => null,
            'lowest_performer' => null,
            'trend' => 'stable',
            'trend_value' => 0,
            'consistency' => 'stable',
            'target_status' => 'behind',
            'prediction' => 0,
            'total_feedback' => 0,
            'total_sessions' => 0,
            'insight' => '',
        ];

        $detailJangkas = $itemDetail->detailTargetKPI->pluck('detail_jangka')->filter();

        if ($detailJangkas->isEmpty()) {
            return $empty;
        }

        $tahun = (int) $detailJangkas->first();

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return $empty;
        }

        $start = "$tahun-01-01";
        $end = "$tahun-12-31";

        $feedbacks = Nilaifeedback::with('rkm.materi')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        if ($feedbacks->isEmpty()) {
            return $empty;
        }

        $groupedFeedbacks = $feedbacks
            ->groupBy(function ($feedback) {
                $materiNama = optional($feedback->rkm)->materi?->nama_materi ?? 'unknown';
                $tanggalAwal = optional($feedback->rkm)->tanggal_awal ?? '0000-00-00';
                return $materiNama . '/' . $tanggalAwal;
            })
            ->filter();

        $averageFeedbacks = [];
        $dailyAverages = [];
        $dailyProgresses = [];
        $categoryTotals = ['M' => 0, 'P' => 0, 'F' => 0, 'I' => 0, 'Ib' => 0, 'Ias' => 0];
        $categoryCounts = ['M' => 0, 'P' => 0, 'F' => 0, 'I' => 0, 'Ib' => 0, 'Ias' => 0];
        $sessionScores = [];

        foreach ($groupedFeedbacks as $key => $group) {
            $totalFeedbacks = $group->count();
            if ($totalFeedbacks === 0) continue;

            $sums = [
                'M' => $group->sum('M1') + $group->sum('M2') + $group->sum('M3') + $group->sum('M4'),
                'P' => $group->sum('P1') + $group->sum('P2') + $group->sum('P3') + $group->sum('P4') + $group->sum('P5') + $group->sum('P6') + $group->sum('P7'),
                'F' => $group->sum('F1') + $group->sum('F2') + $group->sum('F3') + $group->sum('F4') + $group->sum('F5'),
                'I' => $group->sum('I1') + $group->sum('I2') + $group->sum('I3') + $group->sum('I4') + $group->sum('I5') + $group->sum('I6') + $group->sum('I7') + $group->sum('I8'),
                'Ib' => $group->sum('I1b') + $group->sum('I2b') + $group->sum('I3b') + $group->sum('I4b') + $group->sum('I5b') + $group->sum('I6b') + $group->sum('I7b') + $group->sum('I8b'),
                'Ias' => $group->sum('I1as') + $group->sum('I2as') + $group->sum('I3as') + $group->sum('I4as') + $group->sum('I5as') + $group->sum('I6as') + $group->sum('I7as') + $group->sum('I8as'),
            ];

            $avgM = round($sums['M'] / ($totalFeedbacks * 4), 1);
            $avgP = round($sums['P'] / ($totalFeedbacks * 7), 1);
            $avgF = round($sums['F'] / ($totalFeedbacks * 5), 1);
            $avgI = round($sums['I'] / ($totalFeedbacks * 8), 1);
            $avgIb = round($sums['Ib'] / ($totalFeedbacks * 8), 1);
            $avgIas = round($sums['Ias'] / ($totalFeedbacks * 8), 1);

            $values = [$avgM, $avgP, $avgF, $avgI];
            if ($avgIb > 0) $values[] = $avgIb;
            if ($avgIas > 0) $values[] = $avgIas;

            $finalAvg = round(array_sum($values) / count($values), 1);

            $averageFeedbacks[] = $finalAvg;
            $sessionScores[$key] = $finalAvg;

            $sampleDate = $group->first()->created_at->format('Y-m-d');
            $dailyAverages[$sampleDate] = $finalAvg;
            $dailyProgresses[$sampleDate] = round($finalAvg * 20, 1);

            foreach (['M' => $avgM, 'P' => $avgP, 'F' => $avgF, 'I' => $avgI, 'Ib' => $avgIb, 'Ias' => $avgIas] as $k => $v) {
                if ($v > 0) {
                    $categoryTotals[$k] += $v;
                    $categoryCounts[$k]++;
                }
            }
        }

        $totalGroups = count($averageFeedbacks);
        $above = count(array_filter($averageFeedbacks, fn($v) => $v >= 3.5));
        $below = $totalGroups - $above;
        $progress = $totalGroups > 0 ? round(($above / $totalGroups) * 100, 1) : 0;

        $nilaiTarget = $itemDetail->detailTargetKPI->pluck('nilai_target')->first() ?? 0;
        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyAverages as $dateStr => $avg) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $monthlyData[$monthKey][] = $avg;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $avg;

            $pct = $dailyProgresses[$dateStr] ?? 0;
            $monthlyProgress[$monthKey][] = $pct;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $pct;
        }

        $monthlyAverages = [];
        foreach ($monthlyData as $month => $vals) {
            $monthlyAverages[$month] = round(array_sum($vals) / count($vals), 1);
        }

        $monthlyProgressAverages = [];
        foreach ($monthlyProgress as $month => $vals) {
            $monthlyProgressAverages[$month] = round(array_sum($vals) / count($vals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
        ksort($dailyProgressPerMonth);

        $mapping = [
            'M'   => 'Materi',
            'P'   => 'Pelayanan',
            'F'   => 'Fasilitas',
            'I'   => 'Instruktur',
            'Ib'  => 'Instruktur 2',
            'Ias' => 'Asisten Instruktur',
        ];

        $categoryScores = [];
        foreach ($categoryTotals as $k => $total) {
            $label = $mapping[$k] ?? $k;

            $categoryScores[$label] = $categoryCounts[$k] > 0
                ? round($total / $categoryCounts[$k], 1)
                : 0;
        }

        arsort($sessionScores);
        $top = key($sessionScores);
        $topVal = current($sessionScores);

        asort($sessionScores);
        $low = key($sessionScores);
        $lowVal = current($sessionScores);

        $months = array_values($monthlyAverages);
        $trend = 'stable';
        $trendValue = 0;

        if (count($months) >= 2) {
            $trendValue = round(end($months) - prev($months), 1);
            if ($trendValue > 0) $trend = 'up';
            elseif ($trendValue < 0) $trend = 'down';
        }

        $mean = count($averageFeedbacks) > 0 ? array_sum($averageFeedbacks) / count($averageFeedbacks) : 0;
        $variance = 0;
        foreach ($averageFeedbacks as $v) {
            $variance += pow($v - $mean, 2);
        }
        $variance = count($averageFeedbacks) > 0 ? $variance / count($averageFeedbacks) : 0;
        $stdDev = sqrt($variance);
        $consistency = $stdDev < 0.3 ? 'stable' : 'fluctuating';

        $targetStatus = 'behind';
        if ($progress >= $nilaiTarget) $targetStatus = 'on_track';
        elseif ($gapRaw >= -5) $targetStatus = 'at_risk';

        $prediction = count($months) > 0 ? round(array_sum(array_slice($months, -3)) / min(3, count($months)), 1) : 0;

        $insight = "Kepuasan pelanggan {$trend} dengan perubahan {$trendValue}. Konsistensi {$consistency}.";

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
            'category_scores' => $categoryScores,
            'top_performer' => ['label' => $top, 'value' => $topVal],
            'lowest_performer' => ['label' => $low, 'value' => $lowVal],
            'trend' => $trend,
            'trend_value' => $trendValue,
            'consistency' => $consistency,
            'target_status' => $targetStatus,
            'prediction' => $prediction,
            'total_feedback' => $feedbacks->count(),
            'total_sessions' => $totalGroups,
            'insight' => $insight,
        ];
    }

    private function calculatePemasukanKotorDetail($itemDetail)
    {
        $details = $itemDetail->detailTargetKPI;
        $detail = $details->first();

        $defaultResponse = [
            'progress' => 0,
            'gap' => 0,
            'pie_chart' => [
                'above' => 0,
                'below' => 0
            ],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
            'triwulan_data' => [],
            'sales_performance' => null,
            'dataManual' => [
                'manual_document' => $detail->manual_document ?? null
            ],
        ];

        if (
            !$detail ||
            !is_numeric($detail->detail_jangka) ||
            !is_numeric($detail->nilai_target)
        ) {
            return $defaultResponse;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $defaultResponse;
        }

        $sales = ApprovalPendapatan::query()
            ->whereYear('tanggal_mulai', $tahun)
            ->selectRaw('
                tanggal_mulai,
                SUM(CAST(harga_net AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total
            ')
            ->groupBy('tanggal_mulai')
            ->get();

        $totalSales = 0;
        $dailyBreakdownPerMonth = [];
        $monthlyDataTemp = [];
        $triwulanDataTemp = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0
        ];

        foreach ($sales as $row) {
            $date = Carbon::parse($row->tanggal_mulai);

            $dateKey = $date->format('Y-m-d');
            $monthKey = $date->format('Y-m');
            $total = (int) round($row->total ?? 0);

            $totalSales += $total;

            $dailyBreakdownPerMonth[$monthKey][$dateKey] = $total;
            $monthlyDataTemp[$monthKey] =
                ($monthlyDataTemp[$monthKey] ?? 0) + $total;

            $triwulan = ceil($date->month / 3);

            $triwulanDataTemp[$triwulan] += $total;
        }

        $monthlyData = collect($monthlyDataTemp)
            ->sortKeys()
            ->map(fn($v) => (int) round($v))
            ->toArray();

        ksort($dailyBreakdownPerMonth);

        $triwulanData = collect($triwulanDataTemp)
            ->mapWithKeys(fn($value, $key) => [
                'Triwulan_' . $key => (int) round($value)
            ])
            ->toArray();

        $progressGlobal = (int) round($totalSales);
        $gap = (int) round($progressGlobal - $nilaiTarget);

        $above = $totalSales >= $nilaiTarget ? 1 : 0;
        $below = $above ? 0 : 1;

        $monthlyProgress = [];
        $runningMonth = 0;

        foreach ($monthlyData as $month => $value) {
            $runningMonth += $value;

            $monthlyProgress[$month] = $nilaiTarget > 0
                ? round(($runningMonth / $nilaiTarget) * 100)
                : 0;
        }

        $dailyProgressPerMonth = [];
        $runningDay = 0;

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $value) {
                $runningDay += $value;

                $dailyProgressPerMonth[$month][$day] = $nilaiTarget > 0
                    ? round(($runningDay / $nilaiTarget) * 100)
                    : 0;
            }
        }

        $allKaryawan = Karyawan::query()
            ->where(function ($q) {
                $q->where('status_aktif', '1')
                    ->where('jabatan', '!=', 'Outsource')
                    ->where('kode_karyawan', 'NOT LIKE', 'OL%')
                    ->where('jabatan', '!=', 'Pilih Jabatan')
                    ->whereNotNull('nip')
                    ->where('divisi', '!=', 'Direksi')
                    ->orWhereNull('status_aktif');
            })
            ->where(function ($q) {
                $q->where('jabatan', 'Sales')
                    ->orWhereNull('jabatan');
            })
            ->get();

        $revenueBySalesKey = ApprovalPendapatan::with('rkm')
            ->whereYear('tanggal_mulai', $tahun)
            ->get()
            ->filter(function ($item) {
                return $item->rkm && $item->rkm->sales_key;
            })
            ->groupBy(function ($item) {
                return $item->rkm->sales_key;
            })
            ->map(function ($items) {
                return $items->sum('total_penjualan_sales');
            })
            ->toArray();

        $targetPenjualanTahunan = targetKPI::whereHas(
            'detailTargetKPI.dataTarget',
            function ($q) {
                $q->where(
                    'asistant_route',
                    'target penjualan tahunan'
                );
            }
        )->first();

        $idTargetToUse = $targetPenjualanTahunan
            ? $targetPenjualanTahunan->id
            : $itemDetail->id;

        $detailPersons = DetailPersonKPI::query()
            ->where('id_target', $idTargetToUse)
            ->whereIn('id_karyawan', $allKaryawan->pluck('id'))
            ->get()
            ->keyBy('id_karyawan');

        $allSalesData = [];

        foreach ($allKaryawan as $karyawan) {
            $salesKey = $karyawan->kode_karyawan;

            if (!$salesKey) {
                continue;
            }

            $salesRevenue = (int) round(
                $revenueBySalesKey[$salesKey] ?? 0
            );

            $detailPerson = $detailPersons->get($karyawan->id);

            $presentaseKemampuan = $detailPerson
                ? (int) round($detailPerson->presentase_kemampuan ?? 0)
                : 0;

            $percentage = $presentaseKemampuan > 0
                ? round(($salesRevenue / $presentaseKemampuan) * 100)
                : 0;

            $allSalesData[] = [
                'kode_karyawan' => $salesKey,
                'nama' => $karyawan->nama_lengkap
                    ?? $karyawan->nama
                    ?? $salesKey,
                'revenue' => $salesRevenue,
                'id_detailPerson' => $detailPerson?->id,
                'presentase_kemampuan' => $presentaseKemampuan,
                'percentage' => $percentage,
                'status' => $salesRevenue >= $presentaseKemampuan
                    ? 'achieved'
                    : 'pending'
            ];
        }

        return [
            'progress' => $progressGlobal,
            'gap' => $gap,
            'dataManual' => [
                'manual_document' => $detail->manual_document ?? null
            ],
            'pie_chart' => [
                'above' => $above,
                'below' => $below
            ],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
            'triwulan_data' => $triwulanData,
            'sales_performance' => [
                'type' => 'all',
                'data' => $allSalesData
            ]
        ];
    }

    private function calculatePemasukanBersihDetail($itemDetail)
    {
        $empty = [
            'progress' => 0,
            'gap' => 0,
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
            'previous_quarter' => [],
        ];

        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $empty;
        }

        $item = $itemDetail;
        $personId = 0;

        $labaKotor = $this->calculatePemasukanKotor($item, $personId);

        if ($labaKotor <= 0) {
            return $empty;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $empty;
        }

        $currentMonth = now()->month;
        $currentQuarter = ceil($currentMonth / 3);

        $prevQuarter = $currentQuarter - 1;
        $prevYear = $tahun;

        if ($prevQuarter < 1) {
            $prevQuarter = 4;
            $prevYear--;
        }

        $previousQuarterData = AnalysisReport::where('year', $prevYear)->get();

        if (!$previousQuarterData) {
            $previousQuarterData = null;
        }

        $getDataAnalisis = AnalysisReport::where('year', $tahun);
        $above = $getDataAnalisis->count();
        $bellow = $above - 4;
        $dataAnalisis = $getDataAnalisis->get();
        $nominal = $dataAnalisis->sum('nilai');

        if ($nominal === 0) {
            return $empty;
        }

        $progress = $labaKotor > 0 ? round(($nominal / $labaKotor) * 100, 2) : 0;

        $gap = $progress < $nilaiTarget
            ? rtrim(rtrim(sprintf('%.1f', $progress - $nilaiTarget), '0'), '.')
            : 0;

        $monthly_data = [];
        $daily_breakdown_per_month = [];
        $monthly_progress = [];
        $daily_progress_per_month = [];

        foreach ($dataAnalisis as $report) {

            if (is_null($report->nilai)) continue;

            $month = (int) $report->month;
            $monthKey = $tahun . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);

            $nilai = (float) $report->nilai;

            $monthly_data[$monthKey] = $nilai;

            $monthly_progress[$monthKey] = $labaKotor > 0
                ? round(($nilai / $labaKotor) * 100, 1)
                : 0;

            $dayKey = $monthKey . '-01';

            if (!isset($daily_breakdown_per_month[$monthKey])) {
                $daily_breakdown_per_month[$monthKey] = [];
                $daily_progress_per_month[$monthKey] = [];
            }

            $daily_breakdown_per_month[$monthKey][$dayKey] = $nilai;

            $daily_progress_per_month[$monthKey][$dayKey] = $labaKotor > 0
                ? round(($nilai / $labaKotor) * 100, 1)
                : 0;
        }

        ksort($monthly_data);
        ksort($monthly_progress);
        ksort($daily_breakdown_per_month);
        ksort($daily_progress_per_month);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => [
                'manual_document' => $detail->manual_document,
            ],
            'pie_chart' => [
                'above' => $above,
                'below' => $bellow
            ],
            'monthly_data' => $monthly_data,
            'daily_breakdown_per_month' => $daily_breakdown_per_month,
            'monthly_progress' => $monthly_progress,
            'daily_progress_per_month' => $daily_progress_per_month,
            'previous_quarter' => [
                'year' => $prevYear,
                'data' => $previousQuarterData
            ]
        ];
    }

    private function calculateTargetPenjualanProjectTahunanDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
                'triwulan_data' => [],
                'sales_performance' => null,
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
                'triwulan_data' => [],
                'sales_performance' => null,
            ];
        }

        $kodeKaryawan = null;
        $karyawanData = null;

        if ($personId !== null) {
            $karyawanData = Karyawan::find($personId);
            $kodeKaryawan = $karyawanData ? $karyawanData->kode_karyawan : null;
        }

        // Query Project & LeadProject untuk breakdown
        $query = LeadProject::where('status', 'won')
            ->whereYear('updated_at', $tahun);

        if ($kodeKaryawan) {
            $query->where('lead_projects.sales_id', $kodeKaryawan);
        }

        $sales = $query->select('lead_projects.updated_at', DB::raw('SUM(lead_projects.estimasi_nilai) as total'))
            ->groupBy('lead_projects.updated_at')
            ->get();

        $totalSales = 0;
        $dailyBreakdownPerMonth = [];
        $monthlyDataTemp = [];
        $triwulanDataTemp = [1 => 0, 2 => 0, 3 => 0, 4 => 0];

        foreach ($sales as $row) {
            $date = Carbon::parse($row->updated_at);
            $dateKey = $date->format('Y-m-d');
            $monthKey = $date->format('Y-m');
            $total = (float) ($row->total ?? 0);

            // ✅ Akumulasi totalSales dengan nilai mentah (tanpa number_format)
            $totalSales += $total;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }
            // Format hanya untuk display breakdown
            $dailyBreakdownPerMonth[$monthKey][$dateKey] = (float) number_format($total, 1, '.', '');

            if (!isset($monthlyDataTemp[$monthKey])) {
                $monthlyDataTemp[$monthKey] = 0;
            }
            $monthlyDataTemp[$monthKey] += $total;

            $month = (int) $date->format('m');
            $triwulan = (int) ceil($month / 3);
            if (isset($triwulanDataTemp[$triwulan])) {
                $triwulanDataTemp[$triwulan] += $total;
            }
        }

        $monthlyData = [];
        foreach ($monthlyDataTemp as $month => $total) {
            // Format hanya untuk output bulanan
            $monthlyData[$month] = (float) number_format($total, 1, '.', '');
        }

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);

        $triwulanData = [];
        for ($i = 1; $i <= 4; $i++) {
            $triwulanData['Triwulan_' . $i] = (float) number_format($triwulanDataTemp[$i], 1, '.', '');
        }

        // ✅ Gunakan nilai mentah untuk kalkulasi progress
        $progressRupiah = (float) $totalSales;

        // ✅ Target diambil dari $nilaiTarget (sudah valid dari $detail)
        $targetGlobal = $nilaiTarget;

        // ✅ Hitung progress dengan rumus bersih
        $progressGlobal = $progressRupiah;
        $gap = $progressGlobal - $nilaiTarget;

        $above = $totalSales >= $targetGlobal ? 1 : 0;
        $below = 1 - $above;

        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($monthlyData as $month => $value) {
            // Note: $value sudah string dari number_format, cast ke float untuk kalkulasi
            $monthlyProgress[$month] = $targetGlobal > 0
                ? (float) number_format(((float)$value / $targetGlobal) * 100, 1, '.', '')
                : 0;
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $value) {
                if (!isset($dailyProgressPerMonth[$month])) {
                    $dailyProgressPerMonth[$month] = [];
                }
                $dailyProgressPerMonth[$month][$day] = $targetGlobal > 0
                    ? (float) number_format(((float)$value / $targetGlobal) * 100, 1, '.', '')
                    : 0;
            }
        }

        $salesPerformance = null;

        if ($personId === null) {
            $allSalesData = [];

            $allKaryawan = Karyawan::where(function ($q) {
                $q->where('status_aktif', '1')
                    ->whereNot('jabatan', 'Outsource')
                    ->where('kode_karyawan', 'NOT LIKE', 'OL%')
                    ->whereNot('jabatan', 'Pilih Jabatan')
                    ->whereNotNull('nip')
                    ->whereNot('divisi', 'Direksi')
                    ->orWhereNull('status_aktif');
            })
                ->where(function ($q) {
                    $q->where('jabatan', 'Sales')
                        ->orWhere('jabatan', 'Sales Executive')
                        ->orWhere('jabatan', 'Account Manager')
                        ->orWhereNull('jabatan')
                        ->where('status_aktif', '1');
                })
                ->get();

            foreach ($allKaryawan as $karyawan) {
                $salesKey = $karyawan->kode_karyawan;
                if (!$salesKey) continue;

                $salesRevenue = LeadProject::where('status', '!=', 'lost')
                    ->join('projects', 'lead_projects.id', '=', 'projects.lead_id')
                    ->whereYear('projects.tanggal_awal', $tahun)
                    ->where('lead_projects.sales_id', $salesKey)
                    ->select(DB::raw('SUM(projects.nilai_proyek) as total'))
                    ->value('total');

                $salesRevenue = (float) ($salesRevenue ?? 0);

                $detailPerson = DetailPersonKPI::where('id_target', $itemDetail->id)
                    ->where('id_karyawan', $karyawan->id)
                    ->first();

                $presentaseKemampuan = (float) ($detailPerson->presentase_kemampuan ?? 0);
                $idDetailPerson = $detailPerson->id ?? null;

                $percentage = $presentaseKemampuan > 0 ? ($salesRevenue / $presentaseKemampuan) * 100 : 0;

                $allSalesData[] = [
                    'kode_karyawan' => (string) $salesKey,
                    'nama' => (string) ($karyawan->nama_lengkap ?? $karyawan->nama ?? $salesKey),
                    'revenue' => (float) number_format($salesRevenue, 1, '.', ''),
                    'id_detailPerson' => $idDetailPerson,
                    'presentase_kemampuan' => (float) number_format($presentaseKemampuan, 1, '.', ''),
                    'percentage' => (float) number_format($percentage, 1, '.', ''),
                    'status' => $salesRevenue >= $presentaseKemampuan ? 'achieved' : 'pending'
                ];
            }

            $salesPerformance = [
                'type' => 'all',
                'data' => $allSalesData
            ];
        } else {
            $detailPerson = DetailPersonKPI::where('id_target', $itemDetail->id)
                ->where('id_karyawan', $personId)
                ->first();

            $presentaseKemampuan = (float) ($detailPerson->presentase_kemampuan ?? 0);
            $idDetailPerson = $detailPerson->id ?? null;

            $percentage = $presentaseKemampuan > 0 ? ($totalSales / $presentaseKemampuan) * 100 : 0;

            $karyawanName = $karyawanData ? ($karyawanData->nama_lengkap ?? $karyawanData->nama ?? '') : '';

            $salesPerformance = [
                'type' => 'individual',
                'data' => [
                    'kode_karyawan' => (string) $kodeKaryawan,
                    'nama' => (string) $karyawanName,
                    'revenue' => (float) number_format($totalSales, 1, '.', ''),
                    'id_detailPerson' => $idDetailPerson,
                    'presentase_kemampuan' => (float) number_format($presentaseKemampuan, 1, '.', ''),
                    'percentage' => (float) number_format($percentage, 1, '.', ''),
                    'status' => $totalSales >= $presentaseKemampuan ? 'achieved' : 'pending'
                ]
            ];
        }

        return [
            // ✅ Format HANYA saat return output
            'progress' => round($progressGlobal, 2),
            'gap' => round($gap, 1),
            'dataManual' => [
                'manual_document' => $detail->manual_document,
            ],
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
            'triwulan_data' => $triwulanData,
            'sales_performance' => $salesPerformance,
        ];
    }

    private function calculateRasioBiayaOperasionalTerhadapRevenueDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $item = $itemDetail;
        $personId = 0;

        $labaKotor = $this->calculatePemasukanKotor($item, $personId);

        if ($labaKotor == 0) {
            return 0;
        }

        if (is_null($detail) || is_null($detail->manual_value)) {
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $progress = 0;

        if (!is_null($detail->manual_value)) {
            $manualValue = (float) $detail->manual_value;

            if ($labaKotor < $manualValue) {
                return 0;
            }

            if ($manualValue > 0) {
                $rasio = ($manualValue / $labaKotor) * 100;

                $batas = $nilaiTarget;

                $progress = ($batas / $rasio) * 100;
            }
        }

        $progress = round($progress, 1);

        if ($progress < $nilaiTarget) {
            $gapRaw = $progress - $nilaiTarget;
            $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
        } else {
            $gap = 0;
        }

        return [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => [
                'manual_document' => $detail->manual_document,
            ],
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
        ];
    }

    private function calculatePerformaKPIDepartemenDetail($itemDetail, $personId)
    {
        $allTargets = targetKPI::with(['detailTargetKPI.dataTarget'])
            ->whereYear('created_at', now()->year)
            ->get();

        $targetsByDivisi = [];

        foreach ($allTargets as $target) {
            $details = $target->detailTargetKPI;
            if (!$details || $details->isEmpty()) continue;

            $divisions = $details->pluck('divisi')->unique()->filter();

            foreach ($divisions as $divisi) {
                $targetsByDivisi[$divisi][] = $target;
            }
        }

        $divisionAverages = [];
        $divisionBreakdown = [];
        $targetValues = [];
        $allProgress = [];

        foreach ($targetsByDivisi as $divisi => $items) {
            $progresses = [];

            foreach ($items as $item) {
                $detail = $item->detailTargetKPI->first();
                if (!$detail) continue;

                $route = strtolower($detail->dataTarget?->asistant_route ?? '');

                if ($route === 'performa kpi departemen') continue;

                if (!is_null($detail->nilai_target)) {
                    $targetValues[] = (float) $detail->nilai_target;
                }

                $progress = $this->resolveProgress($item, $personId);

                if ($detail->tipe_target === 'rupiah') {
                    $targetVal = $detail->nilai_target;

                    if ($route === 'pemasukan kotor') {
                        $data = $this->calculatePemasukanKotor($item, $personId);
                        $progress = $targetVal > 0 ? max(0, min(100, round(($data / $targetVal) * 100, 1))) : 0;
                    } elseif ($route === 'meningkatkan revenue perusahaan') {
                        $data = $this->calculateMeningkatkanRevenuePerusahaan($item, $personId);
                        $progress = $targetVal > 0 ? max(0, min(100, round(($data / $targetVal) * 100, 1))) : 0;
                    } elseif ($route === 'pendapatan penjualan project') {
                        $data = $this->calculatePendapatanPenjualanProject($item, $personId);
                        $progress = $targetVal > 0 ? max(0, min(100, round(($data / $targetVal) * 100, 2))) : 0;
                    } elseif ($route === 'target penjualan project tahunan') {
                        $data = $this->calculateTargetPenjualanProjectTahunan($item, $personId);
                        $progress = $targetVal > 0 ? max(0, min(100, round(($data / $targetVal) * 100, 2))) : 0;
                    } elseif ($route === 'laporan mom') {
                        $progress = $this->calculateLaporanMOM($item);
                    }
                }

                if (is_numeric($progress)) {
                    $progresses[] = $progress;
                    $allProgress[] = $progress;
                }
            }

            if (!empty($progresses)) {
                $avg = array_sum($progresses) / count($progresses);
                $divisionAverages[$divisi] = round($avg, 1);
                $divisionBreakdown[$divisi] = round($avg, 1);
            }
        }

        $progress = !empty($divisionAverages)
            ? round(array_sum($divisionAverages) / count($divisionAverages), 1)
            : 0;

        $averageTarget = !empty($targetValues)
            ? array_sum($targetValues) / count($targetValues)
            : 100;

        $gapRaw = $progress - $averageTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
        if ($gap === '-0') $gap = '0';

        $above = round(max(0, $progress), 1);
        $below = round(max(0, 100 - $progress), 1);

        arsort($divisionAverages);
        $topDivisionName = key($divisionAverages);
        $topDivisionValue = current($divisionAverages);

        asort($divisionAverages);
        $lowestDivisionName = key($divisionAverages);
        $lowestDivisionValue = current($divisionAverages);

        $mean = count($allProgress) ? array_sum($allProgress) / count($allProgress) : 0;

        $variance = 0;
        foreach ($allProgress as $val) {
            $variance += pow($val - $mean, 2);
        }
        $variance = count($allProgress) ? $variance / count($allProgress) : 0;

        $stdDev = sqrt($variance);
        $consistency = $stdDev < 10 ? 'stable' : 'fluctuating';

        $targetStatus = 'behind';
        if ($progress >= $averageTarget) {
            $targetStatus = 'on_track';
        } elseif ($gapRaw >= -5) {
            $targetStatus = 'at_risk';
        }

        $riskDivisions = [];
        foreach ($divisionBreakdown as $div => $val) {
            if ($val < 70) {
                $riskDivisions[] = [
                    'name' => $div,
                    'value' => $val
                ];
            }
        }

        $insight = "Performa KPI departemen stable dengan rata-rata {$progress}%. ";
        $insight .= "Divisi terbaik {$topDivisionName} ({$topDivisionValue}%), ";
        $insight .= "terendah {$lowestDivisionName} ({$lowestDivisionValue}%). ";
        $insight .= "Status target: {$targetStatus}, konsistensi {$consistency}.";

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'division_breakdown' => $divisionBreakdown,
            'top_division' => [
                'name' => $topDivisionName,
                'value' => $topDivisionValue
            ],
            'lowest_division' => [
                'name' => $lowestDivisionName,
                'value' => $lowestDivisionValue
            ],
            'trend' => 'stable',
            'trend_value' => 0,
            'consistency' => $consistency,
            'target_status' => $targetStatus,
            'total_kpi' => $allTargets->count(),
            'total_division' => count($divisionBreakdown),
            'risk_divisions' => $riskDivisions,
            'insight' => $insight
        ];
    }

    //cs
    private function calculatePesertaPuasDenganPelayananDanFasilitasTrainingDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])->get();

        if ($feedbacks->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $allScores = [];
        $scoreDatePairs = [];

        foreach ($feedbacks as $fb) {

            $values = [
                $fb->F1,
                $fb->F2,
                $fb->F3,
                $fb->F4,
                $fb->F5,
                $fb->P1,
                $fb->P2,
                $fb->P3,
                $fb->P4,
                $fb->P5,
                $fb->P1,
                $fb->P2
            ];

            $cleanValues = [];

            foreach ($values as $v) {
                $cleanValues[] = is_numeric($v) ? (float) $v : 0;
            }

            $avg = array_sum($cleanValues) / 12;
            $avg = min(4, max(1, $avg));

            $allScores[] = $avg;

            $scoreDatePairs[] = [
                'score' => $avg,
                'date' => $fb->created_at->format('Y-m-d')
            ];
        }

        $totalResponden = count($allScores);
        $respondenPuas = 0;

        foreach ($allScores as $score) {
            if ($score >= 3.5) {
                $respondenPuas++;
            }
        }

        $progress = ($respondenPuas / $totalResponden) * 100;
        $progress = round($progress, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        $monthlyTarget = $nilaiTarget / 12;
        $dailyTarget = $nilaiTarget / 365;

        foreach ($scoreDatePairs as $pair) {

            $date = Carbon::parse($pair['date']);
            $monthKey = $date->format('Y-m');
            $dayKey = $pair['date'];
            $score = $pair['score'];
            $pct = $score >= 3.5 ? 100 : 0;

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [];
                $monthlyProgress[$monthKey] = [];
            }

            $monthlyData[$monthKey][] = $score;
            $monthlyProgress[$monthKey][] = $pct;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
                $dailyProgressPerMonth[$monthKey] = [];
            }

            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $score;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $pct;
        }

        $monthlyAverages = [];
        $monthlyProgressAverages = [];

        foreach ($monthlyData as $month => $vals) {
            $monthlyAverages[$month] = round(array_sum($vals) / count($vals), 1);
        }

        foreach ($monthlyProgress as $month => $vals) {
            $monthlyProgressAverages[$month] = round(array_sum($vals) / count($vals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $respondenPuas,
                'below' => $totalResponden - $respondenPuas,
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculateDorongInovasiPelayananDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $progress = 0;

        if (!is_null($detail->manual_value)) {
            $manualValue = (float) $detail->manual_value;

            if ($manualValue > 0) {
                $progress = $manualValue;
            }
        }

        $progress = round($progress);
        $gapRaw = $progress - $nilaiTarget;
        if ($progress > $nilaiTarget) {
            $gap = 0;
        } else {
            $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
        }

        return [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => [
                'manual_document' => $detail->manual_document,
            ],
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
        ];
    }

    private function calculatePenangananKomplainPersetaDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $komplainData = KomplainPeserta::whereBetween('created_at', [$start, $end])->get();

        $totalData = $komplainData->count();

        if ($totalData === 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $dataTepatWaktu = 0;
        $dataTidakTepatWaktu = 0;
        $dailyValues = [];

        foreach ($komplainData as $data) {
            $createdDate = Carbon::parse($data->created_at);
            $dateKey = $createdDate->format('Y-m-d');

            $isTepatWaktu = 0;

            if ($data->tanggal_selesai) {
                $finishedDate = Carbon::parse($data->tanggal_selesai);

                if ($createdDate->format('Y-m-d') === $finishedDate->format('Y-m-d')) {
                    $dataTepatWaktu++;
                    $isTepatWaktu = 1;
                } else {
                    $dataTidakTepatWaktu++;
                }
            } else {
                $dataTidakTepatWaktu++;
            }

            if (!isset($dailyValues[$dateKey])) {
                $dailyValues[$dateKey] = [];
            }
            $dailyValues[$dateKey][] = $isTepatWaktu * 100;
        }

        $presentase = ($dataTepatWaktu / $totalData) * 100;
        $progress = round($presentase, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $dataTepatWaktu;
        $below = $dataTidakTepatWaktu;

        $dailyAverages = [];
        foreach ($dailyValues as $dateStr => $values) {
            $dailyAverages[$dateStr] = round(array_sum($values) / count($values), 1);
        }

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyAverages as $dateStr => $avg) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [];
                $monthlyProgress[$monthKey] = [];
            }
            $monthlyData[$monthKey][] = $avg;
            $monthlyProgress[$monthKey][] = $avg >= 100 ? 100 : 0;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
                $dailyProgressPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $avg;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $avg >= 100 ? 100 : 0;
        }

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }
        foreach ($monthlyProgress as $month => $dailyVals) {
            $monthlyProgressAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculateReportPersiapanKelasDetail($itemDetail, $personId)
    {
        $details = $itemDetail->detailTargetKPI;
        $detail = $details->first();

        $nilaiTarget = (float) optional($detail)->nilai_target;
        $tahun = (int) optional($detail)->detail_jangka ?? now()->year;

        if ($details->isEmpty() || $nilaiTarget <= 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $totalRkm = RKM::with('materi', 'instruktur', 'instruktur2', 'asisten', 'nilaifeedback')
                ->where('status', '0')
                ->whereYear('tanggal_awal', $tahun)->count();

        $checklistItems = ChecklistKeperluan::whereHas('rkm', function ($query) use ($tahun) {
            $query->whereYear('tanggal_awal', $tahun);
        })
            ->whereNotNull('tanggal_keperluan')
            ->where('materi', 1)
            ->where('kelas', 1)
            ->where('cb', 1)
            ->where('maksi', 1)
            ->where('keperluan_kelas', 1)
            ->whereHas('subChecklistKeperluans', function ($subQuery) {
                $subQuery->where('materi_module', 1)
                    ->where('materi_elearning', 1)
                    ->where('cb_instruktur', 1)
                    ->where('cb_peserta', 1)
                    ->where('maksi_instruktur', 1)
                    ->where('maksi_peserta', 1)
                    ->where('kelas_ac', 1)
                    ->where('kelas_jam', 1)
                    ->where('kelas_buku', 1)
                    ->where('kelas_pulpen', 1)
                    ->where('kelas_permen', 1)
                    ->where('kelas_camilan', 1)
                    ->where('kelas_minuman', 1)
                    ->where('kelas_lampu', 1)
                    ->where('kelas_kondisi_kebersihan', 1);
            })
            ->select('tanggal_keperluan')
            ->get();

        $totalTuntas = $checklistItems->count();

        if ($totalRkm > 0) {
            $progress = ($totalTuntas / $totalRkm) * 100;
        } else {
            $progress = 0;
        }

        $dailyBreakdownPerMonth = [];
        $monthlyTotals = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        $monthlyTarget = $nilaiTarget / 12;
        $dailyTarget = $nilaiTarget / 365;

        foreach ($checklistItems as $row) {
            $date = Carbon::parse($row->tanggal_keperluan ?? $row->created_at ?? now());
            $dateKey = $date->format('Y-m-d');
            $monthKey = $date->format('Y-m');

            $value = 1;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
                $dailyProgressPerMonth[$monthKey] = [];
            }

            if (!isset($dailyBreakdownPerMonth[$monthKey][$dateKey])) {
                $dailyBreakdownPerMonth[$monthKey][$dateKey] = 0;
                $dailyProgressPerMonth[$monthKey][$dateKey] = 0;
            }

            $dailyBreakdownPerMonth[$monthKey][$dateKey] += $value;
            $dailyProgressPerMonth[$monthKey][$dateKey] = $dailyTarget > 0
                ? round(($dailyBreakdownPerMonth[$monthKey][$dateKey] / $dailyTarget) * 100, 1)
                : 100;

            if (!isset($monthlyTotals[$monthKey])) {
                $monthlyTotals[$monthKey] = 0;
                $monthlyProgress[$monthKey] = 0;
            }
            $monthlyTotals[$monthKey] += $value;
            $monthlyProgress[$monthKey] = $monthlyTarget > 0
                ? round(($monthlyTotals[$monthKey] / $monthlyTarget) * 100, 1)
                : 100;
        }

        $monthlyData = $monthlyTotals;

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgress);
        ksort($dailyProgressPerMonth);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $pieChart = [
            'above' => $totalTuntas,
            'below' => max(0, $totalRkm - $totalTuntas),
        ];

        return [
            'progress' => round($progress, 1),
            'gap' => $gap,
            'pie_chart' => $pieChart,
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    //finance
    private function calculateOutstandingDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->nilai_target) || !is_numeric($detail->detail_jangka)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $outstandings = Outstanding::whereBetween('created_at', [$start, $end])->get();

        if ($outstandings->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $totalData = $outstandings->count();

        $tepatTenggat = $outstandings->where('due_date', '<', 'tanggal_bayar')->where('status_pembayaran', '1');

        $above = $tepatTenggat->count();
        $below = $totalData - $above;

        $progress = $totalData > 0 ? ($above / $totalData) * 100 : 0;

        $progress = round($progress, 1);

        $gapRaw = $progress - $nilaiTarget;

        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        $monthlyTarget = $nilaiTarget / 12;
        $dailyTarget = $nilaiTarget / 365;

        foreach ($outstandings as $data) {
            $date = Carbon::parse($data->created_at);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $isTepat = $data->status_pembayaran == '1' && $data->tanggal_bayar && $data->due_date && Carbon::parse($data->tanggal_bayar)->lt(Carbon::parse($data->due_date)) ? 1 : 0;
            $pct = $isTepat * 100;

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [];
                $monthlyProgress[$monthKey] = [];
            }
            $monthlyData[$monthKey][] = $pct;
            $monthlyProgress[$monthKey][] = $pct;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
                $dailyProgressPerMonth[$monthKey] = [];
            }
            if (!isset($dailyBreakdownPerMonth[$monthKey][$dayKey])) {
                $dailyBreakdownPerMonth[$monthKey][$dayKey] = [];
                $dailyProgressPerMonth[$monthKey][$dayKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey][] = $pct;
            $dailyProgressPerMonth[$monthKey][$dayKey][] = $pct;
        }

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        foreach ($monthlyData as $month => $values) {
            $monthlyAverages[$month] = round(array_sum($values) / count($values), 1);
        }
        foreach ($monthlyProgress as $month => $values) {
            $monthlyProgressAverages[$month] = round(array_sum($values) / count($values), 1);
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $values) {
                $dailyBreakdownPerMonth[$month][$day] = round(array_sum($values) / count($values), 1);
            }
        }
        foreach ($dailyProgressPerMonth as $month => $days) {
            foreach ($days as $day => $values) {
                $dailyProgressPerMonth[$month][$day] = round(array_sum($values) / count($values), 1);
            }
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $above,
                'below' => $below,
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculateInisiatifEfisiensiKeuanganDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $item = $itemDetail;
        $personId = 0;

        if (is_null($detail) || is_null($detail->manual_value)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'dataManual' => [
                    'manual_document' => $detail->manual_document,
                ],
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $manualValue = (float) $detail->manual_value;

        if ($manualValue == null) {
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $progress = 0;

        if (!is_null($detail->manual_value)) {
            $manualValue = (float) $detail->manual_value;

            if ($manualValue > 0) {
                $progress = $manualValue;
            }
        }

        $progress = round($progress);
        $gapRaw = $progress - $nilaiTarget;
        $gap = $nilaiTarget - $manualValue;

        return [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => [
                'manual_document' => $detail->manual_document,
            ],
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
        ];
    }

    private function calculateMengurangiManualWorkDanErrorDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $item = $itemDetail;
        $personId = 0;

        if (is_null($detail) || is_null($detail->manual_value)) {
           return [
                'progress' => 0,
                'gap' => 0,
                'dataManual' => [
                    'manual_document' => $detail->manual_document,
                ],
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $manualValue = (float) $detail->manual_value;

        if ($manualValue == null) {
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $progress = 0;

        if (!is_null($detail->manual_value)) {
            $manualValue = (float) $detail->manual_value;

            if ($manualValue > 0) {
                $progress = $manualValue;
            }
        }

        $progress = round($progress);
        $gapRaw = $progress - $nilaiTarget;
        $gap = $nilaiTarget - $manualValue;

        return [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => [
                'manual_document' => $detail->manual_document,
            ],
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
        ];
    }

    private function calculateLaporanAnalisisKeuanganDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'analisa_data' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        $GetanalisisData = AnalysisReport::where('year', $tahun);

        $analisisData = $GetanalisisData->count();

        $above = $analisisData;
        $bellow = $nilaiTarget - $analisisData;

        $analisaData = $GetanalisisData->get();

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'analisa_data' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $progress = 0;

        if ($analisisData == 0) {
            return 0;
        }

        if ($analisisData > 0) {
            $progress = $analisisData;
        }

        $progress = round($progress);
        $gapRaw = $analisisData - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        return [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => [
                'manual_document' => $detail->manual_document,
            ],
            'pie_chart' => ['above' => $above, 'below' => $bellow],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'analisa_data' => $analisaData,
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
        ];
    }

    private function calculatePencairanBiayaOperasionalDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $dataPengajuan = PengajuanBarang::with('tracking', 'detail')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $totalPengajuan = 0;
        $jumlahSesuai = 0;

        $completedStatuses = ['Selesai', 'Pencairan Sudah Selesai'];
        $excludedStatuses = [
            'Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi',
            'Finance Menunggu Approve Direksi',
            'Membuat Permintaan Ke Direktur Utama',
            'Diajukan dan Sedang Ditinjau oleh Education Manager',
            'Diajukan dan Sedang Ditinjau oleh Koordinator IT Service Management',
            'Diajukan dan Sedang Ditinjau oleh SPV Sales',
            'Diajukan dan Sedang Ditinjau oleh General Manager'
        ];

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($dataPengajuan as $pengajuan) {
            $trackingStatus = optional($pengajuan->tracking)->tracking;

            if (in_array($trackingStatus, $excludedStatuses)) {
                continue;
            }

            $totalPengajuan++;

            $isCompleted = in_array($trackingStatus, $completedStatuses);
            $score = 0;

            if ($isCompleted) {
                $tanggalTerimaFinance = Carbon::parse($pengajuan->tanggal_terima_finance ?? null);
                $tanggalSelesai = Carbon::parse($pengajuan->tanggal_selesai ?? null);

                if ($tanggalTerimaFinance && $tanggalSelesai && $tanggalTerimaFinance->addDays(7)->isBefore($tanggalSelesai)) {
                    $score = 0;
                } else {
                    $score = 1;
                }
            } else {
                $ageInDays = now()->diffInDays($pengajuan->created_at, false);
                if ($ageInDays <= 2) {
                    $score = 1;
                } elseif ($ageInDays <= 21) {
                    $decayDays = $ageInDays - 2;
                    $score = exp(-0.05 * $decayDays);
                    $score = max(0, min(1, $score));
                } else {
                    $score = 0;
                }
            }

            $jumlahSesuai += $score;

            $date = Carbon::parse($pengajuan->created_at);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = ['total' => 0, 'scored' => 0];
                $monthlyProgress[$monthKey] = ['total' => 0, 'scored' => 0];
            }
            $monthlyData[$monthKey]['total']++;
            $monthlyData[$monthKey]['scored'] += $score;
            $monthlyProgress[$monthKey]['total']++;
            $monthlyProgress[$monthKey]['scored'] += $score * 100;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
                $dailyProgressPerMonth[$monthKey] = [];
            }
            if (!isset($dailyBreakdownPerMonth[$monthKey][$dayKey])) {
                $dailyBreakdownPerMonth[$monthKey][$dayKey] = ['total' => 0, 'scored' => 0];
                $dailyProgressPerMonth[$monthKey][$dayKey] = ['total' => 0, 'scored' => 0];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey]['total']++;
            $dailyBreakdownPerMonth[$monthKey][$dayKey]['scored'] += $score;
            $dailyProgressPerMonth[$monthKey][$dayKey]['total']++;
            $dailyProgressPerMonth[$monthKey][$dayKey]['scored'] += $score * 100;
        }

        $progress = $totalPengajuan > 0 ? round(($jumlahSesuai / $totalPengajuan) * 100, 1) : 0;

        $nilaiTarget = (float) $detail->nilai_target;
        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = round($jumlahSesuai, 1);
        $below = round($totalPengajuan - $jumlahSesuai, 1);

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        foreach ($monthlyData as $month => $data) {
            $monthlyAverages[$month] = $data['total'] > 0
                ? round(($data['scored'] / $data['total']) * 100, 1)
                : 0;
        }
        foreach ($monthlyProgress as $month => $data) {
            $monthlyProgressAverages[$month] = $data['total'] > 0
                ? round(($data['scored'] / $data['total']), 1)
                : 0;
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $data) {
                $dailyBreakdownPerMonth[$month][$day] = $data['total'] > 0
                    ? round(($data['scored'] / $data['total']) * 100, 1)
                    : 0;
            }
        }
        foreach ($dailyProgressPerMonth as $month => $days) {
            foreach ($days as $day => $data) {
                $dailyProgressPerMonth[$month][$day] = $data['total'] > 0
                    ? round(($data['scored'] / $data['total']), 1)
                    : 0;
            }
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $above,
                'below' => $below
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculateAkurasiPencatatanMasukDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (is_null($detail) || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $start = Carbon::create($tahun, 1, 1)->startOfDay();
        $end = Carbon::create($tahun, 12, 31)->endOfDay();

        $data = outstanding::whereBetween('created_at', [$start, $end])->get();

        $total = $data->count();
        $totalAkurat = 0;

        $dailyResult = [];
        $accurateCount = 0;
        $notAccurateCount = 0;

        foreach ($data as $row) {

            $netSales = (float) $row->net_sales;
            $jumlahBayar = (float) $row->jumlah_pembayaran;

            $totalPotongan = 0;

            if (!empty($row->jumlah_potongan)) {
                $potongan = is_array($row->jumlah_potongan)
                    ? $row->jumlah_potongan
                    : json_decode($row->jumlah_potongan, true);

                if (is_array($potongan)) {
                    foreach ($potongan as $p) {
                        $totalPotongan += (float) ($p['jumlah'] ?? 0);
                    }
                }
            }

            $totalHitung = $jumlahBayar + $totalPotongan;

            $isAkurat = round($netSales, 2) == round($totalHitung, 2);

            $tanggal = Carbon::parse($row->created_at);
            $tanggalKey = $tanggal->format('Y-m-d');

            $dailyResult[$tanggalKey][] = $isAkurat ? 1 : 0;

            if ($isAkurat) {
                $totalAkurat++;
                $accurateCount++;
            } else {
                $notAccurateCount++;
            }
        }

        if ($total == 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $progress = ($totalAkurat / $total) * 100;
        $progress = round($progress, 1);

        $nilaiTarget = $detail->nilai_target ?? 0;
        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyResult as $dateStr => $values) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');

            $avg = array_sum($values) / count($values) * 100;
            $avg = round($avg, 1);

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [];
                $monthlyProgress[$monthKey] = [];
            }
            $monthlyData[$monthKey][] = $avg;
            $monthlyProgress[$monthKey][] = $avg;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
                $dailyProgressPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dateStr] = $avg;
            $dailyProgressPerMonth[$monthKey][$dateStr] = $avg;
        }

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        foreach ($monthlyData as $month => $values) {
            $monthlyAverages[$month] = round(array_sum($values) / count($values), 1);
        }
        foreach ($monthlyProgress as $month => $values) {
            $monthlyProgressAverages[$month] = round(array_sum($values) / count($values), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $accurateCount,
                'below' => $notAccurateCount
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculatePenyelesaianTagihanPerusahaanDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => [
                    'above' => 0,
                    'below' => 0
                ],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => [
                    'above' => 0,
                    'below' => 0
                ],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $latestIds = trackingTagihanPerusahaan::whereBetween('tanggal_perkiraan_mulai', [$start, $end])
            ->selectRaw('id_tagihan_perusahaan, MAX(id) as latest_id')
            ->groupBy('id_tagihan_perusahaan')
            ->pluck('latest_id');

        if ($latestIds->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => [
                    'above' => 0,
                    'below' => 0
                ],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $dataTagihan = trackingTagihanPerusahaan::whereIn('id', $latestIds)->get();

        $totalTagihan = $dataTagihan->count();

        $tagihanSelesai = $dataTagihan->filter(function ($row) {
            return $row->status === 'selesai' && $row->tracking === 'Selesai';
        })->count();

        $progress = $totalTagihan > 0
            ? round(($tagihanSelesai / $totalTagihan) * 100, 1)
            : 0;

        $nilaiTarget = (float) $detail->nilai_target;
        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $tagihanSelesai;
        $below = $totalTagihan - $tagihanSelesai;

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($dataTagihan as $tagihan) {
            if (!$tagihan->tanggal_perkiraan_mulai) {
                continue;
            }

            $date = Carbon::parse($tagihan->tanggal_perkiraan_mulai);

            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $isSelesai = (
                $tagihan->status === 'selesai' &&
                $tagihan->tracking === 'Selesai'
            ) ? 1 : 0;

            $pct = $isSelesai * 100;

            $monthlyData[$monthKey]['total'] =
                ($monthlyData[$monthKey]['total'] ?? 0) + 1;

            $monthlyData[$monthKey]['selesai'] =
                ($monthlyData[$monthKey]['selesai'] ?? 0) + $isSelesai;

            $monthlyProgress[$monthKey]['total'] =
                ($monthlyProgress[$monthKey]['total'] ?? 0) + 1;

            $monthlyProgress[$monthKey]['selesai'] =
                ($monthlyProgress[$monthKey]['selesai'] ?? 0) + $pct;

            $dailyBreakdownPerMonth[$monthKey][$dayKey]['total'] =
                ($dailyBreakdownPerMonth[$monthKey][$dayKey]['total'] ?? 0) + 1;

            $dailyBreakdownPerMonth[$monthKey][$dayKey]['selesai'] =
                ($dailyBreakdownPerMonth[$monthKey][$dayKey]['selesai'] ?? 0) + $isSelesai;

            $dailyProgressPerMonth[$monthKey][$dayKey]['total'] =
                ($dailyProgressPerMonth[$monthKey][$dayKey]['total'] ?? 0) + 1;

            $dailyProgressPerMonth[$monthKey][$dayKey]['selesai'] =
                ($dailyProgressPerMonth[$monthKey][$dayKey]['selesai'] ?? 0) + $pct;
        }

        $monthlyAverages = [];
        $monthlyProgressAverages = [];

        foreach ($monthlyData as $month => $data) {
            $total = (int) ($data['total'] ?? 0);
            $selesai = (int) ($data['selesai'] ?? 0);

            $monthlyAverages[$month] = $total > 0
                ? round(($selesai / $total) * 100, 1)
                : 0;
        }

        foreach ($monthlyProgress as $month => $data) {
            $total = (int) ($data['total'] ?? 0);
            $selesai = (float) ($data['selesai'] ?? 0);

            $monthlyProgressAverages[$month] = $total > 0
                ? round(($selesai / $total), 1)
                : 0;
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $data) {
                $total = (int) ($data['total'] ?? 0);
                $selesai = (int) ($data['selesai'] ?? 0);

                $dailyBreakdownPerMonth[$month][$day] = $total > 0
                    ? round(($selesai / $total) * 100, 1)
                    : 0;
            }
        }

        foreach ($dailyProgressPerMonth as $month => $days) {
            foreach ($days as $day => $data) {
                $total = (int) ($data['total'] ?? 0);
                $selesai = (float) ($data['selesai'] ?? 0);

                $dailyProgressPerMonth[$month][$day] = $total > 0
                    ? round(($selesai / $total), 1)
                    : 0;
            }
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $above,
                'below' => $below
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    //HRD
    private function calculatePelaksanaanKegiatanKaryawanDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (is_null($detail) || is_null($detail->manual_value)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $startOfYear = Carbon::create($tahun, 1, 1)->startOfDay();
        $endOfYear = Carbon::create($tahun, 12, 31)->endOfDay();

        $kegiatans = Kegiatan::whereBetween('created_at', [$startOfYear, $endOfYear])->get();

        $totalKegiatan = $kegiatans->count();
        $totalKehadiranValid = 0;

        $dailyAverages = [];
        $aboveCount = 0;
        $belowCount = 0;

        foreach ($kegiatans as $kegiatan) {
            $pesertaIds = is_array($kegiatan->id_peserta) ? $kegiatan->id_peserta : json_decode($kegiatan->id_peserta, true);

            if (empty($pesertaIds)) {
                continue;
            }

            $jumlahPeserta = count($pesertaIds);

            $tanggalKegiatan = Carbon::parse($kegiatan->waktu_kegiatan);
            $startOfDay = $tanggalKegiatan->copy()->startOfDay();
            $endOfDay = $tanggalKegiatan->copy()->endOfDay();

            $jumlahHadir = AbsensiKaryawan::whereIn('id_karyawan', $pesertaIds)
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->where('keterangan', 'Masuk')
                ->count();

            $persentase = ($jumlahHadir / $jumlahPeserta) * 100;

            $tanggalKey = $tanggalKegiatan->format('Y-m-d');
            $dailyAverages[$tanggalKey] = round($persentase, 1);

            if ($persentase >= 80) {
                $totalKehadiranValid++;
                $aboveCount++;
            } else {
                $belowCount++;
            }
        }

        if ($totalKegiatan == 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $progress = ($totalKehadiranValid / $totalKegiatan) * 100;
        $progress = round($progress, 1);

        $nilaiTarget = $detail->nilai_target ?? 0;
        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyAverages as $dateStr => $avg) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [];
                $monthlyProgress[$monthKey] = [];
            }
            $monthlyData[$monthKey][] = $avg;
            $monthlyProgress[$monthKey][] = $avg;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
                $dailyProgressPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $avg;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $avg;
        }

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }
        foreach ($monthlyProgress as $month => $dailyVals) {
            $monthlyProgressAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $aboveCount, 'below' => $belowCount],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculatePengeluaranBiayaKaryawanDetail($itemDetail)
    {
        $defaultResponse = [
            'progress' => 0,
            'gap' => 0,
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
            'dataManual' => [
                'gaji' => 0,
                'bpjs' => 0,
                'rekrutmen' => 0,
                'manual_document' => null,
            ],
        ];

        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka)) {
            return $defaultResponse;
        }

        $tahun = (int) $detail->detail_jangka;
        $nilaiTarget = (int) $detail->nilai_target;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return $defaultResponse;
        }

        $startOfYear = Carbon::create($tahun, 1, 1)->startOfDay();
        $endOfYear = Carbon::create($tahun, 12, 31)->endOfDay();

        $parts = explode(',', $detail->manual_value ?? '');

        $gaji = (float) ($parts[0] ?? 0);
        $bpjsManual = (float) ($parts[1] ?? 0);
        $rekrutmenManual = (float) ($parts[2] ?? 0);

        $defaultResponse['dataManual'] = [
            'gaji' => $gaji,
            'bpjs' => $bpjsManual,
            'rekrutmen' => $rekrutmenManual,
            'manual_document' => $detail->manual_document ?? null,
        ];

        $bpjsIds = JenisTunjangan::whereIn('nama_tunjangan', [
            'BPJS Tenaga Kerja',
            'BPJS Kesehatan'
        ])->pluck('id');

        $bpjsBudget = TunjanganKaryawan::whereBetween('created_at', [$startOfYear, $endOfYear])
            ->whereIn('jenis_tunjangan', $bpjsIds)
            ->sum('total');

        $rekrutmenBudget = Kegiatan::whereBetween('created_at', [$startOfYear, $endOfYear])
            ->where('tipe', 'rekrutment')
            ->sum('realisasi');

        $kegiatanBudget = 0;
        $kegiatanRealisasi = 0;

        $kegiatans = Kegiatan::with('pengajuan_barang.detail')
            ->whereBetween('created_at', [$startOfYear, $endOfYear])
            ->where('tipe', 'kegiatan')
            ->get();

        foreach ($kegiatans as $kegiatan) {

            $kegiatanRealisasi += (float) $kegiatan->realisasi;

            if ($kegiatan->pengajuan_barang) {
                foreach ($kegiatan->pengajuan_barang as $pengajuan) {

                    if ($pengajuan->detail) {
                        foreach ($pengajuan->detail as $d) {

                            $qty = (float) ($d->qty ?? 0);
                            $harga = (float) ($d->harga ?? 0);

                            $kegiatanBudget += $qty * $harga;
                        }
                    }
                }
            }
        }

        $score = 0;

        if ($gaji > 0) {
            $score++;
        }

        if ($bpjsManual > 0 && $bpjsManual <= $bpjsBudget) {
            $score++;
        }

        if ($rekrutmenManual > 0 && $rekrutmenManual <= $rekrutmenBudget) {
            $score++;
        }

        if ($kegiatanRealisasi > 0 && $kegiatanRealisasi <= $kegiatanBudget) {
            $score++;
        }

        $progress = round(($score / 4) * 100, 1);
        $gap = 0;
        if ($progress <= $nilaiTarget) {
            $gap = $progress - $nilaiTarget;
        } else {
            $gap = 0;
        }


        return [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => $defaultResponse['dataManual'],
            'pie_chart' => [
                'above' => $score,
                'below' => 4 - $score
            ],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
        ];
    }

    private function calculateAdministrasiKaryawanDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("detailTargetKPI atau detail_jangka tidak ditemukan untuk item ID: {$itemDetail->id}");
            return [
                'progress'               => 0,
                'gap'                    => 0,
                'pie_chart'              => ['above' => 0, 'below' => 0],
                'monthly_data'           => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress'       => [],
                'daily_progress_per_month'  => [],
            ];
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk item ID: {$itemDetail->id}");
            return [
                'progress'               => 0,
                'gap'                    => 0,
                'pie_chart'              => ['above' => 0, 'below' => 0],
                'monthly_data'           => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress'       => [],
                'daily_progress_per_month'  => [],
            ];
        }

        $allData = AdministrasiKaryawan::whereYear('created_at', $tahun)->get();
        $totalRecords = $allData->count();

        $groupedByMonth = $allData->groupBy(fn($d) => Carbon::parse($d->created_at)->format('Y-m'));

        $penaltyPerDay = 0.1;
        $maxLateDays   = 7;
        $totalSkor     = 0;
        $perfectCount  = 0;

        $monthlyData            = [];
        $monthlyProgress        = [];
        $dailyBreakdownPerMonth = [];
        $dailyProgressPerMonth  = [];

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $monthKey = sprintf('%04d-%02d', $tahun, $bulan);
            $monthRecords = $groupedByMonth->get($monthKey, collect());
            $monthTotal   = $monthRecords->count();

            $monthSkor = 0;
            $monthDailyBreakdown = [];
            $monthDailyProgress  = [];

            if ($monthTotal > 0) {
                foreach ($monthRecords as $data) {
                    $skor = 0;

                    if ($data->status === 'selesai' && $data->dateline && $data->tanggal_selesai) {
                        $dateline = Carbon::parse($data->dateline);
                        $selesai  = Carbon::parse($data->tanggal_selesai);

                        $daysLate = $selesai->greaterThan($dateline) ? $selesai->diffInDays($dateline) : 0;

                        if ($daysLate >= $maxLateDays) {
                            $skor = 0;
                        } else {
                            $skor = max(0, 1 - ($daysLate * $penaltyPerDay));
                        }
                    }

                    $monthSkor += $skor;
                    if ($skor == 1) $perfectCount++;

                    $dayKey = $data->tanggal_selesai
                        ? Carbon::parse($data->tanggal_selesai)->format('Y-m-d')
                        : sprintf('%04d-%02d-10', $tahun, $bulan);

                    $scorePercent = $skor * 100;
                    $monthDailyBreakdown[$dayKey] = $scorePercent;
                    $monthDailyProgress[$dayKey]  = $scorePercent;
                }

                $monthProgress = ($monthSkor / $monthTotal) * 100;
            } else {
                $monthProgress = 0;
                $dayKey = sprintf('%04d-%02d-10', $tahun, $bulan);
                $monthDailyBreakdown[$dayKey] = 0;
                $monthDailyProgress[$dayKey]  = 0;
            }

            $monthlyData[$monthKey]            = round($monthProgress, 1);
            $monthlyProgress[$monthKey]        = round($monthProgress, 1);
            $dailyBreakdownPerMonth[$monthKey] = $monthDailyBreakdown;
            $dailyProgressPerMonth[$monthKey]  = $monthDailyProgress;

            $totalSkor += $monthSkor;
        }

        $progress = $totalRecords > 0 ? round(($totalSkor / $totalRecords) * 100, 1) : 0;
        $nilaiTarget = $itemDetail->detailTargetKPI->pluck('nilai_target')->first() ?? 0;
        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $perfectCount;
        $below = $totalRecords - $perfectCount;

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgress);
        ksort($dailyProgressPerMonth);

        return [
            'progress'               => $progress,
            'gap'                    => $gap,
            'pie_chart'              => ['above' => $above, 'below' => $below],
            'monthly_data'           => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress'       => $monthlyProgress,
            'daily_progress_per_month'  => $dailyProgressPerMonth,
        ];
    }

    //Driver
    private function calculatePerbaikanKendaraanDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (is_null($detail) || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        if ($personId !== null) {
            $allRepairs = PerbaikanKendaraan::whereBetween('created_at', [$start, $end])
                ->where('id_user', $personId)
                ->get();
        } else {
            $allRepairs = PerbaikanKendaraan::whereBetween('created_at', [$start, $end])->get();
        }

        $totalData = $allRepairs->count();

        if ($totalData == 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $dataDiperbaiki = $allRepairs->where('status', 'Selesai')->count();
        $dataBelumDiperbaiki = $totalData - $dataDiperbaiki;
        if ($totalData <= 0) {
            return 0;
        }

        $presentase = ($dataDiperbaiki / $totalData) * 100;
        $progress = round($presentase, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $dataDiperbaiki;
        $below = $dataBelumDiperbaiki;

        $dailyValues = [];

        foreach ($allRepairs as $repair) {
            $tanggal = Carbon::parse($repair->created_at);
            $dateKey = $tanggal->format('Y-m-d');

            $nilaiItem = $repair->status === 'Selesai' ? 100 : 0;

            if (!isset($dailyValues[$dateKey])) {
                $dailyValues[$dateKey] = [];
            }
            $dailyValues[$dateKey][] = $nilaiItem;
        }

        $dailyAverages = [];
        foreach ($dailyValues as $dateStr => $values) {
            $dailyAverages[$dateStr] = round(array_sum($values) / count($values), 1);
        }

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyAverages as $dateStr => $avg) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [];
                $monthlyProgress[$monthKey] = [];
            }
            $monthlyData[$monthKey][] = $avg;
            $monthlyProgress[$monthKey][] = $avg;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
                $dailyProgressPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $avg;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $avg;
        }

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }
        foreach ($monthlyProgress as $month => $dailyVals) {
            $monthlyProgressAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculateKontrolPengeluaranTransportasiDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $query = pickupDriver::whereBetween('created_at', [$start, $end])
            ->whereNotNull('budget');

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $DataPickup = $query->with(['biayaTransportasi'])->get();

        $totalData = $DataPickup->count();

        if ($totalData === 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $countAman = 0;
        $dailyValues = [];

        foreach ($DataPickup as $data) {
            $totalBiaya = $data->biayaTransportasi->sum('harga') ?? 0;

            $isAman = $totalBiaya <= $data->budget ? 1 : 0;
            if ($isAman) {
                $countAman++;
            }

            $tanggal = Carbon::parse($data->created_at);
            $dateKey = $tanggal->format('Y-m-d');

            if (!isset($dailyValues[$dateKey])) {
                $dailyValues[$dateKey] = [];
            }
            $dailyValues[$dateKey][] = $isAman * 100;
        }

        $presentase = ($countAman / $totalData) * 100;
        $progress = round($presentase, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $countAman;
        $below = $totalData - $countAman;

        $dailyAverages = [];
        foreach ($dailyValues as $dateStr => $values) {
            $dailyAverages[$dateStr] = round(array_sum($values) / count($values), 1);
        }

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyAverages as $dateStr => $avg) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [];
                $monthlyProgress[$monthKey] = [];
            }
            $monthlyData[$monthKey][] = $avg;
            $monthlyProgress[$monthKey][] = $avg;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
                $dailyProgressPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $avg;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $avg;
        }

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }
        foreach ($monthlyProgress as $month => $dailyVals) {
            $monthlyProgressAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculateReportKondisiKendaraanDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $response = Http::get("https://libur.deno.dev/api", ['year' => $tahun]);
        if ($response->successful()) {
            foreach ($response->json() as $libur) {
                HariLibur::updateOrCreate(
                    ['tanggal' => $libur['date']],
                    ['nama' => $libur['name'], 'year' => $tahun]
                );
            }
        }

        $startPeriode = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $endPeriode = Carbon::createFromDate($tahun, 12, 31)->endOfDay();
        $hariIni = now()->startOfDay();

        if ($hariIni > $endPeriode) {
            $hariIni = $endPeriode;
        }

        $hariLibur = HariLibur::where('year', $tahun)
            ->pluck('tanggal')
            ->map(function ($d) {
                return Carbon::parse($d)->toDateString();
            })
            ->toArray();

        if ($personId !== null) {
            $firstReport = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$startPeriode, $hariIni])
                ->where('user_id', $personId)
                ->whereNotNull('tanggal_pemeriksaan')
                ->get()
                ->filter(function ($item) use ($hariLibur) {
                    return !in_array(Carbon::parse($item->tanggal_pemeriksaan)->toDateString(), $hariLibur);
                })
                ->sortBy('tanggal_pemeriksaan')
                ->first();
        } else {
            $firstReport = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$startPeriode, $hariIni])
                ->whereNotNull('tanggal_pemeriksaan')
                ->get()
                ->filter(function ($item) use ($hariLibur) {
                    return !in_array(Carbon::parse($item->tanggal_pemeriksaan)->toDateString(), $hariLibur);
                })
                ->sortBy('tanggal_pemeriksaan')
                ->first();
        }

        if (!$firstReport) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $startMinggu = Carbon::parse($firstReport->tanggal_pemeriksaan)->startOfWeek(Carbon::MONDAY);

        $today = Carbon::now();
        $dayOfWeek = $today->dayOfWeek;

        if ($dayOfWeek < 6) {
            $checkUntil = $today->copy()->subWeek()->endOfWeek(Carbon::SUNDAY);
        } else {
            $checkUntil = $today->endOfDay();
        }

        if ($checkUntil > $endPeriode) {
            $checkUntil = $endPeriode;
        }

        $totalMinggu = ceil($startMinggu->diffInDays($checkUntil) / 7);
        if ($totalMinggu < 1) {
            $totalMinggu = 1;
        }

        $jumlahReportTepat = 0;
        $jumlahReportTidakTepat = 0;
        $weeklyData = [];

        for ($i = 0; $i < $totalMinggu; $i++) {
            $weekStart = $startMinggu->copy()->addWeeks($i)->startOfWeek(Carbon::MONDAY);
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

            if ($weekEnd > $checkUntil) {
                $weekEnd = $checkUntil;
            }

            if ($personId !== null) {
                $hasReport = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$weekStart, $weekEnd])
                    ->where('user_id', $personId)
                    ->whereNotNull('tanggal_pemeriksaan')
                    ->get()
                    ->filter(function ($item) use ($hariLibur) {
                        return !in_array(Carbon::parse($item->tanggal_pemeriksaan)->toDateString(), $hariLibur);
                    })
                    ->isNotEmpty();
            } else {
                $hasReport = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$weekStart, $weekEnd])
                    ->whereNotNull('tanggal_pemeriksaan')
                    ->get()
                    ->filter(function ($item) use ($hariLibur) {
                        return !in_array(Carbon::parse($item->tanggal_pemeriksaan)->toDateString(), $hariLibur);
                    })
                    ->isNotEmpty();
            }

            if ($hasReport) {
                $jumlahReportTepat++;
                $weekValue = 100;
            } else {
                $jumlahReportTidakTepat++;
                $weekValue = 0;
            }

            $weeklyData[] = [
                'start' => $weekStart->copy(),
                'end' => $weekEnd->copy(),
                'value' => $weekValue,
            ];
        }

        $presentase = ($jumlahReportTepat / $totalMinggu) * 100;
        $progress = round($presentase, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $jumlahReportTepat;
        $below = $jumlahReportTidakTepat;

        $dailyValues = [];
        foreach ($weeklyData as $week) {
            $currentDate = $week['start']->copy();
            while ($currentDate <= $week['end']) {
                $dateKey = $currentDate->format('Y-m-d');
                if (!isset($dailyValues[$dateKey])) {
                    $dailyValues[$dateKey] = [];
                }
                $dailyValues[$dateKey][] = $week['value'];
                $currentDate->addDay();
            }
        }

        $dailyAverages = [];
        foreach ($dailyValues as $dateStr => $values) {
            $dailyAverages[$dateStr] = round(array_sum($values) / count($values), 1);
        }

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyAverages as $dateStr => $avg) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [];
                $monthlyProgress[$monthKey] = [];
            }
            $monthlyData[$monthKey][] = $avg;
            $monthlyProgress[$monthKey][] = $avg;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
                $dailyProgressPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $avg;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $avg;
        }

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }
        foreach ($monthlyProgress as $month => $dailyVals) {
            $monthlyProgressAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculateFeedbackKenyamananBerkendaraDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $allScores = [];
        $scoreDatePairs = [];

        $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])->get();

        foreach ($feedbacks as $fb) {
            $p8 = is_numeric($fb->p8) ? (float) $fb->p8 : 0;

            $avg = ($p8) / 1;
            $avg = min(4, max(1, $avg));

            $allScores[] = $avg;
            $scoreDatePairs[] = [
                'score' => $avg,
                'date' => $fb->created_at->format('Y-m-d'),
            ];
        }

        if (empty($allScores)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $totalResponden = count($allScores);
        $respondenPuas = 0;

        foreach ($allScores as $skor) {
            if ($skor >= 3.5) {
                $respondenPuas++;
            }
        }

        $progress = ($respondenPuas / $totalResponden) * 100;
        $progress = round($progress, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($scoreDatePairs as $pair) {
            $date = Carbon::parse($pair['date']);
            $monthKey = $date->format('Y-m');
            $dayKey = $pair['date'];
            $score = $pair['score'];
            $pct = round($score * 25, 1);

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [];
                $monthlyProgress[$monthKey] = [];
            }
            $monthlyData[$monthKey][] = $score;
            $monthlyProgress[$monthKey][] = $pct;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
                $dailyProgressPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $score;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $pct;
        }

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }
        foreach ($monthlyProgress as $month => $dailyVals) {
            $monthlyProgressAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $respondenPuas,
                'below' => $totalResponden - $respondenPuas,
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    //Admin Holding
    private function calculateKetepatanWaktuPoDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $tahun = (int) $detail->detail_jangka;
        $nilaiTarget = (float) $detail->nilai_target;

        if ($tahun < 2000 || $tahun > now()->year + 5 || $nilaiTarget <= 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $pos = NomorModul::with('moduls')
            ->whereYear('created_at', $tahun)
            ->get();

        if ($pos->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $allPercents = [];
        $percentDatePairs = [];

        foreach ($pos as $po) {

            if (!$po->uploaded) {
                continue;
            }

            $uploaded = Carbon::parse($po->uploaded)->startOfDay();

            foreach ($po->moduls as $modul) {

                if (!$modul->awal_training) {
                    continue;
                }

                $awalTraining = Carbon::parse($modul->awal_training)->startOfDay();

                $daysBefore = $awalTraining->diffInDays($uploaded);

                if ($daysBefore >= 7) {
                    $percent = 100;
                } elseif ($daysBefore > 0) {
                    $percent = ($daysBefore * 100) / 7;
                } else {
                    $percent = 0;
                }

                $percent = round($percent, 1);

                $allPercents[] = $percent;

                $percentDatePairs[] = [
                    'percent' => $percent,
                    'date' => $uploaded->format('Y-m-d'),
                ];
            }
        }

        if (empty($allPercents)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $totalData = count($allPercents);
        $aboveTarget = 0;

        foreach ($allPercents as $val) {
            if ($val >= $nilaiTarget) {
                $aboveTarget++;
            }
        }

        $progress = ($aboveTarget / $totalData) * 100;
        $progress = round($progress, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($percentDatePairs as $pair) {
            $date = Carbon::parse($pair['date']);
            $monthKey = $date->format('Y-m');
            $dayKey = $pair['date'];
            $percent = $pair['percent'];

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [];
                $monthlyProgress[$monthKey] = [];
            }
            $monthlyData[$monthKey][] = $percent;
            $monthlyProgress[$monthKey][] = $percent;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
                $dailyProgressPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $percent;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $percent;
        }

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        foreach ($monthlyData as $month => $values) {
            $monthlyAverages[$month] = round(array_sum($values) / count($values), 1);
        }
        foreach ($monthlyProgress as $month => $values) {
            $monthlyProgressAverages[$month] = round(array_sum($values) / count($values), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $aboveTarget,
                'below' => $totalData - $aboveTarget,
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculatekualitasDokumentasiSupportDanProctorDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (
            !$detail ||
            !is_numeric($detail->detail_jangka) ||
            !is_numeric($detail->nilai_target)
        ) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $tahun = (int) $detail->detail_jangka;
        $nilaiTarget = (float) $detail->nilai_target;

        if ($tahun < 2000 || $tahun > now()->year + 5 || $nilaiTarget <= 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $registrasi = Registrasi::whereBetween('created_at', [$start, $end])->get();

        if ($registrasi->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $dokumentasi = DokumentasiExam::whereBetween('created_at', [$start, $end])
            ->where(function ($q) {
                $q->whereNotNull('skor')
                    ->orWhereNotNull('dokumentasi');
            })
            ->get();

        $totalRegistrasi = $registrasi->count();
        $totalDokumentasi = $dokumentasi->count();

        $progress = ($totalDokumentasi / $totalRegistrasi) * 100;
        $progress = round($progress, 2);

        if ($progress > $nilaiTarget) {
            $gapRaw = 0;
        } else {
            $gapRaw = $progress - $nilaiTarget;
        }
        $gap = rtrim(rtrim(sprintf('%.2f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($dokumentasi as $doc) {
            $date = $doc->created_at;
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = 0;
                $monthlyProgress[$monthKey] = 0;
            }
            $monthlyData[$monthKey]++;
            $monthlyProgress[$monthKey]++;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
                $dailyProgressPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey] =
                ($dailyBreakdownPerMonth[$monthKey][$dayKey] ?? 0) + 1;
            $dailyProgressPerMonth[$monthKey][$dayKey] =
                ($dailyProgressPerMonth[$monthKey][$dayKey] ?? 0) + 1;
        }

        $monthlyPercentages = [];
        $monthlyProgressPercentages = [];

        foreach ($monthlyData as $month => $countDok) {
            $registrasiPerMonth = $registrasi->filter(function ($r) use ($month) {
                return $r->created_at->format('Y-m') === $month;
            })->count();

            if ($registrasiPerMonth > 0) {
                $monthlyPercentages[$month] = round(($countDok / $registrasiPerMonth) * 100, 2);
                $monthlyProgressPercentages[$month] = round(($countDok / $registrasiPerMonth) * 100, 2);
            } else {
                $monthlyPercentages[$month] = 0;
                $monthlyProgressPercentages[$month] = 0;
            }
        }

        ksort($monthlyPercentages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressPercentages);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $totalDokumentasi,
                'below' => $totalRegistrasi - $totalDokumentasi,
            ],
            'monthly_data' => $monthlyPercentages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressPercentages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    //OB
    private function calculateFeedbackKebersihanDanKenyamananDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $allScores = [];
        $scoreDatePairs = [];

        $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])->get();

        foreach ($feedbacks as $fb) {
            $f1 = is_numeric($fb->F1) ? (float) $fb->F1 : 0;
            $f2 = is_numeric($fb->F2) ? (float) $fb->F2 : 0;
            $f3 = is_numeric($fb->F3) ? (float) $fb->F3 : 0;
            $f4 = is_numeric($fb->F4) ? (float) $fb->F4 : 0;
            $f5 = is_numeric($fb->F5) ? (float) $fb->F5 : 0;

            $avg = ($f1 + $f2 + $f3 + $f4 + $f5) / 5;
            $avg = min(4, max(1, $avg));

            $allScores[] = $avg;
            $scoreDatePairs[] = [
                'score' => $avg,
                'date' => $fb->created_at->format('Y-m-d'),
            ];
        }

        if (empty($allScores)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $totalResponden = count($allScores);
        $respondenPuas = 0;

        foreach ($allScores as $skor) {
            if ($skor >= 3.5) {
                $respondenPuas++;
            }
        }

        $progress = ($respondenPuas / $totalResponden) * 100;
        $progress = round($progress, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($scoreDatePairs as $pair) {
            $date = Carbon::parse($pair['date']);
            $monthKey = $date->format('Y-m');
            $dayKey = $pair['date'];
            $score = $pair['score'];
            $pct = round($score * 25, 1);

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [];
                $monthlyProgress[$monthKey] = [];
            }
            $monthlyData[$monthKey][] = $score;
            $monthlyProgress[$monthKey][] = $pct;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
                $dailyProgressPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $score;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $pct;
        }

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }
        foreach ($monthlyProgress as $month => $dailyVals) {
            $monthlyProgressAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $respondenPuas,
                'below' => $totalResponden - $respondenPuas,
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculatePenyelesaianTugasHarianDetail($itemDetail, $personId = null)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $query = KontrolTugas::whereYear('created_at', $tahun);

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $tugas = $query->get();

        if ($tugas->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $jumlahTugas = $tugas->count();
        $jumlahTugasSelesai = $tugas->where('status', '1')->count();

        $progress = ($jumlahTugasSelesai / $jumlahTugas) * 100;
        $progress = round($progress, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($tugas as $t) {
            $date = $t->created_at;
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $score = $t->status == 1 ? 100 : 0;

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [];
                $monthlyProgress[$monthKey] = [];
            }
            $monthlyData[$monthKey][] = $score;
            $monthlyProgress[$monthKey][] = $score;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
                $dailyProgressPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $score;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $score;
        }

        $monthlyAverages = [];
        $monthlyProgressAverages = [];
        foreach ($monthlyData as $month => $vals) {
            $monthlyAverages[$month] = round(array_sum($vals) / count($vals), 1);
        }
        foreach ($monthlyProgress as $month => $vals) {
            $monthlyProgressAverages[$month] = round(array_sum($vals) / count($vals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAverages);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $jumlahTugasSelesai,
                'below' => $jumlahTugas - $jumlahTugasSelesai,
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAverages,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    //Target Detail itsm
    //Koordinator ITSM
    private function calculateMeningkatkanKepuasanDanLoyalitasPesertaDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $allScores = [];
        $scoreDatePairs = [];

        $dataSurvey = SurveyKepuasan::whereBetween('created_at', [$start, $end])->get();

        foreach ($dataSurvey as $survey) {
            $nilaiQ1 = match ($survey->q1) {
                1 => 10,
                2 => 20,
                3 => 30,
                4 => 40,
                default => 0,
            };

            $nilaiQ4 = match ($survey->q4) {
                1 => 10,
                2 => 20,
                3 => 30,
                4 => 40,
                default => 0,
            };

            $nilaiQ2 = match ($survey->q2) {
                'Ya' => 20,
                'Tidak' => 10,
                default => 0,
            };

            $totalBaris = min(100, max(0, $nilaiQ1 + $nilaiQ2 + $nilaiQ4));
            $skor = 1 + ($totalBaris * 3) / 100;

            $allScores[] = $skor;

            $scoreDatePairs[] = [
                'score' => $skor,
                'date' => $survey->created_at->format('Y-m-d'),
            ];
        }

        if (empty($allScores)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $totalResponden = count($allScores);
        $respondenPuas = 0;

        foreach ($allScores as $skor) {
            if ($skor >= 3.0) {
                $respondenPuas++;
            }
        }

        $progress = ($respondenPuas / $totalResponden) * 100;
        $progress = round($progress, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($scoreDatePairs as $pair) {
            $date = Carbon::parse($pair['date']);
            $monthKey = $date->format('Y-m');
            $dayKey = $pair['date'];
            $score = $pair['score'];
            $isPuas = $score >= 3.0 ? 100 : 0;

            $monthlyData[$monthKey][] = $score;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $score;

            $monthlyProgress[$monthKey][] = $isPuas;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $isPuas;
        }

        $monthlyAverages = [];
        $monthlyProgressAvg = [];

        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }

        foreach ($monthlyProgress as $month => $vals) {
            $monthlyProgressAvg[$month] = round(array_sum($vals) / count($vals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAvg);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $respondenPuas,
                'below' => $totalResponden - $respondenPuas,
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAvg,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculateAvailabilitySistemInternalKritisDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $logs = activityLog::whereBetween('status', ['100', '599'])
            ->whereBetween('checked_at', [$start, $end])
            ->get();

        if ($logs->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $totalChecks = $logs->count();
        $upChecks = $logs->where('is_up', 1)->count();

        $progress = ($upChecks / $totalChecks) * 100;
        $progress = round($progress, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($logs as $log) {
            $date = Carbon::parse($log->checked_at);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $value = $log->is_up ? 100 : 0;

            $monthlyData[$monthKey][] = $value;
            $dailyBreakdownPerMonth[$monthKey][$dayKey][] = $value;

            $monthlyProgress[$monthKey][] = $value;
            $dailyProgressPerMonth[$monthKey][$dayKey][] = $value;
        }

        $monthlyAverages = [];
        $monthlyProgressAvg = [];

        foreach ($monthlyData as $month => $values) {
            $monthlyAverages[$month] = round(array_sum($values) / count($values), 1);
        }

        foreach ($monthlyProgress as $month => $values) {
            $monthlyProgressAvg[$month] = round(array_sum($values) / count($values), 1);
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $values) {
                $dailyBreakdownPerMonth[$month][$day] = round(array_sum($values) / count($values), 1);
            }
        }

        foreach ($dailyProgressPerMonth as $month => $days) {
            foreach ($days as $day => $values) {
                $dailyProgressPerMonth[$month][$day] = round(array_sum($values) / count($values), 1);
            }
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAvg);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $upChecks,
                'below' => $totalChecks - $upChecks,
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAvg,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculatePersentaseGapKompetensiDetail($itemDetail, $personId = null)
    {
        $details = $itemDetail->detailTargetKPI;

        if ($details->isEmpty()) {
            return $this->defaultResult();
        }

        $firstDetail = $details->first();
        $nilaiTarget = (float) $firstDetail->nilai_target;
        $tahun = (int) $firstDetail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $this->defaultResult();
        }

        $detailIds = $details->pluck('id');

        $query = detailPersonKPI::whereIn('detailTargetKey', $detailIds);

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $detailPersons = $query->get();

        if ($detailPersons->isEmpty()) {
            return $this->defaultResult();
        }

        $totalKemampuan = 0;
        $totalStandar = 0;
        $validPersons = [];

        foreach ($detailPersons as $dp) {
            $kemampuan = (float) $dp->presentase_kemampuan;
            $standar = (float) $dp->presentase_standar;

            if ($standar <= 0) {
                continue;
            }

            $totalKemampuan += $kemampuan;
            $totalStandar += $standar;

            $validPersons[] = $dp;
        }

        if ($totalStandar <= 0) {
            $progress = 0;
            $gap = 0;
        } else {
            $progress = ($totalKemampuan / $totalStandar) * 100;
            $progress = round(min($progress, 100), 1);
            $gap = round(100 - $progress, 1);
        }

        $above = 0;
        $below = 0;

        foreach ($validPersons as $dp) {
            $kemampuan = (float) $dp->presentase_kemampuan;
            $standar = (float) $dp->presentase_standar;

            if ($kemampuan >= $standar) {
                $above++;
            } else {
                $below++;
            }
        }

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $above,
                'below' => $below,
            ],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
        ];
    }

    //Tim Digital
    private function calculateKonsistensiCampaignDigitalDetail($itemDetail)
    {
        $details = $itemDetail->detailTargetKPI;

        if ($details->isEmpty()) {
            return [
                'progress' => 0,
                'consistency_score' => 0,
                'productivity_score' => 0,
                'gap' => 0,
                'pie_chart' => [
                    'above' => 0,
                    'below' => 0
                ],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $tahun = (int) $details->first()->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'consistency_score' => 0,
                'productivity_score' => 0,
                'gap' => 0,
                'pie_chart' => [
                    'above' => 0,
                    'below' => 0
                ],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $contentSchedules = ContentSchedule::whereBetween('upload_date', [$start, $end])
            ->whereNotNull('upload_date')
            ->get();

        if ($contentSchedules->isEmpty()) {
            return [
                'progress' => 0,
                'consistency_score' => 0,
                'productivity_score' => 0,
                'gap' => 0,
                'pie_chart' => [
                    'above' => 0,
                    'below' => 0
                ],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $weeklyCounts = [];
        $dailyBreakdownPerWeek = [];

        foreach ($contentSchedules as $schedule) {
            $date = Carbon::parse($schedule->upload_date);

            $weekStart = $date->copy()->startOfWeek(Carbon::MONDAY);
            $weekEnd = $date->copy()->endOfWeek(Carbon::SUNDAY);

            $weekKey = $weekStart->format('Y-m-d') . '_' . $weekEnd->format('Y-m-d');
            $dayKey = $date->format('Y-m-d');

            $weeklyCounts[$weekKey] = ($weeklyCounts[$weekKey] ?? 0) + 1;

            if (!isset($dailyBreakdownPerWeek[$weekKey])) {
                $dailyBreakdownPerWeek[$weekKey] = [];
            }

            $dailyBreakdownPerWeek[$weekKey][$dayKey] =
                ($dailyBreakdownPerWeek[$weekKey][$dayKey] ?? 0) + 1;
        }

        $targetMingguan = 3;

        $compliantWeeks = 0;
        $totalWeeksWithData = 0;

        foreach ($weeklyCounts as $count) {
            if ($count >= 1) {
                $totalWeeksWithData++;

                if ($count >= $targetMingguan) {
                    $compliantWeeks++;
                }
            }
        }

        $CS = $totalWeeksWithData === 0 ? 0 : $compliantWeeks / $totalWeeksWithData;

        $totalKonten = $contentSchedules->count();

        $jumlahMinggu = 0;

        $current = $start->copy()->startOfWeek(Carbon::MONDAY);
        $endOfYearWeek = $end->copy()->endOfWeek(Carbon::SUNDAY);

        while ($current <= $endOfYearWeek) {
            $jumlahMinggu++;
            $current->addWeek();
        }

        $PS = $totalKonten / ($targetMingguan * $jumlahMinggu);
        $PS = min($PS, 1);

        $finalScore = ($CS * 0.6) + ($PS * 0.4);

        $progress = round($finalScore * 100, 1);
        $CSPercent = round($CS * 100, 1);
        $PSPercent = round($PS * 100, 1);

        $nilaiTarget = $details->pluck('nilai_target')->first() ?? 0;
        $gap = round($progress - $nilaiTarget, 1);

        $expectedTotal = $targetMingguan * $jumlahMinggu;

        $above = min($totalKonten, $expectedTotal);
        $below = max($expectedTotal - $totalKonten, 0);

        ksort($weeklyCounts);
        ksort($dailyBreakdownPerWeek);

        return [
            'progress' => $progress,
            'consistency_score' => $CSPercent,
            'productivity_score' => $PSPercent,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $above,
                'below' => $below
            ],
            'monthly_data' => $weeklyCounts,
            'daily_breakdown_per_month' => $dailyBreakdownPerWeek,
        ];
    }

    private function calculateEfektifitasDiitalMarketingDetail($itemDetail, $personId)
    {
        $details = $itemDetail->detailTargetKPI;
        $detail = $details->first();

        if (is_null($detail) || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
            return [
                'progress' => 0,
                'gap' => '0',
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 1) {
            if ($tahun < 2000 || $tahun > now()->year + 1) {
                $tahun = now()->year;
            }
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $dataColaborator = Colaborator::whereBetween('created_at', [$start, $end])->get();

        $totalData = $dataColaborator->count();

        if ($totalData === 0) {
            return [
                'progress' => 0,
                'gap' => rtrim(rtrim(sprintf('%.1f', (float)(0 - $nilaiTarget)), '0'), '.'),
                'pie_chart' => ['above' => 0, 'below' => 4],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $totalQuarters = 4;
        $quartersWith = [];

        foreach ($dataColaborator as $colab) {
            $month = (int) $colab->created_at->month;
            $quarter = (int) ceil($month / 3);
            $quartersWith[$quarter] = true;
        }

        $filledQuartersCount = count($quartersWith);
        $konsistensiPersen = (float) $filledQuartersCount;
        $progress = (float) round($konsistensiPersen);

        $gapRaw = (float) ($progress - $nilaiTarget);
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = (int) $filledQuartersCount;
        $below = (int) ($totalQuarters - $filledQuartersCount);

        $dailyValues = [];

        foreach ($dataColaborator as $colab) {
            $tanggal = Carbon::parse($colab->created_at);
            $dateKey = $tanggal->format('Y-m-d');

            if (!isset($dailyValues[$dateKey])) {
                $dailyValues[$dateKey] = [];
            }

            $dailyValues[$dateKey][] = 1;
        }

        $dailyAverages = [];
        foreach ($dailyValues as $dateStr => $values) {
            $dailyAverages[$dateStr] = (float) round(array_sum($values) / count($values), 1);
        }

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyAverages as $dateStr => $avg) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $monthlyData[$monthKey][] = $avg;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $avg;

            $progressVal = (float) round(min($avg * 100, 100), 1);

            $monthlyProgress[$monthKey][] = $progressVal;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $progressVal;
        }

        $monthlyAverages = [];
        $monthlyProgressAvg = [];

        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = (float) round(array_sum($dailyVals) / count($dailyVals), 1);
        }

        foreach ($monthlyProgress as $month => $vals) {
            $monthlyProgressAvg[$month] = (float) round(array_sum($vals) / count($vals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAvg);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAvg,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    //project administrator & usiness support
    private function calculatePendapatanPenjualanProjectDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
                'triwulan_data' => [],
                'sales_performance' => null,
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
                'triwulan_data' => [],
                'sales_performance' => null,
            ];
        }

        $kodeKaryawan = null;
        $karyawanData = null;

        $query = LeadProject::where('status', 'won')
            ->whereYear('updated_at', $tahun);

        if ($kodeKaryawan) {
            $query->where('lead_projects.sales_id', $kodeKaryawan);
        }

        $sales = $query->select('lead_projects.updated_at', DB::raw('SUM(lead_projects.estimasi_nilai) as total'))
            ->groupBy('lead_projects.updated_at')
            ->get();

        $totalSales = 0;
        $dailyBreakdownPerMonth = [];
        $monthlyDataTemp = [];
        $triwulanDataTemp = [1 => 0, 2 => 0, 3 => 0, 4 => 0];

        foreach ($sales as $row) {
            $date = Carbon::parse($row->tanggal_awal);
            $dateKey = $date->format('Y-m-d');
            $monthKey = $date->format('Y-m');
            $total = (float) ($row->total ?? 0);

            $totalSales += $total;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dateKey] = (float) number_format($total, 1, '.', '');

            if (!isset($monthlyDataTemp[$monthKey])) {
                $monthlyDataTemp[$monthKey] = 0;
            }
            $monthlyDataTemp[$monthKey] += $total;

            $month = (int) $date->format('m');
            $triwulan = (int) ceil($month / 3);
            if (isset($triwulanDataTemp[$triwulan])) {
                $triwulanDataTemp[$triwulan] += $total;
            }
        }

        $monthlyData = [];
        foreach ($monthlyDataTemp as $month => $total) {
            $monthlyData[$month] = (float) number_format($total, 1, '.', '');
        }

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);

        $triwulanData = [];
        for ($i = 1; $i <= 4; $i++) {
            $triwulanData['Triwulan_' . $i] = (float) number_format($triwulanDataTemp[$i], 1, '.', '');
        }

        $progressRupiah = (float) $totalSales;

        $targetGlobal = $nilaiTarget;

        $progressGlobal = $progressRupiah;
        $gap = $progressGlobal - $nilaiTarget;

        $above = $totalSales >= $targetGlobal ? 1 : 0;
        $below = 1 - $above;

        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($monthlyData as $month => $value) {
            $monthlyProgress[$month] = $targetGlobal > 0
                ? (float) number_format(((float)$value / $targetGlobal) * 100, 1, '.', '')
                : 0;
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $value) {
                if (!isset($dailyProgressPerMonth[$month])) {
                    $dailyProgressPerMonth[$month] = [];
                }
                $dailyProgressPerMonth[$month][$day] = $targetGlobal > 0
                    ? (float) number_format(((float)$value / $targetGlobal) * 100, 1, '.', '')
                    : 0;
            }
        }

        $salesPerformance = null;

        $allSalesData = [];

        $allKaryawan = Karyawan::where(function ($q) {
            $q->where('status_aktif', '1')
                ->whereNot('jabatan', 'Outsource')
                ->where('kode_karyawan', 'NOT LIKE', 'OL%')
                ->whereNot('jabatan', 'Pilih Jabatan')
                ->whereNotNull('nip')
                ->whereNot('divisi', 'Direksi')
                ->orWhereNull('status_aktif');
        })
            ->where(function ($q) {
                $q->where('jabatan', 'Sales')
                    ->orWhere('jabatan', 'Sales Executive')
                    ->orWhere('jabatan', 'Account Manager')
                    ->orWhereNull('jabatan')
                    ->where('status_aktif', '1');
            })
            ->get();

        foreach ($allKaryawan as $karyawan) {
            $salesKey = $karyawan->kode_karyawan;
            if (!$salesKey) continue;

            $salesRevenue = LeadProject::where('status', '!=', 'lost')
                ->join('projects', 'lead_projects.id', '=', 'projects.lead_id')
                ->whereYear('projects.tanggal_awal', $tahun)
                ->where('lead_projects.sales_id', $salesKey)
                ->select(DB::raw('SUM(projects.nilai_proyek) as total'))
                ->value('total');

            $salesRevenue = (float) ($salesRevenue ?? 0);

            $detailPerson = DetailPersonKPI::where('id_target', $itemDetail->id)
                ->where('id_karyawan', $karyawan->id)
                ->first();

            $presentaseKemampuan = (float) ($detailPerson->presentase_kemampuan ?? 0);
            $idDetailPerson = $detailPerson->id ?? null;

            $percentage = $presentaseKemampuan > 0 ? ($salesRevenue / $presentaseKemampuan) * 100 : 0;

            $allSalesData[] = [
                'kode_karyawan' => (string) $salesKey,
                'nama' => (string) ($karyawan->nama_lengkap ?? $karyawan->nama ?? $salesKey),
                'revenue' => (float) number_format($salesRevenue, 1, '.', ''),
                'id_detailPerson' => $idDetailPerson,
                'presentase_kemampuan' => (float) number_format($presentaseKemampuan, 1, '.', ''),
                'percentage' => (float) number_format($percentage, 1, '.', ''),
                'status' => $salesRevenue >= $presentaseKemampuan ? 'achieved' : 'pending'
            ];
        }

        $salesPerformance = [
            'type' => 'all',
            'data' => $allSalesData
        ];


        return [
            'progress' => round($progressGlobal, 1),
            'gap' => round($gap, 1),
            'dataManual' => [
                'manual_document' => $detail->manual_document,
            ],
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
            'triwulan_data' => $triwulanData,
            'sales_performance' => $salesPerformance,
        ];
    }

    private function calculateLeadsProjectDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (
            !$detail ||
            !is_numeric($detail->detail_jangka) ||
            !is_numeric($detail->nilai_target)
        ) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
                'triwulan_data' => [],
            ];
        }

        $tahun = (int) $detail->detail_jangka;

        $targetTahunan = (int) $detail->nilai_target;

        if ($targetTahunan <= 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
                'triwulan_data' => [],
            ];
        }

        $leads = LeadProject::whereYear('created_at', $tahun)
            ->selectRaw('DATE(created_at) as tanggal, COUNT(*) as total')
            ->groupByRaw('DATE(created_at)')
            ->get();

        $totalLead = 0;
        $monthlyDataTemp = [];
        $dailyBreakdownPerMonth = [];
        $triwulanDataTemp = [1 => 0, 2 => 0, 3 => 0, 4 => 0];

        foreach ($leads as $row) {

            $date = Carbon::parse($row->tanggal);

            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $jumlah = (int) $row->total;

            $totalLead += $jumlah;

            if (!isset($monthlyDataTemp[$monthKey])) {
                $monthlyDataTemp[$monthKey] = 0;
            }

            $monthlyDataTemp[$monthKey] += $jumlah;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }

            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $jumlah;

            $triwulan = ceil($date->month / 3);

            $triwulanDataTemp[$triwulan] += $jumlah;
        }

        ksort($monthlyDataTemp);
        ksort($dailyBreakdownPerMonth);

        $monthlyProgress = [];
        foreach ($monthlyDataTemp as $month => $value) {
            $monthlyProgress[$month] = round(($value / $targetTahunan) * 100, 1);
        }

        $dailyProgressPerMonth = [];
        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $value) {
                $dailyProgressPerMonth[$month][$day] = round(($value / $targetTahunan) * 100, 1);
            }
        }

        $triwulanData = [];
        for ($i = 1; $i <= 4; $i++) {
            $triwulanData["Triwulan_$i"] = $triwulanDataTemp[$i];
        }

        $progress = ($totalLead / $targetTahunan) * 100;

        $gap = $totalLead - $targetTahunan;

        return [
            'progress' => round($totalLead),
            'gap' => $gap,
            'dataManual' => [
                'manual_document' => $detail->manual_document,
            ],
            'pie_chart' => [
                'above' => $totalLead >= $targetTahunan ? 1 : 0,
                'below' => $totalLead >= $targetTahunan ? 0 : 1,
            ],
            'monthly_data' => $monthlyDataTemp,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
            'triwulan_data' => $triwulanData,
        ];
    }

    //Programmer
    private function calculateMengukurKualitasAplikasiAgarMinimBugDetail($itemDetail, $personId = null)
    {
        $details = $itemDetail->detailTargetKPI;

        if ($details->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $firstDetail = $details->first();
        $nilaiTarget = (float) $firstDetail->nilai_target;
        $tahun = (int) $firstDetail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $picNames = [];

        if ($personId !== null) {
            $idKaryawans = detailPersonKPI::where('detailTargetKey', $firstDetail->id)
                ->where('id_karyawan', $personId)
                ->pluck('id_karyawan')->unique()->toArray();
        } else {
            $idKaryawans = detailPersonKPI::where('detailTargetKey', $firstDetail->id)
                ->pluck('id_karyawan')->unique()->toArray();
        }

        if (!empty($idKaryawans)) {
            $namaLengkapList = karyawan::whereIn('id', $idKaryawans)->pluck('nama_lengkap')->toArray();
            $picNames = array_map(fn($nama) => explode(' ', trim($nama))[0] ?? '', $namaLengkapList);
        }

        $picNames = array_filter($picNames);

        if (empty($picNames)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $normalizedPicNames = array_map(fn($name) => match ($name) {
            'Stepanus' => 'Stefan',
            'Jonathan' => 'Valen',
            default => $name
        }, $picNames);

        $ticketsError = Tickets::whereBetween('created_at', [$start, $end])
            ->where('kategori', 'Error (Aplikasi)')
            ->where('keperluan', 'Programming')
            ->whereIn('pic', $normalizedPicNames)
            ->whereNotNull('tanggal_selesai')
            ->get();

        $ticketsRequest = Tickets::whereBetween('created_at', [$start, $end])
            ->where('kategori', 'Request')
            ->whereIn('pic', $normalizedPicNames)
            ->get();

        $jumlahError = $ticketsError->count();
        $jumlahRequest = $ticketsRequest->count();
        $totalTicket = $jumlahRequest + $jumlahError;

        if ($totalTicket === 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $skorRasio = ($jumlahRequest / $totalTicket) * 100;

        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        if ($jumlahError === 0) {
            $rataSkorError = 100;
            $above = 0;
            $below = 0;
            $monthlyAverages = [];
            $dailyBreakdownPerMonth = [];
        } else {
            $totalSkorError = 0;
            $ticketScores = [];

            foreach ($ticketsError as $ticket) {
                $startAt = Carbon::parse($ticket->created_at, 'Asia/Jakarta');
                $endAt = strlen($ticket->tanggal_selesai) > 10
                    ? Carbon::parse($ticket->tanggal_selesai, 'Asia/Jakarta')
                    : Carbon::parse($ticket->tanggal_selesai . ' ' . ($ticket->jam_selesai ?? '23:59:59'), 'Asia/Jakarta');

                $durasiJam = $this->hitungJamKerja($startAt, $endAt);

                $skorDurasi = match (true) {
                    $durasiJam <= 4 => 100,
                    $durasiJam <= 8 => 80,
                    $durasiJam <= 24 => 60,
                    default => 30,
                };

                $bobot = match ($ticket->tingkat_kesulitan) {
                    'Major' => 1.5,
                    'Moderate' => 1.2,
                    default => 1.0,
                };

                $skorError = min(100, $skorDurasi * $bobot);
                $totalSkorError += $skorError;

                $dateKey = $endAt->format('Y-m-d');
                $ticketScores[$dateKey] = $skorError;
            }

            $rataSkorError = $totalSkorError / $jumlahError;

            $above = count(array_filter($ticketScores, fn($s) => $s >= 70));
            $below = $jumlahError - $above;

            $monthlyData = [];
            $dailyBreakdownPerMonth = [];

            foreach ($ticketScores as $dateStr => $score) {
                $date = Carbon::parse($dateStr);
                $monthKey = $date->format('Y-m');
                $dayKey = $dateStr;

                $dailyBreakdownPerMonth[$monthKey][$dayKey] = round($score, 1);
                $monthlyData[$monthKey][] = $score;

                $dailyProgressPerMonth[$monthKey][$dayKey] = round(min($score, 100), 1);
                $monthlyProgress[$monthKey][] = min($score, 100);
            }

            $monthlyAverages = [];
            $monthlyProgressAvg = [];

            foreach ($monthlyData as $month => $scores) {
                $monthlyAverages[$month] = round(array_sum($scores) / count($scores), 1);
            }

            foreach ($monthlyProgress as $month => $vals) {
                $monthlyProgressAvg[$month] = round(array_sum($vals) / count($vals), 1);
            }

            ksort($monthlyAverages);
            ksort($dailyBreakdownPerMonth);
            ksort($monthlyProgressAvg);
            ksort($dailyProgressPerMonth);
        }

        $skorKualitas = $skorRasio * 0.5 + $rataSkorError * 0.5;
        $progress = $nilaiTarget > 0 ? ($skorKualitas / $nilaiTarget) * 100 : 0;
        $progress = round(min($progress, 100), 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = $gapRaw < 0 ? abs($gapRaw) : 0;
        $gap = rtrim(rtrim(sprintf('%.1f', $gap), '0'), '.');

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages ?? [],
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth ?? [],
            'monthly_progress' => $monthlyProgressAvg ?? [],
            'daily_progress_per_month' => $dailyProgressPerMonth ?? [],
        ];
    }

    private function calculateProgressKetepatanWaktuPenyelesaianFiturDetail($itemDetail, $personId)
    {
        $details = $itemDetail->detailTargetKPI;

        if ($details->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'realisasi_persen' => 0,
                'total_ticket' => 0,
                'sla_met_count' => 0,
                'average_resolution_hours' => 0,
                'fastest_resolution' => 0,
                'slowest_resolution' => 0,
                'sla_rate_per_priority' => [],
                'top_pic_performance' => [],
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'monthly_ticket_count' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $firstDetail = $details->first();
        $nilaiTarget = (float) $firstDetail->nilai_target;
        $tahun = (int) $firstDetail->detail_jangka;

        if ($nilaiTarget <= 0) {
            return [];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $idKaryawans = detailPersonKPI::where('detailTargetKey', $firstDetail->id)
            ->when($personId, fn($q) => $q->where('id_karyawan', $personId))
            ->pluck('id_karyawan')->unique()->toArray();

        $namaLengkapList = karyawan::whereIn('id', $idKaryawans)->pluck('nama_lengkap')->toArray();
        $picNames = array_map(fn($n) => explode(' ', trim($n))[0] ?? '', $namaLengkapList);
        $picNames = array_filter($picNames);

        if (empty($picNames)) {
            return [];
        }

        $normalizedPicNames = array_map(fn($name) => match ($name) {
            'Stepanus' => 'Stefan',
            'Jonathan' => 'Valen',
            default => $name
        }, $picNames);

        $targetJabatanList = $details->pluck('jabatan')->unique()->toArray();

        $picJabatan = karyawan::whereIn('jabatan', $targetJabatanList)
            ->pluck('jabatan')->unique()->toArray();

        $jabatanFilter = array_map(fn($j) => match (strtolower($j)) {
            'programmer', 'koordinator itsm' => 'Programming',
            'technical support' => 'Technical Support',
            'tim digital' => 'Tim Digital',
            default => $j
        }, $picJabatan);

        $tickets = Tickets::whereIn('keperluan', $jabatanFilter)
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('pic', $normalizedPicNames)
            ->whereNotNull('tanggal_selesai')
            ->get();

        if ($tickets->isEmpty()) {
            return [];
        }

        $metCount = 0;
        $total = $tickets->count();
        $totalHours = 0;
        $fastest = null;
        $slowest = 0;

        $monthlyData = [];
        $dailyBreakdown = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($tickets as $t) {
            $priority = 'Low';

            if (in_array(strtolower($t->tingkat_kesulitan), ['major', 'moderate'])) {
                $priority = 'High';
            } elseif (in_array(strtolower($t->tingkat_kesulitan), ['minor', 'normal']) && $t->kategori === 'Error (Aplikasi)') {
                $priority = 'Medium';
            }

            $startAt = Carbon::parse($t->created_at);
            $endAt = strlen($t->tanggal_selesai) > 10
                ? Carbon::parse($t->tanggal_selesai)
                : Carbon::parse($t->tanggal_selesai . ' ' . ($t->jam_selesai ?? '23:59:59'));

            $hours = $this->hitungJamKerja($startAt, $endAt);

            $slaMet = match ($priority) {
                'High' => $hours <= 24,
                'Medium' => $hours <= 40,
                default => true
            };

            if ($slaMet) $metCount++;

            $totalHours += $hours;
            $fastest = is_null($fastest) ? $hours : min($fastest, $hours);
            $slowest = max($slowest, $hours);

            $month = $endAt->format('Y-m');
            $day = $endAt->format('Y-m-d');

            $val = $slaMet ? 1 : 0;

            $monthlyData[$month][] = $val;
            $dailyBreakdown[$month][$day] = $val;

            $progressVal = $val * 100;
            $monthlyProgress[$month][] = $progressVal;
            $dailyProgressPerMonth[$month][$day] = $progressVal;
        }

        $monthlyAvg = [];
        $monthlyProgressAvg = [];

        foreach ($monthlyData as $m => $vals) {
            $monthlyAvg[$m] = round((array_sum($vals) / count($vals)) * 100, 1);
        }

        foreach ($monthlyProgress as $m => $vals) {
            $monthlyProgressAvg[$m] = round(array_sum($vals) / count($vals), 1);
        }

        ksort($monthlyAvg);
        ksort($dailyBreakdown);
        ksort($monthlyProgressAvg);
        ksort($dailyProgressPerMonth);

        $realisasi = ($metCount / $total) * 100;
        $progress = min(round(($realisasi / $nilaiTarget) * 100, 1), 100);

        return [
            'progress' => $progress,
            'gap' => max(0, 100 - $progress),
            'realisasi_persen' => round($realisasi, 1),
            'total_ticket' => $total,
            'sla_met_count' => $metCount,
            'average_resolution_hours' => round($totalHours / $total, 1),
            'fastest_resolution' => $fastest,
            'slowest_resolution' => $slowest,
            'pie_chart' => [
                'above' => $metCount,
                'below' => $total - $metCount
            ],
            'monthly_data' => $monthlyAvg,
            'monthly_ticket_count' => [],
            'daily_breakdown_per_month' => $dailyBreakdown,
            'monthly_progress' => $monthlyProgressAvg,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    //all kecuali Koordinator ITSM
    private function calculateProgressKepuasanClientITSMDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $allScores = [];
        $scoreDatePairs = [];

        $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])->get();

        foreach ($feedbacks as $fb) {
            $f1 = is_numeric($fb->F1) ? (float) $fb->F1 : 0;
            $f2 = is_numeric($fb->F2) ? (float) $fb->F2 : 0;
            $f3 = is_numeric($fb->F3) ? (float) $fb->F3 : 0;
            $f4 = is_numeric($fb->F4) ? (float) $fb->F4 : 0;
            $f5 = is_numeric($fb->F5) ? (float) $fb->F5 : 0;

            $avg = ($f1 + $f2 + $f3 + $f4 + $f5) / 5;
            $avg = min(4, max(1, $avg));

            $allScores[] = $avg;

            $scoreDatePairs[] = [
                'score' => $avg,
                'date' => $fb->created_at->format('Y-m-d'),
            ];
        }

        if (empty($allScores)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $totalResponden = count($allScores);
        $respondenPuas = 0;

        foreach ($allScores as $skor) {
            if ($skor >= 3.0) {
                $respondenPuas++;
            }
        }

        $progress = ($respondenPuas / $totalResponden) * 100;
        $progress = round($progress, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($scoreDatePairs as $pair) {
            $date = Carbon::parse($pair['date']);
            $monthKey = $date->format('Y-m');
            $dayKey = $pair['date'];
            $score = $pair['score'];

            $monthlyData[$monthKey][] = $score;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $score;

            $progressVal = $score >= 3.0 ? 100 : round(($score / 4) * 100, 1);

            $monthlyProgress[$monthKey][] = $progressVal;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $progressVal;
        }

        $monthlyAverages = [];
        $monthlyProgressAvg = [];

        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }

        foreach ($monthlyProgress as $month => $vals) {
            $monthlyProgressAvg[$month] = round(array_sum($vals) / count($vals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAvg);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $respondenPuas,
                'below' => $totalResponden - $respondenPuas,
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAvg,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculateInovationAdaptionRateDetail($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) ($detail->detail_jangka ?? now()->year);

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $start = "$tahun-01-01";
        $end = "$tahun-12-31";

        $ideInovasi = IdeInovasi::whereBetween('created_at', [$start, $end])->get();

        if ($ideInovasi->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $dailyResults = [];

        foreach ($ideInovasi as $ide) {
            $tanggal = $ide->created_at->format('Y-m-d');
            $dailyResults[$tanggal][] = 100;
        }

        $dailyAverages = [];

        foreach ($dailyResults as $tanggal => $values) {
            $dailyAverages[$tanggal] = array_sum($values) / count($values);
        }

        $totalDays = count($dailyAverages);
        $above = $totalDays;
        $below = 0;

        $progress = $totalDays > 0 ? 100 : 0;

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        if ($progress > $nilaiTarget) {
            $gap = 0;
        } else {
            $gap = $progress - $nilaiTarget;
        }

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyAverages as $dateStr => $avg) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $monthlyData[$monthKey][] = $avg;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $avg;

            $monthlyProgress[$monthKey][] = $avg;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $avg;
        }

        $monthlyAverages = [];
        $monthlyProgressAvg = [];

        foreach ($monthlyData as $month => $values) {
            $monthlyAverages[$month] = round(array_sum($values) / count($values), 1);
        }

        foreach ($monthlyProgress as $month => $values) {
            $monthlyProgressAvg[$month] = round(array_sum($values) / count($values), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAvg);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $above,
                'below' => $below
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAvg,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    //TS
    private function calculateTingkatKeberhasilanSupportMemenuhiSLADetail($itemDetail, $personId = null)
    {
        $details = $itemDetail->detailTargetKPI;

        if ($details->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $firstDetail = $details->first();
        $nilaiTarget = (float) $firstDetail->nilai_target;
        $tahun = (int) $firstDetail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $picNames = [];

        if ($personId !== null) {
            $idKaryawans = detailPersonKPI::where('detailTargetKey', $firstDetail->id)
                ->where('id_karyawan', $personId)
                ->pluck('id_karyawan')->unique()->toArray();
        } else {
            $idKaryawans = detailPersonKPI::where('detailTargetKey', $firstDetail->id)
                ->pluck('id_karyawan')->unique()->toArray();
        }

        if (!empty($idKaryawans)) {
            $namaLengkapList = karyawan::whereIn('id', $idKaryawans)->pluck('nama_lengkap')->toArray();
            $picNames = array_map(fn($nama) => explode(' ', trim($nama))[0] ?? '', $namaLengkapList);
        }

        $picNames = array_filter($picNames);

        if (empty($picNames)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $targetJabatanList = $details->pluck('jabatan')->unique()->toArray();

        if (empty($targetJabatanList)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $keperluanPatterns = [];

        foreach ($targetJabatanList as $jabatan) {
            $jabatanLower = strtolower($jabatan);
            if (str_contains($jabatanLower, 'programmer')) {
                $keperluanPatterns[] = 'Programming';
            } elseif (str_contains($jabatanLower, 'technical support') || str_contains($jabatanLower, 'tech support')) {
                $keperluanPatterns[] = 'Technical Support';
            }
        }

        $keperluanPatterns = array_unique($keperluanPatterns);

        if (empty($keperluanPatterns)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $rawTickets = DB::table('tickets')
            ->select('created_at', 'tanggal_response', 'jam_response', 'tanggal_selesai', 'jam_selesai')
            ->whereIn('keperluan', $keperluanPatterns)
            ->whereIn('pic', $picNames)
            ->whereNotNull('tanggal_selesai')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        if ($rawTickets->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $totalTickets = 0;
        $resolutionMet = 0;
        $dailyResults = [];

        foreach ($rawTickets as $ticket) {
            try {
                $createdAt = Carbon::parse($ticket->created_at);

                $responseAt = null;
                if (!empty($ticket->tanggal_response) && !empty($ticket->jam_response)) {
                    $responseAt = Carbon::createFromFormat('Y-m-d H:i:s', $ticket->tanggal_response . ' ' . $ticket->jam_response);
                }

                $resolvedAt = null;
                if (!empty($ticket->tanggal_selesai) && !empty($ticket->jam_selesai)) {
                    $resolvedAt = Carbon::createFromFormat('Y-m-d H:i:s', $ticket->tanggal_selesai . ' ' . $ticket->jam_selesai);
                }

                if (!$resolvedAt || !$createdAt) {
                    continue;
                }

                $totalTickets++;

                $startResolution = $responseAt ?? $createdAt;

                $current = clone $startResolution;
                $endCalc = clone $resolvedAt;
                $totalHours = 0;

                $workStartHour = 8;
                $workEndHour = 17;

                while ($current->lt($endCalc)) {
                    if (in_array($current->dayOfWeek, [0, 6])) {
                        $current->startOfDay()->addDays(1)->setTime($workStartHour, 0, 0);
                        continue;
                    }

                    if ($current->hour < $workStartHour) {
                        $current->setTime($workStartHour, 0, 0);
                    }

                    if ($current->hour >= $workEndHour) {
                        $current->startOfDay()->addDays(1)->setTime($workStartHour, 0, 0);
                        continue;
                    }

                    $endOfWorkDay = clone $current;
                    $endOfWorkDay->setTime($workEndHour, 0, 0);

                    $segmentEnd = $endOfWorkDay->lt($endCalc) ? $endOfWorkDay : $endCalc;

                    $diffInMinutes = $current->diffInMinutes($segmentEnd);
                    $totalHours += $diffInMinutes / 60.0;

                    $current = clone $segmentEnd;
                    if ($current->lt($endCalc)) {
                        $current->startOfDay()->addDays(1)->setTime($workStartHour, 0, 0);
                    }
                }

                $actualResolutionHours = round($totalHours, 2);
                $metSLA = $actualResolutionHours <= 8;

                if ($metSLA) {
                    $resolutionMet++;
                }

                $dateKey = $resolvedAt->format('Y-m-d');
                $dailyResults[$dateKey][] = $metSLA ? 1 : 0;
            } catch (\Exception $e) {
                continue;
            }
        }

        if ($totalTickets === 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $progress = round(($resolutionMet / $totalTickets) * 100, 1);
        $progress = min($progress, 100);

        $gapRaw = $progress - 100;
        $gap = $gapRaw < 0 ? abs($gapRaw) : 0;
        $gap = rtrim(rtrim(sprintf('%.1f', $gap), '0'), '.');

        $above = $resolutionMet;
        $below = $totalTickets - $resolutionMet;

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyResults as $dateStr => $results) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $dateStr;

            $dailyAvg = round((array_sum($results) / count($results)) * 100, 1);

            $monthlyData[$monthKey][] = $dailyAvg;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $dailyAvg;

            $monthlyProgress[$monthKey][] = $dailyAvg;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $dailyAvg;
        }

        $monthlyAverages = [];
        $monthlyProgressAvg = [];

        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }

        foreach ($monthlyProgress as $month => $vals) {
            $monthlyProgressAvg[$month] = round(array_sum($vals) / count($vals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAvg);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAvg,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculateKualitasLayananExamDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (is_null($detail) || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $queryKPI = PenilaianExam::selectRaw('id_rkm, AVG(nilai_emote) as nilai')
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('id_rkm')
            ->groupBy('id_rkm');

        $dataKPI = $queryKPI->get();

        $totalPenilaian = $dataKPI->count();

        if ($totalPenilaian == 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $qualifiedPenilaian = $dataKPI->filter(fn($item) => $item->nilai >= 3.5)->count();

        $presentase = ($qualifiedPenilaian / $totalPenilaian) * 100;
        $progress = round($presentase, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $qualifiedPenilaian;
        $below = $totalPenilaian - $qualifiedPenilaian;

        $allExams = PenilaianExam::select('created_at', 'nilai_emote')
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('id_rkm')
            ->get();

        $dailyValues = [];

        foreach ($allExams as $exam) {
            $tanggal = Carbon::parse($exam->created_at);
            $dateKey = $tanggal->format('Y-m-d');

            $nilaiItem = $exam->nilai_emote >= 3.5 ? 100 : 0;
            $dailyValues[$dateKey][] = $nilaiItem;
        }

        $dailyAverages = [];
        foreach ($dailyValues as $dateStr => $values) {
            $dailyAverages[$dateStr] = round(array_sum($values) / count($values), 1);
        }

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyAverages as $dateStr => $avg) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $monthlyData[$monthKey][] = $avg;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $avg;

            $monthlyProgress[$monthKey][] = $avg;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $avg;
        }

        $monthlyAverages = [];
        $monthlyProgressAvg = [];

        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }

        foreach ($monthlyProgress as $month => $vals) {
            $monthlyProgressAvg[$month] = round(array_sum($vals) / count($vals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgressAvg);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgressAvg,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    //Education
    //Instruktur
    private function calculateKepuasanPesertaPelatihanDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $allScores = [];
        $scoreDatePairs = [];

        if ($personId !== null) {
            $kodeKaryawan = karyawan::where('id', $personId)->first();

            if ($kodeKaryawan) {
                $rkmList = RKM::where('instruktur_key', $kodeKaryawan->kode_karyawan)
                    ->orWhere('instruktur_key2', $kodeKaryawan->kode_karyawan)
                    ->orWhere('asisten_key', $kodeKaryawan->kode_karyawan)
                    ->get();

                if (!$rkmList->isEmpty()) {
                    $rkmIds = $rkmList->pluck('id_rkm')->filter()->toArray();

                    $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])
                        ->whereIn('id_rkm', $rkmIds)
                        ->get();

                    foreach ($feedbacks as $fb) {
                        $rkm = $rkmList->firstWhere('id_rkm', $fb->id_rkm);
                        if (!$rkm) continue;

                        $avg = 0;

                        if ($rkm->instruktur_key == $kodeKaryawan->kode_karyawan) {
                            $scores = [(float)($fb->I1 ?? 0), (float)($fb->I2 ?? 0), (float)($fb->I3 ?? 0), (float)($fb->I4 ?? 0), (float)($fb->I5 ?? 0), (float)($fb->I6 ?? 0), (float)($fb->I7 ?? 0), (float)($fb->I8 ?? 0)];
                            $avg = array_sum($scores) / 8;
                        } elseif ($rkm->instruktur_key2 == $kodeKaryawan->kode_karyawan) {
                            $scores = [(float)($fb->I1b ?? 0), (float)($fb->I2b ?? 0), (float)($fb->I3b ?? 0), (float)($fb->I4b ?? 0), (float)($fb->I5b ?? 0), (float)($fb->I6b ?? 0), (float)($fb->I7b ?? 0), (float)($fb->I8b ?? 0)];
                            $avg = array_sum($scores) / 8;
                        } elseif ($rkm->asisten_key == $kodeKaryawan->kode_karyawan) {
                            $scores = [(float)($fb->I1as ?? 0), (float)($fb->I2as ?? 0), (float)($fb->I3as ?? 0), (float)($fb->I4as ?? 0), (float)($fb->I5as ?? 0), (float)($fb->I6as ?? 0), (float)($fb->I7as ?? 0), (float)($fb->I8as ?? 0)];
                            $avg = array_sum($scores) / 8;
                        }

                        $avg = min(4, max(1, $avg));

                        $allScores[] = $avg;
                        $scoreDatePairs[] = [
                            'score' => $avg,
                            'date' => $fb->created_at->format('Y-m-d'),
                        ];
                    }
                }
            }
        } else {
            $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])->get();

            foreach ($feedbacks as $fb) {
                $i1 = (float)($fb->I1 ?? 0);
                $i2 = (float)($fb->I2 ?? 0);
                $i3 = (float)($fb->I3 ?? 0);
                $i4 = (float)($fb->I4 ?? 0);
                $i5 = (float)($fb->I5 ?? 0);
                $i6 = (float)($fb->I6 ?? 0);
                $i7 = (float)($fb->I7 ?? 0);
                $i8 = (float)($fb->I8 ?? 0);
                $sumBase = $i1 + $i2 + $i3 + $i4 + $i5 + $i6 + $i7 + $i8;

                $i1b = (float)($fb->I1b ?? 0);
                $i2b = (float)($fb->I2b ?? 0);
                $i3b = (float)($fb->I3b ?? 0);
                $i4b = (float)($fb->I4b ?? 0);
                $i5b = (float)($fb->I5b ?? 0);
                $i6b = (float)($fb->I6b ?? 0);
                $i7b = (float)($fb->I7b ?? 0);
                $i8b = (float)($fb->I8b ?? 0);
                $sumB = $i1b + $i2b + $i3b + $i4b + $i5b + $i6b + $i7b + $i8b;

                if ($sumB > 0) {
                    $totalSum = $sumBase + $sumB;
                    $totalItem = 16;
                } else {
                    $totalSum = $sumBase;
                    $totalItem = 8;
                }

                if ($totalItem > 0) {
                    $avg = $totalSum / $totalItem;
                    $avg = min(4, max(1, $avg));

                    $allScores[] = $avg;
                    $scoreDatePairs[] = [
                        'score' => $avg,
                        'date' => $fb->created_at->format('Y-m-d'),
                    ];
                }
            }
        }

        if (empty($allScores)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $totalResponden = count($allScores);
        $respondenPuas = 0;

        foreach ($allScores as $skor) {
            if ($skor >= 3.5) {
                $respondenPuas++;
            }
        }

        $progress = ($respondenPuas / $totalResponden) * 100;
        $progress = round($progress, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgressRaw = [];
        $dailyProgressPerMonthRaw = [];

        foreach ($scoreDatePairs as $pair) {
            $date = Carbon::parse($pair['date']);
            $monthKey = $date->format('Y-m');
            $dayKey = $pair['date'];
            $score = $pair['score'];

            $monthlyData[$monthKey][] = $score;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $score;

            $monthlyProgressRaw[$monthKey][] = $score;
            $dailyProgressPerMonthRaw[$monthKey][$dayKey][] = $score;
        }

        $monthlyAverages = [];
        foreach ($monthlyData as $month => $vals) {
            $monthlyAverages[$month] = round(array_sum($vals) / count($vals), 1);
        }

        $monthlyProgress = [];
        foreach ($monthlyProgressRaw as $month => $vals) {
            $total = count($vals);
            $puas = collect($vals)->filter(fn($v) => $v >= 3.5)->count();
            $monthlyProgress[$month] = $total > 0 ? round(($puas / $total) * 100, 1) : 0;
        }

        $dailyProgressPerMonth = [];
        foreach ($dailyProgressPerMonthRaw as $month => $days) {
            foreach ($days as $day => $vals) {
                $total = count($vals);
                $puas = collect($vals)->filter(fn($v) => $v >= 3.5)->count();
                $dailyProgressPerMonth[$month][$day] = $total > 0 ? round(($puas / $total) * 100, 1) : 0;
            }
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgress);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $respondenPuas,
                'below' => $totalResponden - $respondenPuas,
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculateUpselingLanjutanMateriDetail($itemDetail, $personId): array
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        if ($personId !== null) {
            $kodeKaryawan = karyawan::where('id', $personId)->first();

            if (!$kodeKaryawan) {
                return [
                    'progress' => 0,
                    'gap' => 0,
                    'pie_chart' => ['above' => 0, 'below' => 0],
                    'monthly_data' => [],
                    'daily_breakdown_per_month' => [],
                    'monthly_progress' => [],
                    'daily_progress_per_month' => [],
                ];
            }

            $rkmQuery = RKM::whereBetween('created_at', [$start, $end])
                ->where('instruktur_key', $kodeKaryawan->kode_karyawan)
                ->where('tanggal_akhir', '<', now());
        } else {
            $rkmQuery = RKM::whereBetween('created_at', [$start, $end])
                ->where('tanggal_akhir', '<', now());
        }

        $rkms = $rkmQuery->get(['id', 'created_at']);

        if ($rkms->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $rkmIds = $rkms->pluck('id');
        $rekomendasiRkmIds = RekomendasiLanjutan::whereIn('id_rkm', $rkmIds)->pluck('id_rkm');
        $hasRekomendasiMap = $rekomendasiRkmIds->flip();

        $totalData = $rkms->count();
        $totalRekomendasi = 0;

        $dailyData = [];
        $monthlyDataRaw = [];

        foreach ($rkms as $rkm) {
            $hasRekom = $hasRekomendasiMap->has($rkm->id);
            if ($hasRekom) {
                $totalRekomendasi++;
            }

            $dateObj = Carbon::parse($rkm->created_at);
            $dayKey = $dateObj->format('Y-m-d');
            $monthKey = $dateObj->format('Y-m');

            $dailyData[$dayKey]['total'] = ($dailyData[$dayKey]['total'] ?? 0) + 1;
            if ($hasRekom) {
                $dailyData[$dayKey]['rekom'] = ($dailyData[$dayKey]['rekom'] ?? 0) + 1;
            }

            $monthlyDataRaw[$monthKey]['total'] = ($monthlyDataRaw[$monthKey]['total'] ?? 0) + 1;
            if ($hasRekom) {
                $monthlyDataRaw[$monthKey]['rekom'] = ($monthlyDataRaw[$monthKey]['rekom'] ?? 0) + 1;
            }
        }

        $progress = $totalData > 0 ? round(($totalRekomendasi / $totalData) * 100, 1) : 0;

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $totalRekomendasi;
        $below = $totalData - $totalRekomendasi;

        $monthlyAverages = [];
        $monthlyProgress = [];
        foreach ($monthlyDataRaw as $month => $data) {
            $rekom = $data['rekom'] ?? 0;

            $rate = $data['total'] > 0
                ? ($rekom / $data['total']) * 100
                : 0;

            $monthlyAverages[$month] = round($rate, 1);
            $monthlyProgress[$month] = round($rate, 1);
        }
        ksort($monthlyAverages);
        ksort($monthlyProgress);

        $dailyBreakdownPerMonth = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyData as $dayKey => $data) {
            $dateObj = Carbon::parse($dayKey);
            $monthKey = $dateObj->format('Y-m');

            $rekom = $data['rekom'] ?? 0;

            $rate = $data['total'] > 0
                ? ($rekom / $data['total']) * 100
                : 0;

            $roundedRate = round($rate, 1);

            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $roundedRate;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $roundedRate;
        }

        ksort($dailyBreakdownPerMonth);
        ksort($dailyProgressPerMonth);

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            ksort($dailyBreakdownPerMonth[$month]);
            ksort($dailyProgressPerMonth[$month]);
        }

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculateSertifikasiKompetensiInternalDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        $emptyResponse = [
            'progress' => 0,
            'gap' => 0,
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
        ];

        if (is_null($detail) || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
            return $emptyResponse;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $emptyResponse;
        }

        $startYear = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $endYear = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $query = detailPersonKPI::where('detailTargetKey', $detail->id);

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $detailPersons = $query->get();
        $totalData = $detailPersons->count();

        if ($totalData === 0) {
            return $emptyResponse;
        }

        $countAchieved = 0;
        $dailyValues = [];

        foreach ($detailPersons as $personItem) {
            $validSertifikasis = Sertifikasi::where('user_id', $personItem->id_karyawan)
                ->where('tanggal_berlaku_dari', '<=', $endYear)
                ->where(function ($q) use ($startYear) {
                    $q->where('tanggal_berlaku_sampai', '>=', $startYear)
                        ->orWhereNull('tanggal_berlaku_sampai');
                })
                ->get();

            $validSertifikasi = $validSertifikasis->count();

            if ($personId !== null) {
                $countAchieved += $validSertifikasi;

                foreach ($validSertifikasis as $cert) {
                    $tanggal = Carbon::parse($cert->tanggal_berlaku_dari);
                    if ($tanggal < $startYear) {
                        $tanggal = $startYear;
                    }

                    if ($tanggal >= $startYear && $tanggal <= $endYear) {
                        $dateKey = $tanggal->format('Y-m-d');
                        $dailyValues[$dateKey][] = 1;
                    }
                }
            } else {
                if ($validSertifikasi > 0) {
                    $countAchieved += 1;

                    if ($validSertifikasis->isNotEmpty()) {
                        $firstCert = $validSertifikasis->sortBy('tanggal_berlaku_dari')->first();
                        $tanggal = Carbon::parse($firstCert->tanggal_berlaku_dari);

                        if ($tanggal < $startYear) {
                            $tanggal = $startYear;
                        }

                        if ($tanggal >= $startYear && $tanggal <= $endYear) {
                            $dateKey = $tanggal->format('Y-m-d');
                            $dailyValues[$dateKey][] = 1;
                        }
                    }
                }
            }
        }

        if ($personId !== null) {
            $progress = max(100, $countAchieved);
        } else {
            $progress = $countAchieved;
        }
        $progress = round($progress);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        if ($personId !== null) {
            $above = $countAchieved;
            $below = 0;
        } else {
            $above = $countAchieved;
            $below = $totalData - $countAchieved;
        }

        $dailyAverages = [];
        foreach ($dailyValues as $dateStr => $values) {
            $dailyAverages[$dateStr] = round(array_sum($values) / count($values), 1);
        }

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgressRaw = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyAverages as $dateStr => $avg) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $monthlyData[$monthKey][] = $avg;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $avg;

            $monthlyProgressRaw[$monthKey][] = $avg;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $avg * 100;
        }

        $monthlyAverages = [];
        $monthlyProgress = [];

        foreach ($monthlyData as $month => $dailyVals) {
            $avg = array_sum($dailyVals) / count($dailyVals);
            $monthlyAverages[$month] = round($avg, 1);
            $monthlyProgress[$month] = round($avg * 100, 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgress);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculatePelatihanKompetensiEksternalDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        $emptyResponse = [
            'progress' => 0,
            'gap' => 0,
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
        ];

        if (is_null($detail) || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
            return $emptyResponse;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $emptyResponse;
        }

        $startYear = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $endYear = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $query = detailPersonKPI::where('detailTargetKey', $detail->id);

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $detailPersons = $query->get();
        $totalData = $detailPersons->count();

        if ($totalData === 0) {
            return $emptyResponse;
        }

        $countAchieved = 0;
        $dailyValues = [];

        foreach ($detailPersons as $personItem) {
            $validSertifikasis = Pelatihan::where('user_id', $personItem->id_karyawan)
                ->whereBetween('tanggal_selesai', [$startYear, $endYear])
                ->get();

            $validSertifikasi = $validSertifikasis->count();

            if ($personId !== null) {
                $countAchieved += $validSertifikasi;

                foreach ($validSertifikasis as $cert) {
                    $tanggal = Carbon::parse($cert->tanggal_selesai);
                    if ($tanggal < $startYear) {
                        $tanggal = $startYear;
                    }

                    if ($tanggal >= $startYear && $tanggal <= $endYear) {
                        $dateKey = $tanggal->format('Y-m-d');
                        $dailyValues[$dateKey][] = 1;
                    }
                }
            } else {
                if ($validSertifikasi > 0) {
                    $countAchieved += 1;

                    if ($validSertifikasis->isNotEmpty()) {
                        $firstCert = $validSertifikasis->sortBy('tanggal_selesai')->first();
                        $tanggal = Carbon::parse($firstCert->tanggal_selesai);

                        if ($tanggal < $startYear) {
                            $tanggal = $startYear;
                        }

                        if ($tanggal >= $startYear && $tanggal <= $endYear) {
                            $dateKey = $tanggal->format('Y-m-d');
                            $dailyValues[$dateKey][] = 1;
                        }
                    }
                }
            }
        }

        if ($personId !== null) {
            $progress = max(100, $countAchieved);
        } else {
            $progress = $countAchieved;
        }
        $progress = round($progress);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        if ($personId !== null) {
            $above = $countAchieved;
            $below = 0;
        } else {
            $above = $countAchieved;
            $below = $totalData - $countAchieved;
        }

        $dailyAverages = [];
        foreach ($dailyValues as $dateStr => $values) {
            $dailyAverages[$dateStr] = round(array_sum($values) / count($values), 1);
        }

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgressRaw = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyAverages as $dateStr => $avg) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $monthlyData[$monthKey][] = $avg;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $avg;

            $monthlyProgressRaw[$monthKey][] = $avg;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $avg * 100;
        }

        $monthlyAverages = [];
        $monthlyProgress = [];

        foreach ($monthlyData as $month => $dailyVals) {
            $avg = array_sum($dailyVals) / count($dailyVals);
            $monthlyAverages[$month] = round($avg, 1);
            $monthlyProgress[$month] = round($avg * 100, 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgress);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculatePresentaseKinerjaInstrukturDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        $emptyResponse = [
            'progress' => 0,
            'gap' => 0,
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
            'instruktur_details' => [],
            'hari_libur_nasional' => ['jumlah' => 0, 'daftar' => []],
        ];

        if (!$detail || !$detail->nilai_target || !$detail->detail_jangka) {
            return $emptyResponse;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $emptyResponse;
        }

        $isMonthly = true;
        $jamKerjaPerHari = 9;

        $now = Carbon::now();

        $liburNasional = HariLibur::pluck('tanggal')
            ->map(fn($t) => Carbon::parse($t)->toDateString())
            ->toArray();

        $startMonth = $now->copy()->startOfMonth();
        $endMonth   = $now->copy()->endOfMonth();
        $hariKerjaPerbulan = 0;

        for ($date = $startMonth->copy(); $date->lte($endMonth); $date->addDay()) {
            if (!$date->isWeekend() && !in_array($date->toDateString(), $liburNasional)) {
                $hariKerjaPerbulan++;
            }
        }

        $startYear = $now->copy()->startOfYear();
        $endYear   = $now->copy()->endOfYear();
        $hariKerjaPertahun = 0;

        for ($date = $startYear->copy(); $date->lte($endYear); $date->addDay()) {
            if (!$date->isWeekend() && !in_array($date->toDateString(), $liburNasional)) {
                $hariKerjaPertahun++;
            }
        }

        if ($isMonthly) {
            $startDate = Carbon::create($tahun, now()->month, 1)->startOfMonth();
            $endDate   = $startDate->copy()->endOfMonth();
            $targetJamPerOrang = $jamKerjaPerHari * $hariKerjaPerbulan;
        } else {
            $startDate = Carbon::create($tahun, 1, 1)->startOfYear();
            $endDate   = Carbon::create($tahun, 12, 31)->endOfYear();
            $targetJamPerOrang = $jamKerjaPerHari * $hariKerjaPertahun;
        }

        $totalJamMengajar = 0;
        $dailyValues = [];
        $instrukturDetails = [];

        $hariLiburNasionalList = HariLibur::whereBetween('tanggal', [$startDate, $endDate])
            ->get()
            ->mapWithKeys(function ($libur) {
                return [Carbon::parse($libur->tanggal)->toDateString() => $libur->keterangan ?? 'Hari Libur Nasional'];
            })
            ->toArray();

        if ($personId !== null) {
            $instrukturList = Karyawan::where('id', $personId)->get();
        } else {
            $instrukturList = Karyawan::where('status_aktif', '1')
                ->whereNot('jabatan', 'Outsource')
                ->where('kode_karyawan', 'NOT LIKE', 'OL%')
                ->whereNot('jabatan', 'Pilih Jabatan')
                ->whereNotNull('nip')
                ->whereNot('divisi', 'Direksi')
                ->where('jabatan', 'instruktur')
                ->get();
        }

        foreach ($instrukturList as $instruktur) {
            $kode = $instruktur->kode_karyawan;
            $idInstruktur = $instruktur->id;

            $activityDates = ActivityInstruktur::whereNull('id_rkm')
                ->whereBetween('activity_date', [$startDate, $endDate])
                ->whereHas('user', function ($q) use ($idInstruktur) {
                    $q->where('user_id', $idInstruktur);
                })
                ->pluck('activity_date')
                ->map(fn($date) => Carbon::parse($date)->toDateString())
                ->unique()
                ->toArray();

            $rkms = RKM::where('tanggal_awal', '<=', $endDate)
                ->where('tanggal_akhir', '>=', $startDate)
                ->where(function ($q) use ($kode) {
                    $q->where('instruktur_key', $kode)
                        ->orWhere('instruktur_key2', $kode)
                        ->orWhere('asisten_key', $kode);
                })->get();

            $rkmDates = [];
            foreach ($rkms as $rkm) {
                $rkmStart = Carbon::parse($rkm->tanggal_awal);
                $rkmEnd   = Carbon::parse($rkm->tanggal_akhir);

                $effectiveStart = $rkmStart->greaterThan($startDate) ? $rkmStart : $startDate;
                $effectiveEnd   = $rkmEnd->lessThan($endDate) ? $rkmEnd : $endDate;

                for ($date = $effectiveStart->copy(); $date->lte($effectiveEnd); $date->addDay()) {
                    $rkmDates[] = $date->toDateString();
                }
            }

            $allWorkingDays = array_values(array_unique(array_merge($activityDates, $rkmDates)));

            $cutiDates = [];
            $cutiDetailList = [];
            $cutis = \App\Models\pengajuancuti::where('id_karyawan', $instruktur->id)
                ->where('tanggal_awal', '<=', $endDate)
                ->where('tanggal_akhir', '>=', $startDate)
                ->get();

            foreach ($cutis as $cuti) {
                $cutiStart = Carbon::parse($cuti->tanggal_awal);
                $cutiEnd   = Carbon::parse($cuti->tanggal_akhir);

                $effectiveStart = $cutiStart->greaterThan($startDate) ? $cutiStart : $startDate;
                $effectiveEnd   = $cutiEnd->lessThan($endDate) ? $cutiEnd : $endDate;

                for ($date = $effectiveStart->copy(); $date->lte($effectiveEnd); $date->addDay()) {
                    $dateStr = $date->toDateString();
                    $cutiDates[] = $dateStr;
                    $cutiDetailList[$dateStr] = [
                        'alasan' => $cuti->alasan ?? 'Cuti',
                        'tipe' => $cuti->tipe ?? 'Cuti',
                        'tanggal_awal' => $cuti->tanggal_awal,
                        'tanggal_akhir' => $cuti->tanggal_akhir,
                    ];
                }
            }
            $cutiDates = array_values(array_unique($cutiDates));

            $allWorkingDays = array_values(array_diff($allWorkingDays, $cutiDates));

            $jamAktifInstruktur = count($allWorkingDays) * $jamKerjaPerHari;
            $totalJamMengajar += $jamAktifInstruktur;

            foreach ($allWorkingDays as $dateStr) {
                $dailyValues[$dateStr] = ($dailyValues[$dateStr] ?? 0) + $jamKerjaPerHari;
            }

            $persentaseInstruktur = $targetJamPerOrang > 0
                ? round(($jamAktifInstruktur / $targetJamPerOrang) * 100, 1)
                : 0;

            $daftarLiburPerInstruktur = [];
            foreach ($hariLiburNasionalList as $tgl => $ket) {
                if (Carbon::parse($tgl)->between($startDate, $endDate)) {
                    $daftarLiburPerInstruktur[$tgl] = $ket;
                }
            }

            $kalenderData = [];
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $dateStr = $date->toDateString();
                $month = $date->format('Y-m');
                $day = $date->day;

                if ($date->isWeekend()) {
                    $status = 'weekend';
                    $keterangan = $date->isSaturday() ? 'Sabtu' : 'Minggu';
                } elseif (isset($hariLiburNasionalList[$dateStr])) {
                    $status = 'libur';
                    $keterangan = $hariLiburNasionalList[$dateStr];
                } elseif (in_array($dateStr, $cutiDates)) {
                    $status = 'cuti';
                    $keterangan = $cutiDetailList[$dateStr]['alasan'] ?? 'Cuti';
                } elseif (in_array($dateStr, $allWorkingDays)) {
                    $status = 'working';
                    $keterangan = 'Aktif (' . $jamKerjaPerHari . ' jam)';
                } else {
                    $status = 'empty';
                    $keterangan = 'Tidak ada aktivitas';
                }

                $kalenderData[$month][$day] = [
                    'status' => $status,
                    'keterangan' => $keterangan,
                    'tanggal' => $dateStr,
                ];
            }

            $instrukturDetails[] = [
                'id' => $instruktur->id,
                'nama' => $instruktur->nama_lengkap ?? '-',
                'kode_karyawan' => $kode,
                'jabatan' => $instruktur->jabatan ?? '-',
                'target_jam' => $targetJamPerOrang,
                'jam_aktif' => $jamAktifInstruktur,
                'persentase' => $persentaseInstruktur,
                'total_hari_kerja' => count($allWorkingDays),
                'total_hari_libur' => count($daftarLiburPerInstruktur),
                'total_hari_cuti' => count($cutiDates),
                'daftar_libur' => $daftarLiburPerInstruktur,
                'daftar_cuti' => $cutiDetailList,
                'kalender' => $kalenderData,
            ];
        }

        $jumlahInstruktur = $instrukturList->count();

        // Faktor pembagi: Jika personId tidak null, bagi dengan 1 (data utuh). Jika null, bagi dengan jumlah instruktur.
        $avgFactor = ($personId !== null || $jumlahInstruktur == 0) ? 1 : $jumlahInstruktur;

        $totalJamMengajarRataRata = $totalJamMengajar / $avgFactor;

        // Target jam untuk perbandingan persentase adalah target per orang
        $targetJam = $targetJamPerOrang;

        if ($targetJam <= 0) {
            return $emptyResponse;
        }

        $progress = round(min(100, ($totalJamMengajarRataRata / $targetJam) * 100), 1);
        $gap = round($progress - 100, 1);

        $above = $totalJamMengajarRataRata;
        $below = $personId ? 0 : max(0, $targetJam - $totalJamMengajarRataRata);

        $monthly = [];
        $dailyPerMonth = [];
        $monthlyProgress = [];
        $dailyProgress = [];

        foreach ($dailyValues as $dateStr => $jam) {
            $date = Carbon::parse($dateStr);
            $m = $date->format('Y-m');

            // Bagi dengan avgFactor untuk mendapatkan rata-rata jam per instruktur
            $jamRataRata = $jam / $avgFactor;

            $monthly[$m] = ($monthly[$m] ?? 0) + $jamRataRata;
            $dailyPerMonth[$m][$dateStr] = $jamRataRata;
        }

        foreach ($monthly as $month => $totalJam) {
            $monthlyProgress[$month] = $targetJam > 0
                ? round(($totalJam / $targetJam) * 100, 1)
                : 0;
        }

        foreach ($dailyPerMonth as $month => $days) {
            foreach ($days as $d => $val) {
                $dailyProgress[$month][$d] = $targetJam > 0
                    ? round(($val / $targetJam) * 100, 1)
                    : 0;
            }
        }

        ksort($monthly);
        ksort($dailyPerMonth);
        ksort($monthlyProgress);
        ksort($dailyProgress);

        if ($personId === null && $jumlahInstruktur > 0) {
            $avgHariKerja = array_sum(array_column($instrukturDetails, 'total_hari_kerja')) / $jumlahInstruktur;
            $avgHariCuti = array_sum(array_column($instrukturDetails, 'total_hari_cuti')) / $jumlahInstruktur;

            $instrukturDetails = [
                [
                    'id' => 0,
                    'nama' => 'Rata-rata Seluruh Instruktur',
                    'kode_karyawan' => '-',
                    'jabatan' => '-',
                    'target_jam' => $targetJamPerOrang,
                    'jam_aktif' => round($totalJamMengajarRataRata, 1),
                    'persentase' => $progress,
                    'total_hari_kerja' => round($avgHariKerja, 1),
                    'total_hari_libur' => count($hariLiburNasionalList),
                    'total_hari_cuti' => round($avgHariCuti, 1),
                    'daftar_libur' => $hariLiburNasionalList,
                    'daftar_cuti' => [],
                    'kalender' => [],
                ]
            ];
        }

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => round($above, 1),
                'below' => round($below, 1)
            ],
            'monthly_data' => $monthly,
            'daily_breakdown_per_month' => $dailyPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgress,
            'instruktur_details' => $instrukturDetails,
            'hari_libur_nasional' => [
                'jumlah' => count($hariLiburNasionalList),
                'daftar' => $hariLiburNasionalList,
            ],
        ];
    }

    //Education Manager
    private function calculatePengembanganKurikulumPelatihanDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (is_null($detail) || is_null($detail->detail_jangka)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = isset($detail->nilai_target) ? (float) $detail->nilai_target : 0;
        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $dataMateri = Materi::whereYear('created_at', $tahun)->get();

        $bulanYangAdaMateriList = $dataMateri
            ->pluck('created_at')
            ->map(function ($date) {
                return Carbon::parse($date)->month;
            })
            ->unique()
            ->values()
            ->toArray();

        $bulanYangAdaMateri = count($bulanYangAdaMateriList);
        $totalBulanDalamTahun = 12;

        if ($totalBulanDalamTahun == 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $presentase = $bulanYangAdaMateri;
        $progress = round($presentase);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $bulanYangAdaMateri;
        $below = $totalBulanDalamTahun - $bulanYangAdaMateri;

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        for ($m = 1; $m <= 12; $m++) {
            $monthKey = "{$tahun}-" . str_pad($m, 2, '0', STR_PAD_LEFT);

            $hasMateri = in_array($m, $bulanYangAdaMateriList);
            $monthValue = $hasMateri ? 1.0 : 0.0;

            $monthlyData[$monthKey] = $monthValue;
            $monthlyProgress[$monthKey] = $monthValue * 100;

            $dailyBreakdownPerMonth[$monthKey] = [];
            $dailyProgressPerMonth[$monthKey] = [];
        }

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgress);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculatePeningkatanKnowledgeSharingDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (is_null($detail) || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $dataMateri = ActivityInstruktur::whereYear('activity_date', $tahun)
            ->where('activity_type', 'Sharing Knowledge')
            ->get();

        if ($dataMateri->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => rtrim(rtrim(sprintf('%.1f', 0 - $nilaiTarget), '0'), '.'),
                'pie_chart' => ['above' => 0, 'below' => Carbon::create($tahun, 1, 1)->weeksInYear],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $totalMingguDalamTahun = Carbon::create($tahun, 1, 1)->weeksInYear;

        $mingguYangSudahJalan = [];

        foreach ($dataMateri as $activity) {
            $nomorMinggu = Carbon::parse($activity->activity_date)->week;
            $mingguYangSudahJalan[$nomorMinggu] = true;
        }

        $jumlahMingguTerisi = count($mingguYangSudahJalan);

        $progress = $totalMingguDalamTahun == 0 ? 0 : $jumlahMingguTerisi;

        if ($progress > 100) {
            $progress = 100;
        }

        $progress = round($progress);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $jumlahMingguTerisi;
        $below = max(0, $totalMingguDalamTahun - $jumlahMingguTerisi);

        $dailyValues = [];

        foreach ($dataMateri as $activity) {
            $tanggal = Carbon::parse($activity->activity_date);
            $dateKey = $tanggal->format('Y-m-d');
            $dailyValues[$dateKey][] = 1;
        }

        $dailyAverages = [];
        foreach ($dailyValues as $dateStr => $values) {
            $dailyAverages[$dateStr] = round(array_sum($values) / count($values), 1);
        }

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgressRaw = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyAverages as $dateStr => $avg) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $monthlyData[$monthKey][] = $avg;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $avg;

            $monthlyProgressRaw[$monthKey][] = $avg;
            $dailyProgressPerMonth[$monthKey][$dayKey] = $avg * 100;
        }

        $monthlyAverages = [];
        $monthlyProgress = [];

        foreach ($monthlyData as $month => $dailyVals) {
            $avg = array_sum($dailyVals) / count($dailyVals);
            $monthlyAverages[$month] = round($avg, 1);
            $monthlyProgress[$month] = round($avg * 100, 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgress);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculatePeningkatanKontribusiPelatihanDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        $emptyResponse = [
            'progress' => 0,
            'gap' => 0,
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
            'class_breakdown' => ['offline' => 0, 'online' => 0],
        ];

        if (is_null($detail) || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
            return $emptyResponse;
        }

        $targetKelas = 357;
        $tahun = (int) $detail->detail_jangka;

        if ($targetKelas <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $emptyResponse;
        }

        $startDate = Carbon::create($tahun, 1, 1);
        $endDate = min(Carbon::create($tahun, 12, 31), now());

        if ($startDate > $endDate) {
            return $emptyResponse;
        }

        $rkmQuery = RKM::where('tanggal_awal', '<=', $endDate)
            ->where('tanggal_akhir', '>=', $startDate)
            ->whereNotNull('instruktur_key')
            ->where('instruktur_key', '!=', '-');

        $rkms = $rkmQuery->get();
        $processedRkmIds = [];

        $totalKelas = 0;
        $dailyValues = [];

        foreach ($rkms as $rkm) {
            if (in_array($rkm->id, $processedRkmIds)) continue;
            $processedRkmIds[] = $rkm->id;

            $classDate = Carbon::parse($rkm->tanggal_awal);
            if ($classDate < $startDate || $classDate > $endDate) continue;

            $dateKey = $classDate->format('Y-m-d');
            $totalKelas += 1;
            $dailyValues[$dateKey] = ($dailyValues[$dateKey] ?? 0) + 1;
        }

        $progress = round(($totalKelas / $targetKelas) * 100, 2);
        $gapRaw = $progress - 100;
        $gap = rtrim(rtrim(sprintf('%.2f', $gapRaw), '0'), '.');

        $above = $totalKelas;
        $below = max(0, $targetKelas - $totalKelas);

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgressRaw = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyValues as $dateStr => $total) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $monthlyData[$monthKey][] = $total;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $total;

            $monthlyProgressRaw[$monthKey][] = $total;
            $dailyProgressPerMonth[$monthKey][$dayKey] = ($total / $targetKelas) * 100;
        }

        $monthlyAverages = [];
        $monthlyProgress = [];

        foreach ($monthlyData as $month => $vals) {
            $avg = array_sum($vals) / count($vals);
            $monthlyAverages[$month] = round($avg, 2);
            $monthlyProgress[$month] = round(($avg / $targetKelas) * 100, 2);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgress);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
            'class_breakdown' => [],
        ];
    }

    private function calculateEvaluasiKinerjaInstrukturDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        $emptyResponse = [
            'progress' => 0,
            'gap' => 0,
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
        ];

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $emptyResponse;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $emptyResponse;
        }

        $instrukturs = Karyawan::where('Divisi', '!=', 'Direksi')
            ->where('status_aktif', '1')->whereNot('jabatan', 'Outsource')->where('kode_karyawan', 'NOT LIKE', 'OL%')->whereNot('jabatan', 'Pilih Jabatan')->where('nip', '!=', null)
            ->where('jabatan', 'Instruktur')
            ->get();

        if ($instrukturs->isEmpty()) {
            return $emptyResponse;
        }

        $startDate = Carbon::create($tahun, 1, 1);
        $endDate = min(Carbon::create($tahun, 12, 31), now());

        if ($startDate > $endDate) {
            return $emptyResponse;
        }

        $period = CarbonPeriod::create($startDate, $endDate);

        $activities = ActivityInstruktur::whereYear('created_at', $tahun)
            ->get()
            ->groupBy(function ($item) {
                return $item->user_id . '_' . Carbon::parse($item->created_at)->format('Y-m-d');
            });

        $totalHariKerja = 0;
        $totalAktif = 0;
        $dailyValues = [];

        foreach ($period as $date) {
            if ($date->isWeekend()) continue;

            $totalHariKerja++;
            $dateKey = $date->format('Y-m-d');
            $aktifHariIni = 0;

            foreach ($instrukturs as $instruktur) {
                $key = $instruktur->id . '_' . $dateKey;
                if (isset($activities[$key])) {
                    $totalAktif++;
                    $aktifHariIni++;
                }
            }

            $dailyValues[$dateKey] = $aktifHariIni;
        }

        $totalKemungkinan = $totalHariKerja * $instrukturs->count();
        if ($totalKemungkinan == 0) {
            return $emptyResponse;
        }

        $progress = round(($totalAktif / $totalKemungkinan) * 100, 2);
        $gapRaw = $progress - 100;
        $gap = rtrim(rtrim(sprintf('%.2f', $gapRaw), '0'), '.');

        $above = $totalAktif;
        $below = max(0, $totalKemungkinan - $totalAktif);

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgressRaw = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyValues as $dateStr => $total) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $monthlyData[$monthKey][] = $total;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $total;

            $monthlyProgressRaw[$monthKey][] = $total;
            $dailyProgressPerMonth[$monthKey][$dayKey] = ($total / $instrukturs->count()) * 100;
        }

        $monthlyAverages = [];
        $monthlyProgress = [];

        foreach ($monthlyData as $month => $vals) {
            $avg = array_sum($vals) / count($vals);
            $monthlyAverages[$month] = round($avg, 2);
            $monthlyProgress[$month] = round(($avg / $instrukturs->count()) * 100, 2);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgress);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculatePembuatanArtikelDetail($itemDetail) {
        $detail = $itemDetail->detailTargetKPI->first();
        if (!$detail || !$detail->detail_jangka) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$itemDetail->id}");
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;
        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$itemDetail->id}");
            return 0;
        }

        $startDate = carbon::create($tahun, '01', '01');
        $endDate = carbon::create($tahun, '12', '31');
        $response = Http::get('http://202.138.248.36:8003/api/filtered-articles')->json();

        $apiArtikel = collect($response['data'] ?? []);

        $getData = $apiArtikel->filter(function ($item) use ($startDate, $endDate) {
            $tanggal = Carbon::parse($item['tanggal']);

            return $tanggal->between($startDate, $endDate);
        });

        $totalData = $getData->count();

        if ($totalData == 0) {
            return [
                'progress' => 0,
                'gap' => -24,
                'pie_chart' => [
                    'above' => 0,
                    'below' => 24,
                ],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $progress = round(($totalData / 24) * 100, 2);
        $gap = $totalData - 24;

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($getData as $item) {
            $tanggal = Carbon::parse($item['tanggal']);

            $monthKey = $tanggal->format('Y-m');
            $dayKey = $tanggal->format('Y-m-d');

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = 0;
            }

            if (!isset($dailyBreakdownPerMonth[$monthKey][$dayKey])) {
                $dailyBreakdownPerMonth[$monthKey][$dayKey] = 0;
            }

            $monthlyData[$monthKey]++;
            $dailyBreakdownPerMonth[$monthKey][$dayKey]++;
        }

        foreach ($monthlyData as $month => $count) {
            $monthlyProgress[$month] = round(($count / 24) * 100, 2);

            foreach ($dailyBreakdownPerMonth[$month] as $day => $dailyCount) {
                $dailyProgressPerMonth[$month][$day] = round(($dailyCount / 24) * 100, 2);
            }
        }

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);
        ksort($monthlyProgress);
        ksort($dailyProgressPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $totalData,
                'below' => max(0, 24 - $totalData),
            ],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    //Sales & Marketing
    //Sales
    private function calculateTargetPenjualanTahunanDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
                'triwulan_data' => [],
                'sales_performance' => null,
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
                'triwulan_data' => [],
                'sales_performance' => null,
            ];
        }

        $kodeKaryawan = null;
        $karyawanData = null;

        if ($personId !== null) {
            $karyawanData = Karyawan::find($personId);
            $kodeKaryawan = $karyawanData ? $karyawanData->kode_karyawan : null;
        }

        $query = RKM::where('status', '0')
            ->whereYear('tanggal_awal', $tahun);

        if ($kodeKaryawan) {
            $query->where('sales_key', $kodeKaryawan);
        }

        $sales = $query->select(DB::raw('tanggal_awal, SUM(CAST(harga_jual AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total'))
            ->groupBy('tanggal_awal')
            ->get();

        $totalSales = 0;
        $dailyBreakdownPerMonth = [];
        $monthlyDataTemp = [];
        $triwulanDataTemp = [1 => 0, 2 => 0, 3 => 0, 4 => 0];

        foreach ($sales as $row) {
            $date = Carbon::parse($row->tanggal_awal);
            $dateKey = $date->format('Y-m-d');
            $monthKey = $date->format('Y-m');
            $total = (float) ($row->total ?? 0);

            $totalSales += $total;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dateKey] = (float) number_format($total, 1, '.', '');

            if (!isset($monthlyDataTemp[$monthKey])) {
                $monthlyDataTemp[$monthKey] = 0;
            }
            $monthlyDataTemp[$monthKey] += $total;

            $month = (int) $date->format('m');
            $triwulan = (int) ceil($month / 3);
            if (isset($triwulanDataTemp[$triwulan])) {
                $triwulanDataTemp[$triwulan] += $total;
            }
        }

        $monthlyData = [];
        foreach ($monthlyDataTemp as $month => $total) {
            $monthlyData[$month] = (float) number_format($total, 1, '.', '');
        }

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);

        $triwulanData = [];
        for ($i = 1; $i <= 4; $i++) {
            $triwulanData['Triwulan_' . $i] = (float) number_format($triwulanDataTemp[$i], 1, '.', '');
        }

        $progressRupiah = (float) number_format($totalSales, 1, '.', '');

        $dataTarget = targetKPI::with(['detailTargetKPI', 'dataTarget'])
            ->whereHas('dataTarget', function ($q) {
                $q->where('asistant_route', 'Pemasukan Kotor');
            })
            ->first();

        $targetGM = ModelsTarget::where('quartal', 'All')->first() ?? null;
        $targetGlobal = (float) ($dataTarget->detailTargetKPI->first()->nilai_target ?? $targetGM->target ?? 0);

        $progressGlobal = $targetGlobal > 0 ? ($progressRupiah / $targetGlobal) * 100 : 0;
        $gap = $progressGlobal - $nilaiTarget;

        $above = $totalSales >= $targetGlobal ? 1 : 0;
        $below = 1 - $above;

        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($monthlyData as $month => $value) {
            $monthlyProgress[$month] = $targetGlobal > 0
                ? (float) number_format(($value / $targetGlobal) * 100, 1, '.', '')
                : 0;
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $value) {
                if (!isset($dailyProgressPerMonth[$month])) {
                    $dailyProgressPerMonth[$month] = [];
                }
                $dailyProgressPerMonth[$month][$day] = $targetGlobal > 0
                    ? (float) number_format(($value / $targetGlobal) * 100, 1, '.', '')
                    : 0;
            }
        }

        $salesPerformance = null;

        if ($personId === null) {
            $allSalesData = [];

            $allKaryawan = Karyawan::where(function ($q) {
                $q->where('status_aktif', '1')->whereNot('jabatan', 'Outsource')->where('kode_karyawan', 'NOT LIKE', 'OL%')->whereNot('jabatan', 'Pilih Jabatan')->where('nip', '!=', null)->whereNot('divisi', 'Direksi')
                    ->orWhereNull('status_aktif');
            })
                ->where(function ($q) {
                    $q->where('jabatan', 'Sales')
                        ->orWhere('jabatan', 'Sales Executive')
                        ->orWhere('jabatan', 'Account Manager')
                        ->orWhereNull('jabatan')
                        ->where('status_aktif', '1');
                })
                ->get();

            foreach ($allKaryawan as $karyawan) {
                $salesKey = $karyawan->kode_karyawan;

                if (!$salesKey) {
                    continue;
                }

                $salesRevenue = RKM::where('status', '0')
                    ->whereYear('tanggal_awal', $tahun)
                    ->where('sales_key', $salesKey)
                    ->select(DB::raw('SUM(CAST(harga_jual AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total'))
                    ->value('total');

                $salesRevenue = (float) ($salesRevenue ?? 0);

                $detailPerson = DetailPersonKPI::where('id_target', $itemDetail->id)
                    ->where('id_karyawan', $karyawan->id)
                    ->first();

                $presentaseKemampuan = (float) ($detailPerson->presentase_kemampuan ?? 0);
                $idDetailPerson = $detailPerson->id ?? null;

                $percentage = $presentaseKemampuan > 0 ? ($salesRevenue / $presentaseKemampuan) * 100 : 0;

                $allSalesData[] = [
                    'kode_karyawan' => (string) $salesKey,
                    'nama' => (string) ($karyawan->nama_lengkap ?? $karyawan->nama ?? $salesKey),
                    'revenue' => (float) number_format($salesRevenue, 1, '.', ''),
                    'id_detailPerson' => $idDetailPerson,
                    'presentase_kemampuan' => (float) number_format($presentaseKemampuan, 1, '.', ''),
                    'percentage' => (float) number_format($percentage, 1, '.', ''),
                    'status' => $salesRevenue >= $presentaseKemampuan ? 'achieved' : 'pending'
                ];
            }

            $salesPerformance = [
                'type' => 'all',
                'data' => $allSalesData
            ];
        } else {
            $detailPerson = DetailPersonKPI::where('id_target', $itemDetail->id)
                ->where('id_karyawan', $personId)
                ->first();

            $presentaseKemampuan = (float) ($detailPerson->presentase_kemampuan ?? 0);
            $idDetailPerson = $detailPerson->id ?? null;

            $percentage = $presentaseKemampuan > 0 ? ($totalSales / $presentaseKemampuan) * 100 : 0;

            $karyawanName = $karyawanData ? ($karyawanData->nama_lengkap ?? $karyawanData->nama ?? '') : '';

            $salesPerformance = [
                'type' => 'individual',
                'data' => [
                    'kode_karyawan' => (string) $kodeKaryawan,
                    'nama' => (string) $karyawanName,
                    'revenue' => (float) number_format($totalSales, 1, '.', ''),
                    'id_detailPerson' => $idDetailPerson,
                    'presentase_kemampuan' => (float) number_format($presentaseKemampuan, 1, '.', ''),
                    'percentage' => (float) number_format($percentage, 1, '.', ''),
                    'status' => $totalSales >= $presentaseKemampuan ? 'achieved' : 'pending'
                ]
            ];
        }

        return [
            'progress' => (float) number_format($progressGlobal, 1, '.', ''),
            'gap' => (float) number_format($gap, 1, '.', ''),
            'dataManual' => [
                'manual_document' => $detail->manual_document,
            ],
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
            'triwulan_data' => $triwulanData,
            'sales_performance' => $salesPerformance,
        ];
    }

    private function calculateCustomerAcquisitionCostDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $labaKotor = $this->calculatePemasukanKotor($itemDetail, $personId);

        if ($labaKotor == 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $karyawanIds = detailPersonKPI::where('detailTargetKey', $detail->id)->pluck('id_karyawan');
        $kodeKaryawanList = karyawan::whereIn('id', $karyawanIds)->pluck('kode_karyawan')->filter();

        if ($kodeKaryawanList->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $totalBiayaAkuisisi = perhitunganNetSales::whereHas('rkm', function ($query) use ($kodeKaryawanList, $tahun) {
            $query->whereIn('sales_key', $kodeKaryawanList)
                ->whereYear('tanggal_awal', $tahun);
        })
            ->whereBetween('tgl_pa', [$start, $end])
            ->get()
            ->sum(function ($record) {
                return ($record->transportasi ?? 0) +
                    ($record->akomodasi_peserta ?? 0) +
                    ($record->akomodasi_tim ?? 0) +
                    ($record->fresh_money ?? 0) +
                    ($record->entertaint ?? 0) +
                    ($record->souvenir ?? 0) +
                    ($record->cashback ?? 0) +
                    ($record->sewa_laptop ?? 0);
            });

        if ($totalBiayaAkuisisi > ($labaKotor * ($nilaiTarget / 100))) {
            $totalBiayaAkuisisi = $labaKotor * ($nilaiTarget / 100);
        }

        $progress = 0;

        if ($totalBiayaAkuisisi > 0) {
            $rasio = ($totalBiayaAkuisisi / $labaKotor) * 100;
            $batas = $nilaiTarget;
            $progress = ($batas / $rasio) * 100;
        }

        $progress = round($progress, 1);

        if ($progress > $nilaiTarget) {
            $gapRaw = 0;
        } else {
            $gapRaw = $progress - $nilaiTarget;
        }
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $progress >= $nilaiTarget ? 1 : 0;
        $below = 1 - $above;

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
        ];
    }

    // SPV Sales
    private function calculateMeningkatkanRevenuePerusahaanDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        $emptyResponse = [
            'progress' => 0,
            'gap' => 0,
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
        ];

        if (!$detail || !$detail->detail_jangka) {
            return $emptyResponse;
        }

        $tahun = (int) $detail->detail_jangka;
        $nilaiTarget = (float) $detail->nilai_target;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return $emptyResponse;
        }

        $approvals = ApprovalPendapatan::whereYear('created_at', $tahun)->get();

        $progress = 0;
        $dailyBreakdownPerMonth = [];

        foreach ($approvals as $approval) {

            $bersih = (float) $approval->total_penjualan_bersih;

            $progress += $bersih;

            $date = Carbon::parse($approval->created_at);

            $monthKey = $date->format('Y-m');
            $dayKey   = $date->format('Y-m-d');

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }

            if (!isset($dailyBreakdownPerMonth[$monthKey][$dayKey])) {
                $dailyBreakdownPerMonth[$monthKey][$dayKey] = 0;
            }

            $dailyBreakdownPerMonth[$monthKey][$dayKey] += $bersih;
        }

        ksort($dailyBreakdownPerMonth);

        $monthlyData = [];
        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($dailyBreakdownPerMonth as $month => $days) {

            $totalMonth = array_sum($days);

            // jika ingin TOTAL per bulan
            $monthlyData[$month] = $totalMonth;

            $monthlyProgress[$month] = $nilaiTarget > 0
                ? ($totalMonth / $nilaiTarget) * 100
                : 0;

            foreach ($days as $day => $value) {
                $dailyProgressPerMonth[$month][$day] = $nilaiTarget > 0
                    ? ($value / $nilaiTarget) * 100
                    : 0;
            }
        }

        $gap = $progress - $nilaiTarget;

        return [
            // samakan dengan original
            'progress' => round($progress),

            'gap' => $gap,

            'pie_chart' => [
                'above' => max($gap, 0),
                'below' => abs(min($gap, 0)),
            ],

            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculateEvaluasiKinerjaSalesDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        $emptyResponse = [
            'progress' => 0,
            'gap' => 0,
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
        ];

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return $emptyResponse;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $emptyResponse;
        }

        $Saless = Karyawan::where('Divisi', '!=', 'Direksi')
            ->where('status_aktif', '1')->whereNot('jabatan', 'Outsource')->where('kode_karyawan', 'NOT LIKE', 'OL%')->whereNot('jabatan', 'Pilih Jabatan')->where('nip', '!=', null)
            ->where('jabatan', 'Sales')
            ->get();

        if ($Saless->isEmpty()) {
            return $emptyResponse;
        }

        $startDate = Carbon::create($tahun, 1, 1);
        $endDate = min(Carbon::create($tahun, 12, 31), now());

        if ($startDate > $endDate) {
            return $emptyResponse;
        }

        $period = CarbonPeriod::create($startDate, $endDate);

        $activities = Aktivitas::whereYear('created_at', $tahun)
            ->get()
            ->groupBy(function ($item) {
                return $item->user_id . '_' . Carbon::parse($item->created_at)->format('Y-m-d');
            });

        $totalHariKerja = 0;
        $totalAktif = 0;
        $dailyValues = [];

        foreach ($period as $date) {
            if ($date->isWeekend()) {
                continue;
            }

            $totalHariKerja++;
            $dateKey = $date->format('Y-m-d');
            $aktifHariIni = 0;

            foreach ($Saless as $sales) {
                $key = $sales->kode_karyawan . '_' . $dateKey;

                if (isset($activities[$key])) {
                    $totalAktif++;
                    $aktifHariIni++;
                }
            }

            $dailyValues[$dateKey] = $aktifHariIni;
        }

        $totalKemungkinan = $totalHariKerja * $Saless->count();

        if ($totalKemungkinan == 0) {
            return $emptyResponse;
        }

        $persentase = ($totalAktif / $totalKemungkinan) * 100;
        $progress = round($persentase, 2);

        $gapRaw = $progress - 100;
        $gap = rtrim(rtrim(sprintf('%.2f', $gapRaw), '0'), '.');

        $above = $totalAktif;
        $below = max(0, $totalKemungkinan - $totalAktif);

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];

        foreach ($dailyValues as $dateStr => $total) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [];
            }
            $monthlyData[$monthKey][] = $total;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $total;
        }

        $monthlyAverages = [];
        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 2);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);

        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($monthlyAverages as $month => $value) {
            $monthlyProgress[$month] = 100 > 0 ? round(($value / 100) * 100, 1) : 0;
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $value) {
                if (!isset($dailyProgressPerMonth[$month])) {
                    $dailyProgressPerMonth[$month] = [];
                }
                $dailyProgressPerMonth[$month][$day] = 100 > 0 ? round(($value / 100) * 100, 1) : 0;
            }
        }

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $above,
                'below' => $below
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculateBiayaAkuisisiClientDetail($itemDetail)
    {
        $details = $itemDetail->detailTargetKPI;
        $detail = $details->first();
        $nilaiTarget = (float) ($detail->nilai_target ?? 0);
        $item = $itemDetail;
        $personId = Auth::user()->id;

        if (!$detail || $nilaiTarget <= 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $tahun = (int) $detail->detail_jangka;
        $persentaseTarget = (float) $detail->nilai_target;
        $targetTahunanUnit = $this->calculatePemasukanKotor($item, $personId);

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $peluangs = Peluang::with('rkm.perhitunganNetSales')
            ->whereYear('created_at', $tahun)
            ->get();

        $actualCAC = 0;
        $dailyBreakdownPerMonth = [];

        $maxCAC = ($persentaseTarget / 100) * $targetTahunanUnit;

        foreach ($peluangs as $p) {
            if ($p->tahap !== 'merah') {
                continue;
            }

            $totalBiayaPeluang = 0;
            if ($p->rkm && $p->rkm->perhitunganNetSales) {
                foreach ($p->rkm->perhitunganNetSales as $perhitungan) {
                    $totalBiayaPeluang += ($perhitungan->transportasi ?? 0)
                        + ($perhitungan->akomodasi_peserta ?? 0)
                        + ($perhitungan->akomodasi_tim ?? 0)
                        + ($perhitungan->fresh_money ?? 0)
                        + ($perhitungan->entertaint ?? 0)
                        + ($perhitungan->souvenir ?? 0)
                        + ($perhitungan->cashback ?? 0)
                        + ($perhitungan->sewa_laptop ?? 0);
                }
            }

            $actualCAC += $totalBiayaPeluang;

            $date = \Carbon\Carbon::parse($p->created_at);
            $dateKey = $date->format('Y-m-d');
            $monthKey = $date->format('Y-m');

            if (!isset($dailyBreakdownPerMonth[$monthKey][$dateKey])) {
                $dailyBreakdownPerMonth[$monthKey][$dateKey] = 0;
            }
            $dailyBreakdownPerMonth[$monthKey][$dateKey] += $totalBiayaPeluang;
        }

        $progress = 0;
        if ($actualCAC > 0) {
            $progress = min(($maxCAC / $actualCAC) * 100, 100);
        }

        $monthlyData = [];
        foreach ($dailyBreakdownPerMonth as $month => $days) {
            $monthlyData[$month] = round(array_sum($days) / count($days), 1);
        }

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);

        $monthlyProgress = [];
        $dailyProgressPerMonth = [];

        foreach ($monthlyData as $month => $value) {
            $monthlyProgress[$month] = $maxCAC > 0 ? round(($value / $maxCAC) * 100, 1) : 0;
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $value) {
                if (!isset($dailyProgressPerMonth[$month])) {
                    $dailyProgressPerMonth[$month] = [];
                }
                $dailyProgressPerMonth[$month][$day] = $maxCAC > 0 ? round(($value / $maxCAC) * 100, 1) : 0;
            }
        }

        $gapRaw = $maxCAC - $actualCAC;
        if ($progress > $nilaiTarget) {
            $gapRaw = 0;
        }
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        return [
            'progress' => round($progress, 1),
            'actual_cac' => $actualCAC,
            'max_cac' => $maxCAC,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $actualCAC > $maxCAC ? 1 : 0,
                'below' => $actualCAC <= $maxCAC ? 1 : 0
            ],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    //Adm Sales
    private function calculateLaporanMOMDetail($itemDetail)
    {
        $details = $itemDetail->detailTargetKPI;

        $firstDetail = $details->first();

        $tahun = (int) optional($firstDetail)->detail_jangka;
        $nilaiTarget = (float) optional($firstDetail)->nilai_target;

        if ($details->isEmpty() || $nilaiTarget <= 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $momCount = LaporanHarianSales::whereYear('created_at', $tahun)->count();
        $PACount = checklistRKM::whereYear('created_at', $tahun)->where('PA', '1')->count();
        $SuratKontrakCount = checklistRKM::whereYear('created_at', $tahun)->where('surat_kontrak', '1')->count();

        $rkmBase = RKM::whereYear('tanggal_awal', $tahun);

        $totalDataERegist = (clone $rkmBase)->count();

        $totalDataAboveERegist = (clone $rkmBase)
            ->whereNotNull('registrasi_form')
            ->count();

        $persenCalculationMom = $momCount == 0 ? 100 : 25;
        $persenCalculationERegist = $totalDataERegist == 0 ? 0 : 25;

        $progressMoM = $momCount > 0
            ? ($momCount / $momCount) * $persenCalculationERegist
            : 0;

        $progressSuratKontrak = $SuratKontrakCount > 0
            ? ($SuratKontrakCount / $SuratKontrakCount) * $persenCalculationERegist
            : 0;

        $progressPA = $PACount > 0
            ? ($PACount / $PACount) * $persenCalculationERegist
            : 0;

        $progressERegist = $totalDataERegist > 0
            ? ($totalDataAboveERegist / $totalDataERegist) * $persenCalculationMom
            : 0;

        $progress = $progressMoM + $progressERegist + $progressPA + $progressSuratKontrak;

        $laporans = LaporanHarianSales::whereYear('created_at', $tahun)
            ->select(DB::raw('DATE(created_at) as tanggal, COUNT(*) as total'))
            ->groupBy('tanggal')
            ->get();

        $dailyBreakdownPerMonth = [];
        $monthlyDataTemp = [];

        foreach ($laporans as $row) {
            $date = Carbon::parse($row->tanggal);
            $dateKey = $date->format('Y-m-d');
            $monthKey = $date->format('Y-m');

            $total = (float) $row->total;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dateKey] = $total;

            if (!isset($monthlyDataTemp[$monthKey])) {
                $monthlyDataTemp[$monthKey] = [];
            }
            $monthlyDataTemp[$monthKey][] = $total;
        }

        $monthlyData = [];
        foreach ($monthlyDataTemp as $month => $totals) {
            $count = count($totals);

            $monthlyData[$month] = $count > 0
                ? round(array_sum($totals) / $count, 1)
                : 0;
        }

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);

        $monthlyProgress = [];
        foreach ($monthlyData as $month => $value) {
            $monthlyProgress[$month] = $nilaiTarget > 0
                ? round(($value / $nilaiTarget) * 100, 1)
                : 0;
        }

        $dailyProgressPerMonth = [];
        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $date => $value) {
                $dailyProgressPerMonth[$month][$date] = $nilaiTarget > 0
                    ? round(($value / $nilaiTarget) * 100, 1)
                    : 0;
            }
        }

        $pieChart = [
            'above' => $totalDataAboveERegist,
            'below' => max(0, $totalDataERegist - $totalDataAboveERegist),
        ];

        $gap = 0;

        if ($progress > $nilaiTarget) {
            $gap = 0;
        } else {
            $gapRaw = $progress - $nilaiTarget;
            $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
        }

        return [
            'progress' => round($progress, 1),
            'gap' => $gap,
            'pie_chart' => $pieChart,
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculateAkurasiKelengkapanDataPenjualanDetail($itemDetail, $personId)
    {
        $details = $itemDetail->detailTargetKPI;
        $detail = $details->first();

        $nilaiTarget = (float) optional($detail)->nilai_target;
        $tahun = (int) optional($detail)->detail_jangka ?? now()->year;

        if ($details->isEmpty() || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $rkms = RKM::with(['perhitunganNetSales', 'outstanding'])
            ->whereYear('created_at', $tahun)
            ->get();

        $totalRkmDenganPerhitungan = 0;
        $totalRkmAkurat = 0;

        $dailyBreakdownPerMonth = [];
        $monthlyTotals = [];

        foreach ($rkms as $rkm) {
            $listPerhitungan = $rkm->perhitunganNetSales;

            if (empty($listPerhitungan)) {
                continue;
            }

            $totalRkmDenganPerhitungan++;

            $listOutstanding = $rkm->outstanding;

            if (blank($listOutstanding)) {
                continue;
            }

            $sumKomponen = 0;

            $itemsPerhitungan = $listPerhitungan instanceof \Illuminate\Database\Eloquent\Collection
                ? $listPerhitungan
                : [$listPerhitungan];

            foreach ($itemsPerhitungan as $p) {
                $sumKomponen +=
                    (int)($p->transportasi ?? 0) +
                    (int)($p->akomodasi_peserta ?? 0) +
                    (int)($p->akomodasi_tim ?? 0) +
                    (int)($p->fresh_money ?? 0) +
                    (int)($p->entertaint ?? 0) +
                    (int)($p->souvenir ?? 0) +
                    (int)($p->cashback ?? 0) +
                    (int)($p->sewa_laptop ?? 0);
            }

            $sumOutstanding = 0;

            $itemsOutstanding = $listOutstanding instanceof \Illuminate\Database\Eloquent\Collection
                ? $listOutstanding
                : [$listOutstanding];

            foreach ($itemsOutstanding as $o) {
                $sumOutstanding += (int)($o->net_sales ?? 0);
            }

            if ($sumKomponen === $sumOutstanding) {
                $totalRkmAkurat++;

                $date = \Carbon\Carbon::parse($rkm->created_at);
                $dateKey = $date->format('Y-m-d');
                $monthKey = $date->format('Y-m');

                if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                    $dailyBreakdownPerMonth[$monthKey] = [];
                }

                if (!isset($dailyBreakdownPerMonth[$monthKey][$dateKey])) {
                    $dailyBreakdownPerMonth[$monthKey][$dateKey] = 0;
                }

                $dailyBreakdownPerMonth[$monthKey][$dateKey] += 1;

                if (!isset($monthlyTotals[$monthKey])) {
                    $monthlyTotals[$monthKey] = 0;
                }

                $monthlyTotals[$monthKey] += 1;
            }
        }

        if ($totalRkmDenganPerhitungan > 0) {
            $progress = ($totalRkmAkurat / $totalRkmDenganPerhitungan) * 100;
        } else {
            $progress = 0;
        }

        ksort($monthlyTotals);
        ksort($dailyBreakdownPerMonth);

        $monthlyProgress = [];
        foreach ($monthlyTotals as $month => $value) {
            $monthlyProgress[$month] = $nilaiTarget > 0
                ? round(($value / $nilaiTarget) * 100, 1)
                : 0;
        }

        $dailyProgressPerMonth = [];
        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $date => $value) {
                $dailyProgressPerMonth[$month][$date] = $nilaiTarget > 0
                    ? round(($value / $nilaiTarget) * 100, 1)
                    : 0;
            }
        }

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $pieChart = [
            'above' => $totalRkmAkurat,
            'below' => max(0, $totalRkmDenganPerhitungan - $totalRkmAkurat),
        ];

        return [
            'progress' => round($progress, 1),
            'gap' => $gap,
            'pie_chart' => $pieChart,
            'monthly_data' => $monthlyTotals,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculateTodoAdministrasiDetail($itemDetail)
    {
        $details = $itemDetail->detailTargetKPI;

        $tahun = (int) optional($details->first())->detail_jangka;
        $nilaiTarget = (float) optional($details->first())->nilai_target;

        $default = [
            'progress' => 0,
            'gap' => 0,
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'monthly_progress' => [],
            'daily_progress_per_month' => [],
        ];

        if ($details->isEmpty() || $tahun < 2000 || $tahun > now()->year + 5) {
            return $default;
        }

        $todos = TodoAdministrasi::whereYear('created_at', $tahun)->get();

        if ($todos->isEmpty()) {
            return $default;
        }

        $totalData = $todos->count();

        $totalDone = $todos->where('status', 'selesai')
            ->whereNotNull('solusi')
            ->count();

        $totalNotDone = $totalData - $totalDone;

        $progress = $totalData > 0 ? ($totalDone / $totalData) * 100 : 0;
        $progress = round($progress, 1);

        $dailyBreakdownPerMonth = [];
        $monthlyDataTemp = [];

        foreach ($todos as $todo) {
            $date = \Carbon\Carbon::parse($todo->created_at);
            $dateKey = $date->format('Y-m-d');
            $monthKey = $date->format('Y-m');

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }

            if (!isset($dailyBreakdownPerMonth[$monthKey][$dateKey])) {
                $dailyBreakdownPerMonth[$monthKey][$dateKey] = 0;
            }

            $dailyBreakdownPerMonth[$monthKey][$dateKey]++;

            if (!isset($monthlyDataTemp[$monthKey])) {
                $monthlyDataTemp[$monthKey] = [];
            }

            $monthlyDataTemp[$monthKey][] = $dailyBreakdownPerMonth[$monthKey][$dateKey];
        }

        $monthlyData = [];
        foreach ($monthlyDataTemp as $month => $values) {
            $monthlyData[$month] = round(array_sum($values) / count($values), 1);
        }

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);

        $monthlyProgress = [];
        foreach ($monthlyData as $month => $value) {
            $monthlyProgress[$month] = $nilaiTarget > 0
                ? round(($value / $nilaiTarget) * 100, 1)
                : 0;
        }

        $dailyProgressPerMonth = [];
        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $date => $value) {
                $dailyProgressPerMonth[$month][$date] = $nilaiTarget > 0
                    ? round(($value / $nilaiTarget) * 100, 1)
                    : 0;
            }
        }

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $pieChart = [
            'above' => $totalDone,
            'below' => $totalNotDone,
        ];

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => $pieChart,
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    private function calculateTodoAdministrasi($item)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail) {
            return 0.0;
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return 0.0;
        }

        $momCount = TodoAdministrasi::whereYear('created_at', $tahun)->count();

        if ($momCount == 0) {
            return 0;
        }

        $momDone = TodoAdministrasi::whereYear('created_at', $tahun)
            ->where('status', 'selesai')
            ->whereNotNull('solusi')
            ->count();

        $progress = ($momDone / $momCount) * 100;

        return round($progress, 1);
    }

    //All Sales
    private function calculatePeningkatanKemampuanKompetensiSalesDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;
        $nilaiUkur = 90;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $karyawanJabatan = Karyawan::where('divisi', 'Sales & Marketing')
            ->where('status_aktif', '1')
            ->whereNotIn('jabatan', ['Tim Digital', 'GM'])
            ->pluck('jabatan')
            ->map(fn($jabatan) => strtolower(trim($jabatan)))
            ->unique()
            ->toArray();

        $userQuery = User::whereHas('karyawan', function ($query) use ($personId, $karyawanJabatan) {
            $query->where('divisi', 'Sales & Marketing')
                ->whereIn('jabatan', $karyawanJabatan);

            if ($personId !== null) {
                $query->where('id', $personId);
            }
        });

        $salesUsernames = $userQuery->pluck('username')
            ->filter()
            ->map(fn($username) => strtolower(trim($username)))
            ->toArray();

        if (empty($salesUsernames)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        try {
            $apiUrl = env('MOODLE_API_URL');
            $apiUsername = env('MOODLE_API_USERNAME');
            $apiPassword = env('MOODLE_API_PASSWORD');

            $response = Http::withBasicAuth($apiUsername, $apiPassword)
                ->timeout(15)
                ->get($apiUrl);

            $moodleRaw = $response->successful() ? $response->json() : [];
        } catch (Exception $e) {
            $moodleRaw = [];
        }

        if (empty($moodleRaw['data']) || !is_array($moodleRaw['data'])) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ];
        }

        $totalPenilaian = 0;
        $totalMelebihiNilaiUkur = 0;
        $dailyBreakdownPerMonth = [];
        $monthlyDataTemp = [];

        $moodleDataValid = array_values($moodleRaw['data']);
        $moodleDataCount = count($moodleDataValid);

        for ($i = 0; $i < $moodleDataCount; $i++) {
            $data = $moodleDataValid[$i];
            if (!isset($data['username']))
                continue;

            $moodleUsername = strtolower(trim($data['username']));
            $dateString = $data['activity_submitted_at'] ?? $data['activity_created_at'] ?? null;

            if (in_array($moodleUsername, $salesUsernames) && $dateString) {
                $date = Carbon::parse($dateString);

                if ($date->year === $tahun) {
                    $totalPenilaian++;
                    $score = (float) ($data['score'] ?? 0);

                    $dateKey = $date->format('Y-m-d');
                    $monthKey = $date->format('Y-m');

                    if ($score > $nilaiUkur) {
                        $totalMelebihiNilaiUkur++;

                        if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                            $dailyBreakdownPerMonth[$monthKey] = [];
                        }
                        $dailyBreakdownPerMonth[$monthKey][$dateKey] = ($dailyBreakdownPerMonth[$monthKey][$dateKey] ?? 0) + 1;
                        $monthlyDataTemp[$monthKey] = ($monthlyDataTemp[$monthKey] ?? 0) + 1;
                    }
                }
            }
        }

        $progress = 0;
        if ($totalPenilaian > 0) {
            $progress = round(($totalMelebihiNilaiUkur / $totalPenilaian) * 100, 1);
        }

        $monthlyData = [];
        foreach ($monthlyDataTemp as $month => $total) {
            $monthlyData[$month] = round($total, 1);
        }

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);

        $monthlyProgress = [];
        foreach ($monthlyData as $month => $value) {
            $monthlyProgress[$month] = $nilaiTarget > 0
                ? round(($value / $nilaiTarget) * 100, 1)
                : 0;
        }

        $dailyProgressPerMonth = [];
        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $date => $value) {
                $dailyProgressPerMonth[$month][$date] = $nilaiTarget > 0
                    ? round(($value / $nilaiTarget) * 100, 1)
                    : 0;
            }
        }

        $gap = 0;
        if ($progress <= $nilaiTarget) {
            $gapRaw = $progress - $nilaiTarget;
            $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
        }

        $countAbove = $totalMelebihiNilaiUkur;
        $countBelow = $totalPenilaian - $totalMelebihiNilaiUkur;

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $countAbove,
                'below' => $countBelow
            ],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
            'monthly_progress' => $monthlyProgress,
            'daily_progress_per_month' => $dailyProgressPerMonth,
        ];
    }

    public function updateTargetPerSales(Request $request)
    {
        $request->validate([
            'id_detailPerson' => 'required',
            'presentase_kemampuan' => 'required|numeric|min:0',
        ]);

        $detailPerson = DetailPersonKPI::findOrFail($request->id_detailPerson);

        $detailPerson->presentase_kemampuan = $request->presentase_kemampuan;
        $detailPerson->save();

        $revenue = RKM::where('status', '0')
            ->whereYear('tanggal_awal', date('Y'))
            ->where('sales_key', $request->kode_karyawan)
            ->select(DB::raw('SUM(CAST(harga_jual AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total'))
            ->value('total') ?? 0;

        $target = (float) $request->presentase_kemampuan;
        $percentage = $target > 0 ? ($revenue / $target) * 100 : 0;
        $status = $revenue >= $target ? 'achieved' : 'pending';

        return response()->json([
            'success' => true,
            'message' => 'Target updated successfully',
            'data' => [
                'percentage' => round($percentage, 1),
                'status' => $status,
                'revenue' => round($revenue, 1),
                'target' => round($target, 1)
            ]
        ]);
    }

    //Overview KPI
    public function personalIndex($id = null)
    {
        $targetId = $id ?? auth()->user()->id;
        return view('KPIdata.TargetSubDivisi.overviewKaryawan', compact('targetId'));
    }

    public function getDataOverviewPersonal(Request $request)
    {
        try {
            $karyawanId  = $request->id_karyawan ?? auth()->id();
            $tahunFilter = $request->tahun ?? now()->year;

            $karyawan = karyawan::find($karyawanId);
            if (!$karyawan) {
                return response()->json(['success' => false, 'message' => 'Data karyawan tidak ditemukan'], 404);
            }

            $allTargets = targetKPI::with([
                'karyawan',
                'detailTargetKPI.detailPersonKPI.karyawan',
                'detailTargetKPI.dataTarget'
            ])
                ->whereYear('created_at', $tahunFilter)
                ->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($karyawanId) {
                    $q->where('id_karyawan', $karyawanId);
                })
                ->get();

            $processedTargets = collect();
            $currentYear = now()->year; 

            foreach ($allTargets as $target) {
                foreach ($target->detailTargetKPI as $detail) {

                    $isAssigned = $detail->detailPersonKPI
                        ->where('id_karyawan', $karyawanId)
                        ->isNotEmpty();

                    if (!$isAssigned) continue;

                    $nilaiTarget = $detail->dataTarget?->nilai_target ?? $detail->nilai_target;
                    $tipeTarget  = $detail->tipe_target;

                    $progress = $this->resolveProgress($target, $karyawanId);
                    if ($progress === null) continue;

                    $percent = $nilaiTarget > 0
                        ? round(($progress / $nilaiTarget) * 100, 2)
                        : 0;
                    $percent = max(0, min(100, $percent));

                    if ($tahunFilter < $currentYear) {
                        $status = $percent >= 100 ? 'Selesai' : 'Gagal';
                    } elseif ($tahunFilter == $currentYear) {
                        $status = 'Sedang Berjalan';
                    } else {
                        $status = 'Belum Mulai';
                    }

                    $statusBadge = match ($status) {
                        'Selesai'         => 'bg-success',
                        'Gagal'           => 'bg-dark',
                        'Sedang Berjalan' => 'bg-primary',
                        default           => 'bg-secondary',
                    };

                    $progressDisplay = match (true) {
                        $tipeTarget === 'rupiah' => 'Rp ' . number_format($progress, 0, ',', '.'),
                        $tipeTarget === 'persen' => round($progress, 2) . '%',
                        default                  => number_format($progress, 0, ',', '.'),
                    };

                    $processedTargets->push([
                        'id'              => $target->id,
                        'judul'           => $target->judul,
                        'asistant_route'  => $detail->dataTarget->asistant_route,
                        'periode'         => $detail->jangka_target . ' ' . $detail->detail_jangka,
                        'tipe_target'     => $tipeTarget,
                        'target'          => $nilaiTarget,
                        'progress'        => round($progress),
                        'progress_display' => $progressDisplay,
                        'progress_percent' => $percent,
                        'status'          => $status,
                        'status_badge'    => $statusBadge,
                        'deskripsi'       => $detail->deskripsi ?? '-',
                        'manual_value'    => $detail->manual_value,
                        'created_at'      => $target->created_at->format('d M Y'),
                    ]);
                }
            }

            // FIX Bug 2: Semua target ikut dihitung, termasuk yang 0%
            $progressPercentages = $processedTargets->pluck('progress_percent')->filter(fn($v) => $v !== null);
            $rataRataProgress = $progressPercentages->isNotEmpty()
                ? round($progressPercentages->sum() / $progressPercentages->count(), 2)
                : 0;

            return response()->json([
                'success'      => true,
                'user_info'    => [
                    'nama'    => $karyawan->nama_lengkap ?? '-',
                    'jabatan' => $karyawan->jabatan ?? '-',
                    'divisi'  => $karyawan->divisi ?? '-',
                ],
                'total_target'       => $processedTargets->count(),
                'rata_rata_progress' => $rataRataProgress,
                // kpi_aktif sekarang menghitung status 'Sedang Berjalan'
                'kpi_aktif'          => $processedTargets->where('status', 'Sedang Berjalan')->count(),
                'kpi_selesai'        => $processedTargets->where('status', 'Selesai')->count(),
                'statistik_per_target' => $processedTargets->map(fn($t) => [
                    'judul'      => $t['judul'],
                    'periode'    => $t['periode'],
                    'tipe_target' => $t['tipe_target'],
                    'target'     => $t['target'],
                    'progress'   => $t['progress'],
                    'status'     => $t['status'],
                ])->values(),
                'distribusi_status' => [
                    'Selesai'         => $processedTargets->where('status', 'Selesai')->count(),
                    'Gagal'           => $processedTargets->where('status', 'Gagal')->count(),
                    'Sedang Berjalan' => $processedTargets->where('status', 'Sedang Berjalan')->count(),
                    'Belum Mulai'     => $processedTargets->where('status', 'Belum Mulai')->count(),
                ],
                'daftar_target_pribadi' => $processedTargets->values(),
                'tahun'                 => $tahunFilter,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function kpiOverview()
    {
        $userId = Auth()->id();

        $userKaryawan = karyawan::where('id', $userId)->first();
        $divisi = $userKaryawan->divisi ?? null;
        $jabatan = $userKaryawan->jabatan ?? null;

        $departments = karyawan::where('divisi', '!=', 'Direksi')->whereNotNull('divisi')->distinct()->pluck('divisi')->values();

        return view('KPIdata.TargetDivisi.overview', compact('departments', 'divisi', 'jabatan'));
    }

    public function getDataOverview(Request $request)
    {
        $divisiFilter = $request->divisi;
        $tahunFilter = $request->tahun;

        if (!$divisiFilter || !$tahunFilter) {
            return response()->json(['message' => 'Divisi dan tahun harus diisi'], 400);
        }

        $currentYear = now()->year;

        $karyawanDiDivisi = Karyawan::where('divisi', $divisiFilter)
            ->where('status_aktif', '1')
            ->where('jabatan', '!=', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->where('jabatan', '!=', 'Pilih Jabatan')
            ->whereNotNull('nip')
            ->where('divisi', '!=', 'Direksi')
            ->get();

        $karyawanIds = $karyawanDiDivisi->pluck('id')->toArray();

        $allTargets = DetailTargetKPI::with([
            'targetKPI',
            'dataTarget',
            'detailPersonKPI.karyawan'
        ])
            ->whereYear('created_at', $tahunFilter)
            ->whereHas('detailPersonKPI.karyawan', function ($q) use ($karyawanIds) {
                $q->whereIn('id', $karyawanIds);
            })
            ->get();

        $daftarTargetKPI = [];
        $employeeProgressMap = [];
        $employeeTargetStatusMap = [];
        $employeeTargetsMap = [];
        $processedTargets = [];

        $distribusi = [
            'Sangat Baik' => 0,
            'Baik' => 0,
            'Cukup' => 0,
            'Kurang' => 0,
            'Sangat Kurang' => 0
        ];

        foreach ($allTargets as $detail) {
            $target = $detail->targetKPI;
            if (!$target) continue;

            $nilaiTarget = $detail->dataTarget?->nilai_target ?? $detail->nilai_target;
            $tipeTarget = $detail->tipe_target;

            $assignedPersons = $detail->detailPersonKPI
                ->whereIn('id_karyawan', $karyawanIds)
                ->groupBy('id_karyawan');

            if ($assignedPersons->isEmpty()) continue;

            $targetProgressPercentages = collect();

            foreach ($assignedPersons as $personId => $assignments) {
                $uniqueKey = $detail->id . '_' . $personId;
                if (isset($processedTargets[$uniqueKey])) {
                    continue;
                }
                $processedTargets[$uniqueKey] = true;

                if (!isset($employeeProgressMap[$personId])) {
                    $employeeProgressMap[$personId] = [];
                    $employeeTargetStatusMap[$personId] = [
                        'Sedang Berjalan' => 0,
                        'Selesai' => 0,
                        'Gagal' => 0,
                        'Belum Mulai' => 0
                    ];
                }

                $rawProgress = $this->resolveProgress($target, $personId);
                if ($rawProgress === null) {
                    continue;
                }

                $percent = max(0, min(100, $rawProgress));

                // Progress display
                $progressDisplay = match (true) {
                    $tipeTarget === 'rupiah' => 'Rp ' . number_format($rawProgress, 0, ',', '.'),
                    $tipeTarget === 'persen' => round($rawProgress, 2) . '%',
                    default => number_format($rawProgress, 0, ',', '.'),
                };

                // Status per karyawan berdasarkan tahun
                if ($tahunFilter < $currentYear) {
                    $statusTarget = $percent >= 100 ? 'Selesai' : 'Gagal';
                } elseif ($tahunFilter == $currentYear) {
                    $statusTarget = 'Sedang Berjalan';
                } else {
                    $statusTarget = 'Belum Mulai';
                }

                $employeeTargetStatusMap[$personId][$statusTarget]++;

                $statusBadge = match ($statusTarget) {
                    'Selesai' => 'bg-success',
                    'Gagal' => 'bg-dark',
                    'Sedang Berjalan' => 'bg-primary',
                    default => 'bg-secondary',
                };

                // Simpan target per karyawan
                if (!isset($employeeTargetsMap[$personId])) {
                    $employeeTargetsMap[$personId] = [];
                }

                $employeeTargetsMap[$personId][] = [
                    'judul' => $target->judul,
                    'periode' => $detail->jangka_target . ' ' . $detail->detail_jangka,
                    'tipe_target' => $tipeTarget,
                    'target' => $nilaiTarget,
                    'progress' => round($rawProgress),
                    'progress_display' => $progressDisplay,
                    'progress_percent' => $percent,
                    'status' => $statusTarget,
                    'status_badge' => $statusBadge,
                ];

                $targetProgressPercentages->push($percent);
            }

            // Rata-rata target di level divisi
            $avgTarget = $targetProgressPercentages->isNotEmpty()
                ? round($targetProgressPercentages->avg(), 2)
                : 0;

            // Status untuk overview target divisi
            if ($tahunFilter < $currentYear) {
                $status = $avgTarget >= 100 ? 'Selesai' : 'Gagal';
            } elseif ($tahunFilter == $currentYear) {
                $status = 'Sedang Berjalan';
            } else {
                $status = 'Belum Mulai';
            }

            if ($avgTarget > 0) {
                if ($avgTarget >= 100) {
                    $distribusi['Sangat Baik']++;
                } elseif ($avgTarget >= 80) {
                    $distribusi['Baik']++;
                } elseif ($avgTarget >= 70) {
                    $distribusi['Cukup']++;
                } elseif ($avgTarget >= 60) {
                    $distribusi['Kurang']++;
                } else {
                    $distribusi['Sangat Kurang']++;
                }
            }

            $daftarTargetKPI[] = [
                'judul' => $target->judul,
                'periode' => $detail->jangka_target . ' ' . $detail->detail_jangka,
                'target' => $nilaiTarget,
                'progress' => $avgTarget,
                'status' => $status,
            ];
        }

        // Rata-rata progress per karyawan
        $avgPerEmployee = [];
        foreach ($employeeProgressMap as $personId => $progressList) {
            if (!empty($progressList)) {
                $avgPerEmployee[$personId] = round(array_sum($progressList) / count($progressList), 2);
            }
        }

        $rataRataProgress = !empty($avgPerEmployee)
            ? round(array_sum($avgPerEmployee) / count($avgPerEmployee), 2)
            : 0;

        // Hitung KPI berdasarkan status
        $kpiAktif = collect($daftarTargetKPI)->where('status', 'Sedang Berjalan')->count();
        $kpiSelesai = collect($daftarTargetKPI)->where('status', 'Selesai')->count();
        $kpiGagal = collect($daftarTargetKPI)->where('status', 'Gagal')->count();

        $karyawanDepartemen = $karyawanDiDivisi->map(function ($karyawan) use (
            $employeeProgressMap,
            $employeeTargetStatusMap,
            $employeeTargetsMap
        ) {
            $progressList = $employeeProgressMap[$karyawan->id] ?? [];
            $statusData = $employeeTargetStatusMap[$karyawan->id] ?? [
                'Sedang Berjalan' => 0,
                'Selesai' => 0,
                'Gagal' => 0,
                'Belum Mulai' => 0
            ];

            $avgTarget = !empty($progressList)
                ? round(array_sum($progressList) / count($progressList), 2)
                : 0;

            return [
                'id_karyawan' => $karyawan->id,
                'nama' => $karyawan->nama_lengkap,
                'jabatan' => $karyawan->jabatan,
                'total_target_sedang_berjalan' => $statusData['Sedang Berjalan'],
                'total_target_selesai' => $statusData['Selesai'],
                'total_target_gagal' => $statusData['Gagal'],
                'total_target_belum_mulai' => $statusData['Belum Mulai'],
                'jumlah_target' => count($progressList),
                'rata_rata_progress' => $avgTarget,
                'daftar_target_pribadi' => $employeeTargetsMap[$karyawan->id] ?? [],
            ];
        })->values();

        return response()->json([
            'total_target' => count($daftarTargetKPI),
            'rata_rata_progress' => $rataRataProgress,
            'kpi_aktif' => $kpiAktif,
            'kpi_selesai' => $kpiSelesai,
            'kpi_gagal' => $kpiGagal,
            'karyawan_departemen' => $karyawanDepartemen,
            'statistik_karyawan' => $this->getEmployeeStatistics(
                $karyawanIds,
                $employeeProgressMap,
                $employeeTargetStatusMap,
                $employeeTargetsMap
            ),
            'distribusi_nilai' => $distribusi,
            'daftar_target_kpi' => collect($daftarTargetKPI)->unique('judul')->values()
        ]);
    }

    private function getEmployeeStatistics($karyawanIds, $employeeProgressMap, $employeeTargetStatusMap, $employeeTargetsMap = [])
    {
        return karyawan::whereIn('id', $karyawanIds)->get()->map(function ($karyawan) use ($employeeProgressMap, $employeeTargetStatusMap, $employeeTargetsMap) {
            $progressList = $employeeProgressMap[$karyawan->id] ?? [];
            
            $statusData = $employeeTargetStatusMap[$karyawan->id] ?? [
                'Sedang Berjalan' => 0, 
                'Selesai' => 0, 
                'Gagal' => 0, 
                'Belum Mulai' => 0
            ];

            $totalTarget = count($progressList);
            $rataRataProgress = !empty($progressList)
                ? round(array_sum($progressList) / count($progressList), 2)
                : 0;

            return [
                'nama' => explode(' ', $karyawan->nama_lengkap)[0],
                'jabatan' => $karyawan->jabatan,
                'total_target' => $totalTarget,
                'target_sedang_berjalan' => $statusData['Sedang Berjalan'],
                'target_selesai'         => $statusData['Selesai'],
                'target_gagal'           => $statusData['Gagal'],
                'target_belum_mulai'     => $statusData['Belum Mulai'],
                'rata_rata_progress' => $rataRataProgress,
                'daftar_target_pribadi' => $employeeTargetsMap[$karyawan->id] ?? [],
            ];
        })->values();
    }

    private function normalizeNumber($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return (float) str_replace(',', '', $value);
    }

    private function hitungJamKerja($startAt, $endAt)
    {
        $start = $startAt->copy();
        $end = $endAt->copy();

        $workStart = 8;
        $workEnd = 17;

        $totalMinutes = 0;

        while ($start->lt($end)) {

            // jika weekend skip
            if ($start->isWeekend()) {
                $start->addDay()->startOfDay();
                continue;
            }

            $dayWorkStart = $start->copy()->setHour($workStart)->setMinute(0)->setSecond(0);
            $dayWorkEnd = $start->copy()->setHour($workEnd)->setMinute(0)->setSecond(0);

            $rangeStart = $start->greaterThan($dayWorkStart) ? $start : $dayWorkStart;
            $rangeEnd = $end->lessThan($dayWorkEnd) ? $end : $dayWorkEnd;

            if ($rangeStart->lt($rangeEnd)) {
                $totalMinutes += $rangeStart->diffInMinutes($rangeEnd);
            }

            $start->addDay()->startOfDay();
        }

        return $totalMinutes / 60;
    }

    private function formatTenggatWaktuExport(string $jangka, string $detail): string
    {
        $namaBulanId = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agt',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des',
        ];

        switch (strtolower($jangka)) {
            case 'tahunan':
                $year = (int) $detail;
                return "31 Des {$year}";

            case 'bulanan':
                $parts = explode('-', trim($detail));
                if (count($parts) === 2) {
                    [$year, $month] = $parts;
                    $lastDay  = date('t', mktime(0, 0, 0, (int)$month, 1, (int)$year));
                    return "{$lastDay} " . ($namaBulanId[(int)$month] ?? $month) . " {$year}";
                }
                return $detail;

            case 'kuartalan':
            case 'quartal':
            case 'quarter':
                if (preg_match('/(\d{4})\D?Q?(\d)/i', $detail, $m)) {
                    $year     = $m[1];
                    $quarter  = (int)$m[2];
                    $monthEnd = $quarter * 3;
                    $monthStart = $monthEnd - 2;
                    $lastDay  = date('t', mktime(0, 0, 0, $monthEnd, 1, (int)$year));
                    $bulanStart = $namaBulanId[$monthStart] ?? $monthStart;
                    $bulanEnd   = $namaBulanId[$monthEnd] ?? $monthEnd;
                    return "Q{$quarter} {$year} ({$bulanStart}-{$bulanEnd}) - {$lastDay} {$bulanEnd} {$year}";
                }
                return $detail;

            case 'mingguan':
                if (preg_match('/(\d{4})\D?W?(\d{1,2})/i', $detail, $m)) {
                    return "Minggu ke-{$m[2]}, {$m[1]}";
                }
                return $detail;
        }
        return $detail;
    }

    private function hitungStatusExport(float $progressPersen, float $nilaiTarget, string $tipe, float $progressRaw, string $tenggatWaktu): string
    {
        $isTargetReached = false;
        if ($tipe === 'rupiah') {
            $isTargetReached = $progressRaw >= $nilaiTarget;
        } elseif ($tipe === 'angka') {
            $isTargetReached = $progressRaw >= $nilaiTarget;
        } else {
            $isTargetReached = $progressPersen >= $nilaiTarget;
        }

        try {
            $deadline   = Carbon::parse($tenggatWaktu)->startOfDay();
            $now        = now()->startOfDay();
            $isOverdue  = $now->gt($deadline);
            $isSameYear = $now->year === $deadline->year;
        } catch (\Exception $e) {
            return 'Dalam Progress';
        }

        if (!$isOverdue && $isSameYear) {
            return $progressPersen <= 0 ? 'Belum Dimulai' : 'Dalam Progress';
        }

        if ($isOverdue) {
            return $isTargetReached ? 'Selesai' : 'Gagal';
        }

        return 'Dalam Progress';
    }

    private function getGradeLabel(float $nilai): string
    {
        if ($nilai >= 100) return 'Sangat Baik';
        if ($nilai >= 80) return 'Baik';
        if ($nilai >= 70) return 'Cukup';
        if ($nilai >= 60) return 'Kurang';
        return 'Sangat Kurang';
    }

    private function groupDataByQuarter(array $monthlyData, int $tahun): array
    {
        $quarters = [
            1 => ['months' => [1, 2, 3], 'label' => 'Q1', 'total' => 0, 'count' => 0],
            2 => ['months' => [4, 5, 6], 'label' => 'Q2', 'total' => 0, 'count' => 0],
            3 => ['months' => [7, 8, 9], 'label' => 'Q3', 'total' => 0, 'count' => 0],
            4 => ['months' => [10, 11, 12], 'label' => 'Q4', 'total' => 0, 'count' => 0],
        ];

        foreach ($monthlyData as $key => $value) {
            if (preg_match('/^\d{4}-(\d{2})$/', (string)$key, $m)) {
                $month = (int)$m[1];
            } elseif (is_numeric($key) && $key >= 1 && $key <= 12) {
                $month = (int)$key;
            } else {
                continue;
            }

            foreach ($quarters as $q => &$data) {
                if (in_array($month, $data['months'])) {
                    $data['total'] += (float)$value;
                    $data['count']++;
                    break;
                }
            }
        }

        $result = [];
        foreach ($quarters as $q => $data) {
            $quarterAvg = $data['count'] > 0 ? round($data['total'] / $data['count'], 2) : 0;
            $result[$q] = [
                'label'      => $data['label'],
                'periode'    => "{$tahun}-Q{$q}",
                'rata_rata'  => $quarterAvg,
                'total'      => $quarterAvg,
                'bulan_aktif' => $data['count'],
            ];
        }

        return $result;
    }

    private function formatNilaiTargetExport($nilaiTarget, string $tipe): string
    {
        if ($tipe === 'rupiah') {
            return 'Rp ' . number_format((float) $nilaiTarget, 0, ',', '.');
        }
        if ($tipe === 'persen' || $tipe === 'angka') {
            return number_format((float) $nilaiTarget, 0, ',', '.') . '%';
        }
        return number_format((float) $nilaiTarget, 0, ',', '.');
    }

    private function buildMonitoringData(int $karyawanId, int $tahun, array $filters = []): array
    {
        $karyawan = karyawan::where('id', $karyawanId)
            ->where('status_aktif', '1')
            ->whereNot('jabatan', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNotNull('nip')
            ->whereNot('divisi', 'Direksi')
            ->first();

        if (!$karyawan) return [];

        $allTargets = targetKPI::with(['karyawan', 'detailTargetKPI.detailPersonKPI'])
            ->whereYear('created_at', $tahun)
            ->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($karyawanId) {
                $q->where('id_karyawan', $karyawanId);
            })
            ->get();

        $namaBulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $tabelTarget = [];
        $hasRupiahTarget = false;

        foreach ($allTargets as $item) {
            $detail = $item->detailTargetKPI->first();
            if (!$detail) continue;

            $jangka      = $detail->jangka_target;
            $detailJangka = (string)($detail->detail_jangka ?? '');
            $tipe        = $detail->tipe_target;
            $nilaiTarget = (float)$detail->nilai_target;

            if ($tipe === 'rupiah' && $nilaiTarget > 0) {
                $hasRupiahTarget = true;
            }

            $tenggatWaktu = $this->formatTenggatWaktuExport($jangka, $detailJangka);

            $calc = $this->getCalculationByRoute($item, $karyawanId);

            if (!$calc || !isset($calc['progress'])) {
                $calc = ['progress' => 0, 'gap' => 0, 'pie_chart' => ['above' => 0, 'below' => 0], 'monthly_data' => [], 'daily_breakdown_per_month' => [], 'monthly_progress' => [], 'daily_progress_per_month' => []];
            }

            $progressRaw = (float)($calc['progress'] ?? 0);
            $progressPersen = $tipe === 'rupiah'
                ? round(($progressRaw / $nilaiTarget) * 100, 2)
                : round($progressRaw, 2);

            if ($tipe === 'rupiah') {
                $progressDisplay = 'Rp ' . number_format($progressRaw, 0, ',', '.');
            } else {
                $progressDisplay = $progressPersen . '%';
            }

            $lengthProgress = max(0, min($progressPersen, 100));

            $status = $this->hitungStatusExport($progressPersen, $nilaiTarget, $tipe, $progressRaw, $tenggatWaktu);

            if (!empty($filters['periode']) && $filters['periode'] !== 'all') {
                $jangkaLower = strtolower($jangka);
                $match = false;
                if ($filters['periode'] === 'tahunan' && $jangkaLower === 'tahunan') $match = true;
                if ($filters['periode'] === 'bulanan' && $jangkaLower === 'bulanan') $match = true;
                if ($filters['periode'] === 'kuartalan' && in_array($jangkaLower, ['kuartalan', 'quartal', 'quarter'])) $match = true;
                if (!$match) continue;
            }

            if (!empty($filters['quarter']) && !empty($filters['periode']) && $filters['periode'] === 'kuartalan') {
                if (preg_match('/(\d{4})\D?Q?(\d)/i', $detailJangka, $m)) {
                    if ((int)$m[2] !== (int)$filters['quarter']) continue;
                }
            }

            if (!empty($filters['tahun_filter']) && (int)$filters['tahun_filter'] !== $tahun) continue;

            $jabatanList = $item->detailTargetKPI->pluck('jabatan')->unique()->values()->toArray();
            if (count($jabatanList) === 1) {
                $jabatanDisplay = $jabatanList[0] ?? '-';
            } else {
                $jabatanDisplay = implode(', ', array_map(fn($j) => substr($j, 0, 4) . '...', $jabatanList));
            }

            $monthlyData = $calc['monthly_data'] ?? [];
            $monthlyProgress = $calc['monthly_progress'] ?? [];
            $monthlyDataPersen = [];

            if (!empty($monthlyProgress)) {
                foreach ($monthlyProgress as $key => $val) {
                    $monthlyDataPersen[$key] = round((float)$val, 2);
                }
            } elseif (!empty($monthlyData)) {
                foreach ($monthlyData as $key => $val) {
                    $val = (float)$val;
                    if ($tipe === 'rupiah' && $nilaiTarget > 0) {
                        $monthlyDataPersen[$key] = round(($val / $nilaiTarget) * 100, 2);
                    } else {
                        $monthlyDataPersen[$key] = round($val, 2);
                    }
                }
            }

            $tabelTarget[] = [
                'judul'            => $item->judul,
                'asistant_route'   => $item->asistant_route,
                'jangka_target'    => ucfirst($jangka),
                'detail_jangka'    => $detailJangka,
                'status'           => $status,
                'tipe_target'      => $tipe,
                'nilai_target'     => $nilaiTarget,
                'nilai_target_fmt' => $this->formatNilaiTargetExport($nilaiTarget, $tipe),
                'jabatan'          => $jabatanDisplay,
                'divisi'           => $item->detailTargetKPI->pluck('divisi')->unique()->filter()->implode(', ') ?: '-',
                'pembuat'          => $item->karyawan ? ($item->karyawan->nama_lengkap ?? '-') : '-',
                'progress_raw'     => $progressRaw,
                'progress_persen'  => $progressPersen,
                'length_progress'  => $lengthProgress,
                'progress_display' => $progressDisplay,
                'tenggat_waktu'    => $tenggatWaktu,
                'monthly_data'     => $monthlyDataPersen,
                'monthly_data_raw' => $monthlyData,
                'gap'              => $calc['gap'] ?? 0,
            ];
        }

        $allPersen = array_column($tabelTarget, 'progress_persen');
        $avgProgressAllKPI = count($allPersen) > 0 ? round(array_sum($allPersen) / count($allPersen), 2) : 0;

        $rekapPerBulan  = array_fill(1, 12, []);
        $rupiahPerBulan = array_fill(1, 12, 0);
        $nilaiTargetTahunan = 0;
        $targetPemasukanKotor = null;

        foreach ($tabelTarget as $t) {
            $tipe        = $t['tipe_target'];
            $nilaiTarget = $t['nilai_target'];
            $monthlyData = $t['monthly_data'];

            if ($t['asistant_route'] === 'Pemasukan Kotor') {
                $nilaiTargetTahunan   = $nilaiTarget;
                $targetPemasukanKotor = $t;
            }

            if (empty($monthlyData)) {
                continue;
            }

            foreach ($monthlyData as $monthKey => $nilai) {
                if (preg_match('/^\d{4}-(\d{2})$/', (string)$monthKey, $m)) {
                    $bulan = (int)$m[1];
                } elseif (is_numeric($monthKey) && $monthKey >= 1 && $monthKey <= 12) {
                    $bulan = (int)$monthKey;
                } else {
                    continue;
                }

                $nilai = (float)$nilai;
                $persen = round($nilai, 4);

                if ($tipe === 'rupiah') {
                    $rawMonthly = (float)($t['monthly_data_raw'][$monthKey] ?? 0);
                    $rupiahPerBulan[$bulan] += $rawMonthly;
                }

                $rekapPerBulan[$bulan][] = $persen;
            }
        }

        $rekapBulanan   = [];
        $analisaData    = [];
        $allMonthlyPersen = [];

        for ($b = 1; $b <= 12; $b++) {
            $persenList = $rekapPerBulan[$b];
            $avgPersen  = count($persenList) > 0
                ? round(array_sum($persenList) / count($persenList), 2)
                : 0;

            $allMonthlyPersen[] = $avgPersen;

            $status = $avgPersen > 0 ? 'In Progress' : '-';
            $grade = $this->getGradeLabel($avgPersen);

            $rekapBulanan[$b] = [
                'nama_bulan'     => $namaBulan[$b],
                'persen_capaian' => $avgPersen,
                'status'         => $status,
                'grade'          => $grade,
            ];

            $analisaData[] = [
                'target_tahunan' => $nilaiTargetTahunan,
                'actual_rupiah'  => $rupiahPerBulan[$b] ?? 0,
                'nama_bulan'     => $namaBulan[$b],
                'persen_bulan'   => $avgPersen,
                'kumulatif'      => 0,
                'grade'          => $grade,
            ];
        }

        $totalKumulatif = count($allMonthlyPersen) > 0
            ? round(array_sum($allMonthlyPersen) / count($allMonthlyPersen), 2)
            : 0;

        if ($totalKumulatif === 0 && $avgProgressAllKPI > 0) {
            $totalKumulatif = $avgProgressAllKPI;
        }

        $totalKumulatif    = round($totalKumulatif, 2);
        $totalActualRupiah = array_sum($rupiahPerBulan);
        $gradeAkhir        = $this->getGradeLabel($totalKumulatif);

        $allNilaiKPI = nilaiKPI::where('id_evaluated', $karyawanId)
            ->whereYear('created_at', $tahun)
            ->get();

        $persentaseJenis = [
            'General Manager'                           => 35,
            'Manager/SPV/Team Leader (Atasan Langsung)' => 30,
            'Rekan Kerja (Satu Divisi)'                 => 20,
            'Pekerja (Beda Divisi)'                     => 10,
            'Self Apprisial'                            => 5,
        ];

        $jenisTotalRaw = [];
        foreach ($persentaseJenis as $jenis => $bobot) {
            $nilaiForJenis = $allNilaiKPI->where('jenis_penilaian', $jenis)
                ->pluck('nilai')->filter(fn($n) => is_numeric($n));
            if ($nilaiForJenis->isNotEmpty()) {
                $jenisTotalRaw[$jenis] = ($nilaiForJenis->avg() * $bobot) / 100;
            }
        }
        $nilaiSoftskill = empty($jenisTotalRaw) ? 0 : round(array_sum($jenisTotalRaw), 2);

        $nilaiKPIxBobot       = round($totalKumulatif * 0.6, 2);
        $nilaiSoftskillxBobot = round($nilaiSoftskill * 0.4, 2);
        $totalAkhir           = round($nilaiKPIxBobot + $nilaiSoftskillxBobot, 2);
        $gradeTotalAkhir      = $this->getGradeLabel($totalAkhir);

        $statusCount = ['Selesai' => 0, 'Dalam Progress' => 0, 'Belum Dimulai' => 0, 'Gagal' => 0];
        foreach ($tabelTarget as $t) {
            $key = $t['status'];
            if (array_key_exists($key, $statusCount)) {
                $statusCount[$key]++;
            } else {
                $statusCount['Dalam Progress']++;
            }
        }

        $allMonthlyCombined = [];
        foreach ($tabelTarget as $t) {
            if (!empty($t['monthly_data'])) {
                foreach ($t['monthly_data'] as $key => $val) {
                    $allMonthlyCombined[$key] = ($allMonthlyCombined[$key] ?? 0) + (float)$val;
                }
            }
        }

        $rekapPerQuarter = $this->groupDataByQuarter($allMonthlyCombined, $tahun);

        $gradeDistribution = [
            'Sangat Baik' => 0,
            'Baik' => 0,
            'Cukup' => 0,
            'Kurang' => 0,
            'Sangat Kurang' => 0
        ];
        foreach ($tabelTarget as $t) {
            $grade = $this->getGradeLabel($t['progress_persen']);
            $gradeDistribution[$grade]++;
        }

        return [
            'karyawan'            => $karyawan,
            'tahun'               => $tahun,
            'tabel_target'        => $tabelTarget,
            'rekap_bulanan'       => array_values($rekapBulanan),
            'total_kumulatif'     => $totalKumulatif,
            'analisa_data'        => $analisaData,
            'total_actual_rupiah' => $totalActualRupiah,
            'nilai_target_tahunan' => $nilaiTargetTahunan,
            'rekap_per_quarter'   => $rekapPerQuarter,
            'grade_akhir'         => $gradeAkhir,
            'grade_total_akhir'   => $gradeTotalAkhir,
            'grade_distribution'  => $gradeDistribution,
            'filters_applied'     => $filters,
            'has_rupiah_target'   => $hasRupiahTarget,
            'avg_progress_all_kpi' => $avgProgressAllKPI,
            'penilaian' => [
                'nilai_softskill'       => $nilaiSoftskill,
                'total_capaian_kpi'     => $totalKumulatif,
                'kpi_x_bobot'           => $nilaiKPIxBobot,
                'softskill_x_bobot'     => $nilaiSoftskillxBobot,
                'total_akhir'           => $totalAkhir,
                'grade_total_akhir'     => $gradeTotalAkhir,
            ],
            'status_count' => $statusCount,
        ];
    }

    public function exportMonitoringPdf(Request $request)
    {
        try {
            $karyawanId = (int)($request->query('id_karyawan') ?? $request->input('id_karyawan') ?? Auth::id());
            $tahun      = (int)($request->query('tahun') ?? $request->input('tahun') ?? now()->year);

            $filters = [
                'periode'       => $request->query('periode', 'all'),
                'quarter'       => $request->query('quarter'),
                'tahun_filter'  => $request->query('tahun_filter'),
            ];

            $data = $this->buildMonitoringData($karyawanId, $tahun, $filters);
            if (empty($data)) {
                return back()->withErrors(['export' => 'Data karyawan tidak ditemukan.']);
            }

            $pdf = Pdf::loadView('KPIdata.export.export_pdf', $data)
                ->setPaper('a4', 'landscape')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled'      => false,
                    'defaultFont'          => 'DejaVu Sans',
                    'dpi'                  => 150,
                ]);

            $namaFile = 'KPI_' . str_replace(' ', '_', $data['karyawan']->nama_lengkap ?? 'unknown') . '_' . $tahun . '.pdf';
            return $pdf->download($namaFile);
        } catch (\Exception $e) {
            Log::error('Export PDF KPI error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withErrors(['export' => 'Gagal export PDF: ' . $e->getMessage()]);
        }
    }

    public function exportMonitoringExcel(Request $request)
    {
        try {
            $karyawanId = (int)($request->query('id_karyawan') ?? $request->input('id_karyawan') ?? Auth::id());
            $tahun      = (int)($request->query('tahun') ?? $request->input('tahun') ?? now()->year);

            $filters = [
                'periode'       => $request->query('periode', 'all'),
                'quarter'       => $request->query('quarter'),
                'tahun_filter'  => $request->query('tahun_filter'),
            ];

            $data = $this->buildMonitoringData($karyawanId, $tahun, $filters);
            if (empty($data)) {
                return back()->withErrors(['export' => 'Data karyawan tidak ditemukan.']);
            }

            $namaKaryawan = $data['karyawan']->nama_lengkap ?? '-';
            $jabatan      = $data['karyawan']->jabatan ?? '-';

            $C_HDR   = '2F5496';
            $C_SUB   = '8EA9DB';
            $C_ODD   = 'DCE6F1';
            $C_WH    = 'FFFFFF';
            $C_TOT   = 'D9E1F2';
            $C_GRN   = '70AD47';
            $C_YEL   = 'FFC000';
            $C_RED   = 'FF4444';
            $C_AMB   = 'D97706';
            $C_DRK   = '1F2937';
            $C_GRY   = '888888';

            $spreadsheet = new Spreadsheet();
            $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);

            $s1 = $spreadsheet->getActiveSheet()->setTitle('Daftar Target KPI');
            $colW1 = ['A' => 4, 'B' => 40, 'C' => 12, 'D' => 16, 'E' => 24, 'F' => 20, 'G' => 22, 'H' => 22, 'I' => 18, 'J' => 18];
            foreach ($colW1 as $col => $w) $s1->getColumnDimension($col)->setWidth($w);

            $r = 1;
            $s1->mergeCells("A{$r}:J{$r}");
            $s1->setCellValue("A{$r}", "DAFTAR TARGET KPI — {$namaKaryawan} ({$jabatan}) — Tahun {$tahun}");
            $this->xlStyle($s1, "A{$r}:J{$r}", $C_HDR, $C_WH, 13, true, 'center');
            $s1->getRowDimension($r)->setRowHeight(28);
            $r += 2;

            $headers1 = [
                'A' => 'No',
                'B' => 'Judul KPI',
                'C' => 'Jangka',
                'D' => 'Status',
                'E' => 'Target',
                'F' => 'Jabatan',
                'G' => 'Divisi',
                'H' => 'Pembuat',
                'I' => 'Progress',
                'J' => 'Tenggat'
            ];
            foreach ($headers1 as $col => $h) $s1->setCellValue("{$col}{$r}", $h);
            $this->xlStyle($s1, "A{$r}:J{$r}", $C_HDR, $C_WH, 10, true, 'center');
            $s1->getRowDimension($r)->setRowHeight(20);
            $r++;

            foreach ($data['tabel_target'] as $idx => $t) {
                $bg = ($idx % 2 === 0) ? $C_ODD : $C_WH;
                $sColor = match ($t['status']) {
                    'Selesai' => $C_GRN,
                    'Gagal' => $C_RED,
                    'Belum Dimulai' => $C_YEL,
                    default => $C_AMB,
                };

                $s1->setCellValue("A{$r}", $idx + 1);
                $s1->setCellValue("B{$r}", $t['judul']);
                $s1->setCellValue("C{$r}", $t['jangka_target']);
                $s1->setCellValue("D{$r}", $t['status']);
                $s1->setCellValue("E{$r}", $t['nilai_target_fmt']);
                $s1->setCellValue("F{$r}", $t['jabatan']);
                $s1->setCellValue("G{$r}", $t['divisi']);
                $s1->setCellValue("H{$r}", $t['pembuat']);
                $s1->setCellValue("I{$r}", $t['progress_display']);
                $s1->setCellValue("J{$r}", $t['tenggat_waktu']);

                $this->xlStyle($s1, "A{$r}:J{$r}", $bg, $C_DRK, 10, false, 'center');
                $s1->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $s1->getStyle("F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $s1->getStyle("G{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $s1->getStyle("H{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $styleD = $s1->getStyle("D{$r}");
                $styleD->getFont()->getColor()->setARGB("FF{$sColor}");
                $styleD->getFont()->setBold(true);

                $styleI = $s1->getStyle("I{$r}");
                $styleI->getFont()->getColor()->setARGB("FF{$sColor}");
                $styleI->getFont()->setBold(true);

                $s1->getRowDimension($r)->setRowHeight(18);
                $r++;
            }

            $avgProgress = $data['avg_progress_all_kpi'];

            $s1->mergeCells("A{$r}:H{$r}");
            $s1->setCellValue("A{$r}", 'RATA-RATA PROGRESS SEMUA KPI');
            $s1->setCellValue("I{$r}", $avgProgress . '%');
            $s1->setCellValue("J{$r}", '-');
            $this->xlStyle($s1, "A{$r}:J{$r}", $C_TOT, $C_DRK, 10, true, 'center');
            $s1->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $chartStartRow = $r + 2;
            $s1->setCellValue("A{$chartStartRow}", 'ChartData');
            $this->xlHide($s1, "A{$chartStartRow}");
            foreach ($data['tabel_target'] as $ci => $t) {
                $cr = $chartStartRow + $ci + 1;
                $s1->setCellValue("B{$cr}", $t['judul']);
                $s1->setCellValue("C{$cr}", $t['progress_persen']);
                $this->xlHide($s1, "B{$cr}");
                $this->xlHide($s1, "C{$cr}");
            }
            $chartEnd = $chartStartRow + count($data['tabel_target']);
            $s1Title = $s1->getTitle();
            $nKPI = count($data['tabel_target']);

            if ($nKPI > 0) {
                $lblBar = [new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('String', "'{$s1Title}'!C{$chartStartRow}", null, 1)];
                $xBar = [new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('String', "'{$s1Title}'!\$B\$" . ($chartStartRow + 1) . ":\$B\${$chartEnd}", null, $nKPI)];
                $vBar = [new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('Number', "'{$s1Title}'!\$C\$" . ($chartStartRow + 1) . ":\$C\${$chartEnd}", null, $nKPI)];
                $serBar = new \PhpOffice\PhpSpreadsheet\Chart\DataSeries(\PhpOffice\PhpSpreadsheet\Chart\DataSeries::TYPE_BARCHART, \PhpOffice\PhpSpreadsheet\Chart\DataSeries::GROUPING_CLUSTERED, [0], $lblBar, $xBar, $vBar);
                $serBar->setPlotDirection(\PhpOffice\PhpSpreadsheet\Chart\DataSeries::DIRECTION_COL);
                $chartBar = new \PhpOffice\PhpSpreadsheet\Chart\Chart('chart_progress_kpi', new \PhpOffice\PhpSpreadsheet\Chart\Title('Progress per KPI (%)'), new \PhpOffice\PhpSpreadsheet\Chart\Legend(\PhpOffice\PhpSpreadsheet\Chart\Legend::POSITION_BOTTOM, null, false), new \PhpOffice\PhpSpreadsheet\Chart\PlotArea(null, [$serBar]), true, 0, null, null);
                $chartBar->setTopLeftPosition("A" . ($r + 2));
                $chartBar->setBottomRightPosition("J" . ($r + 22));
                $s1->addChart($chartBar);
            }

            $s2 = $spreadsheet->createSheet()->setTitle('Rekap & Analisa');
            $colW2 = ['A' => 4, 'B' => 18, 'C' => 20, 'D' => 16, 'E' => 22, 'F' => 22, 'G' => 18, 'H' => 18];
            foreach ($colW2 as $col => $w) $s2->getColumnDimension($col)->setWidth($w);
            $r2 = 1;

            $s2->mergeCells("A{$r2}:H{$r2}");
            $s2->setCellValue("A{$r2}", "REKAP & ANALISA KPI — {$namaKaryawan} — Tahun {$tahun}");
            $this->xlStyle($s2, "A{$r2}:H{$r2}", $C_HDR, $C_WH, 13, true, 'center');
            $s2->getRowDimension($r2)->setRowHeight(28);
            $r2 += 2;

            $s2->mergeCells("A{$r2}:E{$r2}");
            $s2->setCellValue("A{$r2}", 'REKAP BULANAN');
            $this->xlStyle($s2, "A{$r2}:E{$r2}", $C_SUB, $C_WH, 11, true, 'center');
            $r2++;
            foreach (['A' => 'No', 'B' => 'Bulan', 'C' => '% Capaian', 'D' => 'Status', 'E' => 'Grade'] as $col => $h) $s2->setCellValue("{$col}{$r2}", $h);
            $this->xlStyle($s2, "A{$r2}:E{$r2}", $C_HDR, $C_WH, 10, true, 'center');
            $r2++;

            foreach ($data['rekap_bulanan'] as $idx => $rekap) {
                $bg = ($idx % 2 === 0) ? $C_ODD : $C_WH;
                $persen = $rekap['persen_capaian'];
                $pColor = $persen >= 80 ? $C_GRN : ($persen >= 40 ? $C_YEL : ($persen > 0 ? $C_RED : $C_GRY));
                $gradeColor = match ($rekap['grade']) {
                    'Sangat Baik' => $C_GRN,
                    'Baik' => '4CAF50',
                    'Cukup' => $C_YEL,
                    'Kurang' => $C_AMB,
                    default => $C_RED
                };

                $s2->setCellValue("A{$r2}", $idx + 1);
                $s2->setCellValue("B{$r2}", $rekap['nama_bulan']);
                $s2->setCellValue("C{$r2}", $persen > 0 ? $persen . '%' : '-');
                $s2->setCellValue("D{$r2}", $rekap['status']);
                $s2->setCellValue("E{$r2}", $rekap['grade']);
                $this->xlStyle($s2, "A{$r2}:E{$r2}", $bg, $C_DRK, 10, false, 'center');
                $s2->getStyle("B{$r2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                if ($persen > 0) {
                    $styleC = $s2->getStyle("C{$r2}");
                    $styleC->getFont()->getColor()->setARGB("FF{$pColor}");
                    $styleC->getFont()->setBold(true);
                }
                $styleE = $s2->getStyle("E{$r2}");
                $styleE->getFont()->getColor()->setARGB("FF{$gradeColor}");
                $styleE->getFont()->setBold(true);
                $r2++;
            }

            $s2->mergeCells("A{$r2}:C{$r2}");
            $s2->setCellValue("A{$r2}", 'TOTAL');
            $s2->setCellValue("D{$r2}", $data['total_kumulatif'] . '%');
            $s2->setCellValue("E{$r2}", $this->getGradeLabel($data['total_kumulatif']));
            $this->xlStyle($s2, "A{$r2}:E{$r2}", $C_TOT, $C_DRK, 10, true, 'center');
            $styleETotal = $s2->getStyle("E{$r2}");
            $styleETotal->getFont()->setBold(true);
            $r2 += 2;

            $s2->mergeCells("A{$r2}:E{$r2}");
            $s2->setCellValue("A{$r2}", 'REKAP PER KUARTAL');
            $this->xlStyle($s2, "A{$r2}:E{$r2}", $C_SUB, $C_WH, 11, true, 'center');
            $r2++;
            foreach (['A' => 'No', 'B' => 'Kuartal', 'C' => 'Rata-rata %', 'D' => 'Total %', 'E' => 'Grade'] as $col => $h) $s2->setCellValue("{$col}{$r2}", $h);
            $this->xlStyle($s2, "A{$r2}:E{$r2}", $C_HDR, $C_WH, 10, true, 'center');
            $r2++;

            foreach ($data['rekap_per_quarter'] as $idx => $q) {
                $bg = ($idx % 2 === 0) ? $C_ODD : $C_WH;
                $gradeColor = match ($this->getGradeLabel($q['rata_rata'])) {
                    'Sangat Baik' => $C_GRN,
                    'Baik' => '4CAF50',
                    'Cukup' => $C_YEL,
                    'Kurang' => $C_AMB,
                    default => $C_RED
                };
                $s2->setCellValue("A{$r2}", $idx + 1);
                $s2->setCellValue("B{$r2}", $q['label'] . ' - ' . $q['periode']);
                $s2->setCellValue("C{$r2}", $q['rata_rata'] . '%');
                $s2->setCellValue("D{$r2}", $q['total'] . '%');
                $s2->setCellValue("E{$r2}", $this->getGradeLabel($q['rata_rata']));
                $this->xlStyle($s2, "A{$r2}:E{$r2}", $bg, $C_DRK, 10, false, 'center');
                $styleEQ = $s2->getStyle("E{$r2}");
                $styleEQ->getFont()->getColor()->setARGB("FF{$gradeColor}");
                $styleEQ->getFont()->setBold(true);
                $r2++;
            }
            $r2 += 2;

            $s2->mergeCells("A{$r2}:D{$r2}");
            $s2->setCellValue("A{$r2}", 'INDIKATOR KEBERHASILAN');
            $this->xlStyle($s2, "A{$r2}:D{$r2}", $C_SUB, $C_WH, 11, true, 'center');
            $r2++;
            foreach (['A' => 'Kategori', 'B' => 'Keterangan', 'C' => 'Bobot', 'D' => ''] as $col => $h) $s2->setCellValue("{$col}{$r2}", $h);
            $this->xlStyle($s2, "A{$r2}:C{$r2}", $C_HDR, $C_WH, 10, true, 'center');
            $r2++;
            foreach ([[$C_ODD, 'Softskill/360', 'Penilaian 360 (softskill)', '40%'], [$C_WH, 'KPI', 'Total pencapaian KPI', '60%'], [$C_TOT, 'TOTAL', '', '100%']] as [$bg, $kat, $ket, $bobot]) {
                $s2->setCellValue("A{$r2}", $kat);
                $s2->setCellValue("B{$r2}", $ket);
                $s2->setCellValue("C{$r2}", $bobot);
                $this->xlStyle($s2, "A{$r2}:C{$r2}", $bg, $C_DRK, 10, $bg === $C_TOT, 'center');
                $s2->getStyle("B{$r2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $r2++;
            }
            $r2++;

            if ($data['has_rupiah_target']) {
                $s2->mergeCells("A{$r2}:D{$r2}");
                $s2->setCellValue("A{$r2}", "LAPORAN RUPIAH (TAHUN {$tahun})");
                $this->xlStyle($s2, "A{$r2}:D{$r2}", $C_SUB, $C_WH, 11, true, 'center');
                $r2++;
                foreach (['A' => 'Actual Per Bulan', 'B' => 'Bulan', 'C' => '% Capaian', 'D' => '% Kumulatif'] as $col => $h) $s2->setCellValue("{$col}{$r2}", $h);
                $this->xlStyle($s2, "A{$r2}:D{$r2}", $C_HDR, $C_WH, 10, true, 'center');
                $s2->getRowDimension($r2)->setRowHeight(18);
                $r2++;

                foreach ($data['analisa_data'] as $idx => $analisa) {
                    $bg = ($idx % 2 === 0) ? $C_ODD : $C_WH;
                    $actualDisp = $analisa['actual_rupiah'] > 0 ? 'Rp ' . number_format($analisa['actual_rupiah'], 0, ',', '.') : '-';

                    $s2->setCellValue("A{$r2}", $actualDisp);
                    $s2->setCellValue("B{$r2}", $analisa['nama_bulan']);
                    $s2->setCellValue("C{$r2}", $analisa['persen_bulan'] > 0 ? $analisa['persen_bulan'] . '%' : '-');
                    $s2->setCellValue("D{$r2}", $analisa['kumulatif'] > 0 ? $analisa['kumulatif'] . '%' : '-');
                    $this->xlStyle($s2, "A{$r2}:D{$r2}", $bg, $C_DRK, 10, false, 'center');
                    $s2->getStyle("A{$r2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $s2->getStyle("B{$r2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $r2++;
                }

                $totalActDisp = $data['total_actual_rupiah'] > 0 ? 'Rp ' . number_format($data['total_actual_rupiah'], 0, ',', '.') : '-';
                $s2->setCellValue("A{$r2}", $totalActDisp);
                $s2->setCellValue("B{$r2}", '-');
                $s2->setCellValue("C{$r2}", '-');
                $s2->setCellValue("D{$r2}", $data['total_kumulatif'] . '%');
                $this->xlStyle($s2, "A{$r2}:D{$r2}", $C_TOT, $C_DRK, 10, true, 'center');
                $s2->getStyle("A{$r2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $r2 += 2;
            }

            $s2->mergeCells("A{$r2}:D{$r2}");
            $s2->setCellValue("A{$r2}", 'TABEL PENILAIAN AKHIR');
            $this->xlStyle($s2, "A{$r2}:D{$r2}", $C_SUB, $C_WH, 11, true, 'center');
            $r2++;
            foreach (['A' => 'Kategori', 'B' => 'Total Capaian (Actual)', 'C' => 'Capaian × Bobot', 'D' => 'Grade'] as $col => $h) $s2->setCellValue("{$col}{$r2}", $h);
            $this->xlStyle($s2, "A{$r2}:D{$r2}", $C_HDR, $C_WH, 10, true, 'center');
            $r2++;

            $p = $data['penilaian'];
            foreach (
                [
                    [$C_ODD, 'Softskill/360', $p['nilai_softskill'] > 0 ? $p['nilai_softskill'] . '%' : '-', $p['softskill_x_bobot'], $this->getGradeLabel($p['nilai_softskill'])],
                    [$C_WH, 'KPI', $p['total_capaian_kpi'] . '%', $p['kpi_x_bobot'], $this->getGradeLabel($p['total_capaian_kpi'])],
                    [$C_TOT, 'TOTAL', '100%', $p['total_akhir'], $p['grade_total_akhir']]
                ] as [$bg, $kat, $act, $xb, $grade]
            ) {
                $s2->setCellValue("A{$r2}", $kat);
                $s2->setCellValue("B{$r2}", $act);
                $s2->setCellValue("C{$r2}", $xb);
                $s2->setCellValue("D{$r2}", $grade);
                $this->xlStyle($s2, "A{$r2}:D{$r2}", $bg, $C_DRK, 10, $bg === $C_TOT, 'center');
                $styleDFinal = $s2->getStyle("D{$r2}");
                $styleDFinal->getFont()->setBold(true);
                $r2++;
            }

            $chartDataRow = $r2 + 2;
            $this->xlHide($s2, "A{$chartDataRow}");
            foreach ($data['rekap_bulanan'] as $ci => $rekap) {
                $cr = $chartDataRow + $ci + 1;
                $s2->setCellValue("B{$cr}", $rekap['nama_bulan']);
                $s2->setCellValue("C{$cr}", $rekap['persen_capaian']);
                $s2->setCellValue("D{$cr}", $data['analisa_data'][$ci]['kumulatif'] ?? 0);
                $this->xlHide($s2, "B{$cr}");
                $this->xlHide($s2, "C{$cr}");
                $this->xlHide($s2, "D{$cr}");
            }
            $chartDataEnd = $chartDataRow + 12;
            $s2Title = $s2->getTitle();
            $xLine = [new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('String', "'{$s2Title}'!\$B\$" . ($chartDataRow + 1) . ":\$B\${$chartDataEnd}", null, 12)];
            $lbl1 = [new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('String', "'{$s2Title}'!C{$chartDataRow}", null, 1)];
            $lbl2 = [new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('String', "'{$s2Title}'!D{$chartDataRow}", null, 1)];
            $v1 = [new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('Number', "'{$s2Title}'!\$C\$" . ($chartDataRow + 1) . ":\$C\${$chartDataEnd}", null, 12)];
            $v2 = [new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('Number', "'{$s2Title}'!\$D\$" . ($chartDataRow + 1) . ":\$D\${$chartDataEnd}", null, 12)];
            $serLine = new \PhpOffice\PhpSpreadsheet\Chart\DataSeries(\PhpOffice\PhpSpreadsheet\Chart\DataSeries::TYPE_LINECHART, \PhpOffice\PhpSpreadsheet\Chart\DataSeries::GROUPING_STANDARD, [0, 1], array_merge($lbl1, $lbl2), $xLine, array_merge($v1, $v2));
            $chartLine = new \PhpOffice\PhpSpreadsheet\Chart\Chart('chart_tren_bulanan', new \PhpOffice\PhpSpreadsheet\Chart\Title('Tren Capaian Bulanan & Kumulatif (%)'), new \PhpOffice\PhpSpreadsheet\Chart\Legend(\PhpOffice\PhpSpreadsheet\Chart\Legend::POSITION_BOTTOM, null, false), new \PhpOffice\PhpSpreadsheet\Chart\PlotArea(null, [$serLine]), true, 0, null, null);
            $chartLine->setTopLeftPosition('G2');
            $chartLine->setBottomRightPosition('N22');
            $s2->addChart($chartLine);

            if ($data['nilai_target_tahunan'] > 0) {
                $rupiahRow = $chartDataEnd + 2;
                foreach ($data['analisa_data'] as $ci => $analisa) {
                    $cr = $rupiahRow + $ci + 1;
                    $s2->setCellValue("B{$cr}", $analisa['nama_bulan']);
                    $s2->setCellValue("C{$cr}", $analisa['actual_rupiah']);
                    $this->xlHide($s2, "B{$cr}");
                    $this->xlHide($s2, "C{$cr}");
                }
                $rupiahEnd = $rupiahRow + 12;
                $xRup = [new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('String', "'{$s2Title}'!\$B\$" . ($rupiahRow + 1) . ":\$B\${$rupiahEnd}", null, 12)];
                $lblRup = [new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('String', "'{$s2Title}'!C{$rupiahRow}", null, 1)];
                $vRup = [new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('Number', "'{$s2Title}'!\$C\$" . ($rupiahRow + 1) . ":\$C\${$rupiahEnd}", null, 12)];
                $serRup = new \PhpOffice\PhpSpreadsheet\Chart\DataSeries(\PhpOffice\PhpSpreadsheet\Chart\DataSeries::TYPE_BARCHART, \PhpOffice\PhpSpreadsheet\Chart\DataSeries::GROUPING_CLUSTERED, [0], $lblRup, $xRup, $vRup);
                $serRup->setPlotDirection(\PhpOffice\PhpSpreadsheet\Chart\DataSeries::DIRECTION_COL);
                $chartRup = new \PhpOffice\PhpSpreadsheet\Chart\Chart('chart_rupiah_bulanan', new \PhpOffice\PhpSpreadsheet\Chart\Title('Actual Pemasukan Per Bulan (Rp)'), new \PhpOffice\PhpSpreadsheet\Chart\Legend(\PhpOffice\PhpSpreadsheet\Chart\Legend::POSITION_BOTTOM, null, false), new \PhpOffice\PhpSpreadsheet\Chart\PlotArea(null, [$serRup]), true, 0, null, null);
                $chartRup->setTopLeftPosition('G24');
                $chartRup->setBottomRightPosition('N44');
                $s2->addChart($chartRup);
            }

            $s3 = $spreadsheet->createSheet()->setTitle('Ringkasan Eksekutif');
            foreach (['A' => 24, 'B' => 16, 'C' => 16, 'D' => 14, 'E' => 14, 'F' => 14, 'G' => 12] as $col => $w) $s3->getColumnDimension($col)->setWidth($w);
            $r3 = 1;

            $s3->mergeCells("A{$r3}:G{$r3}");
            $s3->setCellValue("A{$r3}", "RINGKASAN EKSEKUTIF KPI — {$namaKaryawan} — {$tahun}");
            $this->xlStyle($s3, "A{$r3}:G{$r3}", $C_HDR, $C_WH, 13, true, 'center');
            $s3->getRowDimension($r3)->setRowHeight(28);
            $r3 += 2;

            $s3->mergeCells("A{$r3}:G{$r3}");
            $s3->setCellValue("A{$r3}", 'TABEL GRADE PENILAIAN');
            $this->xlStyle($s3, "A{$r3}:G{$r3}", $C_SUB, $C_WH, 11, true, 'center');
            $r3++;
            foreach (['A' => 'Grade', 'B' => 'Range Nilai', 'C' => 'Keterangan'] as $col => $h) $s3->setCellValue("{$col}{$r3}", $h);
            $this->xlStyle($s3, "A{$r3}:C{$r3}", $C_HDR, $C_WH, 10, true, 'center');
            $r3++;
            $grades = [['Sangat Baik', '≥ 100%', 'Melebihi target, performa luar biasa'], ['Baik', '80% - 99%', 'Mencapai target dengan baik'], ['Cukup', '70% - 79%', 'Memenuhi standar minimum'], ['Kurang', '60% - 69%', 'Perlu peningkatan'], ['Sangat Kurang', '< 60%', 'Perlu evaluasi mendalam']];
            $gColors = ['Sangat Baik' => $C_GRN, 'Baik' => '4CAF50', 'Cukup' => $C_YEL, 'Kurang' => $C_AMB, 'Sangat Kurang' => $C_RED];
            foreach ($grades as [$grade, $range, $ket]) {
                $bg = (($r3 - 4) % 2 === 0) ? $C_ODD : $C_WH;
                $s3->setCellValue("A{$r3}", $grade);
                $s3->setCellValue("B{$r3}", $range);
                $s3->setCellValue("C{$r3}", $ket);
                $this->xlStyle($s3, "A{$r3}:C{$r3}", $bg, $C_DRK, 10, false, 'left');
                $styleAG = $s3->getStyle("A{$r3}");
                $styleAG->getFont()->getColor()->setARGB('FF' . $gColors[$grade]);
                $styleAG->getFont()->setBold(true);
                $s3->getStyle("C{$r3}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $r3++;
            }
            $r3 += 2;

            $sc = $data['status_count'];
            $total = count($data['tabel_target']);
            $ringkasan = [
                ['Total Target KPI', $total, $C_SUB],
                ['Target Selesai', $sc['Selesai'] ?? 0, $C_GRN],
                ['Target Dalam Progress', $sc['Dalam Progress'] ?? 0, $C_YEL],
                ['Target Belum Dimulai', $sc['Belum Dimulai'] ?? 0, $C_GRY],
                ['Target Gagal', $sc['Gagal'] ?? 0, $C_RED],
                ['Total % Kumulatif KPI', $data['total_kumulatif'] . '%', $C_HDR],
                ['Grade Kumulatif', $data['grade_akhir'], $C_HDR],
                ['Nilai KPI × 60%', $p['kpi_x_bobot'], $C_HDR],
                ['Nilai Softskill × 40%', $p['softskill_x_bobot'], $C_SUB],
                ['NILAI AKHIR', $p['total_akhir'], '1A5276'],
                ['GRADE AKHIR', $p['grade_total_akhir'], '1A5276'],
            ];
            foreach ($ringkasan as [$label, $nilai, $col]) {
                $isFinal = in_array($label, ['NILAI AKHIR', 'GRADE AKHIR']);
                $s3->setCellValue("A{$r3}", $label);
                $s3->setCellValue("B{$r3}", $nilai);
                $this->xlStyle($s3, "A{$r3}:B{$r3}", $isFinal ? 'D6EAF8' : $C_ODD, $C_DRK, $isFinal ? 12 : 10, $isFinal, 'left');
                $styleB = $s3->getStyle("B{$r3}");
                $styleB->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $styleB->getFont()->getColor()->setARGB("FF{$col}");
                $styleB->getFont()->setBold(true);
                $s3->getStyle("A{$r3}:B{$r3}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $s3->getRowDimension($r3)->setRowHeight(22);
                $r3++;
            }
            $r3 += 2;

            $s3->mergeCells("A{$r3}:B{$r3}");
            $s3->setCellValue("A{$r3}", 'DISTRIBUSI GRADE KPI');
            $this->xlStyle($s3, "A{$r3}:B{$r3}", $C_SUB, $C_WH, 11, true, 'center');
            $r3++;
            foreach (['A' => 'Grade', 'B' => 'Jumlah Target'] as $col => $h) $s3->setCellValue("{$col}{$r3}", $h);
            $this->xlStyle($s3, "A{$r3}:B{$r3}", $C_HDR, $C_WH, 10, true, 'center');
            $r3++;
            $pieStart = $r3;
            foreach ($data['grade_distribution'] as $grade => $count) {
                $bg = (($r3 - $pieStart) % 2 === 0) ? $C_ODD : $C_WH;
                $s3->setCellValue("A{$r3}", $grade);
                $s3->setCellValue("B{$r3}", $count);
                $this->xlStyle($s3, "A{$r3}:B{$r3}", $bg, $C_DRK, 10, false, 'center');
                $styleAP = $s3->getStyle("A{$r3}");
                $styleAP->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $styleAP->getFont()->getColor()->setARGB('FF' . $gColors[$grade]);
                $styleAP->getFont()->setBold(true);
                $r3++;
            }
            $pieEnd = $r3 - 1;
            $s3Title = $s3->getTitle();
            $lblPie = [new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('String', "'{$s3Title}'!\$A\${$pieStart}:\$A\${$pieEnd}", null, 5)];
            $xPie = [new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('String', "'{$s3Title}'!\$A\${$pieStart}:\$A\${$pieEnd}", null, 5)];
            $vPie = [new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('Number', "'{$s3Title}'!\$B\${$pieStart}:\$B\${$pieEnd}", null, 5)];
            $serPie = new \PhpOffice\PhpSpreadsheet\Chart\DataSeries(\PhpOffice\PhpSpreadsheet\Chart\DataSeries::TYPE_PIECHART, null, [0], $lblPie, $xPie, $vPie);
            $chartPie = new \PhpOffice\PhpSpreadsheet\Chart\Chart('chart_distribusi_grade', new \PhpOffice\PhpSpreadsheet\Chart\Title('Distribusi Grade Target KPI'), new \PhpOffice\PhpSpreadsheet\Chart\Legend(\PhpOffice\PhpSpreadsheet\Chart\Legend::POSITION_BOTTOM, null, false), new \PhpOffice\PhpSpreadsheet\Chart\PlotArea(null, [$serPie]), true, 0, null, null);
            $chartPie->setTopLeftPosition('D2');
            $chartPie->setBottomRightPosition('J26');
            $s3->addChart($chartPie);

            $spreadsheet->setActiveSheetIndex(0);
            $namaFile = 'KPI_' . str_replace(' ', '_', $namaKaryawan) . '_' . $tahun . '.xlsx';
            $tmpPath = tempnam(sys_get_temp_dir(), 'kpi_') . '.xlsx';
            $writer = new Xlsx($spreadsheet);
            $writer->setIncludeCharts(true);
            $writer->save($tmpPath);
            return response()->download($tmpPath, $namaFile, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Export Excel KPI error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withErrors(['export' => 'Gagal export Excel: ' . $e->getMessage()]);
        }
    }

    private function xlStyle(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $range, string $bgRgb, string $fontRgb = '1F2937', int $fontSize = 10, bool $bold = false, string $hAlign = 'left'): void
    {
        $style = $sheet->getStyle($range);
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB("FF{$bgRgb}");
        $style->getFont()->setBold($bold)->setSize($fontSize)->setName('Calibri')->getColor()->setARGB("FF{$fontRgb}");
        $hMap = ['center' => Alignment::HORIZONTAL_CENTER, 'right' => Alignment::HORIZONTAL_RIGHT, 'left' => Alignment::HORIZONTAL_LEFT];
        $style->getAlignment()->setHorizontal($hMap[$hAlign] ?? Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $style->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCCCCCC');
    }

    private function xlHide(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $cell): void
    {
        $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFFFF');
    }

    //export departement
    public function exportDeptExcel(Request $request)
    {
        return $this->handleDeptExport($request, 'excel');
    }

    public function exportDeptPdf(Request $request)
    {
        return $this->handleDeptExport($request, 'pdf');
    }

    private function handleDeptExport(Request $request, string $type)
    {
        try {
            $divisi = $request->query('divisi');
            $tahun  = (int)($request->query('tahun') ?? now()->year);

            if (!$divisi) {
                return back()->withErrors(['export' => 'Departemen belum dipilih.']);
            }

            $karyawanList = karyawan::where('divisi', $divisi)
                ->where('status_aktif', '1')
                ->whereNot('jabatan', 'Outsource')
                ->where('kode_karyawan', 'NOT LIKE', 'OL%')
                ->whereNot('jabatan', 'Pilih Jabatan')
                ->whereNotNull('nip')
                ->whereNot('divisi', 'Direksi')
                ->get();

            if ($karyawanList->isEmpty()) {
                return back()->withErrors(['export' => 'Tidak ada karyawan aktif di departemen ini.']);
            }

            $allEmployeesData = [];
            $allTargetPercentages = []; // Kumpulkan SEMUA persentase target individual
            $totalSelesaiDivisi = 0;
            $totalGagalDivisi = 0;
            $countKaryawan = $karyawanList->count();

            $namaBulan = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Ags', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];

            $deptMonthlyAllValues = array_fill(1, 12, []); // Simpan semua nilai per bulan untuk avg yang akurat

            foreach ($karyawanList as $karyawan) {
                $personId = $karyawan->id;

                $targets = targetKPI::with([
                    'detailTargetKPI.detailPersonKPI',
                    'detailTargetKPI.dataTarget'
                ])
                    ->whereYear('created_at', $tahun)
                    ->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($personId) {
                        $q->where('id_karyawan', $personId);
                    })
                    ->get();

                $empTargetPercentages = []; // Simpan persentase setiap target karyawan ini
                $empStatusCounts = ['Selesai' => 0, 'Dalam Progress' => 0, 'Belum Dimulai' => 0, 'Gagal' => 0];
                $empMonthlyValues = array_fill(1, 12, []);

                foreach ($targets as $item) {
                    $detail = $item->detailTargetKPI->filter(function ($dt) use ($personId) {
                        return $dt->detailPersonKPI->contains('id_karyawan', $personId);
                    })->first();

                    if (!$detail) continue;

                    $dataTarget = $detail->dataTarget;
                    if (!$dataTarget) continue;

                    $tipe = $dataTarget->tipe_target;
                    $nilaiTarget = (float)$dataTarget->nilai_target;

                    $calc = $this->getCalculationByRoute($item, $personId);

                    if (!$calc) {
                        $calc = [
                            'progress' => 0,
                            'monthly_progress' => [],
                            'daily_progress_per_month' => []
                        ];
                    }

                    $progressRaw = (float)($calc['progress'] ?? 0);
                    $progressPersen = 0;

                    if ($tipe === 'rupiah' && $nilaiTarget > 0) {
                        $progressPersen = ($progressRaw / $nilaiTarget) * 100;
                    } elseif ($tipe === 'angka' && $nilaiTarget > 0) {
                        $progressPersen = ($progressRaw / $nilaiTarget) * 100;
                    } else {
                        $progressPersen = $progressRaw;
                    }

                    // Batasi max 100% untuk perhitungan yang wajar
                    $progressPersen = min($progressPersen, 100);
                    $progressPersen = round($progressPersen, 2);

                    $tenggat = $this->formatTenggatWaktuExport($dataTarget->jangka_target, $detail->detail_jangka);
                    $status = $this->hitungStatusExport($progressPersen, $nilaiTarget, $tipe, $progressRaw, $tenggat);

                    // Simpan persentase target ini ke array karyawan dan global
                    $empTargetPercentages[] = $progressPersen;
                    $allTargetPercentages[] = $progressPersen;

                    if (isset($empStatusCounts[$status])) {
                        $empStatusCounts[$status]++;
                    }

                    // Agregasi bulanan
                    $monthlyProg = $calc['monthly_progress'] ?? [];

                    foreach ($monthlyProg as $key => $val) {
                        $bulan = null;
                        if (preg_match('/^\d{4}-(\d{2})$/', (string)$key, $m)) {
                            $bulan = (int)$m[1];
                        } elseif (is_numeric($key) && $key >= 1 && $key <= 12) {
                            $bulan = (int)$key;
                        }

                        if ($bulan !== null) {
                            $valFloat = (float)$val;
                            // Konversi ke persen jika perlu
                            if ($tipe === 'rupiah' && $nilaiTarget > 0) {
                                $valFloat = ($valFloat / $nilaiTarget) * 100;
                            } elseif ($tipe === 'angka' && $nilaiTarget > 0) {
                                $valFloat = ($valFloat / $nilaiTarget) * 100;
                            }
                            $valFloat = min($valFloat, 100);
                            $empMonthlyValues[$bulan][] = round($valFloat, 2);
                        }
                    }

                    // Fallback distribusi merata jika tidak ada data bulanan
                    if (empty($monthlyProg) && count($empTargetPercentages) > 0) {
                        $lastProgress = $empTargetPercentages[count($empTargetPercentages) - 1];
                        $avgMonth = $lastProgress / 12;
                        for ($b = 1; $b <= 12; $b++) {
                            $empMonthlyValues[$b][] = round($avgMonth, 2);
                        }
                    }
                }

                // Hitung rata-rata KPI karyawan dari semua targetnya
                $empAvgKPI = count($empTargetPercentages) > 0
                    ? round(array_sum($empTargetPercentages) / count($empTargetPercentages), 2)
                    : 0;

                $empGrade = $this->getGradeLabel($empAvgKPI);

                $allEmployeesData[] = [
                    'nama' => $karyawan->nama_lengkap,
                    'jabatan' => $karyawan->jabatan,
                    'avg_kpi' => $empAvgKPI,
                    'grade' => $empGrade,
                    'target_count' => count($empTargetPercentages),
                    'status_counts' => $empStatusCounts,
                    'monthly_values' => $empMonthlyValues
                ];

                $totalSelesaiDivisi += $empStatusCounts['Selesai'];
                $totalGagalDivisi += $empStatusCounts['Gagal'];

                // Akumulasi untuk rata-rata bulanan departemen
                for ($b = 1; $b <= 12; $b++) {
                    if (!empty($empMonthlyValues[$b])) {
                        foreach ($empMonthlyValues[$b] as $val) {
                            $deptMonthlyAllValues[$b][] = $val;
                        }
                    }
                }
            }

            // Hitung rata-rata departemen dari SEMUA target individual
            $totalTargetDivisi = count($allTargetPercentages);
            $totalProgressSum = array_sum($allTargetPercentages);
            $avgKPIDivisi = $totalTargetDivisi > 0 ? round($totalProgressSum / $totalTargetDivisi, 2) : 0;
            $targetSelesaiPersen = $totalTargetDivisi > 0 ? round(($totalSelesaiDivisi / $totalTargetDivisi) * 100, 2) : 0;

            // Hitung rata-rata bulanan departemen
            $deptMonthlyFinal = [];
            for ($b = 1; $b <= 12; $b++) {
                $deptMonthlyFinal[$b] = count($deptMonthlyAllValues[$b]) > 0
                    ? round(array_sum($deptMonthlyAllValues[$b]) / count($deptMonthlyAllValues[$b]), 2)
                    : 0;
            }

            usort($allEmployeesData, function ($a, $b) {
                return $b['avg_kpi'] <=> $a['avg_kpi'];
            });

            $exportData = [
                'divisi' => $divisi,
                'tahun' => $tahun,
                'total_karyawan' => $countKaryawan,
                'rata_rata_kpi_divisi' => $avgKPIDivisi,
                'total_target_terdata' => $totalTargetDivisi,
                'target_selesai' => $totalSelesaiDivisi,
                'target_selesai_persen' => $targetSelesaiPersen,
                'ranking_karyawan' => $allEmployeesData,
                'trend_bulanan' => $deptMonthlyFinal,
                'nama_bulan' => $namaBulan,
                'insight_text' => $this->generateInsightText($allEmployeesData, $avgKPIDivisi),
                'risk_distribution' => $this->calculateRiskDistribution($allEmployeesData),
                'status_target_global' => [
                    'selesai' => $totalSelesaiDivisi,
                    'gagal' => $totalGagalDivisi,
                    'progress' => $totalTargetDivisi - $totalSelesaiDivisi - $totalGagalDivisi,
                    'belum_mulai' => 0
                ]
            ];

            if ($type === 'excel') {
                return $this->generateDeptExcel($exportData);
            } else {
                return $this->generateDeptPdf($exportData);
            }
        } catch (\Exception $e) {
            Log::error('Export Dept Error', ['msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withErrors(['export' => 'Terjadi kesalahan saat export: ' . $e->getMessage()]);
        }
    }

    private function generateInsightText(array $employees, float $avgDivisi): string
    {
        $topPerformer = !empty($employees) ? $employees[0] : null;
        $lowPerformers = array_filter($employees, fn($e) => $e['avg_kpi'] < 50);

        $text = "- Performa tertinggi diraih oleh " . ($topPerformer ? $topPerformer['nama'] . " (" . $topPerformer['avg_kpi'] . "%)" : "N/A") . ".\n";
        $text .= "- Terdapat " . count($lowPerformers) . " karyawan yang perlu perhatian khusus (<50%).\n";
        $text .= "- Tingkat penyelesaian target tahun ini adalah " . ($avgDivisi > 0 ? "sedang berjalan" : "belum ada progres signifikan") . ".";

        return $text;
    }

    private function calculateRiskDistribution(array $employees): array
    {
        $top = 0;
        $mid = 0;
        $low = 0;
        foreach ($employees as $emp) {
            if ($emp['avg_kpi'] >= 80) $top++;
            elseif ($emp['avg_kpi'] >= 50) $mid++;
            else $low++;
        }
        $total = count($employees) ?: 1;
        return [
            'top' => ['count' => $top, 'pct' => round(($top / $total) * 100, 0)],
            'mid' => ['count' => $mid, 'pct' => round(($mid / $total) * 100, 0)],
            'low' => ['count' => $low, 'pct' => round(($low / $total) * 100, 0)],
        ];
    }

    private function generateDeptExcel(array $data)
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);

        $s1 = $spreadsheet->getActiveSheet()->setTitle('Executive Summary');

        $C_HDR = '1F2937';
        $C_SUB = '2563EB';
        $C_WH = 'FFFFFF';
        $C_ODD = 'F3F4F6';
        $C_GRN = '10B981';
        $C_RED = 'EF4444';
        $C_YEL = 'F59E0B';
        $C_DRK = '111827';

        $s1->mergeCells("A1:F1");
        $s1->setCellValue("A1", "EXECUTIVE SUMMARY KPI DIVISI " . strtoupper($data['divisi']));
        $this->xlStyle($s1, "A1:F1", $C_HDR, $C_WH, 14, true, 'center');
        $s1->getRowDimension(1)->setRowHeight(30);

        $r = 3;
        $s1->setCellValue("A$r", "Total Karyawan");
        $s1->setCellValue("B$r", ": " . $data['total_karyawan']);
        $s1->setCellValue("D$r", "Rata-Rata KPI Divisi");
        $s1->setCellValue("E$r", ": " . $data['rata_rata_kpi_divisi'] . "%");
        $this->xlStyle($s1, "A{$r}:E{$r}", $C_ODD, $C_DRK, 10, false, 'left');
        $s1->getStyle("A{$r}:B{$r}")->getFont()->setBold(true);
        $s1->getStyle("D{$r}:E{$r}")->getFont()->setBold(true);
        $r++;

        $s1->setCellValue("A$r", "Total Target Terdata");
        $s1->setCellValue("B$r", ": " . $data['total_target_terdata']);
        $s1->setCellValue("D$r", "Target Selesai");
        $s1->setCellValue("E$r", ": " . $data['target_selesai'] . " (" . $data['target_selesai_persen'] . "%)");
        $this->xlStyle($s1, "A{$r}:E{$r}", $C_WH, $C_DRK, 10, false, 'left');
        $s1->getStyle("A{$r}:B{$r}")->getFont()->setBold(true);
        $s1->getStyle("D{$r}:E{$r}")->getFont()->setBold(true);
        $r += 2;

        $s1->mergeCells("A{$r}:F{$r}");
        $s1->setCellValue("A{$r}", "INSIGHT OTOMATIS");
        $this->xlStyle($s1, "A{$r}:F{$r}", $C_SUB, $C_WH, 11, true, 'center');
        $r++;
        $s1->mergeCells("A{$r}:F{$r}");
        $s1->setCellValue("A{$r}", $data['insight_text']);
        $this->xlStyle($s1, "A{$r}:F{$r}", 'EFF6FF', $C_DRK, 10, false, 'left');
        $s1->getStyle("A{$r}")->getAlignment()->setWrapText(true);
        $s1->getRowDimension($r)->setRowHeight(60);
        $r += 2;

        $s1->mergeCells("A{$r}:F{$r}");
        $s1->setCellValue("A{$r}", "RANKING KARYAWAN");
        $this->xlStyle($s1, "A{$r}:F{$r}", $C_HDR, $C_WH, 12, true, 'center');
        $r++;

        $headers = ['Rank', 'Nama', 'Jabatan', 'Avg %', 'Total Target', 'Status'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F'];
        foreach ($headers as $i => $h) {
            $s1->setCellValue($cols[$i] . $r, $h);
        }
        $this->xlStyle($s1, "A{$r}:F{$r}", '374151', $C_WH, 10, true, 'center');
        $r++;

        foreach ($data['ranking_karyawan'] as $idx => $emp) {
            $rank = $idx + 1;
            $bg = ($idx % 2 == 0) ? $C_WH : $C_ODD;

            if ($rank <= 3) $bg = 'D1FAE5';

            $s1->setCellValue("A{$r}", $rank);
            $s1->setCellValue("B{$r}", $emp['nama']);
            $s1->setCellValue("C{$r}", $emp['jabatan']);
            $s1->setCellValue("D{$r}", $emp['avg_kpi'] . "%");
            $s1->setCellValue("E{$r}", $emp['target_count']);

            $statusText = $emp['avg_kpi'] >= 80 ? 'Baik' : ($emp['avg_kpi'] >= 50 ? 'Cukup' : 'Perlu Perhatian');
            $s1->setCellValue("F{$r}", $statusText);

            $this->xlStyle($s1, "A{$r}:F{$r}", $bg, $C_DRK, 10, false, 'center');
            $s1->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $s1->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $color = $emp['avg_kpi'] >= 80 ? $C_GRN : ($emp['avg_kpi'] >= 50 ? $C_YEL : $C_RED);
            $s1->getStyle("D{$r}")->getFont()->getColor()->setARGB("FF{$color}");
            $s1->getStyle("D{$r}")->getFont()->setBold(true);

            $s1->getStyle("F{$r}")->getFont()->getColor()->setARGB("FF{$color}");
            $s1->getStyle("F{$r}")->getFont()->setBold(true);

            $r++;
        }

        $s2 = $spreadsheet->createSheet()->setTitle('Deep Analysis');
        $r2 = 1;

        $s2->mergeCells("A{$r2}:D{$r2}");
        $s2->setCellValue("A{$r2}", "DISTRIBUSI KINERJA & RISIKO");
        $this->xlStyle($s2, "A{$r2}:D{$r2}", $C_HDR, $C_WH, 12, true, 'center');
        $r2++;

        $s2->setCellValue("A{$r2}", "Kategori");
        $s2->setCellValue("B{$r2}", "Jumlah");
        $s2->setCellValue("C{$r2}", "Persentase");
        $s2->setCellValue("D{$r2}", "Risk Level");
        $this->xlStyle($s2, "A{$r2}:D{$r2}", '374151', $C_WH, 10, true, 'center');
        $r2++;

        $risks = [
            ['Top Performer (≥80%)', $data['risk_distribution']['top']['count'], $data['risk_distribution']['top']['pct'] . '%', 'Low Risk'],
            ['Sedang (50-79%)', $data['risk_distribution']['mid']['count'], $data['risk_distribution']['mid']['pct'] . '%', 'Medium Risk'],
            ['Perlu Perhatian (<50%)', $data['risk_distribution']['low']['count'], $data['risk_distribution']['low']['pct'] . '%', 'High Risk'],
        ];

        foreach ($risks as $risk) {
            $s2->fromArray($risk, null, "A{$r2}");
            $this->xlStyle($s2, "A{$r2}:D{$r2}", $C_WH, $C_DRK, 10, false, 'center');
            $s2->getStyle("A{$r2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $r2++;
        }
        $r2 += 2;

        $s2->mergeCells("A{$r2}:C{$r2}");
        $s2->setCellValue("A{$r2}", "TREND PERFORMA BULANAN");
        $this->xlStyle($s2, "A{$r2}:C{$r2}", $C_HDR, $C_WH, 12, true, 'center');
        $r2++;

        $s2->setCellValue("A{$r2}", "Bulan");
        $s2->setCellValue("B{$r2}", "Rata-Rata Dept (%)");
        $s2->setCellValue("C{$r2}", "Grafik");
        $this->xlStyle($s2, "A{$r2}:C{$r2}", '374151', $C_WH, 10, true, 'center');
        $r2++;

        $chartStartRow = $r2;
        foreach ($data['trend_bulanan'] as $bln => $avg) {
            $s2->setCellValue("A{$r2}", $data['nama_bulan'][$bln]);
            $s2->setCellValue("B{$r2}", $avg);
            $this->xlStyle($s2, "A{$r2}:B{$r2}", ($bln % 2 == 0) ? $C_ODD : $C_WH, $C_DRK, 10, false, 'center');
            $r2++;
        }
        $chartEndRow = $r2 - 1;

        $s2Title = $s2->getTitle();
        $xValues = new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'{$s2Title}'!\$A\$" . $chartStartRow . ":\$A\$" . $chartEndRow, null, 12);
        $yValues = new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "'{$s2Title}'!\$B\$" . $chartStartRow . ":\$B\$" . $chartEndRow, null, 12);

        $series = new DataSeries(
            DataSeries::TYPE_LINECHART,
            DataSeries::GROUPING_STANDARD,
            range(0, 0),
            [],
            [$xValues],
            [$yValues]
        );
        $plotArea = new PlotArea(null, [$series]);
        $legend = new Legend(Legend::POSITION_BOTTOM, null, false);
        $title = new Title('Trend Rata-Rata KPI Departemen');
        $chart = new Chart('chart_trend_dept', $title, $legend, $plotArea);
        $chart->setTopLeftPosition('E2');
        $chart->setBottomRightPosition('L20');
        $s2->addChart($chart);

        $s3 = $spreadsheet->createSheet()->setTitle('Visualisasi Grafik');
        $r3 = 1;

        $s3->mergeCells("A{$r3}:D{$r3}");
        $s3->setCellValue("A{$r3}", "GRAFIK DISTRIBUSI GRADE KARYAWAN");
        $this->xlStyle($s3, "A{$r3}:D{$r3}", $C_HDR, $C_WH, 12, true, 'center');
        $r3++;

        $grades = ['Sangat Baik', 'Baik', 'Cukup', 'Kurang', 'Sangat Kurang'];
        $gradeCounts = [0, 0, 0, 0, 0];
        foreach ($data['ranking_karyawan'] as $emp) {
            $g = $emp['grade'];
            if ($g === 'Sangat Baik') $gradeCounts[0]++;
            elseif ($g === 'Baik') $gradeCounts[1]++;
            elseif ($g === 'Cukup') $gradeCounts[2]++;
            elseif ($g === 'Kurang') $gradeCounts[3]++;
            else $gradeCounts[4]++;
        }

        $s3->setCellValue("A{$r3}", "Grade");
        $s3->setCellValue("B{$r3}", "Jumlah");
        $this->xlStyle($s3, "A{$r3}:B{$r3}", '374151', $C_WH, 10, true, 'center');
        $r3++;

        $pieStart = $r3;
        foreach ($grades as $i => $grade) {
            $s3->setCellValue("A{$r3}", $grade);
            $s3->setCellValue("B{$r3}", $gradeCounts[$i]);
            $this->xlStyle($s3, "A{$r3}:B{$r3}", $C_WH, $C_DRK, 10, false, 'center');
            $r3++;
        }
        $pieEnd = $r3 - 1;

        $s3Title = $s3->getTitle();
        $pieLabels = new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'{$s3Title}'!\$A\$" . $pieStart . ":\$A\$" . $pieEnd, null, 5);
        $pieValues = new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "'{$s3Title}'!\$B\$" . $pieStart . ":\$B\$" . $pieEnd, null, 5);

        $pieSeries = new DataSeries(
            DataSeries::TYPE_PIECHART,
            null,
            range(0, 0),
            [$pieLabels],
            [$pieLabels],
            [$pieValues]
        );
        $piePlot = new PlotArea(null, [$pieSeries]);
        $pieLegend = new Legend(Legend::POSITION_BOTTOM, null, false);
        $pieTitle = new Title('Distribusi Grade');
        $pieChart = new Chart('chart_grade_dist', $pieTitle, $pieLegend, $piePlot);
        $pieChart->setTopLeftPosition('D2');
        $pieChart->setBottomRightPosition('J20');
        $s3->addChart($pieChart);

        $filename = 'KPI_Dept_' . str_replace(' ', '_', $data['divisi']) . '_' . $data['tahun'] . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->setIncludeCharts(true);

        ob_end_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    private function generateDeptPdf(array $data)
    {
        $pdf = Pdf::loadView('KPIdata.export.export_dept_pdf', $data)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'dpi' => 150,
            ]);

        $filename = 'KPI_Dept_' . str_replace(' ', '_', $data['divisi']) . '_' . $data['tahun'] . '.pdf';
        return $pdf->download($filename);
    }

    public function executiveDashboard(Request $request)
    {
        $user = auth()->user();
        $allowedRoles = ['GM', 'HRD', 'Direktur Utama'];
        if (!in_array($user->jabatan, $allowedRoles)) {
            abort(403, 'Akses khusus executive required');
        }

        $divisiList = karyawan::whereNotNull('divisi')
            ->where('divisi', '!=', '')
            ->where('divisi', '!=', 'Direksi')
            ->distinct()
            ->pluck('divisi')
            ->filter()
            ->values();

        $jabatanList = karyawan::whereNotNull('jabatan')
            ->where('jabatan', '!=', '')
            ->whereNotIn('jabatan', ['Direktur Utama', 'Direktur', 'Outsource', 'Pilih Jabatan'])
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNotNull('nip')
            ->where('divisi', '!=', 'Direksi')
            ->distinct()
            ->pluck('jabatan')
            ->filter()
            ->values();

        return view('HR.executive.dashboard', compact('divisiList', 'jabatanList'));
    }

    public function exportExecutiveReport(Request $request)
    {
        $user = auth()->user();
        $allowedRoles = ['GM', 'HRD', 'Direktur Utama'];

        if (!in_array($user->jabatan, $allowedRoles)) {
            abort(403, 'Akses khusus executive required');
        }

        $filters = $request->only(['divisi', 'jabatan', 'tahun']);
        $filename = 'Executive_Report_' . date('Ymd_His') . '.pdf';

        $tahun = $filters['tahun'] ?? now()->year;
        $karyawanIds = $this->getKaryawanIdsFromFilters($filters);

        $query = targetKPI::with([
            'karyawan',
            'detailTargetKPI' => function ($q) {
                $q->with(['dataTarget', 'detailPersonKPI.karyawan']);
            },
        ])
            ->whereYear('created_at', $tahun)
            ->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($karyawanIds) {
                $q->whereIn('id_karyawan', $karyawanIds);
            });

        $targets = $query->get();
        $progressData = $this->extractAllProgressData($targets, $filters);

        $summary = [
            'total_targets' => $targets->count(),
            'avg_progress' => round($progressData->pluck('progress')->filter()->avg() ?? 0, 1),
            'completion_rate' => $this->calculateCompletionRate($targets),
            'top_divisions' => $this->getTopDivisions($progressData),
            'period' => $tahun
        ];

        $pdf = PDF::loadView('HR.executive.report-pdf', [
            'filters' => $filters,
            'generated_at' => now(),
            'user' => $user,
            'summary' => $summary
        ])->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    public function getExecutiveTrend(Request $request)
    {
        $user = auth()->user();
        $allowedRoles = ['GM', 'HRD', 'Direktur Utama'];
        if (!in_array($user->jabatan, $allowedRoles)) {
            abort(403, 'Akses khusus executive required');
        }

        $filters = $request->validate([
            'divisi' => 'nullable|string',
            'jabatan' => 'nullable|string',
            'id_karyawan' => 'nullable|integer|exists:karyawan,id',
            'tahun' => 'nullable|integer|min:2020|max:' . date('Y'),
            'granularity' => 'nullable|in:monthly,quarterly,yearly'
        ]);

        $filters['granularity'] = $filters['granularity'] ?? 'monthly';
        $filters['tahun'] = $filters['tahun'] ?? now()->year;

        $tahun = $filters['tahun'];
        $karyawanIds = $this->getKaryawanIdsFromFilters($filters);

        $query = targetKPI::with([
            'karyawan',
            'detailTargetKPI' => function ($q) {
                $q->with(['dataTarget', 'detailPersonKPI.karyawan']);
            },
        ])
            ->whereYear('created_at', $tahun)
            ->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($karyawanIds) {
                $q->whereIn('id_karyawan', $karyawanIds);
            });

        $targets = $query->get();
        $allProgressData = $this->extractAllProgressData($targets, $filters);
        $trendData = $this->calculateTrendMetrics($allProgressData, $filters['granularity']);
        $comparisonData = $this->calculatePeriodComparison($filters, $allProgressData);

        $overallAverage = $allProgressData->isNotEmpty()
            ? round($allProgressData->avg('progress'), 1)
            : 0;

        $completedTargets = $targets->filter(function ($t) {
            return in_array(strtolower($t->status ?? ''), ['completed', 'selesai', 'done']);
        })->count();

        return response()->json([
            'meta' => [
                'filters' => $filters,
                'generated_at' => now(),
                'data_points' => $trendData['count'] ?? 0
            ],
            'trend' => $trendData,
            'comparison' => $comparisonData,
            'summary' => [
                'overall_average' => $overallAverage,
                'total_targets' => $targets->count(),
                'completed_targets' => $completedTargets
            ],
            'insights' => $this->generateTrendInsights($trendData)
        ]);
    }

    public function getPredictiveAnalysis(Request $request)
    {
        $filters = $request->only(['divisi', 'jabatan', 'tahun']);
        $currentYear = $filters['tahun'] ?? now()->year;

        $targets = targetKPI::with([
            'karyawan',
            'detailTargetKPI' => function ($q) {
                $q->with(['dataTarget', 'detailPersonKPI.karyawan']);
            },
        ])
            ->whereYear('created_at', $currentYear)
            ->get();

        $historicalData = $this->extractAllProgressData($targets);
        $timeSeries = $this->prepareTimeSeries($historicalData, 'monthly');

        if (count($timeSeries) < 3) {
            return response()->json([
                'prediction' => [
                    'next_period' => null,
                    'next_3_periods' => [],
                    'confidence_level' => '30%',
                    'method' => 'insufficient_data'
                ],
                'debug' => [
                    'targets_found' => $targets->count(),
                    'progress_points' => $historicalData->count(),
                    'time_series_points' => count($timeSeries),
                ],
                'message' => 'Data historis belum cukup untuk prediksi akurat',
                'recommendations' => ['Kumpulkan minimal 3 bulan data historis untuk prediksi yang lebih akurat']
            ]);
        }

        $predictions = $this->applyLinearRegression($timeSeries);
        $confidence = $this->calculatePredictionConfidence($timeSeries, $predictions);

        return response()->json([
            'prediction' => [
                'next_period' => $predictions['next'],
                'next_3_periods' => $predictions['next_3'],
                'confidence_level' => round($confidence * 100, 1) . '%',
                'method' => 'linear_regression',
                'slope' => $predictions['slope']
            ],
            'historical_basis' => array_slice($timeSeries, -12, null, true),
            'recommendations' => $this->generateRecommendations($predictions, $confidence)
        ]);
    }

    public function getPotentialMatrixUnified(Request $request)
    {
        $filters = $request->only(['divisi', 'jabatan', 'tahun']);
        $tahun = $filters['tahun'] ?? now()->year;

        $query = karyawan::where('status_aktif', '1')
            ->whereNotNull('nama_lengkap')
            ->whereNot('jabatan', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNotNull('nip')
            ->whereNot('divisi', 'Direksi');

        if (!empty($filters['divisi'])) $query->where('divisi', $filters['divisi']);
        if (!empty($filters['jabatan'])) $query->where('jabatan', $filters['jabatan']);
        if (!empty($filters['id_karyawan'])) $query->where('id', $filters['id_karyawan']);
        $employees = $query->get();

        if ($employees->isEmpty()) {
            return response()->json([
                'matrix' => [],
                'summary' => ['total_employees' => 0, 'message' => 'Tidak ada data karyawan untuk periode ini'],
                'visualization_data' => []
            ]);
        }

        $matrixData = [];
        $allPoints = [];

        foreach ($employees as $emp) {
            $kpiTargets = targetKPI::with(['detailTargetKPI.detailPersonKPI'])
                ->whereYear('created_at', $tahun)
                ->whereHas('detailTargetKPI.detailPersonKPI', fn($q) => $q->where('id_karyawan', $emp->id))
                ->get();

            $progressValues = [];
            $processedTargets = [];

            foreach ($kpiTargets as $target) {
                foreach ($target->detailTargetKPI as $detail) {
                    $assignedIds = $detail->detailPersonKPI
                        ->where('id_karyawan', $emp->id)
                        ->pluck('id_karyawan')
                        ->unique()
                        ->toArray();

                    if (empty($assignedIds)) continue;

                    foreach ($assignedIds as $personId) {
                        $targetKey = $target->id . '_' . $detail->id . '_' . $personId;
                        if (isset($processedTargets[$targetKey])) continue;
                        $processedTargets[$targetKey] = true;

                        $result = $this->getCalculationByRoute($target, $personId);
                        if (!$result || !isset($result['progress'])) continue;

                        $rawProgress = $this->normalizeNumber($result['progress']);
                        $percent = max(0, min(100, round($rawProgress, 2)));
                        $progressValues[] = $percent;
                    }
                }
            }

            $performance = !empty($progressValues) ? round(array_sum($progressValues) / count($progressValues), 1) : 0;

            $penilaian = nilaiKPI::where('id_evaluated', $emp->id)->whereYear('created_at', $tahun)->get();
            $bobotJenis = [
                'General Manager' => 35,
                'Manager/SPV/Team Leader (Atasan Langsung)' => 30,
                'Rekan Kerja (Satu Divisi)' => 20,
                'Pekerja (Beda Divisi)' => 10,
                'Self Apprisial' => 5,
            ];

            $jenisTotalRaw = [];
            foreach ($bobotJenis as $jenis => $bobot) {
                $nilaiForJenis = $penilaian->where('jenis_penilaian', $jenis)
                    ->pluck('nilai')
                    ->filter(fn($n) => is_numeric($n) && $n > 0);

                if ($nilaiForJenis->isNotEmpty()) {
                    $avgNilai = $nilaiForJenis->avg();
                    $jenisTotalRaw[$jenis] = ($avgNilai * $bobot) / 100;
                }
            }
            $potential = empty($jenisTotalRaw) ? 0 : round(array_sum($jenisTotalRaw), 1);

            $perfLevel = $performance >= 75 ? 'high' : ($performance >= 50 ? 'moderate' : 'low');
            $potenLevel = $potential >= 70 ? 'high' : ($potential >= 40 ? 'moderate' : 'low');

            $quadrant = match (true) {
                $perfLevel === 'high' && $potenLevel === 'high' => 'star',
                $perfLevel === 'moderate' && $potenLevel === 'high' => 'high_potential',
                $perfLevel === 'low' && $potenLevel === 'high' => 'potential_gem',
                $perfLevel === 'high' && $potenLevel === 'moderate' => 'high_performer',
                $perfLevel === 'moderate' && $potenLevel === 'moderate' => 'core_player',
                $perfLevel === 'low' && $potenLevel === 'moderate' => 'inconsistent',
                $perfLevel === 'high' && $potenLevel === 'low' => 'solid_performer',
                $perfLevel === 'moderate' && $potenLevel === 'low' => 'average_performer',
                $perfLevel === 'low' && $potenLevel === 'low' => 'risk',
                default => 'core_player'
            };

            $strengths = [];
            if ($performance >= 80) $strengths[] = 'Kinerja konsisten di atas target';
            elseif ($performance >= 70) $strengths[] = 'Kinerja stabil dan dapat diandalkan';

            if ($potential >= 75) $strengths[] = 'Potensi pengembangan tinggi';
            elseif ($potential >= 60) $strengths[] = 'Menunjukkan tren peningkatan yang positif';

            if ($potential >= 80) $strengths[] = 'Penilaian 360° sangat baik';
            elseif ($potential >= 70) $strengths[] = 'Penilaian rekan kerja positif';

            $targetCount = targetKPI::whereYear('created_at', now()->year)
                ->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($emp) {
                    $q->where('id_karyawan', $emp->id);
                })
                ->count();

            if ($targetCount >= 5) $strengths[] = 'Pengalaman menangani multiple target';
            if (empty($strengths)) $strengths[] = 'Dalam proses pengembangan';

            $areas = [];
            if ($performance < 60) $areas[] = 'Fokus pada peningkatan kualitas eksekusi target';
            elseif ($performance < 75) $areas[] = 'Optimalkan konsistensi pencapaian target';

            if ($potential < 50) $areas[] = 'Perlu eksposur ke target yang lebih beragam untuk pengembangan skill';

            if ($potential < 60 && $potential > 0) $areas[] = 'Perlu peningkatan kolaborasi dan komunikasi dengan tim';

            $empTargets = targetKPI::whereYear('created_at', now()->year)
                ->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($emp) {
                    $q->where('id_karyawan', $emp->id);
                })->get();

            if ($empTargets->isNotEmpty()) {
                $completionRate = $empTargets->filter(
                    fn($t) => in_array(strtolower($t->status ?? ''), ['completed', 'selesai', 'done'])
                )->count() / $empTargets->count() * 100;

                if ($completionRate < 70) $areas[] = 'Tingkatkan konsistensi penyelesaian target';
            }

            if (empty($areas)) $areas[] = 'Pertahankan kinerja saat ini';

            $matrixData[] = [
                'id' => $emp->id,
                'nama' => $emp->nama_lengkap,
                'jabatan' => $emp->jabatan,
                'divisi' => $emp->divisi,
                'performance_score' => $performance,
                'potential_score' => $potential,
                'three_sixty_score' => $potential,
                'quadrant' => $quadrant,
                'key_strengths' => $strengths,
                'development_areas' => $areas
            ];

            $allPoints[] = [
                'x' => $performance,
                'y' => $potential,
                'name' => $emp->nama_lengkap,
                'jabatan' => $emp->jabatan,
                'divisi' => $emp->divisi,
                'quadrant' => $quadrant,
                'three_sixty' => $potential,
                'type' => 'unified'
            ];
        }

        $matrix = [
            'star' => [],
            'high_potential' => [],
            'potential_gem' => [],
            'high_performer' => [],
            'core_player' => [],
            'inconsistent' => [],
            'solid_performer' => [],
            'average_performer' => [],
            'risk' => [],
        ];

        foreach ($matrixData as $emp) {
            $matrix[$emp['quadrant']][] = $emp;
        }

        $avgPerformance = collect($matrixData)->avg('performance_score') ?? 0;
        $avgPotential = collect($matrixData)->avg('potential_score') ?? 0;
        $highPotentialCount = count($matrix['star']) + count($matrix['high_potential']) + count($matrix['potential_gem']);

        return response()->json([
            'matrix' => $matrix,
            'summary' => [
                'total_employees' => count($employees),
                'high_potential_count' => $highPotentialCount,
                'avg_performance' => round($avgPerformance, 1),
                'avg_potential' => round($avgPotential, 1),
                'avg_three_sixty' => round($avgPotential, 1),
                'type' => 'unified'
            ],
            'visualization_data' => $allPoints
        ]);
    }

    private function getKaryawanIdsFromFilters($filters)
    {
        $query = karyawan::where('status_aktif', '1')
            ->whereNotNull('nama_lengkap')
            ->whereNot('jabatan', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNotNull('nip')
            ->whereNot('divisi', 'Direksi');

        if (!empty($filters['divisi'])) $query->where('divisi', $filters['divisi']);
        if (!empty($filters['jabatan'])) $query->where('jabatan', $filters['jabatan']);
        if (!empty($filters['id_karyawan'])) $query->where('id', $filters['id_karyawan']);

        return $query->pluck('id')->toArray();
    }

    private function extractAllProgressData($targets, $filters = [])
    {
        $progressData = [];
        $processedTargets = [];

        foreach ($targets as $target) {
            foreach ($target->detailTargetKPI as $detail) {
                $assignedPersons = $detail->detailPersonKPI
                    ->whereIn('id_karyawan', $this->getKaryawanIdsFromFilters($filters))
                    ->groupBy('id_karyawan');

                if ($assignedPersons->isEmpty()) continue;

                foreach ($assignedPersons as $personId => $assignments) {
                    $targetKey = $target->id . '_' . $detail->id . '_' . $personId;
                    if (isset($processedTargets[$targetKey])) continue;
                    $processedTargets[$targetKey] = true;

                    $result = $this->getCalculationByRoute($target, $personId);

                    if (!$result || !isset($result['progress'])) continue;

                    $rawProgress = $this->normalizeNumber($result['progress']);
                    $percent = max(0, min(100, round($rawProgress, 2)));

                    $monthlyProgress = $result['monthly_progress'] ?? [];

                    foreach ($monthlyProgress as $month => $mp) {
                        if (is_numeric($mp) && $mp >= 0 && $mp <= 100) {
                            $progressData[] = [
                                'target_id' => $target->id,
                                'employee_id' => $personId,
                                'divisi' => $detail->divisi ?? null,
                                'jabatan' => $detail->jabatan ?? null,
                                'period' => $month,
                                'period_type' => 'monthly',
                                'progress' => (float) $percent,
                                'created_at' => $target->created_at
                            ];
                        }
                    }
                }
            }
        }

        return collect($progressData);
    }

    private function calculateCompletionRate($targets)
    {
        if ($targets->isEmpty()) return 0;
        $completed = $targets->filter(
            fn($t) => in_array(strtolower($t->status ?? ''), ['completed', 'selesai', 'done'])
        )->count();
        return round(($completed / $targets->count()) * 100, 2);
    }

    private function getTopDivisions($progressData, $limit = 5)
    {
        return $progressData->groupBy('divisi')
            ->map(fn($items) => round($items->pluck('progress')->filter()->avg() ?? 0, 1))
            ->filter()
            ->sortDesc()
            ->take($limit)
            ->toArray();
    }

    private function calculateTrendMetrics($progressData, $granularity)
    {
        if ($progressData->isEmpty()) {
            return [
                'count' => 0,
                'avg_progress' => 0,
                'median_progress' => 0,
                'total_targets' => 0,
                'completed' => 0,
                'std_deviation' => 0,
                'trend_direction' => 'stable',
                'trend_delta' => 0,
                'periods' => []
            ];
        }

        $grouped = $progressData->groupBy(function ($item) use ($granularity) {
            $date = Carbon::parse($item['period']);
            return match ($granularity) {
                'monthly' => $date->format('Y-m'),
                'quarterly' => $date->format('Y') . '-Q' . ceil($date->month / 3),
                'yearly' => $date->format('Y'),
                default => $date->format('Y-m')
            };
        });

        $metrics = [];
        foreach ($grouped as $period => $items) {
            $progressValues = $items->pluck('progress')->filter(fn($v) => $v !== null && is_numeric($v));

            $sorted = $progressValues->sort()->values();
            $count = $sorted->count();
            $middle = floor(($count - 1) / 2);
            $median = $count % 2
                ? round($sorted->get($middle), 2)
                : round(($sorted->get($middle) + $sorted->get($middle + 1)) / 2, 2);

            $mean = $progressValues->avg();
            $variance = $progressValues->map(fn($v) => pow($v - $mean, 2))->avg();
            $stdDev = sqrt($variance);

            $metrics[$period] = [
                'avg_progress' => $progressValues->isNotEmpty() ? round($progressValues->avg(), 2) : 0,
                'median_progress' => $median,
                'total_targets' => $items->unique('target_id')->count(),
                'completed' => $progressValues->filter(fn($v) => $v >= 100)->count(),
                'std_deviation' => $progressValues->isNotEmpty() ? round($stdDev, 2) : 0,
                'min_progress' => $progressValues->min() ?? 0,
                'max_progress' => $progressValues->max() ?? 0,
            ];
        }

        ksort($metrics);

        $periods = array_keys($metrics);
        $metrics['trend_direction'] = 'stable';
        $metrics['trend_delta'] = 0;

        if (count($periods) >= 2) {
            $lastPeriod = end($periods);
            $prevPeriod = $periods[count($periods) - 2];

            $last = $metrics[$lastPeriod]['avg_progress'];
            $prev = $metrics[$prevPeriod]['avg_progress'];

            if ($last > $prev + 1) $metrics['trend_direction'] = 'up';
            elseif ($last < $prev - 1) $metrics['trend_direction'] = 'down';

            $metrics['trend_delta'] = round($last - $prev, 2);
        }

        $metrics['count'] = $progressData->unique('target_id')->count();
        $metrics['periods'] = array_values($periods);

        return $metrics;
    }

    private function calculatePeriodComparison($filters, $allProgressData)
    {
        $currentYear = $filters['tahun'] ?? now()->year;
        $previousYear = $currentYear - 1;

        $currentData = $allProgressData->filter(fn($d) => Carbon::parse($d['period'])->year == $currentYear);
        $previousData = $allProgressData->filter(fn($d) => Carbon::parse($d['period'])->year == $previousYear);

        $currentAvg = $currentData->pluck('progress')->filter()->avg() ?? 0;
        $previousAvg = $previousData->pluck('progress')->filter()->avg() ?? 0;

        $change = $previousAvg > 0 ? round((($currentAvg - $previousAvg) / $previousAvg) * 100, 1) : 0;

        return [
            'current_period' => [
                'year' => $currentYear,
                'avg_progress' => round($currentAvg, 1),
                'total_data_points' => $currentData->count(),
                'unique_targets' => $currentData->unique('target_id')->count()
            ],
            'previous_period' => [
                'year' => $previousYear,
                'avg_progress' => round($previousAvg, 1),
                'total_data_points' => $previousData->count(),
                'unique_targets' => $previousData->unique('target_id')->count()
            ],
            'change_percentage' => $change,
            'trend_label' => $change > 2 ? 'improving' : ($change < -2 ? 'declining' : 'stable')
        ];
    }

    private function generateTrendInsights($trendData)
    {
        $insights = [];

        if (isset($trendData['trend_direction'])) {
            if ($trendData['trend_direction'] === 'up' && ($trendData['trend_delta'] ?? 0) > 5) {
                $insights[] = 'Trend kinerja menunjukkan peningkatan signifikan (' . $trendData['trend_delta'] . '%)';
            } elseif ($trendData['trend_direction'] === 'down' && ($trendData['trend_delta'] ?? 0) < -5) {
                $insights[] = 'Perlu perhatian: trend kinerja mengalami penurunan (' . $trendData['trend_delta'] . '%)';
            }
        }

        if (isset($trendData['std_deviation']) && $trendData['std_deviation'] > 20) {
            $insights[] = 'Variasi kinerja antar target cukup tinggi (σ=' . $trendData['std_deviation'] . '), pertimbangkan standarisasi';
        }

        if (isset($trendData['completed'], $trendData['count']) && $trendData['count'] > 0) {
            $completionRate = round(($trendData['completed'] / $trendData['count']) * 100, 1);
            if ($completionRate < 50) {
                $insights[] = 'Tingkat penyelesaian target masih di bawah 50% (' . $completionRate . '%)';
            } elseif ($completionRate >= 80) {
                $insights[] = 'Tingkat penyelesaian target sangat baik (' . $completionRate . '%)';
            }
        }

        if (isset($trendData['avg_progress']) && $trendData['avg_progress'] < 60) {
            $insights[] = 'Rata-rata progress masih di bawah target optimal (60%)';
        }

        return $insights;
    }

    private function prepareTimeSeries($progressData, $granularity = 'monthly')
    {
        if ($progressData->isEmpty()) return [];

        $grouped = $progressData->groupBy(function ($item) use ($granularity) {
            $period = $item['period'] ?? '';

            if (preg_match('/^\d{4}-\d{2}$/', $period)) return $period;

            try {
                $date = Carbon::parse($period);
                return match ($granularity) {
                    'monthly' => $date->format('Y-m'),
                    'quarterly' => $date->format('Y') . '-Q' . ceil($date->month / 3),
                    default => $date->format('Y-m')
                };
            } catch (\Exception $e) {
                return 'unknown';
            }
        });

        $timeSeries = [];
        foreach ($grouped as $period => $items) {
            if ($period === 'unknown') continue;

            $progressValues = $items->pluck('progress')
                ->filter(fn($v) => $v !== null && is_numeric($v) && $v >= 0 && $v <= 100);

            if ($progressValues->isNotEmpty()) {
                $timeSeries[$period] = round($progressValues->avg(), 1);
            }
        }

        ksort($timeSeries);
        return $timeSeries;
    }

    private function applyLinearRegression($dataPoints)
    {
        $n = count($dataPoints);
        if ($n < 2) return ['next' => null, 'next_3' => [], 'slope' => 0];

        $x = array_keys($dataPoints);
        $y = array_values($dataPoints);
        $xNumeric = range(0, $n - 1);

        $sumX = array_sum($xNumeric);
        $sumY = array_sum($y);
        $sumXY = array_sum(array_map(fn($i) => $xNumeric[$i] * $y[$i], range(0, $n - 1)));
        $sumX2 = array_sum(array_map(fn($i) => $xNumeric[$i] * $xNumeric[$i], range(0, $n - 1)));

        $denominator = ($n * $sumX2 - $sumX * $sumX);
        if ($denominator == 0) {
            $b = 0;
            $a = $sumY / $n;
        } else {
            $b = ($n * $sumXY - $sumX * $sumY) / $denominator;
            $a = ($sumY - $b * $sumX) / $n;
        }

        $nextIndex = $n;
        $nextValue = max(0, min(100, $a + $b * $nextIndex));

        $next3 = [];
        for ($i = 1; $i <= 3; $i++) {
            $val = $a + $b * ($nextIndex + $i);
            $next3[] = round(max(0, min(100, $val)), 1);
        }

        return [
            'next' => round($nextValue, 1),
            'next_3' => $next3,
            'slope' => round($b, 3),
            'intercept' => round($a, 3)
        ];
    }

    private function calculatePredictionConfidence($historicalData, $predictions)
    {
        if (count($historicalData) < 3) return 0.3;

        $values = array_values($historicalData);
        $nonZeroValues = array_filter($values, fn($v) => $v > 0);

        if (empty($nonZeroValues)) return 0.3;

        $mean = array_sum($nonZeroValues) / count($nonZeroValues);
        $variance = array_sum(array_map(fn($v) => pow($v - $mean, 2), $nonZeroValues)) / count($nonZeroValues);
        $stdDev = sqrt($variance);

        $cv = $mean > 0 ? $stdDev / $mean : 1;
        $confidence = max(0, min(1, 1 - $cv));

        $dataPoints = count($nonZeroValues);
        $dataFactor = min(1, $dataPoints / 12);

        return max(0.3, min(0.95, $confidence * 0.6 + $dataFactor * 0.4));
    }

    private function generateRecommendations($predictions, $confidence)
    {
        $recommendations = [];

        if ($predictions['slope'] > 0.5) {
            $recommendations[] = 'Pertahankan strategi saat ini, trend menunjukkan peningkatan konsisten';
        } elseif ($predictions['slope'] < -0.5) {
            $recommendations[] = 'Evaluasi pendekatan saat ini, pertimbangkan intervensi strategis untuk membalikkan trend';
        } elseif ($predictions['slope'] > 0) {
            $recommendations[] = 'Trend positif namun moderat, optimalkan eksekusi untuk akselerasi';
        }

        if ($confidence < 0.6) {
            $recommendations[] = 'Data historis belum cukup konsisten untuk prediksi akurat, kumpulkan lebih banyak data';
        } elseif ($confidence >= 0.8) {
            $recommendations[] = 'Prediksi memiliki tingkat kepercayaan tinggi, dapat dijadikan dasar perencanaan';
        }

        if ($predictions['next'] < 70) {
            $recommendations[] = 'Fokus pada peningkatan kualitas eksekusi target untuk mencapai threshold optimal (70%)';
        } elseif ($predictions['next'] >= 90) {
            $recommendations[] = 'Kinerja diprediksi sangat baik, pertimbangkan target yang lebih menantang';
        }

        return $recommendations;
    }

    //==========================
    // PERFOMANCE DASHBOARD HR
    //==========================

    public function performanceDashboard()
    {
        $divisiList = karyawan::whereNotNull('divisi')
            ->where('divisi', '!=', '')
            ->where('divisi', '!=', 'Direksi')
            ->distinct()->pluck('divisi')->filter()->values();

        $jabatanList = karyawan::whereNotNull('jabatan')
            ->where('jabatan', '!=', '')
            ->whereNotIn('jabatan', ['Direktur Utama', 'Direktur', 'Outsource', 'Pilih Jabatan'])
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNotNull('nip')
            ->where('divisi', '!=', 'Direksi')
            ->distinct()->pluck('jabatan')->filter()->values();

        return view('HR.performance.dashboard', compact('divisiList', 'jabatanList'));
    }

    public function getPerformanceDashboardData(Request $request)
    {
        $tahun = $request->input('tahun', now()->year);
        $divisi = $request->input('divisi');
        $jabatan = $request->input('jabatan');
        $search = $request->input('search');

        $query = karyawan::where('status_aktif', '1')
            ->whereNot('jabatan', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNotNull('nip')
            ->whereNot('divisi', 'Direksi');

        if ($divisi) $query->where('divisi', $divisi);
        if ($jabatan) $query->where('jabatan', $jabatan);
        if ($search) $query->where('nama_lengkap', 'LIKE', "%{$search}%");

        $karyawans = $query->orderBy('nama_lengkap')->get();

        $users = [];
        foreach ($karyawans as $emp) {
            // Get KPI details
            $kpiDetails = $this->getKPIDetails($emp->id, $tahun);
            $kpiScore = $this->calculatePerformanceScore($emp, $tahun);

            // Get 360 assessment details
            $assessment360Details = $this->getAssessment360Details($emp->id, $tahun);
            $score360 = $this->calculateThreeSixtyScore($emp->id, $tahun);

            $users[] = [
                'id' => $emp->id,
                'nama' => $emp->nama_lengkap,
                'jabatan' => $emp->jabatan,
                'divisi' => $emp->divisi,
                'kpi' => [
                    'score' => round($kpiScore, 1),
                    'grade' => $this->getGradeLabel($kpiScore),
                    'details' => $kpiDetails
                ],
                'assessment_360' => [
                    'score' => round($score360, 1),
                    'grade' => $this->getGradeLabel($score360),
                    'details' => $assessment360Details
                ]
            ];
        }

        return response()->json([
            'success' => true,
            'total' => count($users),
            'users' => $users
        ]);
    }

    private function getKPIDetails($karyawanId, $tahun)
    {
        $targets = targetKPI::with(['detailTargetKPI.detailPersonKPI', 'detailTargetKPI.dataTarget'])
            ->whereYear('created_at', $tahun)
            ->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($karyawanId) {
                $q->where('id_karyawan', $karyawanId);
            })
            ->get();

        // ✅ DAFTAR ROUTE YANG BERSIFAT GENERAL / PERUSAHAAN / DIVISI
        // Target ini akan mengambil total data keseluruhan, tidak peduli siapa yang login/ditugaskan
        $generalRoutes = [
            'pemasukan kotor',
            'pemasukan bersih',
            'target penjualan project tahunan',
            'target penjualan tahunan',
            'rasio biaya operasional terhadap revenue',
            'meningkatkan revenue perusahaan',
            'customer acquisition cost',
            'biaya akuisisi perclient',
            'performa kpi departemen',
            'kepuasan pelanggan',
            'peserta puas dengan pelayanan dan fasilitas training',
            'penanganan komplain perseta',
            'penanganan komplain peserta',
            'outstanding',
            'laporan analisis keuangan',
            'pencairan biaya operasional',
            'penyelesaian tagihan perusahaan',
            'akurasi pencatatan masuk',
            'pelaksanaan kegiatan karyawan',
            'pengeluaran biaya karyawan',
            'perbaikan kendaraan',
            'kontrol pengeluaran transportasi',
            'report kondisi kendaraan',
            'feedback kenyamanan berkendaran',
            'feedback kebersihan dan kenyamanan',
            'kepuasan client itsm',
            'inovation adaption rate',
            'availability sistem internal kritis',
            'meningkatkan kepuasan dan loyalitas peserta/client',
            'persentase gap kompetensi tim terhadap standar skill',
            'ketepatan waktu penyelesaian fitur',
            'mengukur kualitas aplikasi agar minim bug',
            'konsistensi campaign digital',
            'efektifitas digital marketing',
            'keberhasilan support memenuhi sla',
            'kualitas layanan exam',
            'kepuasan peserta pelatihan',
            'upseling lanjutan materi',
            'presentase kinerja instruktur',
            'pengembangan kurikulum pelatihan',
            'peningkatan knowledge sharing',
            'peningkatan kontribusi pelatihan',
            'evaluasi kinerja instruktur',
            'evaluasi kinerja sales',
            'laporan mom',
            'akurasi kelengkapan data penjualan',
            'ketepatan waktu po',
            'kualitas dokumentasi support dan proctor',
        ];

        $details = [];
        foreach ($targets as $target) {
            foreach ($target->detailTargetKPI as $detail) {
                $assignedPersons = $detail->detailPersonKPI
                    ->where('id_karyawan', $karyawanId);

                if ($assignedPersons->isEmpty()) continue;

                $targetForCalc = $this->prepareTargetForCalculation($target, $detail);

                $asistantRoute = strtolower($detail->dataTarget?->asistant_route ?? '');

                // ✅ LOGIKA PENTING: 
                // Jika route ada di daftar general, kirim NULL agar ambil data total perusahaan.
                // Jika tidak ada di daftar (misal: sertifikasi, tugas harian), kirim $karyawanId agar tetap individual.
                $personIdForCalc = in_array($asistantRoute, $generalRoutes) ? null : $karyawanId;

                $rawProgress = $this->calculateProgressByRoute($asistantRoute, $targetForCalc, $personIdForCalc);

                $nilaiTarget = $detail->dataTarget?->nilai_target ?? $detail->nilai_target;
                $tipeTarget  = $detail->tipe_target;

                // Hitung persentase berdasarkan tipe target
                $percent = 0;
                if ($rawProgress > 0 && $nilaiTarget > 0) {
                    if ($tipeTarget === 'rupiah' || $tipeTarget === 'angka') {
                        $percent = ($rawProgress / $nilaiTarget) * 100;
                    } else {
                        $percent = $rawProgress;
                    }
                }

                $percent = max(0, min(100, round($percent, 2)));

                Log::info("KPI Detail Progress", [
                    'target_id' => $target->id,
                    'detail_id' => $detail->id,
                    'detail_jangka' => $detail->detail_jangka,
                    'judul' => $target->judul,
                    'asistant_route' => $asistantRoute,
                    'is_general' => is_null($personIdForCalc), // Debugging: true jika general
                    'raw_progress' => $rawProgress,
                    'nilai_target' => $nilaiTarget,
                    'percent' => $percent
                ]);

                $details[] = [
                    'judul'            => $target->judul,
                    'asistant_route'   => $detail->dataTarget?->asistant_route ?? '-',
                    'tipe_target'      => $tipeTarget,
                    'nilai_target'     => $nilaiTarget,
                    'progress'         => $percent,
                    'progress_display' => $this->formatProgressDisplay($rawProgress, $tipeTarget),
                    'status'           => $percent >= 100 ? 'Selesai' : ($percent > 0 ? 'Aktif' : 'Belum Mulai')
                ];
            }
        }
        return $details;
    }

    /**
     * ✅ METHOD BARU: Siapkan target untuk kalkulasi
     * Memastikan detail yang sedang diproses menjadi "first" di detailTargetKPI
     */
    private function prepareTargetForCalculation($target, $currentDetail)
    {
        // Buat clone target
        $targetClone = clone $target;

        // Ambil semua detail dan reorder agar currentDetail menjadi first
        $details = $target->detailTargetKPI->values()->all();
        $currentIndex = -1;

        foreach ($details as $index => $detail) {
            if ($detail->id === $currentDetail->id) {
                $currentIndex = $index;
                break;
            }
        }

        // Jika currentDetail bukan first, swap dengan first
        if ($currentIndex > 0) {
            $temp = $details[0];
            $details[0] = $details[$currentIndex];
            $details[$currentIndex] = $temp;
        }

        // Set relation dengan collection yang sudah di-reorder
        $targetClone->setRelation('detailTargetKPI', collect($details));

        return $targetClone;
    }

    /**
     * Hitung progress berdasarkan asistant_route dari $detail yang sedang diproses
     */
    private function calculateProgressByRoute(string $asistantRoute, $target, $personId)
    {
        return match ($asistantRoute) {
            'kepuasan pelanggan' => $this->calculateProgressKepuasanPelanggan($target, $personId),
            'pemasukan kotor' => $this->calculatePemasukanKotor($target, $personId),
            'pemasukan bersih' => $this->calculatePemasukanBersih($target, $personId),
            'target penjualan project tahunan' => $this->calculateTargetPenjualanProjectTahunan($target, $personId),
            'rasio biaya operasional terhadap revenue' => $this->calculateRasioBiayaOperasionalTerhadapRevenue($target, $personId),
            'performa kpi departemen' => $this->calculatePerformaKPIDepartemen($target, $personId),
            'peserta puas dengan pelayanan dan fasilitas training' => $this->calculatePesertaPuasDenganPelayananDanFasilitasTraining($target, $personId),
            'dorong inovasi pelayanan' => $this->calculateDorongInovasiPelayanan($target, $personId),
            'penanganan komplain perseta', 'penanganan komplain peserta' => $this->calculatePenangananKomplainPerseta($target, $personId),
            'report persiapan kelas' => $this->calculateReportPersiapanKelas($target, $personId),
            'outstanding' => $this->calculateOutstanding($target, $personId),
            'inisiatif efisiensi keuangan' => $this->calculateInisiatifEfisiensiKeuangan($target, $personId),
            'mengurangi manual work dan error' => $this->calculateMengurangiManualWorkDanError($target, $personId),
            'laporan analisis keuangan' => $this->calculateLaporanAnalisisKeuangan($target, $personId),
            'pencairan biaya operasional' => $this->calculatePencairanBiayaOperasional($target, $personId),
            'penyelesaian tagihan perusahaan' => $this->calculatePenyelesaianTagihanPerusahaan($target, $personId),
            'akurasi pencatatan masuk' => $this->calculateAkurasiPencatatanMasuk($target, $personId),
            'pelaksanaan kegiatan karyawan' => $this->calculatePelaksanaanKegiatanKaryawan($target, $personId),
            'pengeluaran biaya karyawan' => $this->calculatePengeluaranBiayaKaryawan($target, $personId),
            'administrasi karyawan' => $this->calculateAdministrasiKaryawan($target, $personId),
            'perbaikan kendaraan' => $this->calculatePerbaikanKendaraan($target, $personId),
            'kontrol pengeluaran transportasi' => $this->calculateKontrolPengeluaranTransportasi($target, $personId),
            'report kondisi kendaraan' => $this->calculateReportKondisiKendaraan($target, $personId),
            'feedback kenyamanan berkendaran' => $this->calculateFeedbackKenyamananBerkendara($target, $personId),
            'ketepatan waktu po' => $this->calculateKetepatanWaktuPo($target, $personId),
            'kualitas dokumentasi support dan proctor' => $this->calculatekualitasDokumentasiSupportDanProctor($target, $personId),
            'feedback kebersihan dan kenyamanan' => $this->calculateFeedbackKebersihanDanKenyamanan($target, $personId),
            'penyelesaian tugas harian' => $this->calculatePenyelesaianTugasHarian($target, $personId),
            'kepuasan client itsm' => $this->calculateProgressKepuasanClientITSM($target, $personId),
            'inovation adaption rate' => $this->calculateInovationAdaptionRate($target, $personId),
            'availability sistem internal kritis' => $this->calculateAvailabilitySistemInternalKritis($target, $personId),
            'meningkatkan kepuasan dan loyalitas peserta/client' => $this->calculateMeningkatkanKepuasanDanLoyalitasPeserta($target, $personId),
            'persentase gap kompetensi tim terhadap standar skill' => $this->calculatePersentaseGapKompetensi($target, $personId),
            'ketepatan waktu penyelesaian fitur' => $this->calculateProgressKetepatanWaktuPenyelesaianFitur($target, $personId),
            'mengukur kualitas aplikasi agar minim bug' => $this->calculateMengukurKualitasAplikasiAgarMinimBug($target, $personId),
            'konsistensi campaign digital' => $this->calculateKonsistensiCampaignDigital($target, $personId),
            'efektifitas digital marketing' => $this->calculateEfektifitasDiitalMarketing($target, $personId),
            'keberhasilan support memenuhi sla' => $this->calculateTingkatKeberhasilanSupportMemenuhiSLA($target, $personId),
            'kualitas layanan exam' => $this->calculateKualitasLayananExam($target, $personId),
            'kepuasan peserta pelatihan' => $this->calculateKepuasanPesertaPelatihan($target, $personId),
            'upseling lanjutan materi' => $this->calculateUpselingLanjutanMateri($target, $personId),
            'sertifikasi kompetensi internal' => $this->calculateSertifikasiKompetensiInternal($target, $personId),
            'pelatihan kompetensi eksternal' => $this->calculatePelatihanKompetensiEksternal($target, $personId),
            'presentase kinerja instruktur' => $this->calculatePresentaseKinerjaInstruktur($target, $personId),
            'pengembangan kurikulum pelatihan' => $this->calculatePengembanganKurikulumPelatihan($target, $personId),
            'peningkatan knowledge sharing' => $this->calculatePeningkatanKnowledgeSharing($target, $personId),
            'peningkatan kontribusi pelatihan' => $this->calculatePeningkatanKontribusiPelatihan($target, $personId),
            'evaluasi kinerja instruktur' => $this->calculateEvaluasiKinerjaInstruktur($target, $personId),
            'target penjualan tahunan' => $this->calculateTargetPenjualanTahunan($target, $personId),
            'peningkatan kemampuan kompetensi sales' => $this->calculatePeningkatanKemampuanKompetensiSales($target, $personId),
            'customer acquisition cost' => $this->calculateCustomerAcquisitionCost($target, $personId),
            'meningkatkan revenue perusahaan' => $this->calculateMeningkatkanRevenuePerusahaan($target, $personId),
            'evaluasi kinerja sales' => $this->calculateEvaluasiKinerjaSales($target, $personId),
            'biaya akuisisi perclient' => $this->calculateBiayaAkuisisiClient($target, $personId),
            'laporan mom' => $this->calculateLaporanMOM($target, $personId),
            'akurasi kelengkapan data penjualan' => $this->calculateAkurasiKelengkapanDataPenjualan($target, $personId),
            'todo administrasi' => $this->calculateTodoAdministrasi($target),
            default => 0
        };
    }

    // Helper untuk memformat tampilan teks progress
    private function formatProgressDisplay($rawProgress, $tipeTarget)
    {
        if ($rawProgress <= 0) return '-';

        if ($tipeTarget === 'rupiah') {
            return 'Rp ' . number_format((float)$rawProgress, 0, ',', '.');
        } elseif ($tipeTarget === 'persen') {
            return round($rawProgress, 2) . '%';
        }

        return number_format((float)$rawProgress, 0, ',', '.');
    }

    private function getAssessment360Details($karyawanId, $tahun)
    {
        $persentaseJenis = [
            'General Manager' => 35,
            'Manager/SPV/Team Leader (Atasan Langsung)' => 30,
            'Rekan Kerja (Satu Divisi)' => 20,
            'Pekerja (Beda Divisi)' => 10,
            'Self Apprisial' => 5,
        ];

        $allNilaiKPI = nilaiKPI::where('id_evaluated', $karyawanId)
            ->whereYear('created_at', $tahun)
            ->get();

        $details = [];
        foreach ($persentaseJenis as $jenis => $bobot) {
            $nilaiForJenis = $allNilaiKPI->where('jenis_penilaian', $jenis)
                ->pluck('nilai')
                ->filter(fn($n) => is_numeric($n) && $n > 0);

            if ($nilaiForJenis->isNotEmpty()) {
                $avgNilai = $nilaiForJenis->avg();
                $score = ($avgNilai * $bobot) / 100;

                $details[] = [
                    'jenis_penilaian' => $jenis,
                    'bobot' => $bobot,
                    'rata_rata_nilai' => round($avgNilai, 1),
                    'score' => round($score, 1),
                    'jumlah_evaluator' => $nilaiForJenis->count()
                ];
            }
        }

        return $details;
    }

    public function getAssessment360DetailTab(Request $request)
    {
        $request->validate([
            'id_karyawan' => 'required|integer',
            'tahun'       => 'required|integer'
        ]);

        $id_karyawan = $request->input('id_karyawan');
        $tahun       = $request->input('tahun');

        $karyawan = karyawan::find($id_karyawan);
        if (!$karyawan) {
            return response()->json(['message' => 'Karyawan tidak ditemukan'], 404);
        }

        // Ambil semua form penilaian 360 untuk tahun tersebut (semua jenis form)
        $formPenilaians = formPenilaian::with('karyawan')
            ->where('id_karyawan', $id_karyawan)
            ->where('tahun', $tahun)
            ->get();

        if ($formPenilaians->isEmpty()) {
            $availableYears = formPenilaian::where('id_karyawan', $id_karyawan)
                ->select('tahun', 'quartal', 'jenis_form')
                ->distinct()
                ->orderBy('tahun', 'desc')
                ->get();

            return response()->json([
                'success' => false,
                'message' => "Belum ada data penilaian 360 untuk tahun {$tahun}",
                'debug' => [
                    'id_karyawan' => $id_karyawan,
                    'tahun_dipilih' => $tahun,
                    'tahun_tersedia' => $availableYears->pluck('tahun')->unique()->values(),
                ]
            ], 404);
        }

        // Group by quartal
        $groupedByQuartal = $formPenilaians->groupBy('quartal')->sortKeysDesc();
        $firstForm = $formPenilaians->first();

        // Info evaluated
        $evaluated = [
            'nama'        => optional($firstForm->karyawan)->nama_lengkap . ' - ' . (optional($firstForm->karyawan)->divisi ?? '-'),
            'id_karyawan' => $firstForm->id_karyawan,
            'quartal'     => $firstForm->quartal,
            'tahun'       => $firstForm->tahun,
            'catatan'     => $firstForm->catatan ?? '-',
            'kode_form'   => $firstForm->kode_form,
        ];

        // Data absensi untuk tahun tersebut
        $dataAbsensi = AbsensiKaryawan::where('id_karyawan', $id_karyawan)
            ->whereYear('created_at', $tahun)
            ->get();

        $dataAbsen = [
            'sakit' => $dataAbsensi->where('keterangan', 'Sakit')->count(),
            'telat' => $dataAbsensi->where('keterangan', 'Telat')->count(),
            'izin'  => $dataAbsensi->where('keterangan', 'Izin')->count(),
        ];

        // Ambil semua kategori dari semua form
        $kodeKategoriList = $formPenilaians->pluck('kode_kategori')->unique();
        $allKategoriKPIs = kategoriKPI::whereIn('kode_kategori', $kodeKategoriList)->get();

        // Ambil semua evaluator
        $kodeFormList = $formPenilaians->pluck('kode_form')->unique();
        $allEvaluatorData = shareForm::with('evaluator')
            ->where('id_evaluated', $id_karyawan)
            ->whereIn('kode_form', $kodeFormList)
            ->get();

        // Build evaluator list dengan detail nilai
        $evaluatorList = [];
        foreach ($allEvaluatorData as $evaluatorItem) {
            $jenis_penilaian = $evaluatorItem->jenis_penilaian;
            $id_evaluator    = $evaluatorItem->id_evaluator;

            $nilaiKPIByEvaluator = nilaiKPI::where('id_evaluated', $id_karyawan)
                ->whereIn('kode_form', $kodeFormList)
                ->where('id_evaluator', $id_evaluator)
                ->where('jenis_penilaian', $jenis_penilaian)
                ->get();

            $groupedByKategori = $nilaiKPIByEvaluator->groupBy('name_variabel');

            $listNilaiEvaluator = [];
            foreach ($allKategoriKPIs as $kategori) {
                $nilaiItem = $groupedByKategori->get($kategori->judul_kategori);

                if ($nilaiItem && $nilaiItem->count() > 0) {
                    $firstItem = $nilaiItem->first();
                    $listNilaiEvaluator[] = [
                        'pesan' => $firstItem->pesan ?? '-',
                        'nilai' => $firstItem->nilai ?? '-'
                    ];
                } else {
                    $listNilaiEvaluator[] = [
                        'pesan' => '-',
                        'nilai' => '-'
                    ];
                }
            }

            $evaluatorList[] = [
                'nama'            => optional($evaluatorItem->evaluator)->nama_lengkap . ' - ' . (optional($evaluatorItem->evaluator)->divisi ?? '-'),
                'jenis_penilaian' => $evaluatorItem->jenis_penilaian ?? '-',
                'nilai'           => $listNilaiEvaluator
            ];
        }

        $evaluatorList = collect($evaluatorList)
            ->unique(fn($item) => $item['nama'] . $item['jenis_penilaian'])
            ->values();

        // Build data kriteria
        $dataKriteria = $formPenilaians
            ->groupBy(fn($item) => $item->kode_form . '|' . $item->nama_penilaian)
            ->map(function ($groupedForms, $combinedKey) {
                [$kodeForm, $namaPenilaian] = explode('|', $combinedKey);

                $kategoriKPIs = $groupedForms->flatMap(function ($form) {
                    return kategoriKPI::where('kode_kategori', $form->kode_kategori)->get();
                })->unique('judul_kategori')->values();

                $detailKriteria = $kategoriKPIs->map(function ($kategori) {
                    $tipeDetails = tipeKategoriTabel::where('id_kategori', $kategori->id)->get();

                    return [
                        'sub_kriteria' => $kategori->judul_kategori,
                        'bobot'        => $kategori->bobot,
                        'tipe_input'   => $kategori->tipe_kategori,
                        'detailTipeSubKriteria' => $tipeDetails->map(fn($tipe) => [
                            'ket_sub_tipe'       => $tipe->ket_tipe,
                            'nilai_ket_sub_tipe' => $tipe->nilai_ket_sub_tipe
                        ])->toArray()
                    ];
                });

                return [
                    'kriteria'       => $namaPenilaian,
                    'kodeForm'       => $kodeForm,
                    'detailKriteria' => $detailKriteria
                ];
            })
            ->values()
            ->toArray();

        // Chart data
        $persentaseJenis = [
            'General Manager' => 35,
            'Manager/SPV/Team Leader (Atasan Langsung)' => 30,
            'Rekan Kerja (Satu Divisi)' => 20,
            'Pekerja (Beda Divisi)' => 10,
            'Self Apprisial' => 5,
        ];

        $chartData = [];
        foreach ($groupedByQuartal as $quartal => $forms) {
            $kodeForms = $forms->pluck('kode_form')->unique();
            $evaluators = shareForm::where('id_evaluated', $id_karyawan)
                ->whereIn('kode_form', $kodeForms)
                ->get()
                ->groupBy('jenis_penilaian');

            $totalSkor = 0;
            foreach ($evaluators as $jenis => $evalGroup) {
                $bobotJenis = $persentaseJenis[$jenis] ?? 0;
                if ($bobotJenis === 0) continue;

                $skorJenis = 0;
                foreach ($allKategoriKPIs as $kategori) {
                    $nilaiPerEvaluator = [];
                    foreach ($evalGroup as $eval) {
                        $itemNilai = nilaiKPI::where('id_evaluator', $eval->id_evaluator)
                            ->where('id_evaluated', $id_karyawan)
                            ->where('kode_form', $eval->kode_form)
                            ->where('jenis_penilaian', $jenis)
                            ->where('name_variabel', $kategori->judul_kategori)
                            ->whereNotNull('nilai')
                            ->first();
                        if ($itemNilai && is_numeric($itemNilai->nilai)) {
                            $nilaiPerEvaluator[] = (float) $itemNilai->nilai;
                        }
                    }
                    $avgNilai = count($nilaiPerEvaluator) > 0 ? array_sum($nilaiPerEvaluator) / count($nilaiPerEvaluator) : 0;
                    $skorJenis += $avgNilai * ($kategori->bobot / 100);
                }
                $totalSkor += ($skorJenis * $bobotJenis) / 100;
            }

            $chartData[$quartal] = number_format($totalSkor, 2, '.', '');
        }

        return response()->json([
            'success' => true,
            'data' => [[
                'evaluated' => $evaluated,
                'dataAbsen' => $dataAbsen,
                'data' => [
                    'evaluator'    => $evaluatorList,
                    'dataKriteria' => $dataKriteria,
                ],
                'chart' => [
                    'quartal' => $chartData,
                    'all'     => []
                ]
            ]],
            'karyawan' => [
                'nama'    => $karyawan->nama_lengkap,
                'jabatan' => $karyawan->jabatan,
                'divisi'  => $karyawan->divisi,
                'foto'    => $karyawan->foto ? asset('storage/' . $karyawan->foto) : asset('assets/img/avatars/1.png'),
            ]
        ]);
    }

    private function calculatePerformanceScore($employee, $tahun)
    {
        $karyawanIds = [$employee->id];
        $targets = targetKPI::with(['detailTargetKPI.dataTarget'])
            ->whereYear('created_at', $tahun)
            ->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($karyawanIds) {
                $q->whereIn('id_karyawan', $karyawanIds);
            })
            ->get();

        if ($targets->isEmpty()) return 0;

        $generalRoutes = [
            'pemasukan kotor',
            'pemasukan bersih',
            'target penjualan project tahunan',
            'target penjualan tahunan',
            'rasio biaya operasional terhadap revenue',
            'meningkatkan revenue perusahaan',
            'customer acquisition cost',
            'biaya akuisisi perclient',
            'performa kpi departemen',
            'kepuasan pelanggan',
            'peserta puas dengan pelayanan dan fasilitas training',
            'penanganan komplain perseta',
            'penanganan komplain peserta',
            'outstanding',
            'laporan analisis keuangan',
            'pencairan biaya operasional',
            'penyelesaian tagihan perusahaan',
            'akurasi pencatatan masuk',
            'pelaksanaan kegiatan karyawan',
            'pengeluaran biaya karyawan',
            'perbaikan kendaraan',
            'kontrol pengeluaran transportasi',
            'report kondisi kendaraan',
            'feedback kenyamanan berkendaran',
            'feedback kebersihan dan kenyamanan',
            'kepuasan client itsm',
            'inovation adaption rate',
            'availability sistem internal kritis',
            'meningkatkan kepuasan dan loyalitas peserta/client',
            'persentase gap kompetensi tim terhadap standar skill',
            'ketepatan waktu penyelesaian fitur',
            'mengukur kualitas aplikasi agar minim bug',
            'konsistensi campaign digital',
            'efektifitas digital marketing',
            'keberhasilan support memenuhi sla',
            'kualitas layanan exam',
            'kepuasan peserta pelatihan',
            'upseling lanjutan materi',
            'presentase kinerja instruktur',
            'pengembangan kurikulum pelatihan',
            'peningkatan knowledge sharing',
            'peningkatan kontribusi pelatihan',
            'evaluasi kinerja instruktur',
            'evaluasi kinerja sales',
            'laporan mom',
            'akurasi kelengkapan data penjualan',
            'ketepatan waktu po',
            'kualitas dokumentasi support dan proctor',
        ];

        $allProgressValues = [];
        $processedTargets = [];

        foreach ($targets as $target) {
            foreach ($target->detailTargetKPI as $detail) {
                $assignedIds = $detail->detailPersonKPI
                    ->whereIn('id_karyawan', $karyawanIds)
                    ->pluck('id_karyawan')
                    ->unique()
                    ->toArray();

                if (empty($assignedIds)) continue;

                foreach ($assignedIds as $personId) {
                    $targetKey = $target->id . '_' . $detail->id . '_' . $personId;
                    if (isset($processedTargets[$targetKey])) continue;
                    $processedTargets[$targetKey] = true;

                    $targetForCalc = $this->prepareTargetForCalculation($target, $detail);
                    $asistantRoute = strtolower($detail->dataTarget?->asistant_route ?? '');

                    $personIdForCalc = in_array($asistantRoute, $generalRoutes) ? null : $personId;

                    $result = $this->getCalculationByRoute($targetForCalc, $personIdForCalc);
                    if (!$result || !isset($result['progress'])) continue;

                    $rawProgress = (float) $result['progress'];
                    $nilaiTarget = (float) ($detail->dataTarget?->nilai_target ?? $detail->nilai_target);
                    $tipeTarget = $detail->tipe_target;

                    $percent = 0;
                    if ($rawProgress > 0 && $nilaiTarget > 0) {
                        if ($tipeTarget === 'rupiah' || $tipeTarget === 'angka') {
                            $percent = ($rawProgress / $nilaiTarget) * 100;
                        } else {
                            $percent = $rawProgress;
                        }
                    }

                    $percent = max(0, min(100, round($percent, 2)));
                    $allProgressValues[] = $percent;
                }
            }
        }

        if (empty($allProgressValues)) return 0;
        return round(array_sum($allProgressValues) / count($allProgressValues), 2);
    }

    private function calculateThreeSixtyScore($employeeId, $tahun)
    {
        $persentaseJenis = [
            'General Manager' => 35,
            'Manager/SPV/Team Leader (Atasan Langsung)' => 30,
            'Rekan Kerja (Satu Divisi)' => 20,
            'Pekerja (Beda Divisi)' => 10,
            'Self Apprisial' => 5,
        ];

        $allNilaiKPI = nilaiKPI::where('id_evaluated', $employeeId)
            ->whereYear('created_at', $tahun)
            ->get();

        if ($allNilaiKPI->isEmpty()) return 0;

        $jenisTotalRaw = [];
        foreach ($persentaseJenis as $jenis => $bobot) {
            $nilaiForJenis = $allNilaiKPI->where('jenis_penilaian', $jenis)
                ->pluck('nilai')
                ->filter(fn($n) => is_numeric($n) && $n > 0);

            if ($nilaiForJenis->isNotEmpty()) {
                $avgNilai = $nilaiForJenis->avg();
                $jenisTotalRaw[$jenis] = ($avgNilai * $bobot) / 100;
            }
        }

        return empty($jenisTotalRaw) ? 0 : round(array_sum($jenisTotalRaw), 2);
    }
}
