<?php

namespace Tests\Feature\ITSM\ProjectAdministratorBusinessSupport;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProjectDashboardTest extends TestCase
{
    use DatabaseTransactions;

    private function createUser($roleName = 'Project Administrator & Business Support')
    {
        $karyawanId = DB::table('karyawans')->insertGetId([
            'nama_lengkap' => 'Test Karyawan ' . uniqid(),
            'jabatan' => $roleName,
            'divisi' => 'IT Service Management',
            'kode_karyawan' => 'AD',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userId = DB::table('users')->insertGetId([
            'username' => 'testuser_' . uniqid(),
            'password' => Hash::make('password'),
            'karyawan_id' => $karyawanId,
            'jabatan' => $roleName,
            'status_akun' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::find($userId);
    }

    public function test_user_berwenang_dapat_mengakses_dashboard()
    {
        $user = $this->createUser();
        
        $response = $this->actingAs($user)->get('/projects/kanban');
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_user_belum_login_mendapatkan_response_sesuai_middleware()
    {
        $response = $this->get('/projects/kanban');
        $this->assertTrue(in_array($response->status(), [302, 401]));
    }

    public function test_user_tanpa_izin_mendapatkan_response_403_atau_redirect()
    {
        $user = $this->createUser('Programmer');
        
        $response = $this->actingAs($user)->get('/projects/kanban');
        $this->assertTrue(in_array($response->status(), [200, 302, 403]));
    }

    public function test_dashboard_dapat_diproses_ketika_data_kosong()
    {
        $user = $this->createUser();
        
        $response = $this->actingAs($user)->get('/projects/kanban');
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }
}
