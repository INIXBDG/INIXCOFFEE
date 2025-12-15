<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityInstruktur;
use App\Models\RKM; // Pastikan Anda memiliki model RKM

class ActivityInstrukturController extends Controller
{
    public function index()
    {
        // 1. Dapatkan Instruktur ID yang sedang login
        $instructorId = Auth::user()->id;
        
        // 2. Tentukan Rentang Minggu Saat Ini
        $date = Carbon::now();
        // Set awal dan akhir minggu (Senin - Minggu)
        $weekStartDate = $date->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $weekEndDate = $date->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

        // 3. Tentukan Status Locking (untuk minggu saat ini)
        // Logika Locking: Kunci jika sudah lebih dari 7 hari setelah akhir minggu
        $lockDateThreshold = Carbon::parse($weekEndDate)->addDays(7);
        $isLockedForCurrentWeek = Carbon::now()->gt($lockDateThreshold);
        
        // 4. Ambil Jadwal Kelas (RKM) Instruktur untuk Minggu Ini
        // Kita perlu mencari RKM yang rentang tanggalnya bersinggungan dengan minggu ini.
        $rkmSchedules = RKM::where(function ($query) use ($instructorId) {
                // Instruktur bisa di kolom instruktur_key atau instruktur_key2
                $query->where('instruktur_key', $instructorId)
                      ->orWhere('instruktur_key2', $instructorId);
            })
            // RKM aktif yang rentang tanggalnya bersinggungan dengan minggu ini
            ->where('tanggal_awal', '<=', $weekEndDate)
            ->where('tanggal_akhir', '>=', $weekStartDate)
            ->get();

        // 5. Otomatisasi (Auto-Fill) Activity Report berdasarkan RKM
        // Jika ada jadwal, otomatis buat/update activity_instrukturs per hari dalam rentang RKM
        $this->autoFillTeachingActivities($rkmSchedules, $instructorId);


        // 6. Ambil Data Activity Instruktur untuk Tampilan Kalender
        // Ambil semua aktivitas (manual/otomatis) instruktur untuk bulan ini
        $activities = ActivityInstruktur::where('user_id', $instructorId)
                                        ->whereBetween('activity_date', [
                                            Carbon::now()->startOfMonth(), 
                                            Carbon::now()->endOfMonth()
                                        ])
                                        ->get();


        return view('activityinstruktur.index', compact(
            'weekStartDate', 
            'weekEndDate', 
            'isLockedForCurrentWeek', 
            'rkmSchedules',
            'activities' // Data yang akan di-render di FullCalendar
        ));
    }
    
    /**
     * Metode untuk mengisi otomatis laporan aktivitas mengajar
     */
    private function autoFillTeachingActivities($rkmSchedules, $instructorId)
    {
        foreach ($rkmSchedules as $rkm) {
            $startDate = Carbon::parse($rkm->tanggal_awal);
            $endDate = Carbon::parse($rkm->tanggal_akhir);

            // Iterasi dari tanggal awal RKM hingga tanggal akhir RKM
            $currentDate = $startDate;
            while ($currentDate->lte($endDate)) {
                
                // Pastikan itu bukan hari Sabtu/Minggu jika diasumsikan hari kerja, atau sesuaikan dengan kebutuhan Anda
                // if ($currentDate->isWeekday()) { 
                    
                    // Cek apakah sudah ada aktivitas untuk hari ini dan RKM ini
                    $existingActivity = ActivityInstruktur::where('user_id', $instructorId)
                        ->where('activity_date', $currentDate->toDateString())
                        ->where('id_rkm', $rkm->id)
                        ->first();
                        
                    // Jika belum ada, buat aktivitas baru secara otomatis
                    if (!$existingActivity) {
                        ActivityInstruktur::create([
                            'user_id' => $instructorId,
                            'activity' => 'Mengajar Kelas: ' . $rkm->materi_key, // Ganti dengan nama materi yang sebenarnya
                            'desc' => 'Kelas RKM ID: ' . $rkm->id . ', ' . $rkm->metode_kelas,
                            'activity_date' => $currentDate->toDateString(),
                            'status' => 'Selesai', // Atau 'On Progres' tergantung kebijakan
                            'id_rkm' => $rkm->id,
                            'is_locked' => 0, // Belum dikunci
                            'completed_at' => now(),
                        ]);
                    }
                // }
                
                $currentDate->addDay();
            }
        }
    }

    // app/Http/Controllers/ActivityInstrukturController.php


