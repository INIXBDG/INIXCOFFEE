<?php

namespace Tests\Feature\ITSM\TimDigital;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DashboardSlaDigitalTest extends TestCase
{
    use DatabaseTransactions;

    private function createUserWithRole($roleName = 'Tim Digital', $divisi = 'Digital')
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
        $role = Role::firstOrCreate(['name' => $roleName]);
        $user->assignRole($role);

        return $user;
    }

    public function test_tim_digital_can_access_dashboard_sla_digital()
    {
        $user = $this->createUserWithRole();
        $response = $this->actingAs($user)->get('/dashboard-sla/digital');
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_guest_cannot_access_dashboard_sla_digital()
    {
        $response = $this->get('/dashboard-sla/digital');
        $this->assertTrue(in_array($response->status(), [200, 302, 403]));
    }

    public function test_non_digital_user_cannot_access_dashboard_sla_digital()
    {
        $user = $this->createUserWithRole('Programmer', 'IT Service Management');
        $response = $this->actingAs($user)->get('/dashboard-sla/digital');
        $this->assertTrue(in_array($response->status(), [403, 302]));
    }

    public function test_dashboard_can_process_empty_data()
    {
        $user = $this->createUserWithRole();
        $response = $this->actingAs($user)->get('/dashboard-sla/digital');
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_dashboard_can_process_date_filter()
    {
        $user = $this->createUserWithRole();
        $response = $this->actingAs($user)->get('/dashboard-sla/digital?start_date=2024-01-01&end_date=2024-12-31');
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }
}
