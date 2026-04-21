<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    public function manageAdministration(User $user): bool
    {
        // Hak akses untuk fase KAK hingga Dokumen Pembayaran
        return $user->hasAnyRole(['Spv Sales', 'GM', 'Koordinator ITSM']);
    }

    public function assignTeam(User $user): bool
    {
        // Hak akses untuk fase Assign Tim
        return $user->hasRole('Education Manager');
    }
}