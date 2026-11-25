<?php

namespace App\Policies;

use App\Models\karyawan;
use App\Models\User;

class KaryawanPolicy
{
    /**
     * Apakah user memiliki hak administratif penuh untuk mengelola seluruh data karyawan.
     */
    public function manageAll(User $user): bool
    {
        return $user->isAdmin()
            || $user->role === 'Admin'
            || $user->hasRole('Admin')
            || $user->can('Edit DataKaryawan');
    }

    /**
     * Apakah user boleh melihat/mengubah profil atau foto karyawan tertentu.
     */
    public function update(User $user, karyawan $karyawan, ?User $ownerUser = null): bool
    {
        if ($ownerUser && (int) $user->id === (int) $ownerUser->id) {
            return true;
        }

        if ((string) $user->karyawan_id !== '' && (string) $user->karyawan_id === (string) $karyawan->id) {
            return true;
        }

        return $this->manageAll($user);
    }
}
