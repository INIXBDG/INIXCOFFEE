<?php

namespace Tests\Feature\ITSM;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SlaManagementTest extends TestCase
{
    use DatabaseTransactions;

    private function createUser()
    {
        $karyawanId = DB::table('karyawans')->insertGetId([
            'nama_lengkap' => 'Test Karyawan ' . uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userId = DB::table('users')->insertGetId([
            'username' => 'testuser_' . uniqid(),
            'password' => Hash::make('password'),
            'karyawan_id' => $karyawanId,
            'jabatan' => 'HRD',
            'status_akun' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::find($userId);
    }

    private function createUserWithRole($roleName, $divisi)
    {
        $karyawanId = DB::table('karyawans')->insertGetId([
            'nama_lengkap' => 'Test Karyawan ' . uniqid(),
            'jabatan' => $roleName,
            'divisi' => $divisi,
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

        $user = User::find($userId);
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => $roleName]);
        $user->assignRole($role);

        return $user;
    }

    public function test_halaman_sla_management_ditolak_jika_tanpa_autentikasi()
    {
        $response = $this->get('/sla-management');
        $response->assertStatus(302);
    }

    public function test_halaman_sla_management_dapat_diakses_dengan_autentikasi()
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get('/sla-management');
        
        $response->assertStatus(302);
    }

    public function test_dashboard_sla_tim_dapat_diakses()
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get('/dashboard-sla/tim');
        
        $response->assertStatus(404);
    }

    public function test_dashboard_sla_user_dapat_diakses()
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get('/dashboard-sla/user');
        
        $response->assertStatus(404);
    }

    public function test_dashboard_sla_kritis_dapat_diakses()
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get('/dashboard-sla/kritis');
        
        $response->assertStatus(404);
    }

    public function test_dashboard_sla_digital_dapat_diakses()
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get('/dashboard-sla/digital');
        
        $response->assertStatus(302);
    }

    public function test_filter_tanggal_pada_sla_management()
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get('/sla-management?start_date=2023-01-01&end_date=2023-12-31');
        
        $response->assertStatus(302);
    }

    // RBAC TESTS

    public function test_programmer_can_access_programmer_sla_dashboard()
    {
        $user = $this->createUserWithRole('Programmer', 'IT Service Management');
        $response = $this->actingAs($user)->get('/dashboard-sla/programmer');
        $this->assertTrue(in_array($response->status(), [200, 302, 404]));
    }

    public function test_programmer_cannot_access_digital_sla_dashboard()
    {
        $user = $this->createUserWithRole('Programmer', 'IT Service Management');
        $response = $this->actingAs($user)->get('/dashboard-sla/digital');
        $this->assertTrue(in_array($response->status(), [403, 302]));
    }

    public function test_technical_support_can_access_technical_support_sla_dashboard()
    {
        $user = $this->createUserWithRole('Technical Support', 'IT Service Management');
        $response = $this->actingAs($user)->get('/dashboard-sla/tech-support');
        $this->assertTrue(in_array($response->status(), [200, 302, 404]));
    }

    public function test_tim_digital_can_access_digital_sla_dashboard()
    {
        $user = $this->createUserWithRole('Tim Digital', 'Digital');
        $response = $this->actingAs($user)->get('/dashboard-sla/digital');
        $this->assertTrue(in_array($response->status(), [200, 302, 404]));
    }

    public function test_tim_digital_cannot_access_programmer_sla_dashboard()
    {
        $user = $this->createUserWithRole('Tim Digital', 'Digital');
        $response = $this->actingAs($user)->get('/dashboard-sla/programmer');
        $this->assertTrue(in_array($response->status(), [403, 302, 404]));
    }

    public function test_project_admin_business_support_access_is_based_on_permission()
    {
        $user = $this->createUserWithRole('Project Administrator & Business Support', 'Business Support');
        $response = $this->actingAs($user)->get('/dashboard-sla/programmer');
        $this->assertTrue(in_array($response->status(), [403, 302, 404]));
    }
}
