<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Policies\ProjectPolicy;
use App\Policies\ProjectTaskPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Telegram\Bot\Methods\Get;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Project::class => ProjectPolicy::class,
        ProjectTask::class => ProjectTaskPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('akses-crm', function ($user) {
            $allowedJabatan = [
                'Adm Sales', 'SPV Sales', 'HRD', 'Finance & Accounting',
                'GM', 'Sales', 'Direktur Utama', 'Direktur'
            ];
            return in_array($user->jabatan, $allowedJabatan);
        });

        Gate::define('akses-filter-sales', function ($user) {
            $allowedJabatan = [
                'Adm Sales', 'SPV Sales', 'HRD', 'Finance & Accounting',
                'GM', 'Direktur Utama', 'Direktur'
            ];
            return in_array($user->jabatan, $allowedJabatan);
        });

        Gate::define('akses-tambah-lead', function ($user) {
            $restrictedJabatan = ['HRD', 'Finance & Accounting', 'GM', 'Direktur Utama', 'Direktur'];
            return !in_array($user->jabatan, $restrictedJabatan);
        });
    }
}
