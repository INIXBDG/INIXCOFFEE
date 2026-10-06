<?php

namespace App\Http\Controllers;

use App\Models\DashboardLayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class DashboardLayoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Pastikan kolom card_order ada di tabel dashboard_layouts.
     */
    private function ensureCardOrderColumnExists(): void
    {
        if (Schema::hasTable('dashboard_layouts') && !Schema::hasColumn('dashboard_layouts', 'card_order')) {
            Schema::table('dashboard_layouts', function (Blueprint $table) {
                $table->text('card_order')->nullable()->after('sort_order');
            });
        }
    }

    /**
     * Tampilkan halaman pengaturan urutan dashboard.
     */
    public function index()
    {
        $this->ensureCardOrderColumnExists();
        $layouts = DashboardLayout::orderBy('sort_order', 'asc')->get();
        return view('admin.layout-setting', compact('layouts'));
    }

    /**
     * Update urutan section dan card dashboard via AJAX.
     */
    public function update(Request $request)
    {
        $this->ensureCardOrderColumnExists();

        $order = $request->input('order');
        $cardsOrder = $request->input('cards_order');

        if ($order && is_array($order)) {
            foreach ($order as $index => $key) {
                DashboardLayout::where('section_key', $key)->update([
                    'sort_order' => $index + 1,
                ]);
            }
        }

        if ($cardsOrder && is_array($cardsOrder)) {
            foreach ($cardsOrder as $secKey => $cOrder) {
                DashboardLayout::where('section_key', $secKey)->update([
                    'card_order' => is_array($cOrder) ? json_encode(array_values($cOrder)) : $cOrder,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan urutan dashboard berhasil disimpan.',
        ]);
    }

    /**
     * Reset urutan dashboard ke default (mendukung reset spesifik: cards atau sections).
     */
    public function reset(Request $request)
    {
        $this->ensureCardOrderColumnExists();
        $type = $request->input('type', 'all');

        $defaultSections = [
            'karyawan',
            'peserta',
            'itsm',
            'rkm',
            'finance',
            'performance',
            'education',
            'office',
            'crm',
            'management',
            'project',
        ];

        if ($type === 'cards') {
            // Hanya reset urutan card di setiap section
            DashboardLayout::query()->update(['card_order' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Urutan seluruh kartu (cards) berhasil dikembalikan ke default.',
                'type' => 'cards',
            ]);
        }

        if ($type === 'sections') {
            // Hanya reset urutan section (grid & tabel)
            foreach ($defaultSections as $index => $key) {
                DashboardLayout::updateOrCreate(
                    ['section_key' => $key],
                    ['sort_order' => $index + 1]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Urutan section berhasil dikembalikan ke default.',
                'order' => $defaultSections,
                'type' => 'sections',
            ]);
        }

        // Default / All: Reset section dan cards
        foreach ($defaultSections as $index => $key) {
            DashboardLayout::updateOrCreate(
                ['section_key' => $key],
                [
                    'sort_order' => $index + 1,
                    'card_order' => null,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Seluruh urutan section dan kartu berhasil dikembalikan ke default.',
            'order' => $defaultSections,
            'type' => 'all',
        ]);
    }
}
