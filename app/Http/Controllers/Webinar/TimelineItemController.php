<?php

namespace App\Http\Controllers\Webinar;

use App\Http\Controllers\Controller;
use App\Models\TimelineItem;
use App\Models\YearMapping;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TimelineItemController extends Controller
{
    /**
     * SIMPAN / UPDATE ITEM HARIAN
     */
    public function store(Request $request)
    {
        // --- AUTH CHECK: Hanya Tim Digital ---
        if (auth()->user()->jabatan !== 'Tim Digital') {
            return response()->json(['message' => 'Akses Ditolak: Hanya Tim Digital yang boleh mengubah timeline.'], 403);
        }
        // -------------------------------------

        $request->validate([
            'item_date' => 'required|date',
            'content' => 'nullable|string',
            'year_mapping_id' => 'required|exists:year_mappings,id'
        ]);

        if (empty($request->content)) {
            TimelineItem::where('item_date', $request->item_date)->delete();
            return response()->json(['status' => 'deleted']);
        }

        $item = TimelineItem::updateOrCreate(
            ['item_date' => $request->item_date],
            [
                'year_mapping_id' => $request->year_mapping_id,
                'content' => $request->content,
                'color' => $request->color ?? 'blue'
            ]
        );

        return response()->json(['status' => 'saved', 'data' => $item]);
    }

}
