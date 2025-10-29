<?php

namespace App\Http\Controllers;

use App\Models\DailyActivity;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DailyActivityController extends Controller
{
    public function index()
    {
        $activities = DailyActivity::with(['user.karyawan', 'task'])
                                ->latest('activity_date') // Urutkan dari tanggal terbaru
                                ->latest('created_at')    // Urutkan lagi by waktu pembuatan
                                ->get(); // Gunakan get() BUKAN paginate()
        return view('daily_activities.index', compact('activities'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_task' => 'required|exists:tasks,id',
            'activity' => 'required|string',
            'description' => 'nullable|string',
            'doc' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:2048',
            'activity_date' => 'required|date',
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
            'activity_date' => $request->input('activity_date'),
        ];

        $activity = DailyActivity::create($dataToStore);

        $activity->updateStatus('On Progres');

        return redirect()->route('daily-activities.index')
                            ->with('success', 'Aktivitas harian berhasil ditambahkan.');
    }

    public function create()
    {
        $tasks = Task::whereIn('state', ['todo', 'inprogress'])->orderBy('title')->get();
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

    public function edit(DailyActivity $dailyActivity) // Route Model Binding
    {
        $tasks = Task::whereIn('state', ['todo', 'inprogress'])->orderBy('title')->get();

        $statuses = ['On Progres', 'On Progres Dilanjutkan Besok', 'Gagal', 'Selesai'];

        return view('daily_activities.edit', compact('dailyActivity', 'tasks', 'statuses'));
    }

    public function update(Request $request, DailyActivity $dailyActivity) // Route Model Binding
    {
        // Validasi data yang masuk
         $validator = Validator::make($request->all(), [
            'id_task'       => 'required|exists:tasks,id',
            'activity'      => 'required|string',
            'description'   => 'nullable|string',
            'doc'           => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:2048', // Validasi file baru
            'activity_date' => 'required|date',
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
            'activity_date' => $request->input('activity_date'),
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
}
