<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class DashboardRoleLayout extends Model
{
    protected $table = 'dashboard_role_layouts';

    protected $fillable = ['role_name', 'section_order', 'card_order'];

    protected $casts = [
        'section_order' => 'array',
        'card_order'    => 'array',
    ];

    /**
     * Ambil instance override layout untuk user yang sedang login.
     * Mengembalikan null jika user tidak memiliki override khusus (akan ikut global).
     */
    public static function forUser($user = null)
    {
        $user = $user ?: Auth::user();
        if (!$user) {
            return null;
        }

        $userRoles = [];

        // 1. Cek jika menggunakan Spatie Permission
        if (method_exists($user, 'getRoleNames')) {
            $userRoles = array_merge($userRoles, $user->getRoleNames()->toArray());
        }

        // 2. Cek jika menggunakan kolom 'jabatan' di tabel users
        if (!empty($user->jabatan)) {
            $userRoles[] = $user->jabatan;
        }

        $userRoles = array_unique($userRoles);

        if (empty($userRoles)) {
            return null;
        }

        // Cari layout override pertama yang cocok dengan salah satu role/jabatan user
        return static::whereIn('role_name', $userRoles)->first();
    }

    /**
     * Gabungkan layout global dengan override milik satu role.
     */
    public static function merge(array $globalSections, array $globalCards, ?self $override, array $allowed): array
    {
        $sections = $globalSections;
        $cards    = $globalCards;

        if ($override) {
            if (!empty($override->section_order)) {
                $roleOrder = array_values(array_filter(
                    $override->section_order,
                    fn($s) => !empty($allowed[$s]) && in_array($s, $globalSections, true)
                ));

                // Section yang diizinkan tapi belum ada di override -> ikut urutan global
                foreach ($globalSections as $s) {
                    if (!empty($allowed[$s]) && !in_array($s, $roleOrder, true)) {
                        $roleOrder[] = $s;
                    }
                }

                // Isi slot-slot milik role dengan urutan role; slot section lain tetap
                $i = 0;
                foreach ($globalSections as $idx => $s) {
                    if (!empty($allowed[$s])) {
                        $sections[$idx] = $roleOrder[$i++];
                    }
                }
            }

            foreach (($override->card_order ?? []) as $sec => $c) {
                if (!empty($allowed[$sec]) && is_array($c)) {
                    $cards[$sec] = $c;
                }
            }
        }

        return ['sections' => $sections, 'cards' => $cards];
    }

    /**
     * Layout efektif untuk satu role (opsional, dipakai jika butuh hardcode role tertentu).
     */
    public static function effective(string $role, array $allowed): array
    {
        $global   = DashboardLayout::orderBy('sort_order', 'asc')->get();
        $sections = $global->pluck('section_key')->toArray();
        $cards    = [];

        foreach ($global as $l) {
            $c = $l->card_order;
            if (is_string($c)) {
                $c = json_decode($c, true);
            }
            $cards[$l->section_key] = is_array($c) ? $c : null;
        }

        $override = Schema::hasTable('dashboard_role_layouts')
            ? static::where('role_name', $role)->first()
            : null;

        return self::merge($sections, $cards, $override, $allowed);
    }
}