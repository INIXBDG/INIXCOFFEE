<?php

namespace App\Http\Controllers;

use App\Models\DashboardLayout;
use Illuminate\Http\Request;

class DashboardLayoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Tampilkan halaman pengaturan urutan dashboard.
     */
    public function index()
    {
        $layouts = DashboardLayout::orderBy('sort_order', 'asc')->get();
        return view('admin.layout-setting', compact('layouts'));
    }

    /**
     * Update urutan section dashboard via AJAX.
     */
    public function update(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|string',
        ]);

        $order = $request->input('order');

        foreach ($order as $index => $key) {
            DashboardLayout::where('section_key', $key)->update([
                'sort_order' => $index + 1,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Urutan section dashboard berhasil disimpan.',
        ]);
    }
}
