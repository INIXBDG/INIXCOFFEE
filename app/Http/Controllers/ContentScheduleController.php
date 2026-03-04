<?php

namespace App\Http\Controllers;

use App\Models\ContentSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContentScheduleController extends Controller
{
    public function index()
    {
        $schedules = ContentSchedule::latest()->get();
        return view('schedules.index', compact('schedules'));
    }

    public function store(Request $request)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'content_form' => 'required|in:Reels,Youtube,Feed,Story',
            'talents'      => 'nullable|array',
            'talents.*'    => 'string',
            'description'  => 'nullable|string',
            'proof_script' => 'nullable|string',
            'proof_image'  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_tiktok'    => 'nullable',
        ]);

        // 2. Proses Upload Gambar (Jika ada)
        if ($request->hasFile('proof_image')) {
            // Simpan di folder storage/app/public/proofs
            $filePath = $request->file('proof_image')->store('proofs', 'public');
            $validated['proof_image_path'] = $filePath;
        }

        $validated['talents'] = isset($validated['talents'])
            ? implode(',', $validated['talents'])
            : null;

        $validated['is_tiktok'] = $request->boolean('is_tiktok');

        // 5. Simpan Data
        ContentSchedule::create($validated);

        return redirect()->back()->with('success', 'Jadwal konten berhasil ditambahkan.');
    }

    public function markAsUploaded(ContentSchedule $contentSchedule)
    {
        $contentSchedule->update([
            'upload_date' => now(), // Mengisi dengan tanggal & waktu saat ini
        ]);

        return redirect()->back()->with('success', 'Status berhasil diperbarui menjadi Uploaded.');
    }

    public function update(Request $request, ContentSchedule $contentSchedule)
    {
        // 1. Validasi Input Update
        $validated = $request->validate([
            'content_form' => 'in:Reels,Youtube,Feed,Story',
            'upload_date'  => 'date',
            'talents'      => 'array',
            'description'  => 'nullable|string',
            'proof_script' => 'nullable|string',
            'proof_image'  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_tiktok'    => 'nullable',
        ]);

        // 2. Proses Ganti Gambar (Jika ada upload baru)
        if ($request->hasFile('proof_image')) {
            // Hapus gambar lama jika ada
            if ($contentSchedule->proof_image_path && Storage::disk('public')->exists($contentSchedule->proof_image_path)) {
                Storage::disk('public')->delete($contentSchedule->proof_image_path);
            }

            // Simpan gambar baru
            $filePath = $request->file('proof_image')->store('proofs', 'public');
            $validated['proof_image_path'] = $filePath;
        }

        // 3. Konversi Array Talents (Jika ada perubahan)
        if ($request->has('talents')) {
            $validated['talents'] = implode(',', $request->talents);
        }

        // 4. Konversi Checkbox
        if ($request->has('is_tiktok')) {
             $validated['is_tiktok'] = $request->boolean('is_tiktok');
        } else {
             // Jika checkbox tidak dicentang saat update, set false (tergantung logika UI Anda)
             $validated['is_tiktok'] = false;
        }

        // 5. Update Data
        $contentSchedule->update($validated);

        return redirect()->back()->with('success', 'Jadwal konten berhasil diperbarui.');
    }

    public function destroy(ContentSchedule $contentSchedule)
    {
        // Hapus file gambar terkait sebelum menghapus data
        if ($contentSchedule->proof_image_path && Storage::disk('public')->exists($contentSchedule->proof_image_path)) {
            Storage::disk('public')->delete($contentSchedule->proof_image_path);
        }

        $contentSchedule->delete();

        return redirect()->back()->with('success', 'Jadwal konten berhasil dihapus.');
    }
}
