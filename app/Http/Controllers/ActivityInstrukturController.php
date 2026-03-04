<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityInstruktur;
use App\Models\izinTigaJam;
use App\Models\karyawan;
use App\Models\pengajuancuti;
use App\Models\RKM; // Pastikan Anda memiliki model RKM
use Illuminate\Support\Facades\Storage; // Import Storage facade
use Illuminate\Support\Str;
class ActivityInstrukturController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        return view('activityinstruktur.index');
    }

    private function syncTeachingActivities($userId, $start, $end)
    {
        $rkms = RKM::where(function ($q) use ($userId) {
            $q->where('instruktur_key', $userId)
            ->orWhere('instruktur_key2', $userId);
        })
        ->where('tanggal_awal', '<=', $end)
        ->where('tanggal_akhir', '>=', $start)
        ->get();

        foreach ($rkms as $rkm) {
            $date = Carbon::parse($rkm->tanggal_awal);
            $endDate = Carbon::parse($rkm->tanggal_akhir);

            while ($date->lte($endDate)) {
                ActivityInstruktur::firstOrCreate([
                    'user_id' => $userId,
                    'activity_date' => $date->toDateString(),
                    'id_rkm' => $rkm->id,
                ], [
                    'activity' => $rkm->materi_key,
                    'desc' => 'Mengajar (RKM)',
                    'status' => 'On Progress',
                ]);
                $date->addDay();
            }
        }
    }

    private function isWeekLocked($date)
    {
        $weekEnd = \Carbon\Carbon::parse($date)->endOfWeek(\Carbon\Carbon::SUNDAY);
        return now()->gt($weekEnd->addDays(7));
    }


    public function getActivitiesData(Request $request)
    {
        $user = auth()->user();
        $userId = $user->id;
        $karyawan = karyawan::findOrFail($userId);
        $instructorId = $karyawan->kode_karyawan;
        $Eduman = 'AD';

        $start = Carbon::parse($request->start)->toDateString();
        $end   = Carbon::parse($request->end)->toDateString();

        $events = [];
        // dd($Eduman, $instructorId);

        /*
        |--------------------------------------------------------------------------
        | 1. AKTIVITAS MANUAL (DISIMPAN)
        |--------------------------------------------------------------------------
        */
        $manualQuery = ActivityInstruktur::whereNull('id_rkm')
            ->whereBetween('activity_date', [$start, $end]);

        if ($instructorId !== $Eduman) {
            $manualQuery->where('user_id', $userId);
        }

        foreach ($manualQuery->with('user.karyawan')->get() as $activity) {

            $locked = $this->isWeekLocked($activity->activity_date);
            $color  = $locked ? '#6c757d' : '#007bff';

            // Default title
            $title = $activity->activity ?: 'Aktivitas Manual';

            // 🔥 KHUSUS EDUMAN → tambahkan kode instruktur
            if ($instructorId === $Eduman) {
                $kodeInstruktur = optional($activity->user)->karyawan->kode_karyawan ?? 'AD';
                $title .= ' (' . $kodeInstruktur . ')';
            }

            $events[] = [
                'id'    => $activity->id,
                'title' => $title,
                'start' => $activity->activity_date,
                'allDay' => true,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'type' => 'manual',
                    'is_locked' => $locked,
                    'desc' => $activity->desc,
                    'activity_type' => $activity->activity_type,
                    'status' => $activity->status,
                    'doc' => $activity->doc ?? null,

                ]
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | 2. RKM (HANYA TAMPILAN)
        |--------------------------------------------------------------------------
        */
        $rkmQuery = RKM::where('tanggal_awal', '<=', $end)
            ->where('tanggal_akhir', '>=', $start)
            ->with('materi');

        if ($instructorId !== $Eduman) {
            $rkmQuery->where(function ($q) use ($instructorId) {
                $q->where('instruktur_key', $instructorId)
                ->orWhere('instruktur_key2', $instructorId);
            });
        }

        $rkmGrouped = [];

        foreach ($rkmQuery->get() as $rkm) {

            // Cek apakah Key 1 Kosong / Null / Strip
            $noInstruktur1 = empty($rkm->instruktur_key) || $rkm->instruktur_key === '-';
            
            // Cek apakah Key 2 Kosong / Null / Strip (Opsional, jika sistem Anda pakai 2 instruktur)
            $noInstruktur2 = empty($rkm->instruktur_key2) || $rkm->instruktur_key2 === '-';

            // JIKA tidak ada instruktur sama sekali, lewati (jangan ditampilkan)
            if ($noInstruktur1 && $noInstruktur2) {
                continue; 
            }

            // 🔑 KEY UNIK UNTUK GROUPING
            $groupKey = implode('|', [
                $rkm->materi_id,
                $rkm->tanggal_awal,
                $rkm->tanggal_akhir,
                $rkm->instruktur_key
            ]);

            if (!isset($rkmGrouped[$groupKey])) {
                $rkmGrouped[$groupKey] = [
                    'rkm' => $rkm,
                    'count' => 1
                ];
            } else {
                $rkmGrouped[$groupKey]['count']++;
            }
        }

        foreach ($rkmGrouped as $group) {

            $rkm   = $group['rkm'];
            $count = $group['count'];

            $title = optional($rkm->materi)->nama_materi ?? $rkm->materi_key;

            // 🔥 EDUMAN → tampilkan kode instruktur
            if ($instructorId === $Eduman) {
                $title .= ' (' . $rkm->instruktur_key . ')';
            }

            $events[] = [
                'id' => 'rkm-group-' . md5($rkm->id),
                'title' => $title,
                'start' => $rkm->tanggal_awal,
                'end' => Carbon::parse($rkm->tanggal_akhir)->addDay(),
                'allDay' => true,
                'backgroundColor' => '#28a745',
                'borderColor' => '#28a745',
                'extendedProps' => [
                    'type' => 'rkm',
                    'grouped' => true,
                    'total_kelas' => $count,
                    'materi' => optional($rkm->materi)->nama_materi ?? $rkm->materi_key,
                    'tanggal_awal' => $rkm->tanggal_awal,
                    'tanggal_akhir' => $rkm->tanggal_akhir,
                    'metode_kelas' => $rkm->metode_kelas,
                ]
            ];
        }
        /*
        |--------------------------------------------------------------------------
        | 3. CUTI & SAKIT (ADDED)
        |--------------------------------------------------------------------------
        */
        $cutiQuery = pengajuancuti::with('karyawan')
        // Pastikan ada relasi ke model karyawan
            ->whereHas('karyawan', function($q) {
                // Filter berdasarkan kolom jabatan di tabel karyawan
                $q->whereIn('jabatan', ['Instruktur', 'Education Manager']);
            })
            ->where('tanggal_awal', '<=', $end)
            ->where('tanggal_akhir', '>=', $start);
        // return $cutiQuery->get();
        // Jika bukan Eduman, hanya ambil data cuti diri sendiri
        if ($instructorId !== $Eduman) {
            $cutiQuery->where('id_karyawan', $userId);
        }
        
        // Opsional: Filter hanya yang disetujui
        // $cutiQuery->where('approval_manager', 1); 

        foreach ($cutiQuery->get() as $cuti) {
            
            // Tentukan Warna & Label berdasarkan Tipe
            // Asumsi kolom 'tipe' berisi string "Cuti" atau "Sakit"
            $isSakit = stripos($cuti->tipe, 'sakit') !== false; 
            
            // Merah untuk Sakit, Oranye untuk Cuti
            $bgColor = $isSakit ? '#dc3545' : '#fd7e14'; 
            // $icon    = $isSakit ? '🏥' : '✈️';
            
            $title = "{$cuti->tipe} : {$cuti->alasan}";

            // Jika Eduman, tambahkan nama karyawan
            if ($instructorId === $Eduman) {
                $namaKaryawan = optional($cuti->karyawan)->nama_lengkap ?? 'Unknown';
                $title = "$namaKaryawan - $title";
            }

            $events[] = [
                'id' => 'cuti-' . $cuti->id,
                'title' => $title,
                'start' => $cuti->tanggal_awal,
                // FullCalendar 'end' bersifat eksklusif, jadi harus +1 hari agar range visualnya benar
                'end' => Carbon::parse($cuti->tanggal_akhir)->addDay()->toDateString(),
                'allDay' => true,
                'backgroundColor' => $bgColor,
                'borderColor' => $bgColor,
                'extendedProps' => [
                    'type' => 'cuti',
                    'alasan' => $cuti->alasan,
                    'approval' => $cuti->approval_manager,
                    'tipe' => $cuti->tipe,
                    'durasi' => $cuti->durasi
                ]
            ];
        }
        /*
        |--------------------------------------------------------------------------
        | 4. IZIN 3 JAM (KHUSUS INSTRUKTUR & EDUCATION MANAGER)
        |--------------------------------------------------------------------------
        */
        $izin3JamQuery = izinTigaJam::with('karyawan')
            ->whereHas('karyawan', function($q) {
                // Filter Jabatan
                $q->whereIn('jabatan', ['Instruktur', 'Education Manager']);
            })
            ->where('approval', '1')
            // Filter Tanggal (Karena izin 3 jam biasanya 1 hari, cukup cek tanggal pengajuan/pelaksanaan)
            ->whereBetween('tanggal', [$start, $end]);

        // Jika bukan Eduman, hanya ambil data diri sendiri
        if ($instructorId !== $Eduman) {
            $izin3JamQuery->where('id_karyawan', $userId);
        }
        
        // Opsional: Filter Approval
        // $izin3JamQuery->where('approval', 1);

        foreach ($izin3JamQuery->get() as $izin) {
            
            // Format Judul: [Izin 3 Jam] 09:00-12:00 Alasan...
            $jamRange = substr($izin->jam_mulai, 0, 5) . '-' . substr($izin->jam_selesai, 0, 5);
            $title = "[Izin 3 Jam] ($jamRange): {$izin->alasan}";

            // Jika Eduman, tambahkan nama karyawan di depan
            if ($instructorId === $Eduman) {
                $namaKaryawan = optional($izin->karyawan)->nama_lengkap ?? 'Unknown';
                $title = "$namaKaryawan - $title";
            }

            // Warna Ungu untuk membedakan dengan Cuti/Sakit/Mengajar
            $bgColor = '#6f42c1'; 

            $events[] = [
                'id' => 'izin3jam-' . $izin->id,
                'title' => $title,
                'start' => $izin->tanggal, 
                // Karena izin jam biasanya 1 hari, 'end' bisa disamakan atau tidak diisi (allDay true)
                // Jika ingin spesifik jam, set allDay false dan masukkan start/end datetime lengkap
                // Tapi agar rapi di view 'Month', kita set allDay true saja.
                'allDay' => true, 
                'backgroundColor' => $bgColor,
                'borderColor' => $bgColor,
                'extendedProps' => [
                    'type' => 'izin_3jam',
                    'alasan' => $izin->alasan,
                    'approval' => $izin->approval,
                    'jam_mulai' => $izin->jam_mulai,
                    'jam_selesai' => $izin->jam_selesai,
                    'durasi' => $izin->durasi
                ]
            ];
        }

        return response()->json($events);
    }



    public function store(Request $request)
    {
        $instructorId = Auth::user()->id;
        // dd($request->all());
        // 1. Validasi Input Dasar
         $validator = $request->validate([
            'activity_date' => 'required|date',
            'activity' => 'required|string|max:255',
            'desc' => 'nullable|string',
            // Tambahkan validasi untuk 'doc' jika Anda mengimplementasikan upload file
        ]);

        $activityDate = Carbon::parse($request->activity_date);

        // 2. Cek Status Locking (Guard Rail Server-Side)
        
        // Tentukan akhir minggu dari tanggal aktivitas yang dikirim
        // $weekEndDate = $activityDate->copy()->endOfWeek(Carbon::SUNDAY);
        
        // Tentukan ambang batas kunci: 7 hari setelah akhir minggu
        // $lockThreshold = $weekEndDate->addDays(7); 
        
        // if (Carbon::now()->gt($lockThreshold)) {
        //     // Jika hari ini sudah melewati ambang batas kunci, TOLAK
        //     return response()->json([
        //         'message' => 'Laporan Aktivitas untuk minggu tanggal ' . $activityDate->format('d M Y') . ' sudah dikunci dan tidak dapat diubah.'
        //     ], 403); 
        // }

        // 3. Tentukan Aksi: CREATE atau UPDATE
        $activityId = $request->activity_id;

        if ($activityId) {
            // Aksi: UPDATE
            $activity = ActivityInstruktur::find($activityId);

            if (!$activity || $activity->user_id != $instructorId) {
                return redirect()->route('activities.index')->with(['error' => 'Aktivitas tidak ditemukan atau Anda tidak berhak mengubahnya.']);
            }

            // Jika entri sudah dikunci di DB, TOLAK (validasi ganda)
            // if ($activity->is_locked) {
            //     return redirect()->route('activities.index')->with(['error' => 'Aktivitas ini sudah ditandai terkunci di database']);
            // }
            
            // Khusus Aktivitas Mengajar (RKM):
            // Jangan izinkan perubahan pada kolom 'activity' jika itu adalah aktivitas RKM
            $updateData = [
                'desc' => $request->desc,
            ];

            if (!$activity->id_rkm) {
                // Jika ini Aktivitas Manual, izinkan perubahan pada judul
                $updateData['activity'] = $request->activity;
            } else {
                // Jika RKM, pastikan Judul tetap menggunakan data yang sudah di-auto-fill, 
                // kecuali jika Anda ingin instruktur bisa meng-override, namun disarankan tidak.
            }

            $activity->update($updateData);

        } else {
            // Aksi: CREATE (Hanya untuk Aktivitas Non-Mengajar/Manual)
            
            // Cek apakah sudah ada aktivitas RKM untuk tanggal tersebut, jika ya, buat entry baru manual.
            
            // Catatan: Jika instruktur mengklik tanggal yang sama dua kali, ini akan membuat dua entri. 
            // Pertimbangkan apakah Anda ingin membatasi 1 entri manual per hari.
            
            $activity = ActivityInstruktur::create([
                'user_id' => $instructorId,
                'activity_date' => $request->activity_date,
                'activity' => $request->activity,
                'activity_type' => $request->activity_type,
                'desc' => $request->desc,
                'status' => 'On Progres', // Default status untuk aktivitas manual baru
                'is_locked' => 0,
                'on_progress_at' => now(),
                'id_rkm' => null, // Pastikan id_rkm kosong untuk aktivitas manual
                // 'doc' => $fileName, // Tambahkan logic upload file di sini
            ]);
        }
        return redirect()->route('activities.index')->with(['success' => 'Laporan aktivitas berhasil disimpan/diperbarui.']);

    }

    public function update(Request $request)
    {
        $instructorId = Auth::user()->id;

        // 1. Validasi Input
        // Kita gunakan 'sometimes' agar validasi hanya jalan jika field tersebut ada (dikirim form)
        $validate = $request->validate([
            'activity_id'   => 'required|exists:activity_instrukturs,id',
            'doc'           => 'nullable|url', // Bisa null jika hanya edit judul, tapi jika diisi harus URL
            'activity'      => 'sometimes|required|string|max:255',
            'activity_type' => 'sometimes|required|string',
            'desc'          => 'nullable|string',
        ]);

        // dd($validate);

        $activityId = $request->activity_id;
        $activity = ActivityInstruktur::find($activityId);

        // 2. Cek Kepemilikan
        if (!$activity || $activity->user_id != $instructorId) {
            return redirect()->route('activities.index')->with(['error' => 'Aktivitas tidak ditemukan atau akses ditolak.']);
        }

        // 3. Cek Locking
        $activityDate = Carbon::parse($activity->activity_date);
        $weekEndDate = $activityDate->copy()->endOfWeek(Carbon::SUNDAY);
        $lockThreshold = $weekEndDate->addDays(7); 
        
        // if (Carbon::now()->gt($lockThreshold)) {
        //     return redirect()->route('activities.index')->with(['error' => 'Aktivitas ini sudah terkunci.']);
        // }

        try {
            // 4. Siapkan Data Update
            $dataToUpdate = [];

            // Update Dokumen & Status (Jika user mengisi link dokumen)
            if ($request->filled('doc')) {
                $dataToUpdate['doc'] = $request->doc;
                
                // Jika sebelumnya belum selesai, tandai selesai
                if ($activity->status !== 'Selesai') {
                    $dataToUpdate['status'] = 'Selesai';
                    $dataToUpdate['completed_at'] = now();
                }
            }

            // Update Data Aktivitas (Jika Mode Edit ON dan input dikirim)

            if ($request->has('activity')) {
                // Ambil input mentah
                $rawActivity = $request->activity;

                // Gunakan Regex untuk menghapus spasi dan tanda kurung di akhir string
                // Pola: \s* (spasi opsional) + \( (kurung buka) + [^)]+ (isi apapun) + \) (kurung tutup) + $ (akhir baris)
                $cleanActivity = preg_replace('/\s*\([^)]+\)$/', '', $rawActivity);

                // Simpan hasil yang sudah bersih
                $dataToUpdate['activity'] = trim($cleanActivity);
            }
            if ($request->has('activity_type') && $request->activity_type !== 'pilih') {
                $dataToUpdate['activity_type'] = $request->activity_type;
            }
            if ($request->has('desc')) {
                $dataToUpdate['desc'] = $request->desc;
            }

            // Eksekusi Update
            $activity->update($dataToUpdate);

            return redirect()->route('activities.index')->with(['success' => 'Data aktivitas berhasil diperbarui.']);

        } catch (\Exception $e) {
            return redirect()->route('activities.index')->with(['error' => 'Gagal menyimpan: ' . $e->getMessage()]);
        }
    }

    // File: app/Http/Controllers/ActivityInstrukturController.php

    public function getSummaryData(Request $request)
    {
        $user = auth()->user();
        $userId = $user->id;
        $karyawan = karyawan::findOrFail($userId);
        $instructorId = $karyawan->kode_karyawan;
        $Eduman = 'AD';

        $start = Carbon::parse($request->start)->toDateString();
        $end   = Carbon::parse($request->end)->toDateString();

        // ==========================================
        // 1. DETAIL AKTIVITAS MANUAL
        // ==========================================
        $manualQuery = ActivityInstruktur::with('user.karyawan') // Eager load relasi
            ->whereNull('id_rkm')
            ->whereBetween('activity_date', [$start, $end]);

        if ($instructorId !== $Eduman) {
            $manualQuery->where('user_id', $userId);
        }

        $manualActivities = $manualQuery->get();

        // Grouping: Tipe Aktivitas -> Nama Instruktur
        $manualDetails = $manualActivities->groupBy(function($item) {
            // Group Level 1: Activity Type (Contoh: "Sharing Knowledge")
            return $item->activity_type ?? 'Lainnya';
        })->map(function($group) {
            
            // Group Level 2: Nama User di dalam tipe tersebut
            $users = $group->groupBy(function($item) {
                // Pastikan kolom 'nama_lengkap' atau 'nama_karyawan' sesuai database Anda
                return optional(optional($item->user)->karyawan)->nama_lengkap 
                    ?? optional($item->user)->name ?? 'Unknown';
            })->map(function($userGroup) {
                return $userGroup->count(); // Hitung jumlah per orang
            });

            return [
                'total'       => $group->count(),
                'selesai'     => $group->where('status', 'Selesai')->count(),
                'on_progress' => $group->where('status', '!=', 'Selesai')->count(),
                'users'       => $users // Data ini akan dipakai JS untuk list di bawah judul
            ];
        });

        // ==========================================
        // 2. DETAIL MENGAJAR (RKM)
        // ==========================================
        $rkmQuery = RKM::where('tanggal_awal', '<=', $end)
            ->where('tanggal_akhir', '>=', $start)
            ->whereNotNull('instruktur_key')
            ->where('instruktur_key', '!=', '-');

        if ($instructorId !== $Eduman) {
            $rkmQuery->where(function ($q) use ($instructorId) {
                $q->where('instruktur_key', $instructorId)
                ->orWhere('instruktur_key2', $instructorId);
            });
        }

        $rkms = $rkmQuery->get();

        // Mapping Kode Instruktur ke Nama
        $allCodes = $rkms->pluck('instruktur_key')
                        ->merge($rkms->pluck('instruktur_key2'))
                        ->unique()
                        ->filter();
        
        $karyawanMap = karyawan::whereIn('kode_karyawan', $allCodes)
                            ->pluck('nama_lengkap', 'kode_karyawan'); 

        // --- BAGIAN INI YANG DIUBAH (LOGIKA GROUPING) ---
        $rkmDetails = [];
        $processedEvents = []; // Array untuk melacak duplikasi

        foreach ($rkms as $rkm) {
            
            // -----------------------------------------
            // LOGIKA UTAMA: Buat ID Unik
            // ID = NamaInstruktur + MateriID + Tanggal
            // -----------------------------------------

            // 1. Cek Instruktur 1
            if (!empty($rkm->instruktur_key) && $rkm->instruktur_key !== '-') {
                $name = $karyawanMap[$rkm->instruktur_key] ?? $rkm->instruktur_key;
                
                if ($name !== '-') {
                    // Kunci Unik: Gabungan Nama, Materi, dan Tanggal
                    // Ini memastikan jika Instruktur SAMA, Materi SAMA, Tanggal SAMA -> Dianggap 1
                    // Tapi jika Instruktur BEDA -> Kunci uniknya beda -> Dihitung terpisah
                    $uniqueId = $name . '|' . $rkm->materi_id . '|' . $rkm->tanggal_awal;

                    // Cek apakah kombinasi ini sudah dihitung?
                    if (!isset($processedEvents[$uniqueId])) {
                        if (!isset($rkmDetails[$name])) $rkmDetails[$name] = 0;
                        $rkmDetails[$name]++; // Hitung +1
                        
                        $processedEvents[$uniqueId] = true; // Tandai sudah dihitung
                    }
                }
            }
            
            // 2. Cek Instruktur 2 (Pendamping)
            if (!empty($rkm->instruktur_key2) && $rkm->instruktur_key2 !== '-') {
                $name = $karyawanMap[$rkm->instruktur_key2] ?? $rkm->instruktur_key2;
                
                if ($name !== '-') {
                    // Buat ID Unik untuk Instruktur 2
                    $uniqueId = $name . '|' . $rkm->materi_id . '|' . $rkm->tanggal_awal;

                    if (!isset($processedEvents[$uniqueId])) {
                        if (!isset($rkmDetails[$name])) $rkmDetails[$name] = 0;
                        $rkmDetails[$name]++;
                        
                        $processedEvents[$uniqueId] = true;
                    }
                }
            }
        }

        // Safety Net
        if (isset($rkmDetails['-'])) {
            unset($rkmDetails['-']);
        }

        // Filter User Biasa
        if ($instructorId !== $Eduman) {
            $myName = $karyawan->nama_lengkap; 
            $rkmDetails = array_intersect_key($rkmDetails, [$myName => 0]);
        }

        arsort($rkmDetails);

        // Hitung Total Berdasarkan Hasil Grouping (Bukan count baris DB mentah)
        $totalRkmGrouped = array_sum($rkmDetails);

        // ==========================================
        // 3. SUMMARY CUTI & SAKIT
        // ==========================================
        $cutiQuery = pengajuancuti::with('karyawan')
            ->whereHas('karyawan', function($q) {
                $q->whereIn('jabatan', ['Instruktur', 'Education Manager']);
            })
            ->where('tanggal_awal', '<=', $end)
            ->where('tanggal_akhir', '>=', $start);

        if ($instructorId !== $Eduman) {
            $cutiQuery->where('id_karyawan', $userId);
        }
        
        $cutiData = $cutiQuery->get();

        // Pisahkan Data Cuti dan Sakit
        $listCuti  = [];
        $listSakit = [];

        foreach ($cutiData as $row) {
            $nama = optional($row->karyawan)->nama_lengkap ?? 'Unknown';
            
            // -----------------------------------------------------------
            // LOGIKA HITUNG HARI (INTERSECTION)
            // -----------------------------------------------------------
            // Kita hitung hari yang HANYA tampil di rentang filter ($start s/d $end)
            // Ini menangani kasus cuti lintas bulan.
            
            $reqStart = Carbon::parse($start);
            $reqEnd   = Carbon::parse($end);
            $cutiStart = Carbon::parse($row->tanggal_awal);
            $cutiEnd   = Carbon::parse($row->tanggal_akhir);

            // Cari tanggal mulai yang efektif (Pilih mana yang lebih akhir: Start Filter atau Start Cuti)
            $effectiveStart = $cutiStart->greaterThan($reqStart) ? $cutiStart : $reqStart;

            // Cari tanggal selesai yang efektif (Pilih mana yang lebih awal: End Filter atau End Cuti)
            $effectiveEnd = $cutiEnd->lessThan($reqEnd) ? $cutiEnd : $reqEnd;

            // Hitung selisih hari (+1 agar tanggal yang sama dihitung 1 hari)
            $days = $effectiveEnd->diffInDays($effectiveStart) + 1;

            // Safety net: Pastikan hari tidak negatif (jika ada data aneh)
            if ($days < 0) $days = 0;

            // -----------------------------------------------------------
            // AKUMULASI KE LIST
            // -----------------------------------------------------------

            // Cek tipe: Jika mengandung kata 'sakit', masuk list Sakit
            if (stripos($row->tipe, 'sakit') !== false) {
                if (!isset($listSakit[$nama])) $listSakit[$nama] = 0;
                $listSakit[$nama] += $days; // Tambahkan jumlah hari
            } else {
                // Selain itu masuk list Cuti
                if (!isset($listCuti[$nama])) $listCuti[$nama] = 0;
                $listCuti[$nama] += $days; // Tambahkan jumlah hari
            }
        }

        // ==========================================
        // 4. SUMMARY IZIN 3 JAM
        // ==========================================
        $izinQuery = izinTigaJam::with('karyawan')
            ->whereHas('karyawan', function($q) {
                $q->whereIn('jabatan', ['Instruktur', 'Education Manager']);
            })
            ->whereBetween('tanggal', [$start, $end]);

        if ($instructorId !== $Eduman) {
            $izinQuery->where('id_karyawan', $userId);
        }

        $izinData = $izinQuery->get();
        $listIzin = [];

        foreach ($izinData as $row) {
            $nama = optional($row->karyawan)->nama_lengkap ?? 'Unknown';
            if (!isset($listIzin[$nama])) $listIzin[$nama] = 0;
            $listIzin[$nama]++;
        }

        // ==========================================
        // 5. RESPONSE JSON LENGKAP
        // ==========================================
        return response()->json([
            'manual_summary' => [
                'total_all' => $manualActivities->count(),
                'details'   => $manualDetails
            ],
            'rkm_summary' => [
                'total_all' => $totalRkmGrouped,
                'details'   => $rkmDetails
            ],
            // --- TAMBAHAN SUMMARY BARU ---
            'cuti_summary' => [
                'total_all' => array_sum($listCuti),
                'details'   => $listCuti
            ],
            'sakit_summary' => [
                'total_all' => array_sum($listSakit),
                'details'   => $listSakit
            ],
            'izin_summary' => [
                'total_all' => array_sum($listIzin),
                'details'   => $listIzin
            ]
        ]);
    }

    
}


