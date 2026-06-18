<?php

namespace App\Http\Controllers;

use App\Models\ContentSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContentScheduleController extends Controller
{
    public function index()
    {
        $schedules = ContentSchedule::orderByRaw('upload_date IS NULL DESC, upload_date DESC')->get();

        $defaultTalents = [
            'Hera', 'Savanna', 'Reni', 'Rara', 'Alfi', 'Nabila', 'Fia', 'Ani', 'Yanuar',
            'Adit', 'Luki', 'Sabdhan', 'Rustan', 'Wahyu', 'Sahrul', 'Pani', 'Yayat',
            'Stepan', 'Vicky', 'Sergio', 'Donna', 'Eggi', 'Ardhan', 'Julie', 'Ferdi',
            'Aulia', 'Alysia', 'Xepi', 'Rifa'
        ];

        $dbTalents = ContentSchedule::pluck('talents')->filter()->toArray();
        $allTalents = $defaultTalents;

        foreach ($dbTalents as $talentString) {
            $talentsArray = explode(',', $talentString);
            foreach ($talentsArray as $t) {
                $cleanTalent = trim($t);
                if (!empty($cleanTalent)) {
                    $allTalents[] = $cleanTalent;
                }
            }
        }

        $uniqueTalents = array_unique($allTalents);
        sort($uniqueTalents);

        return view('schedules.index', compact('schedules', 'uniqueTalents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'content_form' => 'required|in:Reels,Youtube,Feed,Story,Tiktok',
            'talents'      => 'nullable|array',
            'talents.*'    => 'string',
            'description'  => 'nullable|string',
            'proof_script' => 'nullable|string',
            'proof_image'  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_tiktok'    => 'nullable',
        ]);

        if ($request->hasFile('proof_image')) {
            $filePath = $request->file('proof_image')->store('proofs', 'public');
            $validated['proof_image_path'] = $filePath;
        }

        $validated['talents'] = isset($validated['talents'])
            ? implode(',', $validated['talents'])
            : null;

        $validated['is_tiktok'] = $request->boolean('is_tiktok');

        ContentSchedule::create($validated);

        return redirect()->back()->with('success', 'Jadwal konten berhasil ditambahkan.');
    }

    public function markAsUploaded(ContentSchedule $contentSchedule)
    {
        $contentSchedule->update([
            'upload_date' => now(),
        ]);

        return redirect()->back()->with('success', 'Status berhasil diperbarui menjadi Uploaded.');
    }

    public function update(Request $request, ContentSchedule $contentSchedule)
    {
        $validated = $request->validate([
            'content_form' => 'in:Reels,Youtube,Feed,Story,Tiktok',
            'upload_date'  => 'date',
            'talents'      => 'array',
            'description'  => 'nullable|string',
            'proof_script' => 'nullable|string',
            'proof_image'  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_tiktok'    => 'nullable',
        ]);

        if ($request->hasFile('proof_image')) {
            if ($contentSchedule->proof_image_path && Storage::disk('public')->exists($contentSchedule->proof_image_path)) {
                Storage::disk('public')->delete($contentSchedule->proof_image_path);
            }

            $filePath = $request->file('proof_image')->store('proofs', 'public');
            $validated['proof_image_path'] = $filePath;
        }

        if ($request->has('talents')) {
            $validated['talents'] = implode(',', $request->talents);
        }

        if ($request->has('is_tiktok')) {
             $validated['is_tiktok'] = $request->boolean('is_tiktok');
        } else {
             $validated['is_tiktok'] = false;
        }

        $contentSchedule->update($validated);

        return redirect()->back()->with('success', 'Jadwal konten berhasil diperbarui.');
    }

    public function destroy(ContentSchedule $contentSchedule)
    {
        if ($contentSchedule->proof_image_path && Storage::disk('public')->exists($contentSchedule->proof_image_path)) {
            Storage::disk('public')->delete($contentSchedule->proof_image_path);
        }

        $contentSchedule->delete();

        return redirect()->back()->with('success', 'Jadwal konten berhasil dihapus.');
    }
}
