<?php

namespace App\Http\Controllers;

use App\Models\FeatureDocumentation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class FeatureDocumentationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $features = FeatureDocumentation::parentsOnly()
            ->withCount('children')
            ->with([
                'children' => function ($q) {
                    $q->withCount('children');
                },
            ])
            ->latest()
            ->paginate(10);

        return view('system.documentation.features.index', compact('features'));
    }

    public function show($id)
    {
        $feature = FeatureDocumentation::withCount('children')
            ->with([
                'children' => function ($q) {
                    $q->withCount('children')->orderBy('created_at')->orderBy('id');
                },
            ])
            ->with('codeDocumentations')
            ->findOrFail($id);

        $ancestors = collect();
        $current = $feature->parentFeature()->first();
        while ($current) {
            $ancestors->prepend($current);
            $current = $current->parentFeature()->first();
        }

        $history = $feature->getVersionHistory();
        $userIds = collect($history)->pluck('updated_by')->filter()->unique()->values();
        $users = User::whereIn('id', $userIds)->pluck('username', 'id');

        $history = collect($history)
            ->map(function ($entry) use ($users) {
                $entry['updated_by_name'] = $users[$entry['updated_by']] ?? 'Pengguna Tidak Diketahui';
                return $entry;
            })
            ->all();

        return view('system.documentation.features.show', compact('feature', 'ancestors', 'history'));
    }

    public function editData($id)
    {
        $feature = FeatureDocumentation::with(['codeDocumentations', 'children', 'parentFeature'])->findOrFail($id);

        return response()->json($feature);
    }

    public function options(Request $request)
    {
        $excludeId = $request->query('exclude');

        $query = FeatureDocumentation::orderBy('name');

        if ($excludeId) {
            $excludeIds = [(int) $excludeId];
            $feature = FeatureDocumentation::find($excludeId);
            if ($feature) {
                $excludeIds = array_merge($excludeIds, $feature->allDescendantIds());
            }
            $query->whereNotIn('id', $excludeIds);
        }

        $features = $query->get(['id', 'name', 'parent_id']);

        return response()->json($features);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:feature_documentations,id',
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'status' => 'required|in:draft,development,production,deprecated',
            'short_description' => 'required|string',
            'purpose' => 'required|string',
            'problem_solved' => 'nullable|string',
            'how_it_works' => 'nullable|string',
            'user_access' => 'nullable|string',
        ]);

        $userId = Auth::id();

        $validated['update_by'] = $userId;
        $validated['log_update'] = [$userId];
        $validated['log_time_update'] = [now()->toDateTimeString()];

        $storeData = FeatureDocumentation::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Feature documentation created successfully',
            'data' => $storeData,
        ]);
    }

    public function update(Request $request, $id)
    {
        $feature = FeatureDocumentation::findOrFail($id);

        $validated = $request->validate([
            'parent_id' => 'nullable|exists:feature_documentations,id',
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'status' => 'required|in:draft,development,production,deprecated',
            'short_description' => 'required|string',
            'purpose' => 'required|string',
            'problem_solved' => 'nullable|string',
            'how_it_works' => 'nullable|string',
            'user_access' => 'nullable|string',
        ]);

        if (!empty($validated['parent_id'])) {
            if ((int) $validated['parent_id'] === (int) $feature->id) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Fitur tidak dapat menjadi induk dari dirinya sendiri',
                    ],
                    422,
                );
            }

            $descendantIds = $feature->allDescendantIds();
            if (in_array((int) $validated['parent_id'], $descendantIds)) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Tidak dapat memilih sub-fitur sendiri sebagai fitur induk',
                    ],
                    422,
                );
            }
        }

        $userId = Auth::id();

        $logUpdate = $feature->log_update ?? [];
        $logTimeUpdate = $feature->log_time_update ?? [];

        array_unshift($logUpdate, $userId);
        array_unshift($logTimeUpdate, now()->toDateTimeString());

        $validated['update_by'] = $userId;
        $validated['log_update'] = $logUpdate;
        $validated['log_time_update'] = $logTimeUpdate;

        $feature->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Feature documentation updated successfully',
            'data' => $feature->fresh(),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $feature = FeatureDocumentation::findOrFail($id);
        $force = $request->query('force') == 1;

        if ($feature->children()->exists() && !$force) {
            return response()->json(
                [
                    'success' => false,
                    'has_children' => true,
                    'message' => 'Fitur ini memiliki sub-fitur. Hapus juga seluruh sub-fitur?',
                ],
                409,
            );
        }

        if ($force) {
            $this->deleteWithChildren($feature);
        } else {
            $feature->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Feature documentation deleted successfully',
        ]);
    }

    protected function deleteWithChildren(FeatureDocumentation $feature)
    {
        foreach ($feature->children as $child) {
            $this->deleteWithChildren($child);
        }
        $feature->delete();
    }

    public function downloadManual($id)
    {
        $feature = FeatureDocumentation::with(['codeDocumentations', 'childrenRecursive.updater', 'childrenRecursive.childrenRecursive', 'childrenRecursive.codeDocumentations', 'updater', 'parentFeature'])->findOrFail($id);

        $htmlFields = ['short_description', 'purpose', 'problem_solved', 'how_it_works', 'user_access'];

        foreach ($htmlFields as $field) {
            if (!empty($feature->{$field})) {
                $feature->{$field} = $this->convertImagesToBase64($feature->{$field});
                $feature->{$field} = $this->normalizeHtmlLists($feature->{$field});
            }
        }

        if ($feature->childrenRecursive) {
            foreach ($feature->childrenRecursive as $child) {
                foreach ($htmlFields as $field) {
                    if (!empty($child->{$field})) {
                        $child->{$field} = $this->convertImagesToBase64($child->{$field});
                        $child->{$field} = $this->normalizeHtmlLists($child->{$field});
                    }
                }
            }
        }

        $pdf = Pdf::loadView('system.documentation.pdf.manual-book', compact('feature'));

        $pdf->setPaper('a4', 'portrait');

        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
            'chroot' => public_path(),
        ]);

        $filename = 'Manual_Book_' . Str::slug($feature->name) . '.pdf';

        return $pdf->download($filename);
    }

    protected function convertImagesToBase64($html)
    {
        if (empty($html)) {
            return $html;
        }

        return preg_replace_callback(
            '/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i',
            function ($matches) {
                $src = $matches[1];

                if (strpos($src, 'data:image') === 0) {
                    return $matches[0];
                }

                if (strpos($src, 'http://') === 0 || strpos($src, 'https://') === 0) {
                    return $matches[0];
                }

                $path = public_path(ltrim($src, '/'));

                if (file_exists($path)) {
                    $imageData = base64_encode(file_get_contents($path));
                    $mime = mime_content_type($path);
                    $newSrc = 'data:' . $mime . ';base64,' . $imageData;
                    return str_replace($src, $newSrc, $matches[0]);
                }

                return $matches[0];
            },
            $html,
        );
    }

    protected function normalizeHtmlLists($html)
    {
        if (empty($html)) {
            return $html;
        }

        if (preg_match('/<(ol|ul)[\s>]/i', $html)) {
            return $html;
        }

        $lines = preg_split('/\r\n|\r|\n/', $html);
        $result = [];
        $inList = false;
        $listType = null;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (preg_match('/^(\d+)\.\s+(.+)$/', $trimmed, $matches)) {
                if (!$inList || $listType !== 'ol') {
                    if ($inList) {
                        $result[] = '</' . $listType . '>';
                    }
                    $result[] = '<ol>';
                    $inList = true;
                    $listType = 'ol';
                }
                $result[] = '<li>' . $matches[2] . '</li>';
            } elseif (preg_match('/^[-*•]\s+(.+)$/', $trimmed, $matches)) {
                if (!$inList || $listType !== 'ul') {
                    if ($inList) {
                        $result[] = '</' . $listType . '>';
                    }
                    $result[] = '<ul>';
                    $inList = true;
                    $listType = 'ul';
                }
                $result[] = '<li>' . $matches[1] . '</li>';
            } else {
                if ($inList) {
                    $result[] = '</' . $listType . '>';
                    $inList = false;
                    $listType = null;
                }
                if (!empty($trimmed) && !preg_match('/^<[a-z][\s\S]*>/i', $trimmed)) {
                    $result[] = '<p>' . $trimmed . '</p>';
                } else {
                    $result[] = $line;
                }
            }
        }

        if ($inList) {
            $result[] = '</' . $listType . '>';
        }

        return implode("\n", $result);
    }
}
