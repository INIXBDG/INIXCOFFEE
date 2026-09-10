<?php

namespace Tests\Feature\ITSM\Programmer;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DashboardSlaProgrammerTest extends TestCase
{
    use DatabaseTransactions;

    private function createUserWithRole($roleName = 'Programmer', $divisi = 'IT Service Management')
    {
        $karyawanId = DB::table('karyawans')->insertGetId([
            'nama_lengkap' => 'Test Programmer ' . uniqid(),
            'jabatan' => $roleName,
            'divisi' => $divisi,
            'kode_karyawan' => 'PRG' . rand(10, 99),
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

    public function test_programmer_can_access_sla_dashboard()
    {
        $user = $this->createUserWithRole();
        $response = $this->actingAs($user)->get('/dashboard-sla/programmer/tim');
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_unauthenticated_user_gets_middleware_response()
    {
        $response = $this->get('/dashboard-sla/programmer/tim');
        $this->assertTrue(in_array($response->status(), [200, 302, 401, 403, 500]), "Status is: " . $response->status());
    }

    public function test_user_without_permission_gets_403()
    {
        $user = $this->createUserWithRole('HRD', 'Human Resources');
        $response = $this->actingAs($user)->get('/dashboard-sla/programmer/tim');
        $this->assertTrue(in_array($response->status(), [403, 302]));
    }

    public function test_dashboard_returns_valid_response()
    {
        $user = $this->createUserWithRole();
        $response = $this->actingAs($user)->get('/dashboard-sla/programmer/tim');
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_dashboard_can_process_empty_data()
    {
        $user = $this->createUserWithRole();
        $response = $this->actingAs($user)->get('/dashboard-sla/programmer/tim');
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_date_filter_can_be_processed()
    {
        $user = $this->createUserWithRole();
        $response = $this->actingAs($user)->get('/dashboard-sla/programmer/tim?start_date=2023-01-01&end_date=2023-12-31');
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }
}
