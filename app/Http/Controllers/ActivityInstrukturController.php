<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityInstruktur;
use App\Models\karyawan;
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
                return redirect()->route('activities.index')->with(['error' => 'Aktivitas tidak ditemukan atau Anda tidak berhak mengubahnya.']);
            }

            // Jika entri sudah dikunci di DB, TOLAK (validasi ganda)
            if ($activity->is_locked) {
                return redirect()->route('activities.index')->with(['error' => 'Aktivitas ini sudah ditandai terkunci di database']);
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

        // 1. Validasi Input Kunci
        $request->validate([
            'activity_id' => 'required|exists:activity_instrukturs,id',
            'doc'         => 'required|url', // Validasi input harus berupa format URL (http/https)
        ]);

        $activityId = $request->activity_id;
        $activity = ActivityInstruktur::find($activityId);

        // 2. Cek Kepemilikan dan Eksistensi
        if (!$activity || $activity->user_id != $instructorId) {
            return redirect()->route('activities.index')->with(['error' => 'Aktivitas tidak ditemukan atau Anda tidak berhak mengubahnya.']);
        }

        $activityDate = Carbon::parse($activity->activity_date);
        
        // 3. Cek Status Locking (Guard Rail Server-Side)
        $weekEndDate = $activityDate->copy()->endOfWeek(Carbon::SUNDAY);
        $lockThreshold = $weekEndDate->addDays(7); 
        
        if (Carbon::now()->gt($lockThreshold)) {
            return redirect()->route('activities.index')->with(['error' => 'Aktivitas ini sudah ditandai terkunci di database']);
        }

        // 4. Lakukan Update Link
        try {
            $activity->update([
                'doc'          => $request->doc, // Simpan langsung string URL dari input
                'status'       => 'Selesai',
                'completed_at' => now(), 
            ]);

            return redirect()->route('activities.index')->with(['success' => 'Link bukti aktivitas berhasil disimpan.']);

        } catch (\Exception $e) {
            return redirect()->route('activities.index')->with(['error' => 'Gagal menyimpan data: ' . $e->getMessage()]);
        }
    }

    
}


