<?php

namespace App\Http\Controllers;

use App\Models\DashboardLayout;
use App\Models\DashboardRoleLayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class DashboardLayoutController extends Controller
{
    /** Urutan section bawaan (factory default). */
    private const DEFAULT_SECTIONS = [
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

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Pastikan kolom card_order ada di tabel dashboard_layouts (layout GLOBAL).
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
     * Pastikan tabel override layout PER ROLE ada.
     * (Migration juga disediakan; kalau migration sudah dijalankan method ini tidak melakukan apa-apa.)
     */
    private function ensureRoleLayoutTableExists(): void
    {
        if (!Schema::hasTable('dashboard_role_layouts')) {
            Schema::create('dashboard_role_layouts', function (Blueprint $table) {
                $table->id();
                $table->string('role_name')->unique();
                $table->longText('section_order')->nullable();
                $table->longText('card_order')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Ambil semua role (Spatie) + jabatan user, dan daftar gabungan nama role.
     *
     * @return array [$roles (Collection), $allRoleList (array)]
     */
    private function loadRoles(): array
    {
        $roles = class_exists(\Spatie\Permission\Models\Role::class)
            ? \Spatie\Permission\Models\Role::with('permissions')->orderBy('name')->get()
            : collect();
        $existingRoleNames = $roles->pluck('name')->toArray();

        $userJabatan = class_exists(\App\Models\User::class)
            ? \App\Models\User::whereNotNull('jabatan')->where('jabatan', '!=', '')->pluck('jabatan')->unique()->toArray()
            : [];

        $allRoleList = array_values(array_unique(array_merge($existingRoleNames, $userJabatan)));
        sort($allRoleList);

        return [$roles, $allRoleList];
    }

    /**
     * Peta layout efektif:
     *   'all'    => layout global (tabel dashboard_layouts)
     *   '<Role>' => layout global + override khusus role itu (tabel dashboard_role_layouts)
     */
    private function buildStateMap(array $allRoleList, $roles): array
    {
        $global    = DashboardLayout::orderBy('sort_order', 'asc')->get();
        $gSections = $global->pluck('section_key')->toArray();
        $gCards    = [];

        foreach ($global as $l) {
            $c = $l->card_order;
            if (is_string($c)) {
                $c = json_decode($c, true);
            }
            $gCards[$l->section_key] = is_array($c) ? $c : null;
        }

        $overrides = DashboardRoleLayout::all()->keyBy('role_name');
        $permMap   = $this->getRolePermissionsMap($allRoleList, $roles);

        $map = ['all' => ['sections' => $gSections, 'cards' => $gCards]];
        foreach ($allRoleList as $roleName) {
            $map[$roleName] = DashboardRoleLayout::merge(
                $gSections,
                $gCards,
                $overrides->get($roleName),
                $permMap[$roleName] ?? []
            );
        }

        return $map;
    }

    /**
     * Tampilkan halaman pengaturan urutan dashboard.
     */
    public function index()
    {
        $this->ensureCardOrderColumnExists();
        $this->ensureRoleLayoutTableExists();

        $layouts = DashboardLayout::orderBy('sort_order', 'asc')->get();

        [$roles, $allRoleList] = $this->loadRoles();
        $rolePermissionsMap = $this->getRolePermissionsMap($allRoleList, $roles);
        $layoutStateMap     = $this->buildStateMap($allRoleList, $roles);

        return view('admin.layout-setting', compact('layouts', 'allRoleList', 'rolePermissionsMap', 'layoutStateMap'));
    }

    /**
     * Helper untuk mendapatkan mapping permission section dashboard per role/jabatan.
     */
    private function getRolePermissionsMap($allRoleList, $roles)
{
    $rolePermissionsMap = [];
    foreach ($allRoleList as $roleName) {
        $roleObj   = $roles->firstWhere('name', $roleName);
        // Role Spatie -> permission role. Kalau nama ini hanya jabatan -> gabungan permission user berjabatan itu.
        $rolePerms = $roleObj
            ? $roleObj->permissions->pluck('name')->toArray()
            : $this->permissionsFromJabatan($roleName);

        $rolePermissionsMap[$roleName] = [
            'karyawan'    => true,
            'peserta'     => in_array('Fitur Menu Peserta', $rolePerms),
            'itsm'        => !empty(array_intersect(['Fitur Webinar', 'Fitur Content', 'Fitur Penilaian Exam', 'Fitur Registry Feature', 'View ITSM Only'], $rolePerms)),
            'rkm'         => in_array('Fitur Menu RKM', $rolePerms),
            'finance'     => in_array('Fitur Menu Finance', $rolePerms),
            'performance' => in_array('View KPI Penilaian', $rolePerms) || in_array($roleName, ['Koordinator ITSM', 'HRD', 'Education Manager', 'GM', 'SPV Sales', 'Direktur', 'Direktur Utama', 'Komisaris']),
            'education'   => in_array('Fitur Menu Education', $rolePerms),
            'office'      => in_array('Fitur Menu Office', $rolePerms),
            'crm'         => in_array('Fitur CRM', $rolePerms),
            'management'  => in_array('Fitur Menu Manajemen', $rolePerms),
            'project'     => in_array('Fitur Menu Project', $rolePerms),
        ];
    }
    return $rolePermissionsMap;
}

private function permissionsFromJabatan(string $jabatan): array
{
    $perms = [];
    \App\Models\User::where('jabatan', $jabatan)->get()->each(function ($u) use (&$perms) {
        $perms = array_merge($perms, $u->getAllPermissions()->pluck('name')->toArray());
    });
    return array_values(array_unique($perms));
}

    /**
     * Simpan urutan section dan card.
     *
     *  - tanpa 'role' (atau 'all') -> GLOBAL : tulis ke dashboard_layouts
     *  - dengan 'role'             -> PER ROLE: tulis HANYA ke dashboard_role_layouts
     *                                 (layout global & role lain tidak disentuh)
     */
    public function update(Request $request)
    {
        $this->ensureCardOrderColumnExists();
        $this->ensureRoleLayoutTableExists();

        $order      = $request->input('order');
        $cardsOrder = $request->input('cards_order');
        $role       = $request->input('role');
        $isRole     = $role && $role !== 'all';

        [$roles, $allRoleList] = $this->loadRoles();

        if ($isRole) {
            // ===== PER ROLE =====
            $allowed = $this->getRolePermissionsMap([$role], $roles)[$role] ?? [];
            $row     = DashboardRoleLayout::firstOrNew(['role_name' => $role]);

            if (is_array($order)) {
                // Simpan hanya section yang diizinkan role, sesuai urutan yang diatur user
                $row->section_order = array_values(array_filter($order, fn($s) => !empty($allowed[$s])));
            }

            if (is_array($cardsOrder)) {
                $cards = $row->card_order ?? [];
                foreach ($cardsOrder as $secKey => $cOrder) {
                    if (!empty($allowed[$secKey]) && is_array($cOrder)) {
                        $cards[$secKey] = array_values($cOrder);
                    }
                }
                $row->card_order = $cards;
            }

            $row->save();
        } else {
            // ===== GLOBAL =====
            if (is_array($order)) {
                foreach ($order as $index => $key) {
                    DashboardLayout::where('section_key', $key)->update([
                        'sort_order' => $index + 1,
                    ]);
                }
            }

            if (is_array($cardsOrder)) {
                foreach ($cardsOrder as $secKey => $cOrder) {
                    DashboardLayout::where('section_key', $secKey)->update([
                        'card_order' => is_array($cOrder) ? json_encode(array_values($cOrder)) : $cOrder,
                    ]);
                }
            }
        }

        return response()->json([
            'success'      => true,
            'message'      => $isRole
                ? "Urutan layout untuk role \"{$role}\" berhasil disimpan. Role lain tidak terpengaruh."
                : 'Pengaturan urutan dashboard (Global) berhasil disimpan.',
            'layout_state' => $this->buildStateMap($allRoleList, $roles),
        ]);
    }

    /**
     * Reset ke default.
     *
     *  - role 'all' (GLOBAL)  : hanya menyentuh dashboard_layouts. Override role dibiarkan.
     *  - role tertentu        : hanya menghapus override role itu di dashboard_role_layouts.
     *                           Layout global & role lain TIDAK disentuh.
     *
     * type: 'sections' | 'cards' | 'all'
     */
    public function reset(Request $request)
    {
        $this->ensureCardOrderColumnExists();
        $this->ensureRoleLayoutTableExists();

        $type = $request->input('type', 'all');
        if (!in_array($type, ['sections', 'cards', 'all'], true)) {
            $type = 'all';
        }
        $role   = $request->input('role', 'all');
        $isRole = $role && $role !== 'all';

        [$roles, $allRoleList] = $this->loadRoles();

        if ($isRole) {
            // ===== RESET PER ROLE =====
            $row = DashboardRoleLayout::where('role_name', $role)->first();

            if ($row) {
                if ($type === 'sections') {
                    $row->section_order = null;
                } elseif ($type === 'cards') {
                    $row->card_order = null;
                } else {
                    $row->section_order = null;
                    $row->card_order = null;
                }

                if (empty($row->section_order) && empty($row->card_order)) {
                    $row->delete(); // tidak ada override lagi -> role ikut global sepenuhnya
                } else {
                    $row->save();
                }
            }

            $label = $type === 'cards' ? 'kartu' : ($type === 'sections' ? 'section' : 'section dan kartu');
            $message = "Urutan {$label} untuk role \"{$role}\" berhasil dikembalikan ke default global. Role lain tidak terpengaruh.";
        } else {
            // ===== RESET GLOBAL =====
            if ($type === 'cards') {
                DashboardLayout::query()->update(['card_order' => null]);
                $message = 'Urutan seluruh kartu (Global) berhasil dikembalikan ke default.';
            } elseif ($type === 'sections') {
                foreach (self::DEFAULT_SECTIONS as $index => $key) {
                    DashboardLayout::updateOrCreate(
                        ['section_key' => $key],
                        ['sort_order' => $index + 1]
                    );
                }
                $message = 'Urutan seluruh section (Global) berhasil dikembalikan ke default.';
            } else {
                foreach (self::DEFAULT_SECTIONS as $index => $key) {
                    DashboardLayout::updateOrCreate(
                        ['section_key' => $key],
                        ['sort_order' => $index + 1, 'card_order' => null]
                    );
                }
                $message = 'Seluruh urutan section dan kartu (Global) berhasil dikembalikan ke default.';
            }
        }

        return response()->json([
            'success'      => true,
            'message'      => $message,
            'type'         => $type,
            'role'         => $isRole ? $role : 'all',
            'layout_state' => $this->buildStateMap($allRoleList, $roles),
        ]);
    }
}