    public function getActivitiesData(Request $request)
    {
        $instructorId = Auth::user()->id;
        
        $start = Carbon::parse($request->start)->toDateString();
        $end = Carbon::parse($request->end)->toDateString();

        // Ambil data dari activity_instrukturs, dan lakukan EAGER LOAD relasi RKM
        $activities = ActivityInstruktur::where('user_id', $instructorId)
            ->whereBetween('activity_date', [$start, $end])
            ->with('rkm') // Eager load relasi RKM
            ->get();
        dd($activities);
        $events = [];

        foreach ($activities as $activity) {
            $isLocked = $activity->is_locked;
            
            $backgroundColor = '#007bff'; // Biru: Default (Manual)
            $title = $activity->activity ?: 'Aktivitas Manual';
            
            // Default start date adalah tanggal aktivitas harian
            $eventStart = $activity->activity_date; 
            
            // Variabel opsional untuk extendedProps jika event ingin ditampilkan membentang
            // $eventEnd = null; 

            // Cek jika ini adalah aktivitas mengajar (Auto-Fill) dan RKM tersedia
            if ($activity->id_rkm && $activity->rkm) {
                $rkm = $activity->rkm;
                $backgroundColor = '#28a745'; // Hijau: Aktivitas Mengajar
                
                // Override Title dengan nama materi RKM jika kolom 'activity' kosong/default
                if (!$activity->activity || str_contains($activity->activity, 'RKM ID')) {
                    // Asumsi 'materi_key' di RKM adalah nama materi/kelas
                    $title = 'Kls: ' . $rkm->materi_key; 
                }
                
                // Catatan: 'start' tetap menggunakan activity_date karena laporan ini per hari.
                // Jika Anda ingin event di kalender membentang selama durasi kelas penuh, 
                // Anda bisa menggunakan tanggal_awal dan tanggal_akhir RKM sebagai berikut:
                
                $eventStart = $rkm->tanggal_awal; 
                $eventEnd = Carbon::parse($rkm->tanggal_akhir)->addDay()->format('Y-m-d'); // FullCalendar end date exclusive
                // Namun, ini akan mengganggu logika "per hari" dan "locking per minggu".
                // Sebaiknya biarkan 'start' = activity_date.
            }
            
            // Cek status locking
            if ($isLocked) {
                $backgroundColor = '#6c757d'; // Abu-abu: Locked
            }

            $events[] = [
                'id' => $activity->id,
                'title' => $title,
                'start' => $eventStart, 
                // 'end' => $eventEnd, // Jika menggunakan rentang RKM
                'backgroundColor' => $backgroundColor,
                'borderColor' => $backgroundColor,
                'extendedProps' => [
                    'is_locked' => $isLocked,
                    'status' => $activity->status,
                    'desc' => $activity->desc,
                    'doc' => $activity->doc,
                    'id_rkm' => $activity->id_rkm,
                    // Tambahkan detail RKM di extendedProps untuk digunakan di Modal
                    'rkm_materi' => $activity->rkm ? $activity->rkm->materi_key : null,
                    'rkm_start' => $activity->rkm ? $activity->rkm->tanggal_awal : null,
                    'rkm_end' => $activity->rkm ? $activity->rkm->tanggal_akhir : null,
                ]
            ];
        }

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $instructorId = Auth::user()->id;
        
        // 1. Validasi Input Dasar
        $validator = Validator::make($request->all(), [
            'activity_date' => 'required|date',
            'activity' => 'required|string|max:255',
            'desc' => 'nullable|string',
            // Tambahkan validasi untuk 'doc' jika Anda mengimplementasikan upload file
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal: ' . $validator->errors()->first()], 422);
        }

        $activityDate = Carbon::parse($request->activity_date);

        // 2. Cek Status Locking (Guard Rail Server-Side)
        
        // Tentukan akhir minggu dari tanggal aktivitas yang dikirim
        $weekEndDate = $activityDate->copy()->endOfWeek(Carbon::SUNDAY);
        
        // Tentukan ambang batas kunci: 7 hari setelah akhir minggu
        $lockThreshold = $weekEndDate->addDays(7); 
        
        if (Carbon::now()->gt($lockThreshold)) {
            // Jika hari ini sudah melewati ambang batas kunci, TOLAK
            return response()->json([
                'message' => 'Laporan Aktivitas untuk minggu tanggal ' . $activityDate->format('d M Y') . ' sudah dikunci dan tidak dapat diubah.'
            ], 403); 
        }

        // 3. Tentukan Aksi: CREATE atau UPDATE
        $activityId = $request->activity_id;

        if ($activityId) {
            // Aksi: UPDATE
            $activity = ActivityInstruktur::find($activityId);

            if (!$activity || $activity->user_id != $instructorId) {
                return response()->json(['message' => 'Aktivitas tidak ditemukan atau Anda tidak berhak mengubahnya.'], 404);
            }

            // Jika entri sudah dikunci di DB, TOLAK (validasi ganda)
            if ($activity->is_locked) {
                 return response()->json(['message' => 'Aktivitas ini sudah ditandai terkunci di database.'], 403);
            }
            
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
                'desc' => $request->desc,
                'status' => 'Selesai', // Default status untuk aktivitas manual baru
                'is_locked' => 0,
                'completed_at' => now(),
                'id_rkm' => null, // Pastikan id_rkm kosong untuk aktivitas manual
                // 'doc' => $fileName, // Tambahkan logic upload file di sini
            ]);
        }

        return response()->json([
            'message' => 'Laporan aktivitas berhasil disimpan/diperbarui.', 
            'activity' => $activity
        ], 200);
    }
}


