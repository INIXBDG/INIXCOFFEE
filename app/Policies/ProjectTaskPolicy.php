<?php

namespace App\Policies;

use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectTaskPolicy
{
    public function updateProgress(User $user): bool
    {
        // Hak akses untuk pergerakan status Backlog -> To Do -> In Progress -> Testing -> Deploy
        return $user->hasAnyRole(['Programmer', 'Instruktur', 'Koordinator ITSM']);
    }

    public function validateTask(User $user): bool
    {
        // Hak akses untuk titik keputusan "Validate"
        return $user->hasRole('Education Manager', 'Koordinator ITSM');
    }

    public function evaluateTask(User $user): bool
    {
        // Hak akses untuk titik keputusan "Good?" (Evaluasi)
        return $user->hasRole('Education Manager');
    }
}