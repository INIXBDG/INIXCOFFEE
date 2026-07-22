<?php

namespace App\Http\Controllers;

use App\Models\CodeDocumentation;
use App\Models\FeatureDocumentation;
use Illuminate\Http\Request;

class CodeDocumentationController extends Controller
{
    public function index($featureId)
    {
        $feature = FeatureDocumentation::findOrFail($featureId);
        $codeDocs = CodeDocumentation::where('feature_documentation_id', $featureId)->latest()->paginate(10);

        return view('system.documentation.codes.index', compact('feature', 'codeDocs'));
    }

    public function store(Request $request, $featureId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'flow_type' => 'nullable|in:text,diagram,mermaid',
            'flow_content' => 'nullable|string',
            'code_blocks' => 'nullable|array',
            'relations' => 'nullable|array',
            'change_logs' => 'nullable|array',
            'future_development' => 'nullable|array',
        ]);

        $data = [
            'feature_documentation_id' => $featureId,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
        ];

        if (!empty($validated['flow_type']) && !empty($validated['flow_content'])) {
            $data['flow_program'] = [
                'type' => $validated['flow_type'],
                'content' => $validated['flow_content'],
            ];
        }

        $data['code_blocks'] = $validated['code_blocks'] ?? [];
        $data['relations'] = $validated['relations'] ?? [];
        $data['change_logs'] = $validated['change_logs'] ?? [];
        $data['future_development'] = $validated['future_development'] ?? [];

        CodeDocumentation::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Code documentation created successfully',
        ]);
    }

    public function show($id)
    {
        $codeDoc = CodeDocumentation::with('featureDocumentation')->findOrFail($id);
        return response()->json($codeDoc);
    }

    public function update(Request $request, $id)
    {
        $codeDoc = CodeDocumentation::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'flow_type' => 'nullable|in:text,diagram,mermaid',
            'flow_content' => 'nullable|string',
            'code_blocks' => 'nullable|array',
            'relations' => 'nullable|array',
            'change_logs' => 'nullable|array',
            'future_development' => 'nullable|array',
        ]);

        $data = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
        ];

        if (!empty($validated['flow_type']) && !empty($validated['flow_content'])) {
            $data['flow_program'] = [
                'type' => $validated['flow_type'],
                'content' => $validated['flow_content'],
            ];
        }

        $data['code_blocks'] = $validated['code_blocks'] ?? $codeDoc->code_blocks;
        $data['relations'] = $validated['relations'] ?? $codeDoc->relations;
        $data['change_logs'] = $validated['change_logs'] ?? $codeDoc->change_logs;
        $data['future_development'] = $validated['future_development'] ?? $codeDoc->future_development;

        $codeDoc->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Code documentation updated successfully',
        ]);
    }

    public function destroy($id)
    {
        $codeDoc = CodeDocumentation::findOrFail($id);
        $codeDoc->delete();

        return response()->json([
            'success' => true,
            'message' => 'Code documentation deleted successfully',
        ]);
    }
}
