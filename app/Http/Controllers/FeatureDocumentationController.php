<?php

namespace App\Http\Controllers;

use App\Models\FeatureDocumentation;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; // Import PDF Facade

class FeatureDocumentationController extends Controller
{
    public function index()
    {
        $features = FeatureDocumentation::latest()->paginate(10);
        return view('system.documentation.features.index', compact('features'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'status' => 'required|in:draft,development,production,deprecated',
            'short_description' => 'required|string',
            'purpose' => 'required|string',
            'background' => 'nullable|string',
            'problem_solved' => 'nullable|string',
            'how_it_works' => 'nullable|string',
            'user_access' => 'nullable|string',
            // Hapus validasi manual_file
        ]);

        FeatureDocumentation::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Feature documentation created successfully',
        ]);
    }

    public function show($id)
    {
        $feature = FeatureDocumentation::with('codeDocumentations')->findOrFail($id);
        return response()->json($feature);
    }

    public function update(Request $request, $id)
    {
        $feature = FeatureDocumentation::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'status' => 'required|in:draft,development,production,deprecated',
            'short_description' => 'required|string',
            'purpose' => 'required|string',
            'background' => 'nullable|string',
            'problem_solved' => 'nullable|string',
            'how_it_works' => 'nullable|string',
            'user_access' => 'nullable|string',
            // Hapus validasi manual_file
        ]);

        $feature->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Feature documentation updated successfully',
        ]);
    }

    public function destroy($id)
    {
        $feature = FeatureDocumentation::findOrFail($id);
        $feature->delete();

        return response()->json([
            'success' => true,
            'message' => 'Feature documentation deleted successfully',
        ]);
    }

    // --- METHOD BARU: Generate & Download Manual Book ---
    public function downloadManual($id)
    {
        $feature = FeatureDocumentation::findOrFail($id);

        // Load view khusus untuk PDF
        $pdf = Pdf::loadView('system.documentation.pdf.manual-book', compact('feature'));

        // Opsional: Set kertas dan orientasi
        $pdf->setPaper('a4', 'portrait');

        // Nama file aman (mengganti spasi dengan underscore)
        $filename = 'Manual_Book_' . str_replace(' ', '_', $feature->name) . '.pdf';

        return $pdf->download($filename);
    }
}
