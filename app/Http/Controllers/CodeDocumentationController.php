<?php

namespace App\Http\Controllers;

use App\Models\CodeDocumentation;
use App\Models\FeatureDocumentation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CodeDocumentationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected array $trackedFields = [
        'title',
        'description',
        'flow_program',
        'code_blocks',
        'relations',
        'change_logs',
        'future_development',
    ];

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

        $userId = Auth::id();

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

        $data['update_by'] = $userId;
        $data['log_update'] = [$userId];
        $data['log_time_update'] = [now()->toDateTimeString()];
        $data['log_changes'] = [[
            'action' => 'created',
            'fields' => array_keys(array_intersect_key($data, array_flip($this->trackedFields))),
        ]];

        $codeDoc = CodeDocumentation::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Code documentation created successfully',
            'data' => $codeDoc,
        ]);
    }

    public function show($id)
    {
        $codeDoc = CodeDocumentation::with('featureDocumentation')->findOrFail($id);

        $logUpdate = $codeDoc->log_update ?? [];
        $logTimeUpdate = $codeDoc->log_time_update ?? [];
        $logChanges = $codeDoc->log_changes ?? [];

        $userIds = collect($logUpdate)->filter()->unique()->values();
        $users = User::whereIn('id', $userIds)->pluck('username', 'id');

        $history = [];
        foreach ($logUpdate as $index => $userId) {
            $history[] = [
                'action' => $logChanges[$index]['action'] ?? 'updated',
                'fields' => $logChanges[$index]['fields'] ?? [],
                'updated_by' => $userId,
                'updated_by_name' => $users[$userId] ?? 'Pengguna Tidak Diketahui',
                'updated_at' => $logTimeUpdate[$index] ?? null,
            ];
        }

        $codeDoc->change_history = $history;

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

        $changedFields = $this->getChangedFields($codeDoc, $data);

        $userId = Auth::id();

        $logUpdate = $codeDoc->log_update ?? [];
        $logTimeUpdate = $codeDoc->log_time_update ?? [];
        $logChanges = $codeDoc->log_changes ?? [];

        array_unshift($logUpdate, $userId);
        array_unshift($logTimeUpdate, now()->toDateTimeString());
        array_unshift($logChanges, [
            'action' => 'updated',
            'fields' => $changedFields,
        ]);

        $data['update_by'] = $userId;
        $data['log_update'] = $logUpdate;
        $data['log_time_update'] = $logTimeUpdate;
        $data['log_changes'] = $logChanges;

        $codeDoc->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Code documentation updated successfully',
            'data' => $codeDoc->fresh(),
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

    protected function getChangedFields(CodeDocumentation $codeDoc, array $newData): array
    {
        $changed = [];

        foreach ($this->trackedFields as $field) {
            if (!array_key_exists($field, $newData)) {
                continue;
            }

            $oldValue = $codeDoc->{$field};
            $newValue = $newData[$field];

            $oldNormalized = is_array($oldValue) ? json_encode($oldValue) : $oldValue;
            $newNormalized = is_array($newValue) ? json_encode($newValue) : $newValue;

            if ($oldNormalized !== $newNormalized) {
                $changed[] = $field;
            }
        }

        return $changed;
    }
}