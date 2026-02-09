<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Models\DailyActivity;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DailyActivityController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();
        $karyawan = $currentUser->karyawan;

        $divisionName = 'Tidak Terdaftar';
        $activities = collect();

        $userDivisionName = null;
        $tasks = collect(); // Default: koleksi kosong

        if ($karyawan) {
            $userDivisionName = $karyawan->divisi;
        }

        if (!empty($userDivisionName)) {
            $tasks = Task::whereHas('user.karyawan', function ($query) use ($userDivisionName) {
                               $query->where('divisi', $userDivisionName);
                           })
                           ->orderBy('title')
                           ->get();
        }

        if ($karyawan) {
            $userDivisionName = $karyawan->divisi;
            $userJobTitle = $karyawan->jabatan; // Mendapatkan data jabatan user saat ini

            if (!empty($userDivisionName)) {
                $divisionName = $userDivisionName;

                $activities = DailyActivity::with(['user.karyawan', 'task'])
                    ->whereHas('user.karyawan', function ($query) use ($userDivisionName, $userJobTitle) {

                        // 1. Filter dasar: Kesamaan Divisi
                        $query->where('divisi', $userDivisionName);

                        // 2. Logika Khusus untuk 'IT Service Management'
                        if ($userDivisionName === 'IT Service Management') {

                            // Cek grup jabatan: Koordinator ITSM & Programmer
                            if (in_array($userJobTitle, ['Koordinator ITSM', 'Programmer'])) {
                                $query->whereIn('jabatan', ['Koordinator ITSM', 'Programmer']);
                            } else {
                                // Untuk jabatan lain di ITSM, filter per jabatan spesifik
                                $query->where('jabatan', $userJobTitle);
                            }
                        }
                    })
                    ->latest('start_date')
                    ->latest('created_at')
                    ->get();

            } else {
                $divisionName = 'Karyawan Tanpa Divisi';
            }
        }

        return view('daily_activities.index', compact('activities', 'divisionName', 'tasks'));
    }

    public function activitiesData()
    {
        $currentUser = Auth::user();
        $karyawan = $currentUser->karyawan;
        $activities = collect();

        $userDivisionName = $karyawan->divisi;
        $userJobTitle = $karyawan->jabatan; // Mendapatkan data jabatan user saat ini

        if (!empty($userDivisionName)) {

            $activities = DailyActivity::with(['user.karyawan', 'task'])
                ->whereHas('user.karyawan', function ($query) use ($userDivisionName, $userJobTitle) {

                    // 1. Filter dasar: Kesamaan Divisi
                    $query->where('divisi', $userDivisionName);

                    // 2. Logika Khusus untuk 'IT Service Management'
                    if ($userDivisionName === 'IT Service Management') {

                        // Cek grup jabatan: Koordinator ITSM & Programmer
                        if (in_array($userJobTitle, ['Koordinator ITSM', 'Programmer'])) {
                            $query->whereIn('jabatan', ['Koordinator ITSM', 'Programmer']);
                        } else {
                            // Untuk jabatan lain di ITSM, filter per jabatan spesifik
                            $query->where('jabatan', $userJobTitle);
                        }
                    }
                })
                ->latest('start_date')
                ->latest('created_at')
                ->get();
        };

        foreach ($activities as $activity) {
            $events[] = [
                'id' => $activity->id,
                'title' => $activity->activity,
                'start' => $activity->start_date,
                'end' => Carbon::parse($activity->end_date)->addDay(),
                'color' => match ($activity->status) {
                    'Selesai' => '#28a745',
                    'Gagal' => '#bd2e21',
                    'On Progres'  => '#0d6efd',
                    'On Progres Dilanjutkan Besok'   => '#ee811b'
                },
                'allDay' => true,
                'extendedProps' => [
                    'status' => $activity->status,
                    'id_task' => $activity->id_task,
                    'activity' => $activity->activity,
                    'description' => $activity->description,
                    'doc' => $activity->doc,
                    'start_date' => $activity->start_date->format('Y-m-d'),
                    'end_date' => $activity->end_date ? $activity->end_date->format('Y-m-d') : null,
                ]
            ]; 
        }

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_task' => 'nullable|exists:tasks,id',
            'activity' => 'required|string',
            'description' => 'nullable|string',
            'doc' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:2048',
            'start_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }

        $docPath = null;
        if ($request->hasFile('doc')) {
            $docPath = $request->file('doc')->store('activity_docs', 'public');
        }

        $dataToStore = [
            'user_id' => Auth::id(),
            'id_task' => $request->input('id_task'),
            'activity' => $request->input('activity'),
            'status' => 'On Progres',
            'description' => $request->input('description'),
            'doc' => $docPath,
            'start_date' => $request->input('start_date'),
        ];

        $activity = DailyActivity::create($dataToStore);

        $activity->updateStatus('On Progres');

        return redirect()->route('daily-activities.index')
                            ->with('success', 'Aktivitas harian berhasil ditambahkan.');
    }

    public function create()
    {
        $currentUser = Auth::user();
        $karyawan = $currentUser->karyawan;

        $userDivisionName = null;
        $tasks = collect(); // Default: koleksi kosong

        if ($karyawan) {
            $userDivisionName = $karyawan->divisi;
        }

        if (!empty($userDivisionName)) {
            $tasks = Task::whereHas('user.karyawan', function ($query) use ($userDivisionName) {
                               $query->where('divisi', $userDivisionName);
                           })
                           ->orderBy('title')
                           ->get();
        }
        return view('daily_activities.create', compact('tasks'));
    }

    public function updateStatus(Request $request, DailyActivity $dailyActivity) // Otomatis inject DailyActivity
    {
        // Validasi status baru
        $validator = Validator::make($request->all(), [
            'status' => [
                'required',
                Rule::in(['On Progres', 'On Progres Dilanjutkan Besok', 'Gagal', 'Selesai']),
            ],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->with('error', 'Gagal memperbarui status: '. $validator->errors()->first()); // Tampilkan pesan error
        }

        try {
            // Panggil method updateStatus dari Model
            $dailyActivity->updateStatus($request->input('status'));

            return redirect()->route('daily-activities.index')
                             ->with('success', 'Status aktivitas berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()
                             ->with('error', 'Terjadi kesalahan saat memperbarui status.');
        }
    }

    public function show(DailyActivity $dailyActivity) // Route Model Binding
    {
        $dailyActivity->load(['task', 'user.karyawan']);

        return response()->json($dailyActivity);
    }

    public function edit(DailyActivity $dailyActivity)
    {
        $currentUser = Auth::user();
        $karyawan = $currentUser->karyawan;

        $userDivisionName = null;
        $tasks = collect(); // Default: kosong

        if ($karyawan) {
            $userDivisionName = $karyawan->divisi;
        }

        if (!empty($userDivisionName)) {
            $tasks = Task::whereHas('user.karyawan', function ($query) use ($userDivisionName) {
                            $query->where('divisi', $userDivisionName);
                        })
                        ->orderBy('title')
                        ->get();
        }

        $statuses = ['On Progres', 'On Progres Dilanjutkan Besok', 'Gagal', 'Selesai'];

        return view('daily_activities.edit', compact('dailyActivity', 'tasks', 'statuses'));
    }

    public function update(Request $request, DailyActivity $dailyActivity) // Route Model Binding
    {
        // Validasi data yang masuk
         $validator = Validator::make($request->all(), [
            'id_task'       => 'nullable|exists:tasks,id',
            'activity'      => 'required|string',
            'description'   => 'nullable|string',
            'doc'           => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:2048', // Validasi file baru
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }

        // Handle file upload baru (jika ada)
        $docPath = $dailyActivity->doc; // Ambil path lama by default
        if ($request->hasFile('doc')) {
            // Hapus file lama jika ada dan file baru diupload
            if ($docPath && Storage::disk('public')->exists($docPath)) {
                Storage::disk('public')->delete($docPath);
            }
            // Simpan file baru
            $docPath = $request->file('doc')->store('activity_docs', 'public');
        } elseif ($request->input('remove_doc') == '1') { // Cek jika ada checkbox untuk hapus doc
             // Hapus file lama jika ada
            if ($docPath && Storage::disk('public')->exists($docPath)) {
                Storage::disk('public')->delete($docPath);
            }
            $docPath = null; // Set path jadi null
        }


        // Siapkan data untuk diupdate
        $dataToUpdate = [
            'id_task'       => $request->input('id_task'),
            'activity'      => $request->input('activity'),
            'description'   => $request->input('description'),
            'doc'           => $docPath,
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];

        try {
            // Update data dasar
            $dailyActivity->update($dataToUpdate);

            return redirect()->route('daily-activities.index')
                             ->with('success', 'Aktivitas harian berhasil diperbarui.');

        } catch (\Exception $e) {
            return redirect()->back()
                             ->with('error', 'Terjadi kesalahan saat memperbarui aktivitas.')
                             ->withInput();
        }
    }

    public function destroy(DailyActivity $dailyActivity) // Route Model Binding
    {
        try {
            $docPath = $dailyActivity->doc;
            $dailyActivity->delete();
            if ($docPath && Storage::disk('public')->exists($docPath)) {
                Storage::disk('public')->delete($docPath);
            }
            return redirect()->route('daily-activities.index')
                             ->with('success', 'Aktivitas harian berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()
                             ->with('error', 'Gagal menghapus aktivitas: ' . $e->getMessage());
        }
    }
}
