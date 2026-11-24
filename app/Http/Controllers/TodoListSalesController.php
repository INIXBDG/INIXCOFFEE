<?php

namespace App\Http\Controllers;

use App\Models\HariLibur;
use App\Models\TodoAdministrasi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TodoListSalesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Memeriksa apakah user memiliki akses ke fitur To Do List Sales.
     * Hak akses sesuai arahan: SPV Sales dan Admin Sales (Adm Sales), serta Super Admin.
     */
    private function authorizeAccess()
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Silakan login terlebih dahulu.');
        }

        $jabatan = strtolower(trim($user->jabatan ?? ''));
        $allowed = ['spv sales', 'adm sales', 'admin sales', 'super admin', 'programmer'];

        if (in_array($jabatan, $allowed) || 
            $user->hasAnyRole(['SPV Sales', 'Adm Sales', 'Admin Sales', 'Super Admin', 'Programmer']) || 
            $user->can('View Todo Administrasi')) {
            return true;
        }

        abort(403, 'Akses terbatas: Fitur To Do List hanya dapat diakses oleh SPV Sales dan Admin Sales.');
    }

    /**
     * Tampilan utama To Do List & Schedule Sales
     */
    public function index(Request $request)
    {
        $this->authorizeAccess();

        $today = Carbon::today();

        // Mengambil daftar to-do untuk list view di sidebar
        $todos = TodoAdministrasi::orderByRaw("CASE 
                WHEN status = 'progres' THEN 0 
                WHEN status = 'gagal' THEN 1 
                ELSE 2 
            END")
            ->orderByDesc('created_at')
            ->get();

        // Statistik ringkasan
        $stats = [
            'total' => $todos->count(),
            'progres' => $todos->where('status', 'progres')->count(),
            'selesai' => $todos->where('status', 'selesai')->count(),
            'today' => $todos->filter(function ($item) use ($today) {
                return $item->created_at && Carbon::parse($item->created_at)->isSameDay($today);
            })->count(),
        ];

        return view('todo_sales.index', compact('todos', 'stats'));
    }

    /**
     * Endpoint API JSON untuk FullCalendar & update real-time
     */
    public function getEvents(Request $request)
    {
        $this->authorizeAccess();

        $start = $request->get('start');
        $end = $request->get('end');

        $query = TodoAdministrasi::query();

        if ($start && $end) {
            $query->whereBetween('created_at', [
                Carbon::parse($start)->startOfDay(),
                Carbon::parse($end)->endOfDay()
            ]);
        }

        $todos = $query->get();

        $events = $todos->map(function ($item) {
            $startDate = Carbon::parse($item->created_at);
            $waktuMulai = $startDate->translatedFormat('d M Y, H:i');
            $jamMulai = $startDate->format('H:i');

            // Logika waktu & warna sesuai permintaan:
            // - Sukses / Selesai: Biru (#0d6efd)
            // - Dalam Progres: Kuning/Amber (#f59e0b)
            // - Gagal: Merah (#dc3545)
            $isSelesai = ($item->status === 'selesai');
            if ($isSelesai) {
                $completionDate = $item->updated_at ? Carbon::parse($item->updated_at) : ($item->tanggal_selesai ? Carbon::parse($item->tanggal_selesai) : now());
                $displayTime = $completionDate->format('H:i');
                $waktuSelesai = $completionDate->translatedFormat('d M Y, H:i');
                $color = '#0d6efd'; // Sukses / Selesai: Biru
            } elseif ($item->status === 'gagal') {
                $displayTime = $jamMulai;
                $waktuSelesai = null;
                $color = '#dc3545'; // Gagal: Merah
            } else {
                $displayTime = $jamMulai;
                $waktuSelesai = null;
                $color = '#f59e0b'; // Dalam Progres: Kuning / Amber
            }

            // Pastikan event to do list selalu berada di dalam sel tanggal tugasnya (tidak melebar ke hari lain)
            $endDate = $startDate->copy()->addHour();

            return [
                'id' => $item->id,
                'title' => $item->case,
                'start' => $startDate->toIso8601String(),
                'end' => $endDate->toIso8601String(),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => '#ffffff',
                'display' => 'block', // Selalu tampil sebagai bar/pill solid berwarna
                'className' => 'fc-task-event task-' . $item->status,
                'extendedProps' => [
                    'status' => $item->status,
                    'catatan' => $item->catatan ?? '',
                    'solusi' => $item->solusi ?? '',
                    'tanggal_selesai' => $item->tanggal_selesai ? Carbon::parse($item->tanggal_selesai)->format('Y-m-d') : null,
                    'waktu_mulai' => $waktuMulai,
                    'waktu_selesai' => $waktuSelesai,
                    'display_time' => $displayTime,
                    'dokumen' => $item->dokumen ? asset('storage/' . $item->dokumen) : null,
                    'dokumen_name' => $item->dokumen ? basename($item->dokumen) : null,
                ]
            ];
        });

        // Ambil Hari Libur Nasional & Libur Perusahaan/HRD dari database hari_liburs
        $holidayEvents = collect();
        try {
            $startDateObj = $start ? Carbon::parse($start) : now()->startOfMonth();
            $endDateObj = $end ? Carbon::parse($end) : now()->endOfMonth();
            $year = $startDateObj->year;

            // Auto-sync hari libur nasional jika database untuk tahun tersebut masih kosong
            if (HariLibur::whereYear('tanggal', $year)->doesntExist()) {
                try {
                    $response = \Illuminate\Support\Facades\Http::timeout(5)->get('https://libur.deno.dev/api', ['year' => $year]);
                    if ($response->successful()) {
                        foreach ($response->json() as $libur) {
                            HariLibur::updateOrCreate(
                                ['tanggal' => $libur['date']],
                                ['nama' => $libur['name'], 'year' => $year, 'tipe' => 'nasional']
                            );
                        }
                    }
                } catch (\Exception $ex) {
                    \Log::warning('Auto sync hari libur gagal: ' . $ex->getMessage());
                }
            }

            $holidayQuery = HariLibur::query();
            if ($start && $end) {
                $holidayQuery->whereBetween('tanggal', [
                    $startDateObj->toDateString(),
                    $endDateObj->toDateString(),
                ]);
            }
            $holidays = $holidayQuery->get();

            foreach ($holidays as $h) {
                $tgl = Carbon::parse($h->tanggal)->toDateString();
                $isHrd = ($h->tipe === 'perusahaan');
                $title = ($isHrd ? '🏢 Libur HRD: ' : '🏖️ Libur: ') . $h->nama;

                // 1. Background Event (Mewarnai seluruh kotak tanggal jadi merah muda/pink seperti Sabtu-Minggu)
                $holidayEvents->push([
                    'id' => 'bg_holiday_' . $h->id,
                    'start' => $tgl,
                    'allDay' => true,
                    'display' => 'background',
                    'backgroundColor' => '#fee2e2',
                    'classNames' => ['fc-holiday-bg'],
                ]);

                // 2. Foreground Event (Badge pill merah dengan teks nama libur)
                $holidayEvents->push([
                    'id' => 'holiday_' . $h->id,
                    'title' => $title,
                    'start' => $tgl,
                    'allDay' => true,
                    'backgroundColor' => '#dc2626',
                    'borderColor' => '#b91c1c',
                    'textColor' => '#ffffff',
                    'className' => 'fc-holiday-event',
                    'display' => 'block',
                    'extendedProps' => [
                        'is_holiday' => true,
                        'holiday_name' => $h->nama,
                        'tipe' => $h->tipe ?? 'nasional',
                        'is_hrd' => $isHrd,
                        'tanggal' => $tgl,
                    ],
                ]);
            }
        } catch (\Exception $e) {
            \Log::warning('Gagal memuat hari libur: ' . $e->getMessage());
        }

        $allEvents = $events->concat($holidayEvents);

        return response()->json($allEvents);
    }

    /**
     * Simpan tugas baru (Quick Add atau Form Modal)
     */
    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'case' => 'required|string|max:255',
            'catatan' => 'nullable|string',
            'status' => 'nullable|in:progres,selesai,gagal',
            'created_at' => 'nullable|date',
            'dokumen' => 'nullable|file|max:10240',
        ]);

        $validated['status'] = $validated['status'] ?? 'progres';
        $validated['created_at'] = $validated['created_at'] ? Carbon::parse($validated['created_at']) : now();

        // Atur logika otomatis: jika status selesai, tanggal_selesai = now(), jika belum = null
        if ($validated['status'] === 'selesai') {
            $validated['tanggal_selesai'] = now()->toDateString();
        } else {
            $validated['tanggal_selesai'] = null;
        }

        // Handle dokumen jika diupload
        if ($request->hasFile('dokumen') && $request->file('dokumen')->isValid()) {
            $path = $request->file('dokumen')->store('todo-administrasi', 'public');
            $validated['dokumen'] = $path;
        }

        $todo = TodoAdministrasi::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil ditambahkan ke jadwal!',
                'data' => $todo
            ]);
        }

        return redirect()->route('todo-list.index')->with('success', 'Tugas berhasil ditambahkan ke jadwal.');
    }

    /**
     * Perbarui tugas
     */
    public function update(Request $request, $id)
    {
        $this->authorizeAccess();

        $todo = TodoAdministrasi::findOrFail($id);

        $validated = $request->validate([
            'case' => 'required|string|max:255',
            'catatan' => 'nullable|string',
            'status' => 'required|in:progres,selesai,gagal',
            'created_at' => 'nullable|date',
            'dokumen' => 'nullable|file|max:10240',
        ]);

        // Atur logika otomatis: jika status selesai, tanggal_selesai = now(), jika belum = null
        if ($validated['status'] === 'selesai') {
            $validated['tanggal_selesai'] = now()->toDateString();
        } else {
            $validated['tanggal_selesai'] = null;
        }

        if ($request->hasFile('dokumen') && $request->file('dokumen')->isValid()) {
            if ($todo->dokumen && Storage::disk('public')->exists($todo->dokumen)) {
                Storage::disk('public')->delete($todo->dokumen);
            }
            $path = $request->file('dokumen')->store('todo-administrasi', 'public');
            $validated['dokumen'] = $path;
        }

        $todo->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil diperbarui!',
                'data' => $todo
            ]);
        }

        return redirect()->route('todo-list.index')->with('success', 'Tugas berhasil diperbarui.');
    }

    /**
     * Toggle status to-do (Ceklis selesai / kembali progres) ala Google Tasks
     */
    public function toggle(Request $request, $id)
    {
        $this->authorizeAccess();

        $todo = TodoAdministrasi::findOrFail($id);

        if ($todo->status === 'selesai') {
            $todo->status = 'progres';
            $todo->tanggal_selesai = null;
        } else {
            $todo->status = 'selesai';
            $todo->tanggal_selesai = now()->toDateString();
            $todo->updated_at = now();
        }

        $todo->save();

        $today = Carbon::today();
        $stats = [
            'total' => TodoAdministrasi::count(),
            'progres' => TodoAdministrasi::where('status', 'progres')->count(),
            'selesai' => TodoAdministrasi::where('status', 'selesai')->count(),
            'today' => TodoAdministrasi::whereDate('created_at', $today)->count(),
        ];

        return response()->json([
            'success' => true,
            'message' => $todo->status === 'selesai' ? 'Tugas ditandai selesai!' : 'Tugas dikembalikan ke progres.',
            'status' => $todo->status,
            'id' => $todo->id,
            'stats' => $stats,
            'waktu_selesai' => $todo->status === 'selesai' ? now()->translatedFormat('d M Y, H:i') : null,
            'display_time' => now()->format('H:i'),
        ]);
    }

    /**
     * Hapus tugas
     */
    public function destroy(Request $request, $id)
    {
        $this->authorizeAccess();

        $todo = TodoAdministrasi::findOrFail($id);

        if ($todo->dokumen && Storage::disk('public')->exists($todo->dokumen)) {
            Storage::disk('public')->delete($todo->dokumen);
        }

        $todo->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil dihapus!'
            ]);
        }

        return redirect()->route('todo-list.index')->with('success', 'Tugas berhasil dihapus.');
    }
}
